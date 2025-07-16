<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Security Functions
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

/**
 * Input sanitization
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
        return $input;
    }
    
    // Remove null bytes
    $input = str_replace("\0", '', $input);
    
    // Trim whitespace
    $input = trim($input);
    
    // Convert special characters
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    return $input;
}

/**
 * Clean string for database
 */
function cleanString($string) {
    return strip_tags(trim($string));
}

/**
 * Sanitize HTML content
 */
function sanitizeHtml($html) {
    // Allowed tags for rich content
    $allowed_tags = '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6>';
    
    return strip_tags($html, $allowed_tags);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Rate limiting
 */
function checkRateLimit($key, $max_attempts = 5, $time_window = 300) {
    global $pdo;
    
    try {
        // Clean old attempts
        $pdo->prepare("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)")
            ->execute([$time_window]);
        
        // Count current attempts
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE identifier = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$key, $time_window]);
        $attempts = $stmt->fetchColumn();
        
        if ($attempts >= $max_attempts) {
            return false;
        }
        
        // Log this attempt
        $stmt = $pdo->prepare("INSERT INTO rate_limits (identifier, ip_address, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$key, $_SERVER['REMOTE_ADDR'] ?? '']);
        
        return true;
        
    } catch (Exception $e) {
        // If rate limiting fails, allow the request
        return true;
    }
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate phone number (Turkish format)
 */
function isValidPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^(90|0)?5[0-9]{9}$/', $phone);
}

/**
 * XSS Protection
 */
function preventXSS($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = preventXSS($value);
        }
        return $data;
    }
    
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * SQL Injection protection (additional layer)
 */
function escapeSQLWildcards($string) {
    return str_replace(['%', '_'], ['\%', '\_'], $string);
}

/**
 * File upload security check
 */
function isValidUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return false;
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_types)) {
        return false;
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg', 
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    if (!isset($allowed_mimes[$extension]) || $mime_type !== $allowed_mimes[$extension]) {
        return false;
    }
    
    return true;
}

/**
 * Secure redirect
 */
function secureRedirect($url) {
    // Only allow relative URLs or same domain
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $parsed = parse_url($url);
        $current_host = $_SERVER['HTTP_HOST'];
        
        if ($parsed['host'] !== $current_host) {
            $url = '/';
        }
    }
    
    header('Location: ' . $url);
    exit;
}

/**
 * Clean filename for uploads
 */
function cleanFilename($filename) {
    // Remove special characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    // Remove multiple dots
    $filename = preg_replace('/\.+/', '.', $filename);
    
    // Limit length
    if (strlen($filename) > 100) {
        $filename = substr($filename, 0, 100);
    }
    
    return $filename;
}

/**
 * IP Whitelist check
 */
function isIPWhitelisted($ip = null) {
    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    // Admin IP whitelist (configure as needed)
    $whitelist = [
        '127.0.0.1',
        '::1'
    ];
    
    return in_array($ip, $whitelist);
}

/**
 * Brute force protection
 */
function checkBruteForce($identifier, $max_attempts = 5, $lockout_time = 1800) {
    global $pdo;
    
    try {
        // Check failed attempts
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM login_attempts 
            WHERE identifier = ? 
            AND success = 0 
            AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$identifier, $lockout_time]);
        $failed_attempts = $stmt->fetchColumn();
        
        return $failed_attempts < $max_attempts;
        
    } catch (Exception $e) {
        return true;
    }
}

/**
 * Log login attempt
 */
function logLoginAttempt($identifier, $success = false, $ip = null) {
    global $pdo;
    
    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (identifier, ip_address, success, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$identifier, $ip, $success ? 1 : 0]);
    } catch (Exception $e) {
        // Silent fail
    }
}
?>