<?php
/**
 * Path: /ptwo/api/pipeline/get_opportunities.php
 */
require_once '../../src/db.php';
header('Content-Type: application/json');

$tenantId = $_SESSION['tenant_id'] ?? null;
if (!$tenantId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // JOIN ensures we get Title and Agency from the Lake
    $sql = "SELECT t.id, t.status, l.solicitation_number, l.title, l.agency_name 
            FROM tenant_opportunities t
            JOIN research_lake l ON t.lake_ref_id = l.id
            WHERE t.tenant_id = ? 
            ORDER BY t.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tenantId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $results]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}