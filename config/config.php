<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Configuration File
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Prevent direct access
if (!defined('BONUSBOSS_LOADED')) {
    define('BONUSBOSS_LOADED', true);
}

// Development mode (set to false for production)
define('DEVELOPMENT', true);

// Error reporting
if (DEVELOPMENT) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Site configuration
define('SITE_NAME', 'BonusBoss');
define('SITE_DESCRIPTION', 'En iyi casino deneme bonusları ve güvenilir casino siteleri');
define('SITE_KEYWORDS', 'deneme bonusu, casino bonusu, bedava bonus, casino siteleri');
define('SITE_URL', 'https://kurumsalv8.webtasarimci.app');

// Database configuration - CONFIGURE THESE FOR YOUR SERVER
define('DB_HOST', 'localhost');
define('DB_NAME', 'bonusboss');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security
define('SECRET_KEY', 'BonusBoss2024_Secret_Key_' . md5(__DIR__));
define('CSRF_TOKEN_NAME', 'csrf_token');
define('ENCRYPTION_KEY', 'your-32-character-secret-key-here');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('session.use_only_cookies', 1);
define('SESSION_LIFETIME', 3600); // 1 hour
define('REMEMBER_ME_LIFETIME', 2592000); // 30 days

// Timezone
date_default_timezone_set('Europe/Istanbul');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('CACHE_PATH', ROOT_PATH . '/cache');
define('LOGS_PATH', ROOT_PATH . '/logs');

// URLs
define('ASSETS_URL', '/assets');
define('UPLOADS_URL', '/uploads');
define('ADMIN_URL', '/admin');

// Cache settings
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 hour
define('CACHE_LIFETIME', 3600); // 1 hour

// Pagination
define('ITEMS_PER_PAGE', 20);
define('BONUSES_PER_PAGE', 12);

// Upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'webm', 'ogg']);

// Mail settings (configure as needed)
define('SMTP_HOST', '');
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');

// Contact email
define('CONTACT_EMAIL', 'admin@bonusboss.com');

// Social media
define('TWITTER_URL', '');
define('FACEBOOK_URL', '');
define('INSTAGRAM_URL', '');
define('TELEGRAM_URL', '');

// API Keys (configure as needed)
define('GOOGLE_ANALYTICS_ID', '');
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// Admin settings
define('ADMIN_SESSION_DURATION', 86400); // 24 hours
define('ADMIN_MAX_LOGIN_ATTEMPTS', 5);
define('ADMIN_LOCKOUT_DURATION', 1800); // 30 minutes

// Auto-create required directories
$required_dirs = [
    UPLOADS_PATH,
    CACHE_PATH,
    LOGS_PATH,
    UPLOADS_PATH . '/banners',
    UPLOADS_PATH . '/logos',
    UPLOADS_PATH . '/thumbnails'
];

foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Set default .htaccess for uploads directory
$htaccess_content = "Options -Indexes\n";
$htaccess_content .= "# Deny access to PHP files\n";
$htaccess_content .= "<Files *.php>\n";
$htaccess_content .= "    Order allow,deny\n";
$htaccess_content .= "    Deny from all\n";
$htaccess_content .= "</Files>\n";

$htaccess_file = UPLOADS_PATH . '/.htaccess';
if (!file_exists($htaccess_file)) {
    @file_put_contents($htaccess_file, $htaccess_content);
}
?>