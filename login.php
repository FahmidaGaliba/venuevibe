<?php
require_once 'config.php';

// Direct GET request opens Login.html view
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include 'Login.html';
    exit;
}

header('Content-Type: application/json');

function respond(bool $success, string $message = '', string $redirect = ''): void {
    echo json_encode(compact('success', 'message', 'redirect'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$loginAs = ($_POST['login_type'] ?? 'user') === 'admin' ? 'admin' : 'user';

if ($email === '' || $password === '') {
    respond(false, 'Please enter both email and password.');
}

if ($pdo === null) {
    // If DB is offline, provide graceful demo fallback so users can test
    if ($email === 'student@uiu.ac.bd' && $password === 'password123') {
        session_regenerate_id(true);
        $_SESSION['user_id'] = 2;
        $_SESSION['full_name'] = 'Fahim Ahmed';
        $_SESSION['email'] = 'student@uiu.ac.bd';
        $_SESSION['role'] = 'student';
        $_SESSION['is_admin'] = false;
        $_SESSION['student_id'] = '011 221 045';
        $_SESSION['department'] = 'Computer Science & Engineering';
        $_SESSION['phone'] = '+880 1712-345678';
        respond(true, 'Login successful (Demo Mode).', 'user.php');
    } elseif ($email === 'admin@uiu.ac.bd' && $password === 'password123') {
        session_regenerate_id(true);
        $_SESSION['user_id'] = 1;
        $_SESSION['full_name'] = 'Campus Admin';
        $_SESSION['email'] = 'admin@uiu.ac.bd';
        $_SESSION['role'] = 'admin';
        $_SESSION['is_admin'] = true;
        $_SESSION['student_id'] = 'ADM-001';
        $_SESSION['department'] = 'Campus Facilities Administration';
        $_SESSION['phone'] = '+880 1700-000000';
        respond(true, 'Admin Login successful (Demo Mode).', 'user.php');
    }
    respond(false, 'Database connection is offline. Please start Apache & MySQL in XAMPP (or use demo: student@uiu.ac.bd / password123).');
}

try {
    $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role, is_admin, student_id, department, phone FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
} catch (Throwable $e) {
    respond(false, 'Database query error. Please ensure database tables are imported.');
}

if (!$user || !password_verify($password, $user['password_hash'])) {
    respond(false, 'Incorrect email or password.');
}

if ($loginAs === 'admin' && !$user['is_admin']) {
    respond(false, 'This account does not have admin access.');
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['is_admin'] = (bool)$user['is_admin'];
$_SESSION['student_id'] = $user['student_id'] ?? '011 221 045';
$_SESSION['department'] = $user['department'] ?? 'Computer Science & Engineering';
$_SESSION['phone'] = $user['phone'] ?? '+880 1712-345678';

// Redirect to user.php (or requests.php if admin prefers)
$redirect = 'user.php';

respond(true, 'Login successful.', $redirect);
?>