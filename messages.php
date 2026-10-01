<?php
/* ============================================================
   VenueVibe - messages.php
   Full Messaging System Backend & Page Entry Point
   ============================================================ */

require_once 'config.php';

$currentUserId   = $_SESSION['user_id']    ?? 2;
$currentUserName = $_SESSION['full_name']  ?? 'Fahim Ahmed';
$currentUserRole = strtolower($_SESSION['role'] ?? 'student');
$currentUserEmail= $_SESSION['email']      ?? 'fahim@uiu.ac.bd';
$studentId       = $_SESSION['student_id'] ?? '011 221 045';
$department      = $_SESSION['department'] ?? 'Computer Science & Engineering';
$avatarLetter    = strtoupper(substr($currentUserName, 0, 1));
$isAdmin         = !empty($_SESSION['is_admin']) || ($currentUserRole === 'admin');
$adminId = 1;

$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

// API mode: Handle AJAX POST (Send message)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($contentType, 'application/json') !== false) {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $text = trim($input['text'] ?? '');

    if (empty($text)) {
        echo json_encode(["status" => "error", "message" => "Message text cannot be empty"]);
        exit;
    }

    if ($currentUserRole === 'student') {
        $receiverId = $adminId;
        $responseMsg = "Message sent to Admin.";
    } else {
        $receiverId = isset($input['receiver_id']) ? (int)$input['receiver_id'] : 2;
        $responseMsg = "Reply sent to student #{$receiverId}.";
    }

    $timeSent = date("h:i A");

    if ($pdo === null) {
        echo json_encode(["status" => "error", "message" => "Database offline."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, sender_name, text, time_sent, is_read) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->execute([$currentUserId, $receiverId, $currentUserName, $text, $timeSent]);
        $newId = $pdo->lastInsertId();

        echo json_encode([
            "status" => "success",
            "message" => $responseMsg,
            "data" => [
                "id" => $newId,
                "sender_id" => $currentUserId,
                "receiver_id" => $receiverId,
                "sender_name" => $currentUserName,
                "text" => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
                "time" => $timeSent,
                "read" => false
            ]
        ]);
    } catch (Throwable $e) {
        echo json_encode(["status" => "error", "message" => "Could not save message."]);
    }
    exit;
}

// API mode: Handle AJAX GET for messages
if (isset($_GET['api']) && $_GET['api'] === 'messages') {
    header('Content-Type: application/json');
    if ($pdo === null) {
        echo json_encode(["status" => "success", "messages" => []]);
        exit;
    }
    try {
        if ($currentUserRole === 'student') {
            $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
            $stmt->execute([$currentUserId, $adminId, $adminId, $currentUserId]);
            $msgs = $stmt->fetchAll();
        } else {
            $selectedStudent = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 2;
            $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
            $stmt->execute([$selectedStudent, $adminId, $adminId, $selectedStudent]);
            $msgs = $stmt->fetchAll();
        }
        echo json_encode(["status" => "success", "messages" => $msgs]);
    } catch (Throwable $e) {
        echo json_encode(["status" => "error", "messages" => []]);
    }
    exit;
}

// API mode: Handle AJAX GET for student contacts (for Admin)
if (isset($_GET['api']) && $_GET['api'] === 'contacts') {
    header('Content-Type: application/json');
    if ($pdo === null) {
        echo json_encode(["status" => "success", "contacts" => []]);
        exit;
    }
    try {
        $stmt = $pdo->query("SELECT id, full_name, email, role, student_id FROM users WHERE role = 'student' ORDER BY full_name ASC");
        $contacts = $stmt->fetchAll();
        echo json_encode(["status" => "success", "contacts" => $contacts]);
    } catch (Throwable $e) {
        echo json_encode(["status" => "error", "contacts" => []]);
    }
    exit;
}
?>
<script>
window.SERVER_SESSION = <?= json_encode([
    'userId'       => $currentUserId,
    'userName'     => $currentUserName,
    'userRole'     => $currentUserRole,
    'userEmail'    => $currentUserEmail,
    'studentId'    => $studentId,
    'department'   => $department,
    'avatarLetter' => $avatarLetter,
    'isAdmin'      => $isAdmin
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<?php
// Render the messages view
include 'messages.html';
exit;
?>
