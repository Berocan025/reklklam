<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Tıklama Takibi API
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Sadece POST isteklerine izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // JSON verisi al
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $type = sanitizeInput($input['type'] ?? '');
    $id = (int)($input['id'] ?? 0);
    $token = $input['_token'] ?? '';
    
    // CSRF token kontrolü (opsiyonel)
    if (!empty($token)) {
        session_start();
        if (!verifyCSRFToken($token)) {
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
    }
    
    // Gerekli parametreleri kontrol et
    if (empty($type) || $id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }
    
    // İzin verilen tipler
    $allowedTypes = ['bonus', 'site', 'banner'];
    if (!in_array($type, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid type']);
        exit;
    }
    
    // IP ve User Agent bilgilerini al
    $visitorIp = getUserIP();
    $userAgent = getUserAgent();
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $clickDate = date('Y-m-d');
    $clickTime = date('H:i:s');
    
    // Rate limiting - aynı IP'den çok hızlı tıklamalar için
    $rateLimitKey = "click_{$type}_{$id}_" . $visitorIp;
    if (!checkRateLimit($rateLimitKey, 10, 60)) { // Dakikada 10 tıklama
        echo json_encode(['success' => false, 'message' => 'Rate limit exceeded']);
        exit;
    }
    
    $db->beginTransaction();
    
    switch ($type) {
        case 'bonus':
            // Bonus'un var olduğunu kontrol et
            $bonus = $db->fetchOne("SELECT id FROM bonuses WHERE id = ? AND status = 1", [$id]);
            
            if (!$bonus) {
                echo json_encode(['success' => false, 'message' => 'Bonus not found']);
                exit;
            }
            
            // Bonus tıklama sayısını artır
            $db->query("UPDATE bonuses SET click_count = click_count + 1 WHERE id = ?", [$id]);
            
            // Detaylı tıklama kaydı (opsiyonel)
            $db->query(
                "INSERT INTO bonus_clicks (bonus_id, visitor_ip, user_agent, referer, click_date, click_time) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$id, $visitorIp, $userAgent, $referer, $clickDate, $clickTime]
            );
            
            break;
            
        case 'site':
            // Site'nin var olduğunu kontrol et
            $site = $db->fetchOne("SELECT id FROM sites WHERE id = ? AND status = 1", [$id]);
            
            if (!$site) {
                echo json_encode(['success' => false, 'message' => 'Site not found']);
                exit;
            }
            
            // Site tıklama sayısını artır
            $db->query("UPDATE sites SET click_count = click_count + 1 WHERE id = ?", [$id]);
            
            // Detaylı tıklama kaydı (opsiyonel)
            $db->query(
                "INSERT INTO site_clicks (site_id, visitor_ip, user_agent, referer, click_date, click_time) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$id, $visitorIp, $userAgent, $referer, $clickDate, $clickTime]
            );
            
            break;
            
        case 'banner':
            // Banner'ın var olduğunu kontrol et
            $banner = $db->fetchOne("SELECT id FROM banners WHERE id = ? AND status = 1", [$id]);
            
            if (!$banner) {
                echo json_encode(['success' => false, 'message' => 'Banner not found']);
                exit;
            }
            
            // Banner tıklama sayısını artır
            $db->query("UPDATE banners SET click_count = click_count + 1 WHERE id = ?", [$id]);
            
            // Banner tıklama kaydı
            $db->query(
                "INSERT INTO banner_clicks (banner_id, visitor_ip, user_agent, referer, click_date, click_time) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$id, $visitorIp, $userAgent, $referer, $clickDate, $clickTime]
            );
            
            break;
    }
    
    $db->commit();
    
    // Başarılı yanıt
    echo json_encode([
        'success' => true,
        'message' => 'Click tracked successfully',
        'data' => [
            'type' => $type,
            'id' => $id,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    $db->rollback();
    
    // Hata logu
    error_log("Click tracking error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while tracking click'
    ]);
}
?>