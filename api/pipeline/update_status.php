<?php
/**
 * Path: /ptwo/api/pipeline/update_status.php
 */
require_once '../../src/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$tenantId = $_SESSION['tenant_id'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Validation
$allowed = ['Qualified', 'Proposal', 'Submitted', 'Won', 'Lost'];
if (!in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Status']);
    exit;
}

try {
    // Security: Ensure the opportunity belongs to the user's tenant
    $stmt = $pdo->prepare("UPDATE tenant_opportunities SET status = ? WHERE id = ? AND tenant_id = ?");
    $stmt->execute([$status, $id, $tenantId]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Update failed.']);
}