<?php
/* ============================================================
   VenueVibe - notifications.php
   Notifications System Backend & Page Entry Point
   ============================================================ */

require_once 'config.php';

$userId = $_SESSION['user_id'] ?? 2;

// API mode: Get notifications as JSON
if (isset($_GET['api']) && $_GET['api'] === 'list') {
    header('Content-Type: application/json');
    if ($pdo === null) {
        echo json_encode(["status" => "success", "notifications" => []]);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $list = $stmt->fetchAll();
        echo json_encode(["status" => "success", "notifications" => $list]);
    } catch (Throwable $e) {
        echo json_encode(["status" => "error", "notifications" => []]);
    }
    exit;
}

// API mode: Mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    header('Content-Type: application/json');
    if ($pdo === null) {
        echo json_encode(["status" => "success"]);
        exit;
    }
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? OR user_id IS NULL");
        $stmt->execute([$userId]);
        echo json_encode(["status" => "success"]);
    } catch (Throwable $e) {
        echo json_encode(["status" => "error"]);
    }
    exit;
}

$userName     = $_SESSION['full_name']  ?? 'Fahim Ahmed';
$userRole     = $_SESSION['role']       ?? 'Student';
$userEmail    = $_SESSION['email']      ?? 'fahim@uiu.ac.bd';
$studentId    = $_SESSION['student_id'] ?? '011 221 045';
$department   = $_SESSION['department'] ?? 'Computer Science & Engineering';
$avatarLetter = strtoupper(substr($userName, 0, 1));
?>
<script>
window.USER_SESSION = <?= json_encode([
    'userId'       => $userId,
    'userName'     => $userName,
    'userRole'     => ucfirst($userRole),
    'userEmail'    => $userEmail,
    'studentId'    => $studentId,
    'department'   => $department,
    'avatarLetter' => $avatarLetter
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<?php
// Render the notifications view
include 'notifications.html';
exit;
?>
