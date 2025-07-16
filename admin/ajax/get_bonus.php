<?php
require_once '../includes/admin_auth.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid bonus ID']);
    exit;
}

$bonus_id = (int)$_GET['id'];

try {
    // Get bonus data
    $stmt = $pdo->prepare("SELECT * FROM bonuses WHERE id = ?");
    $stmt->execute([$bonus_id]);
    $bonus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bonus) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Bonus not found']);
        exit;
    }
    
    // Get sites for dropdown
    $sites_stmt = $pdo->query("SELECT id, name FROM sites WHERE is_active = 1 ORDER BY name");
    $sites = $sites_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for dropdown
    $categories_stmt = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return data
    echo json_encode([
        'success' => true,
        'bonus' => $bonus,
        'sites' => $sites,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>