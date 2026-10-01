<?php
/* ============================================================
   VenueVibe - config.php
   Shared Database Connection & Session Initializer
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Dhaka');

$DB_HOST = 'localhost';
$DB_NAME = 'venuevibe';
$DB_USER = 'root';
$DB_PASS = '';

$pdo = null;
$dbError = null;

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    $pdo = null;
    $dbError = $e->getMessage();
}
?>