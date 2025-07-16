<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Ortak Fonksiyonlar
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

require_once dirname(__FILE__) . '/../config/database.php';

/**
 * Site ayarlarını getir
 */
function getSetting($key, $default = '') {
    global $db;
    
    static $settings = [];
    
    if (empty($settings)) {
        $settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM settings WHERE 1");
        foreach ($settingsData as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
    }
    
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Site ayarını güncelle
 */
function updateSetting($key, $value) {
    global $db;
    
    return $db->query(
        "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        [$key, $value]
    );
}

/**
 * URL slug oluştur
 */
function createSlug($text) {
    $text = strtolower($text);
    $text = str_replace(['ç', 'ğ', 'ı', 'i', 'ö', 'ş', 'ü'], ['c', 'g', 'i', 'i', 'o', 's', 'u'], $text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', trim($text));
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * SEO dostu URL oluştur
 */
function seoUrl($url) {
    return SITE_URL . '/' . ltrim($url, '/');
}

/**
 * Admin URL oluştur
 */
function adminUrl($url = '') {
    return ADMIN_URL . '/' . ltrim($url, '/');
}

/**
 * Asset URL oluştur
 */
function assetUrl($url) {
    return ASSETS_URL . '/' . ltrim($url, '/');
}

/**
 * Upload URL oluştur
 */
function uploadUrl($url) {
    return UPLOADS_URL . '/' . ltrim($url, '/');
}

/**
 * Metin kısalt
 */
function truncateText($text, $length = 100, $ending = '...') {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . $ending;
    }
    return $text;
}

/**
 * HTML temizle
 */
function cleanHtml($text) {
    return htmlspecialchars(strip_tags($text), ENT_QUOTES, 'UTF-8');
}

/**
 * Tarih formatla
 */
function formatDate($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

/**
 * Türkçe tarih formatla
 */
function formatDateTurkish($date) {
    $months = [
        '01' => 'Ocak', '02' => 'Şubat', '03' => 'Mart', '04' => 'Nisan',
        '05' => 'Mayıs', '06' => 'Haziran', '07' => 'Temmuz', '08' => 'Ağustos',
        '09' => 'Eylül', '10' => 'Ekim', '11' => 'Kasım', '12' => 'Aralık'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[date('m', $timestamp)];
    $year = date('Y', $timestamp);
    
    return $day . ' ' . $month . ' ' . $year;
}

/**
 * Para formatı
 */
function formatMoney($amount, $currency = 'TL') {
    return number_format($amount, 0, ',', '.') . ' ' . $currency;
}

/**
 * Sayfa başlığı oluştur
 */
function getPageTitle($title = '') {
    $siteTitle = getSetting('site_title', 'BonusBoss');
    
    if (empty($title)) {
        return $siteTitle;
    }
    
    return $title . ' - ' . $siteTitle;
}

/**
 * Meta description oluştur
 */
function getMetaDescription($description = '') {
    if (empty($description)) {
        return getSetting('site_description', '');
    }
    
    return truncateText(strip_tags($description), 160);
}

/**
 * Breadcrumb oluştur
 */
function createBreadcrumb($items) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach ($items as $key => $item) {
        if ($key === count($items) - 1) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }
    
    $html .= '</ol></nav>';
    return $html;
}

/**
 * Rastgele token oluştur
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * CSRF token oluştur
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrula
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Form field oluştur
 */
function formField($name, $type = 'text', $value = '', $attributes = []) {
    $attrs = '';
    foreach ($attributes as $key => $val) {
        $attrs .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
    }
    
    switch ($type) {
        case 'textarea':
            return '<textarea name="' . $name . '" id="' . $name . '"' . $attrs . '>' . htmlspecialchars($value) . '</textarea>';
        case 'select':
            $options = isset($attributes['options']) ? $attributes['options'] : [];
            $html = '<select name="' . $name . '" id="' . $name . '"' . $attrs . '>';
            foreach ($options as $optValue => $optText) {
                $selected = ($optValue == $value) ? ' selected' : '';
                $html .= '<option value="' . htmlspecialchars($optValue) . '"' . $selected . '>' . htmlspecialchars($optText) . '</option>';
            }
            $html .= '</select>';
            return $html;
        default:
            return '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" value="' . htmlspecialchars($value) . '"' . $attrs . '>';
    }
}

/**
 * Dosya boyutunu formatla
 */
function formatFileSize($size) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $unit = 0;
    
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    
    return round($size, 2) . ' ' . $units[$unit];
}

/**
 * IP adresini al
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * User agent bilgisini al
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Cihaz tipini belirle
 */
function getDeviceType() {
    $userAgent = getUserAgent();
    
    if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
        if (preg_match('/iPad/', $userAgent)) {
            return 'tablet';
        }
        return 'mobile';
    }
    
    return 'desktop';
}

/**
 * Log kaydı oluştur
 */
function createLog($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    global $db;
    
    $adminId = $_SESSION['admin_id'] ?? null;
    $ipAddress = getUserIP();
    $userAgent = getUserAgent();
    
    return $db->query(
        "INSERT INTO logs (admin_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $adminId,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ipAddress,
            $userAgent
        ]
    );
}

/**
 * Analitik kayıt oluştur
 */
function trackPageView() {
    global $db;
    
    if (getSetting('analytics_enabled', '1') == '1') {
        $pageUrl = $_SERVER['REQUEST_URI'];
        $visitorIp = getUserIP();
        $userAgent = getUserAgent();
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $sessionId = session_id();
        $visitDate = date('Y-m-d');
        $visitTime = date('H:i:s');
        $deviceType = getDeviceType();
        
        $db->query(
            "INSERT INTO analytics (page_url, visitor_ip, user_agent, referer, session_id, visit_date, visit_time, device_type) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$pageUrl, $visitorIp, $userAgent, $referer, $sessionId, $visitDate, $visitTime, $deviceType]
        );
    }
}

/**
 * Email gönder
 */
function sendEmail($to, $subject, $message, $headers = []) {
    $defaultHeaders = [
        'From' => getSetting('contact_email', 'noreply@bonusboss.com'),
        'Content-Type' => 'text/html; charset=UTF-8'
    ];
    
    $headers = array_merge($defaultHeaders, $headers);
    $headerString = '';
    
    foreach ($headers as $key => $value) {
        $headerString .= $key . ': ' . $value . "\r\n";
    }
    
    return mail($to, $subject, $message, $headerString);
}

/**
 * Popup banner kontrol
 */
function shouldShowPopup() {
    if (getSetting('popup_enabled', '1') != '1') {
        return false;
    }
    
    // Popup gösterildi mi kontrolü (cookie ile)
    if (isset($_COOKIE['popup_shown'])) {
        return false;
    }
    
    return true;
}

/**
 * Banner göster
 */
function showBanner($position) {
    global $db;
    
    $banners = $db->fetchAll(
        "SELECT * FROM banners 
         WHERE position = ? AND status = 1 
         AND (start_date IS NULL OR start_date <= NOW()) 
         AND (end_date IS NULL OR end_date >= NOW()) 
         ORDER BY sort_order ASC",
        [$position]
    );
    
    foreach ($banners as $banner) {
        echo renderBanner($banner);
        
        // İmpression sayısını artır
        $db->query("UPDATE banners SET impression_count = impression_count + 1 WHERE id = ?", [$banner['id']]);
    }
}

/**
 * Banner render et
 */
function renderBanner($banner) {
    $html = '<div class="banner-container" data-banner-id="' . $banner['id'] . '">';
    
    if (!empty($banner['link_url'])) {
        $target = $banner['target_blank'] ? ' target="_blank"' : '';
        $html .= '<a href="' . htmlspecialchars($banner['link_url']) . '" class="banner-link"' . $target . '>';
    }
    
    if (!empty($banner['video_url'])) {
        $html .= '<video autoplay muted loop>';
        $html .= '<source src="' . htmlspecialchars($banner['video_url']) . '" type="video/mp4">';
        $html .= '</video>';
    } elseif (!empty($banner['gif_url'])) {
        $html .= '<img src="' . htmlspecialchars($banner['gif_url']) . '" alt="' . htmlspecialchars($banner['alt_text']) . '" class="img-fluid">';
    } elseif (!empty($banner['image'])) {
        $html .= '<img src="' . uploadUrl($banner['image']) . '" alt="' . htmlspecialchars($banner['alt_text']) . '" class="img-fluid">';
    }
    
    if (!empty($banner['link_url'])) {
        $html .= '</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Cache kontrol et
 */
function getCache($key) {
    if (!CACHE_ENABLED) {
        return false;
    }
    
    $cacheFile = ROOT_PATH . '/cache/' . md5($key) . '.cache';
    
    if (file_exists($cacheFile)) {
        $cacheData = unserialize(file_get_contents($cacheFile));
        
        if ($cacheData['expire'] > time()) {
            return $cacheData['data'];
        } else {
            unlink($cacheFile);
        }
    }
    
    return false;
}

/**
 * Cache kaydet
 */
function setCache($key, $data, $lifetime = CACHE_LIFETIME) {
    if (!CACHE_ENABLED) {
        return false;
    }
    
    $cacheDir = ROOT_PATH . '/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
    $cacheData = [
        'data' => $data,
        'expire' => time() + $lifetime
    ];
    
    return file_put_contents($cacheFile, serialize($cacheData));
}

/**
 * Cache temizle
 */
function clearCache($key = null) {
    $cacheDir = ROOT_PATH . '/cache';
    
    if ($key) {
        $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    } else {
        $files = glob($cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
?>