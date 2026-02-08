<?php
/**
 * Path: /ptwo/api/capture/search_lake.php
 */
require_once '../../src/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$tenantId = $_SESSION['tenant_id'];
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

try {

    // Run the Full-Text Search on the Lake
    // We search across Title, Agency, and Description
    $sql = "SELECT id, solicitation_number, title, agency_name, award_amount 
            FROM research_lake 
            WHERE MATCH(title, agency_name, description) AGAINST(? IN NATURAL LANGUAGE MODE)
            LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$query]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server Error']);
}