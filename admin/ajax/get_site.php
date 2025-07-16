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
    echo json_encode(['success' => false, 'error' => 'Invalid site ID']);
    exit;
}

$site_id = (int)$_GET['id'];

try {
    // Get site data
    $stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ?");
    $stmt->execute([$site_id]);
    $site = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$site) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Site not found']);
        exit;
    }
    
    // Return data
    echo json_encode([
        'success' => true,
        'site' => $site
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>