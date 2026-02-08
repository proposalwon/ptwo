<?php
/**
 * Path: /ptwo/fix.php
 * RUN ONCE AND DELETE
 */
require_once 'src/db.php';

$email = 'admin@proposalwon.com';
$password = 'Rdabites1';
$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, email = ? WHERE id = 1");
    $stmt->execute([$hash, $email]);
    
    echo "Database updated successfully. New Hash: " . $hash;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}