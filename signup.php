<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    include 'Signup.html';
    exit;
}

function back_with_error(string $msg): void {
    header('Location: signup.php?error=' . urlencode($msg));
    exit;
}

$fullName=trim($_POST['fullname'] ?? '');
$email=trim($_POST['email'] ?? '');
$role=$_POST['role'] ?? 'Student';
$password=$_POST['password'] ?? '';
$terms=$_POST['terms'] ?? null;

if($fullName===''||$email===''||$password===''){
    back_with_error('Please fill in every field.');
}
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    back_with_error('Please enter a valid email address.');
}
if(strlen($password)<8){
    back_with_error('Password must be at least 8 character.');
}
if($terms===null){
    back_with_error('You must accept the Terms of Service.');
}

$role=strtolower($role)==='faculty' ? 'faculty': 'student';


if ($pdo === null) {
    back_with_error('Database connection is offline. Please start Apache & MySQL in XAMPP.');
}

try {
    $check=$pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);
    if($check->fetch()){
        back_with_error('An account with that email already exists.');
    }

    $hash=password_hash($password,PASSWORD_DEFAULT);

    $insert=$pdo->prepare(
        'INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)'
    );
    $insert->execute([$fullName, $email, $hash, $role]);
} catch (Throwable $e) {
    back_with_error('Database error: ' . $e->getMessage());
}

header('Location: login.php?signup=success');
exit;
?>