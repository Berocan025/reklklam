<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Güvenlik Fonksiyonları
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

/**
 * SQL injection koruması
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
        return $input;
    }
    
    // HTML tag'lerini temizle
    $input = strip_tags($input);
    
    // Özel karakterleri encode et
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    // Fazla boşlukları temizle
    $input = trim($input);
    
    return $input;
}

/**
 * XSS koruması
 */
function preventXSS($input) {
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    // JavaScript event handler'ları kaldır
    $input = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $input);
    
    // JavaScript protokolünü kaldır
    $input = preg_replace('/javascript:/i', '', $input);
    
    // Data URI'leri kaldır
    $input = preg_replace('/data:/i', '', $input);
    
    return $input;
}

/**
 * CSRF token doğrulama
 */
function requireCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        
        if (!verifyCSRFToken($token)) {
            http_response_code(403);
            die('CSRF token doğrulaması başarısız!');
        }
    }
}

/**
 * Admin oturum kontrolü
 */
function requireAdmin($redirectUrl = 'login.php') {
    session_start();
    
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in'])) {
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    // Oturum süresini kontrol et
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
        
        destroyAdminSession();
        header('Location: ' . $redirectUrl . '?timeout=1');
        exit;
    }
    
    $_SESSION['last_activity'] = time();
    
    return getAdminUser($_SESSION['admin_id']);
}

/**
 * Admin kullanıcı bilgilerini getir
 */
function getAdminUser($adminId) {
    global $db;
    
    $admin = $db->fetchOne(
        "SELECT id, username, email, role, status FROM admins WHERE id = ? AND status = 1",
        [$adminId]
    );
    
    if (!$admin) {
        destroyAdminSession();
        header('Location: login.php');
        exit;
    }
    
    return $admin;
}

/**
 * Admin yetki kontrolü
 */
function hasPermission($permission, $admin = null) {
    if (!$admin) {
        $admin = $_SESSION['admin_data'] ?? null;
    }
    
    if (!$admin) {
        return false;
    }
    
    // Süper admin her şeyi yapabilir
    if ($admin['role'] === 'super_admin') {
        return true;
    }
    
    // Yetki matrisi
    $permissions = [
        'admin' => [
            'view_dashboard', 'manage_content', 'manage_banners', 
            'manage_bonuses', 'manage_sites', 'view_analytics'
        ],
        'editor' => [
            'view_dashboard', 'manage_content', 'manage_bonuses', 'manage_sites'
        ],
        'moderator' => [
            'view_dashboard', 'manage_content'
        ]
    ];
    
    $userPermissions = $permissions[$admin['role']] ?? [];
    
    return in_array($permission, $userPermissions);
}

/**
 * Admin oturumunu başlat
 */
function createAdminSession($admin) {
    session_start();
    session_regenerate_id(true);
    
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_data'] = $admin;
    $_SESSION['last_activity'] = time();
    $_SESSION['login_time'] = time();
    
    // Giriş kaydını güncelle
    global $db;
    $db->query(
        "UPDATE admins SET last_login = NOW(), login_ip = ? WHERE id = ?",
        [getUserIP(), $admin['id']]
    );
    
    // Log kaydı oluştur
    createLog('Admin Login', 'admins', $admin['id']);
}

/**
 * Admin oturumunu sonlandır
 */
function destroyAdminSession() {
    session_start();
    
    // Log kaydı oluştur
    if (isset($_SESSION['admin_id'])) {
        createLog('Admin Logout', 'admins', $_SESSION['admin_id']);
    }
    
    session_unset();
    session_destroy();
    
    // Session cookie'sini sil
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

/**
 * Şifre hash'le
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Şifre doğrula
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Güçlü şifre kontrolü
 */
function isStrongPassword($password) {
    // En az 8 karakter
    if (strlen($password) < 8) {
        return false;
    }
    
    // En az bir büyük harf
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // En az bir küçük harf
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // En az bir rakam
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // En az bir özel karakter
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return false;
    }
    
    return true;
}

/**
 * Dosya yükleme güvenliği
 */
function validateUpload($file) {
    $errors = [];
    
    // Dosya yüklendi mi kontrol et
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Dosya yükleme hatası: ' . $file['error'];
        return $errors;
    }
    
    // Dosya boyutu kontrolü
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'Dosya boyutu çok büyük. Maksimum: ' . formatFileSize(MAX_FILE_SIZE);
    }
    
    // Dosya uzantısı kontrolü
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_VIDEO_TYPES);
    
    if (!in_array($fileExtension, $allowedTypes)) {
        $errors[] = 'Geçersiz dosya türü. İzin verilen türler: ' . implode(', ', $allowedTypes);
    }
    
    // MIME type kontrolü
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg'
    ];
    
    if (isset($allowedMimes[$fileExtension])) {
        $expectedMime = $allowedMimes[$fileExtension];
        $actualMime = mime_content_type($file['tmp_name']);
        
        if ($actualMime !== $expectedMime) {
            $errors[] = 'Dosya içeriği uzantısıyla uyuşmuyor.';
        }
    }
    
    // Görüntü dosyası ise boyut kontrolü
    if (in_array($fileExtension, ALLOWED_IMAGE_TYPES)) {
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'Geçersiz görüntü dosyası.';
        }
    }
    
    return $errors;
}

