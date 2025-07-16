<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Header Template
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Güvenlik ve fonksiyonları dahil et
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__FILE__)));
}

require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/security.php';

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Analitik takip
trackPageView();

// Sayfa değişkenleri
$pageTitle = $pageTitle ?? '';
$metaDescription = $metaDescription ?? '';
$metaKeywords = $metaKeywords ?? '';
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?php echo getPageTitle($pageTitle); ?></title>
    <meta name="description" content="<?php echo getMetaDescription($metaDescription); ?>">
    <meta name="keywords" content="<?php echo $metaKeywords ?: getSetting('site_keywords'); ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="BERAT K">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo getPageTitle($pageTitle); ?>">
    <meta property="og:description" content="<?php echo getMetaDescription($metaDescription); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo assetUrl('images/logo-og.png'); ?>">
    <meta property="og:site_name" content="<?php echo getSetting('site_title'); ?>">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo getPageTitle($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo getMetaDescription($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo assetUrl('images/logo-twitter.png'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo assetUrl('images/favicon.ico'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo assetUrl('images/favicon-32x32.png'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo assetUrl('images/favicon-16x16.png'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo assetUrl('images/apple-touch-icon.png'); ?>">
    
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo assetUrl('css/style.css'); ?>" rel="stylesheet">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?php echo getSetting('site_title'); ?>",
        "description": "<?php echo getSetting('site_description'); ?>",
        "url": "<?php echo SITE_URL; ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo SITE_URL; ?>/ara?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
</head>
<body class="<?php echo $bodyClass; ?>">

<!-- Header Banner Area -->
<div class="header-banner-area">
    <?php showBanner('header'); ?>
</div>

<!-- Main Header -->
<header class="main-header">
    <div class="container">
        <nav class="navbar navbar-expand-lg">
            <!-- Logo -->
            <a class="navbar-brand" href="<?php echo seoUrl('/'); ?>">
                <?php if (getSetting('site_logo')): ?>
                    <img src="<?php echo uploadUrl(getSetting('site_logo')); ?>" alt="<?php echo getSetting('site_title'); ?>" class="logo-img">
                <?php else: ?>
                    <span class="logo-text">BonusBoss</span>
                <?php endif; ?>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo seoUrl('/'); ?>">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo seoUrl('deneme-bonusu'); ?>">Deneme Bonusları</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Kategoriler
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            $categories = $db->fetchAll("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC");
                            foreach ($categories as $category):
                            ?>
                            <li><a class="dropdown-item" href="<?php echo seoUrl('kategori/' . $category['slug']); ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo seoUrl('canli-yayin'); ?>">Canlı Yayın</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo seoUrl('hakkimizda'); ?>">Hakkımızda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo seoUrl('iletisim'); ?>">İletişim</a>
                    </li>
                </ul>
                
                <!-- Social Media Links -->
                <div class="navbar-social">
                    <?php if (getSetting('social_facebook')): ?>
                    <a href="<?php echo getSetting('social_facebook'); ?>" target="_blank" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (getSetting('social_twitter')): ?>
                    <a href="<?php echo getSetting('social_twitter'); ?>" target="_blank" class="social-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (getSetting('social_instagram')): ?>
                    <a href="<?php echo getSetting('social_instagram'); ?>" target="_blank" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (getSetting('social_youtube')): ?>
                    <a href="<?php echo getSetting('social_youtube'); ?>" target="_blank" class="social-link">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</header>

<!-- Mobile Search -->
<div class="mobile-search d-lg-none">
    <div class="container">
        <form class="search-form" action="<?php echo seoUrl('ara'); ?>" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" name="q" placeholder="Bonus ara..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Popup Modal -->
<?php if (shouldShowPopup()): ?>
<div class="modal fade" id="bonusPopup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo getSetting('popup_title', 'Özel Bonus Fırsatı!'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body text-center">
                <div class="popup-content">
                    <?php echo getSetting('popup_content', 'Özel deneme bonusu fırsatını kaçırma!'); ?>
                </div>
                <div class="popup-bonus-list mt-4">
                    <?php
                    $popupBonuses = $db->fetchAll(
                        "SELECT b.*, s.name as site_name, s.logo as site_logo 
                         FROM bonuses b 
                         JOIN sites s ON b.site_id = s.id 
                         WHERE b.status = 1 AND b.is_featured = 1 AND s.status = 1 
                         ORDER BY b.amount DESC 
                         LIMIT 3"
                    );
                    
                    foreach ($popupBonuses as $bonus):
                    ?>
                    <div class="popup-bonus-item">
                        <div class="bonus-amount"><?php echo formatMoney($bonus['amount'], $bonus['currency']); ?></div>
                        <div class="bonus-site"><?php echo htmlspecialchars($bonus['site_name']); ?></div>
                        <a href="<?php echo $bonus['claim_link'] ?: '#'; ?>" class="btn btn-success btn-sm" target="_blank">
                            HEMEN AL
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <a href="<?php echo seoUrl('deneme-bonusu'); ?>" class="btn btn-primary">Tüm Bonusları Gör</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Content Start -->
<main class="main-content">