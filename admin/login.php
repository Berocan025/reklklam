<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Admin Login Sayfası
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

require_once '../config/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

session_start();

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';

// Login form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // CSRF token kontrolü
    if (!verifyCSRFToken($_POST['_token'] ?? '')) {
        $error_message = 'Güvenlik doğrulaması başarısız!';
    } else {
        // Rate limiting
        $rateLimitKey = 'login_' . getUserIP();
        if (!checkRateLimit($rateLimitKey, 5, 300)) { // 5 deneme / 5 dakika
            $error_message = 'Çok fazla giriş denemesi. 5 dakika sonra tekrar deneyin.';
        } else {
            if (empty($username) || empty($password)) {
                $error_message = 'Kullanıcı adı ve şifre gereklidir.';
            } else {
                // Veritabanından kullanıcı bilgilerini al
                $admin = $db->fetchOne(
                    "SELECT * FROM admins WHERE (username = ? OR email = ?) AND status = 1",
                    [$username, $username]
                );
                
                if ($admin && verifyPassword($password, $admin['password'])) {
                    // Başarılı giriş
                    createAdminSession($admin);
                    
                    // Remember me cookie
                    if ($remember_me) {
                        $token = generateToken();
                        setcookie('remember_admin', $token, time() + REMEMBER_ME_LIFETIME, '/', '', false, true);
                        
                        // Token'ı veritabanına kaydet (remember_tokens tablosuna)
                        $db->query(
                            "INSERT INTO admin_remember_tokens (admin_id, token, expires_at) VALUES (?, ?, ?)",
                            [$admin['id'], hash('sha256', $token), date('Y-m-d H:i:s', time() + REMEMBER_ME_LIFETIME)]
                        );
                    }
                    
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error_message = 'Kullanıcı adı veya şifre hatalı!';
                    
                    // Güvenlik logu
                    logSecurityEvent('Failed Login Attempt', [
                        'username' => $username,
                        'ip' => getUserIP()
                    ]);
                }
            }
        }
    }
}

// Remember me cookie kontrolü
if (isset($_COOKIE['remember_admin']) && !isset($_SESSION['admin_logged_in'])) {
    $token = $_COOKIE['remember_admin'];
    $hashedToken = hash('sha256', $token);
    
    $tokenData = $db->fetchOne(
        "SELECT rt.*, a.* FROM admin_remember_tokens rt 
         JOIN admins a ON rt.admin_id = a.id 
         WHERE rt.token = ? AND rt.expires_at > NOW() AND a.status = 1",
        [$hashedToken]
    );
    
    if ($tokenData) {
        createAdminSession($tokenData);
        header('Location: dashboard.php');
        exit;
    } else {
        // Geçersiz token, cookie'yi sil
        setcookie('remember_admin', '', time() - 3600, '/');
    }
}

