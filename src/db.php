<?php
/**
 * Path: /ptwo/src/db.php
 */

// Production Session Security
ini_set('session.cookie_httponly', 1); // Prevents JS from reading session ID
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
$host = 'localhost';
$db   = 'u390413620_ptwo';
$user = 'u390413620_ptwo';
$pass = 'G@mmons2020';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Critical for real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log("DB Connection Error: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    exit("Database Error.");
}

/**
 * Helper: Validates if user is logged in for API calls
 */
function protect() {
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}