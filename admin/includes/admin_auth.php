<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Admin Authentication System
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Database ve security dahil et
require_once dirname(__FILE__) . '/../../includes/database.php';
require_once dirname(__FILE__) . '/../../includes/functions.php';
require_once dirname(__FILE__) . '/../../includes/security.php';

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Admin girişi gerekli fonksiyonu
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Admin giriş kontrolü
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Mevcut admin bilgilerini getir
 */
function getCurrentAdmin() {
    global $db;
    
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return $db->fetchOne(
        "SELECT * FROM admins WHERE id = ? AND status = 1",
        [$_SESSION['admin_id']]
    );
}

/**
 * Admin giriş fonksiyonu
 */
function adminLogin($username, $password) {
    global $db;
    
    try {
        // Admin bilgilerini getir
        $admin = $db->fetchOne(
            "SELECT * FROM admins WHERE username = ? AND status = 1",
            [$username]
        );
        
        if (!$admin) {
            return ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
        }
        
        // Şifre kontrolü
        if (!password_verify($password, $admin['password'])) {
            // Başarısız giriş denemesini logla
            $db->insert('logs', [
                'action' => 'failed_login',
                'data' => json_encode(['username' => $username]),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return ['success' => false, 'message' => 'Hatalı şifre.'];
        }
        
        // Session ayarla
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_login_time'] = time();
        
        // Son giriş tarihini güncelle
        $db->update('admins', 
            ['last_login' => date('Y-m-d H:i:s')],
            ['id' => $admin['id']]
        );
        
        // Başarılı girişi logla
        $db->insert('logs', [
            'admin_id' => $admin['id'],
            'action' => 'login',
            'data' => json_encode(['username' => $username]),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return ['success' => true, 'message' => 'Giriş başarılı.'];
        
    } catch (Exception $e) {
        error_log("Admin login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
    }
}

/**
 * Admin çıkış fonksiyonu
 */
function adminLogout() {
    global $db;
    
    if (isAdminLoggedIn()) {
        // Çıkışı logla
        $db->insert('logs', [
            'admin_id' => $_SESSION['admin_id'],
            'action' => 'logout',
            'data' => json_encode(['username' => $_SESSION['admin_username']]),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    // Session temizle
    session_destroy();
    
    // Login sayfasına yönlendir
    header('Location: login.php');
    exit;
}

/**
 * Admin yetkisi kontrolü
 */
function hasAdminPermission($permission) {
    if (!isAdminLoggedIn()) {
        return false;
    }
    
    $role = $_SESSION['admin_role'] ?? '';
    
    // Super admin her şeyi yapabilir
    if ($role === 'super_admin') {
        return true;
    }
    
    // Yetki kontrolü
    $permissions = [
        'admin' => [
            'view_dashboard',
            'manage_sites',
            'manage_bonuses',
            'manage_banners',
            'manage_media',
            'view_analytics'
        ],
        'editor' => [
            'view_dashboard',
            'manage_sites',
            'manage_bonuses',
            'manage_media'
        ],
        'viewer' => [
            'view_dashboard',
            'view_analytics'
        ]
    ];
    
    return in_array($permission, $permissions[$role] ?? []);
}

/**
 * CSRF token kontrolü
 */
function validateAdminCSRF($token) {
    return validateCSRFToken($token);
}

/**
 * Admin session timeout kontrolü
 */
function checkAdminSessionTimeout() {
    $timeout = 3600; // 1 saat
    
    if (isAdminLoggedIn()) {
        $loginTime = $_SESSION['admin_login_time'] ?? 0;
        
        if (time() - $loginTime > $timeout) {
            adminLogout();
        }
        
        // Session süresini yenile
        $_SESSION['admin_login_time'] = time();
    }
}

/**
 * Remember Me token oluştur
 */
function createRememberToken($adminId) {
    global $db;
    
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $db->update('admins',
        ['remember_token' => $token, 'remember_expires' => $expires],
        ['id' => $adminId]
    );
    
    // Cookie ayarla
    setcookie('admin_remember', $token, strtotime('+30 days'), '/', '', true, true);
    
    return $token;
}

/**
 * Remember Me token kontrolü
 */
function checkRememberToken() {
    global $db;
    
    if (isAdminLoggedIn()) {
        return;
    }
    
    $token = $_COOKIE['admin_remember'] ?? '';
    
    if (empty($token)) {
        return;
    }
    
    $admin = $db->fetchOne(
        "SELECT * FROM admins 
         WHERE remember_token = ? AND remember_expires > NOW() AND status = 1",
        [$token]
    );
    
    if ($admin) {
        // Session ayarla
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_login_time'] = time();
        
        // Yeni token oluştur
        createRememberToken($admin['id']);
    } else {
        // Geçersiz token, cookie'yi sil
        setcookie('admin_remember', '', time() - 3600, '/');
    }
}

/**
 * Admin şifre sıfırlama
 */
function requestPasswordReset($email) {
    global $db;
    
    try {
        $admin = $db->fetchOne(
            "SELECT * FROM admins WHERE email = ? AND status = 1",
            [$email]
        );
        
        if (!$admin) {
            return ['success' => false, 'message' => 'E-posta adresi bulunamadı.'];
        }
        
        // Reset token oluştur
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $db->update('admins',
            ['reset_token' => $token, 'reset_expires' => $expires],
            ['id' => $admin['id']]
        );
        
        // E-posta gönder (gerçek uygulamada PHPMailer kullanın)
        $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/admin/reset-password.php?token=" . $token;
        
        $subject = "Şifre Sıfırlama - BonusBoss Admin";
        $message = "
            Merhaba {$admin['username']},
            
            Şifre sıfırlama talebiniz alındı. Aşağıdaki linke tıklayarak yeni şifrenizi belirleyebilirsiniz:
            
            {$resetLink}
            
            Bu link 1 saat geçerlidir.
            
            Saygılarımızla,
            BonusBoss Ekibi
        ";
        
        // mail($admin['email'], $subject, $message);
        
        // Logla
        $db->insert('logs', [
            'admin_id' => $admin['id'],
            'action' => 'password_reset_request',
            'data' => json_encode(['email' => $email]),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return ['success' => true, 'message' => 'Şifre sıfırlama linki e-posta adresinize gönderildi.'];
        
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
    }
}

/**
 * Şifre sıfırlama token kontrolü
 */
function validateResetToken($token) {
    global $db;
    
    return $db->fetchOne(
        "SELECT * FROM admins 
         WHERE reset_token = ? AND reset_expires > NOW() AND status = 1",
        [$token]
    );
}

/**
 * Yeni şifre belirleme
 */
function resetPassword($token, $newPassword) {
    global $db;
    
    try {
        $admin = validateResetToken($token);
        
        if (!$admin) {
            return ['success' => false, 'message' => 'Geçersiz veya süresi dolmuş token.'];
        }
        
        // Şifre güçlülük kontrolü
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Şifre en az 8 karakter olmalıdır.'];
        }
        
        // Şifreyi hash'le ve güncelle
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $db->update('admins',
            [
                'password' => $hashedPassword,
                'reset_token' => null,
                'reset_expires' => null
            ],
            ['id' => $admin['id']]
        );
        
        // Logla
        $db->insert('logs', [
            'admin_id' => $admin['id'],
            'action' => 'password_reset',
            'data' => json_encode(['username' => $admin['username']]),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return ['success' => true, 'message' => 'Şifreniz başarıyla güncellendi.'];
        
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Sistem hatası oluştu.'];
    }
}

// Session timeout kontrolü
checkAdminSessionTimeout();

// Remember me kontrolü
checkRememberToken();
?>