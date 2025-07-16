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
    echo json_encode(['success' => false, 'error' => 'Invalid banner ID']);
    exit;
}

$banner_id = (int)$_GET['id'];

try {
    // Get banner data
    $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([$banner_id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$banner) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Banner not found']);
        exit;
    }
    
    // Return data
    echo json_encode([
        'success' => true,
        'banner' => $banner
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>