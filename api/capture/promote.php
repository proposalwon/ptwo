<?php
/**
 * Path: /ptwo/api/capture/promote.php
 */
require_once '../../src/db.php';
header('Content-Type: application/json');

// 1. Auth Guard
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$tenantId = $_SESSION['tenant_id'];
// Using POST because we are modifying data
$lakeId = isset($_POST['lake_id']) ? (int)$_POST['lake_id'] : 0;

if ($lakeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Record ID']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 2. Check Promotion Limits
    $stmt = $pdo->prepare("SELECT current_month_promotions, plan_promotion_limit FROM tenants WHERE id = ? FOR UPDATE");
    $stmt->execute([$tenantId]);
    $tenant = $stmt->fetch();

    if ($tenant['current_month_promotions'] >= $tenant['plan_promotion_limit']) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Monthly promotion limit reached. Upgrade for more.']);
        exit;
    }

    // 3. Fetch data from the Lake
    $stmt = $pdo->prepare("SELECT * FROM research_lake WHERE id = ?");
    $stmt->execute([$lakeId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Master record not found.']);
        exit;
    }

    // 4. Copy to Tenant Bucket
    // Note: We use 'Qualified' as the default status for a new pipeline item
    $sql = "INSERT INTO tenant_opportunities 
            (tenant_id, lake_ref_id, solicitation_number, title, agency_name, description, award_amount, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Qualified')";
    
    $pdo->prepare($sql)->execute([
        $tenantId, 
        $record['id'], 
        $record['solicitation_number'], 
        $record['title'], 
        $record['agency_name'], 
        $record['description'], 
        $record['award_amount']
    ]);

    // 5. Increment the Usage Counter
    $pdo->prepare("UPDATE tenants SET current_month_promotions = current_month_promotions + 1 WHERE id = ?")
        ->execute([$tenantId]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Successfully added to your pipeline!']);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server Error during promotion.']);
    }