/**
 * Güvenli dosya adı oluştur
 */
function generateSecureFilename($originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $timestamp = time();
    $randomString = bin2hex(random_bytes(8));
    
    return $timestamp . '_' . $randomString . '.' . $extension;
}

/**
 * Rate limiting
 */
function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300) {
    $cacheKey = 'rate_limit_' . $key;
    $attempts = getCache($cacheKey);
    
    if ($attempts === false) {
        $attempts = 0;
    }
    
    if ($attempts >= $maxAttempts) {
        return false;
    }
    
    setCache($cacheKey, $attempts + 1, $timeWindow);
    return true;
}

/**
 * IP adresi engelleme kontrolü
 */
function isIPBlocked($ip = null) {
    if (!$ip) {
        $ip = getUserIP();
    }
    
    // Basit IP engelleme listesi (gerçek uygulamada veritabanında tutulabilir)
    $blockedIPs = [
        // Örnek bloklu IP'ler
    ];
    
    return in_array($ip, $blockedIPs);
}

/**
 * User agent doğrulama
 */
function isValidUserAgent($userAgent = null) {
    if (!$userAgent) {
        $userAgent = getUserAgent();
    }
    
    // Şüpheli user agent'ları filtrele
    $suspiciousAgents = [
        'curl',
        'wget',
        'bot',
        'crawler',
        'spider'
    ];
    
    $userAgentLower = strtolower($userAgent);
    
    foreach ($suspiciousAgents as $suspicious) {
        if (strpos($userAgentLower, $suspicious) !== false) {
            return false;
        }
    }
    
    return true;
}

/**
 * Honeypot alanı kontrolü
 */
function checkHoneypot($honeypotField = 'website') {
    return !empty($_POST[$honeypotField]);
}

/**
 * SQL injection tespit etme
 */
function detectSQLInjection($input) {
    $sqlPatterns = [
        '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b)/i',
        '/(\bOR\b|\bAND\b)\s+\d+\s*=\s*\d+/i',
        '/[\'"]\s*(OR|AND)\s+[\'"]/i',
        '/[\'"]\s*;\s*(DROP|DELETE|INSERT|UPDATE)/i'
    ];
    
    foreach ($sqlPatterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

/**
 * XSS tespit etme
 */
function detectXSS($input) {
    $xssPatterns = [
        '/<script[^>]*>.*?<\/script>/i',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<iframe[^>]*>/i',
        '/<object[^>]*>/i',
        '/<embed[^>]*>/i'
    ];
    
    foreach ($xssPatterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Güvenlik logları
 */
function logSecurityEvent($event, $details = []) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => getUserIP(),
        'user_agent' => getUserAgent(),
        'event' => $event,
        'details' => $details
    ];
    
    $logFile = ROOT_PATH . '/logs/security_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Güvenlik middleware
 */
function securityMiddleware() {
    // IP engelleme kontrolü
    if (isIPBlocked()) {
        http_response_code(403);
        die('IP adresiniz engellenmiştir.');
    }
    
    // Rate limiting kontrolü
    $rateKey = getUserIP() . '_' . $_SERVER['REQUEST_URI'];
    if (!checkRateLimit($rateKey, 100, 60)) { // Dakikada 100 istek
        http_response_code(429);
        die('Çok fazla istek gönderdiniz. Lütfen bekleyin.');
    }
    
    // SQL injection kontrolü
    foreach ($_REQUEST as $key => $value) {
        if (is_string($value) && detectSQLInjection($value)) {
            logSecurityEvent('SQL Injection Attempt', ['field' => $key, 'value' => $value]);
            http_response_code(403);
            die('Güvenlik ihlali tespit edildi.');
        }
    }
    
    // XSS kontrolü
    foreach ($_REQUEST as $key => $value) {
        if (is_string($value) && detectXSS($value)) {
            logSecurityEvent('XSS Attempt', ['field' => $key, 'value' => $value]);
            http_response_code(403);
            die('Güvenlik ihlali tespit edildi.');
        }
    }
}

/**
 * Güvenli yönlendirme
 */
function safeRedirect($url, $allowedDomains = []) {
    // Varsayılan olarak aynı domain'e izin ver
    $allowedDomains[] = $_SERVER['HTTP_HOST'];
    
    $parsedUrl = parse_url($url);
    
    // Relative URL ise güvenli
    if (!isset($parsedUrl['host'])) {
        header('Location: ' . $url);
        exit;
    }
    
    // İzin verilen domain'ler arasında mı?
    if (in_array($parsedUrl['host'], $allowedDomains)) {
        header('Location: ' . $url);
        exit;
    }
    
    // Güvenli değilse ana sayfaya yönlendir
    header('Location: /');
    exit;
}

// Her sayfada güvenlik kontrolü çalıştır
securityMiddleware();
?>