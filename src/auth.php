<?php
/**
 * Path: /ptwo/src/auth.php
 */
require_once 'db.php';

// Ensure no previous output (like warnings) ruins the JSON response
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $input['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials format.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.tenant_id, u.is_platform_admin, u.email, u.password_hash, 
               u.first_name, u.last_name, u.timezone,
               t.company_name, t.plan_level 
        FROM users u
        JOIN tenants t ON u.tenant_id = t.id
        WHERE u.email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        
        // Regenerate ID to prevent session fixation attacks
        session_regenerate_id(true);

        // Fetch Permissions
        $permStmt = $pdo->prepare("SELECT module_slug, access_level FROM user_permissions WHERE user_id = ?");
        $permStmt->execute([$user['id']]);
        $permissions = $permStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Populate Session
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['tenant_id']  = $user['tenant_id'];
        $_SESSION['is_platform_admin'] = (bool)$user['is_platform_admin'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name']  = $user['last_name'];
        $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['company']    = $user['company_name'];
        $_SESSION['plan']       = $user['plan_level'];
        $_SESSION['timezone']   = $user['timezone'];
        $_SESSION['perms']      = $permissions;

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    }
} catch (Exception $e) {
    error_log("Auth Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal Server Error.']);
}