// Timeout mesajı
if (isset($_GET['timeout'])) {
    $error_message = 'Oturum süreniz dolmuştur. Lütfen tekrar giriş yapın.';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - BonusBoss</title>
    
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #007bff;
            --primary-dark: #0056b3;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gradient-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: var(--gradient-bg);
            color: white;
            text-align: center;
            padding: 40px 30px 30px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
        
        .login-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-floating {
            position: relative;
            margin-bottom: 20px;
        }
        
        .form-floating .form-control {
            height: 60px;
            padding: 20px 15px 5px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
        }
        
        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .form-floating label {
            padding: 15px;
            font-weight: 500;
            color: #6c757d;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 1.1rem;
            z-index: 5;
        }
        
        .remember-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        
        .form-check {
            display: flex;
            align-items: center;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }
        
        .form-check-label {
            font-size: 0.9rem;
            color: var(--dark-color);
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            height: 50px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            background: var(--gradient-bg);
            border: none;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login .spinner-border {
            width: 20px;
            height: 20px;
        }
        
        .alert {
            border-radius: 10px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        
        .login-footer {
            background: var(--light-color);
            padding: 20px 30px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--dark-color);
        }
        
        .developer-credit {
            margin-top: 10px;
            opacity: 0.7;
        }
        
        .security-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .security-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 0 15px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .login-header {
                padding: 30px 20px 20px;
            }
            
            .security-info {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="login-logo">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h1 class="login-title">BonusBoss</h1>
                    <p class="login-subtitle">Admin Panel Girişi</p>
                </div>
                
                <div class="login-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="loginForm" class="needs-validation" novalidate>
                        <input type="hidden" name="_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-floating">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Kullanıcı Adı" required autocomplete="username">
                            <label for="username">Kullanıcı Adı veya E-posta</label>
                            <div class="invalid-feedback">
                                Kullanıcı adı gereklidir.
                            </div>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Şifre" required autocomplete="current-password">
                            <label for="password">Şifre</label>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                            <div class="invalid-feedback">
                                Şifre gereklidir.
                            </div>
                        </div>
                        
                        <div class="remember-check">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Beni Hatırla
                                </label>
                            </div>
                            <a href="#" class="forgot-password" onclick="showForgotPassword()">
                                Şifremi Unuttum?
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-login" id="loginBtn">
                            <span class="btn-text">Giriş Yap</span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Giriş yapılıyor...
                            </span>
                        </button>
                    </form>
                    
                    <div class="security-info">
                        <div class="security-item">
                            <i class="fas fa-shield-alt text-success"></i>
                            <span>SSL Korumalı</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-lock text-primary"></i>
                            <span>Güvenli Giriş</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-eye text-warning"></i>
                            <span>İzlenen Erişim</span>
                        </div>
                    </div>
                </div>
                
                <div class="login-footer">
                    <p>&copy; <?php echo date('Y'); ?> BonusBoss. Tüm hakları saklıdır.</p>
                    <p class="developer-credit">Geliştirici: <strong>BERAT K</strong></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Şifre Sıfırlama</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="resetEmail" class="form-label">E-posta Adresiniz</label>
                            <input type="email" class="form-control" id="resetEmail" name="email" required>
                            <div class="form-text">
                                Şifre sıfırlama bağlantısı e-posta adresinize gönderilecektir.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="sendResetEmail()">
                        Sıfırlama Bağlantısı Gönder
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            
            const form = document.getElementById('loginForm');
            
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    showLoading();
                }
                
                form.classList.add('was-validated');
            });
        })();
        
        // Password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
        
        // Show loading
        function showLoading() {
            const btn = document.getElementById('loginBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');
            
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            btn.disabled = true;
        }
        
        // Show forgot password modal
        function showForgotPassword() {
            const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            modal.show();
        }
        
        // Send reset email
        function sendResetEmail() {
            const email = document.getElementById('resetEmail').value;
            
            if (!email) {
                alert('Lütfen e-posta adresinizi girin.');
                return;
            }
            
            // AJAX request to send reset email
            fetch('forgot-password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    _token: '<?php echo generateCSRFToken(); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                    modal.hide();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Bir hata oluştu. Lütfen tekrar deneyin.');
            });
        }
        
        // Auto focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            // Enter to submit
            if (event.key === 'Enter' && !event.shiftKey) {
                const activeElement = document.activeElement;
                if (activeElement.tagName === 'INPUT') {
                    document.getElementById('loginForm').dispatchEvent(new Event('submit'));
                }
            }
        });
        
        // Session timeout warning
        let sessionTimeout = <?php echo SESSION_LIFETIME; ?> * 1000; // Convert to milliseconds
        let warningShown = false;
        
        setTimeout(function() {
            if (!warningShown) {
                warningShown = true;
                alert('Oturumunuz yakında sona erecek. Sayfayı yenileyin.');
            }
        }, sessionTimeout - 300000); // 5 minutes before timeout
        
        // Security monitoring
        document.addEventListener('keydown', function(event) {
            // Detect potential security issues
            if (event.ctrlKey && (event.key === 'u' || event.key === 'U')) {
                // Prevent view source
                event.preventDefault();
            }
            
            if (event.key === 'F12') {
                // Prevent developer tools
                event.preventDefault();
            }
        });
        
        // Right click disable
        document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
        });
    </script>
</body>
</html>