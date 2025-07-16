<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Admin Panel Header
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Admin kontrolü
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Mevcut admin bilgilerini al
$currentAdmin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>BonusBoss Admin Panel</title>
    
    <!-- SEO Meta Tags -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="BonusBoss Admin Panel - Casino bonus yönetim sistemi">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link href="assets/css/admin.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
</head>
<body class="admin-panel">
    
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary admin-navbar fixed-top">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <i class="fas fa-crown me-2"></i>
                <span class="fw-bold">BonusBoss</span>
                <small class="ms-2 opacity-75">Admin</small>
            </a>
            
            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Items -->
            <div class="collapse navbar-collapse" id="adminNavbar">
                <!-- Quick Actions -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-plus me-1"></i> Hızlı Ekle
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="sites.php?action=create">
                                <i class="fas fa-globe me-2"></i> Yeni Site
                            </a></li>
                            <li><a class="dropdown-item" href="bonuses.php?action=create">
                                <i class="fas fa-gift me-2"></i> Yeni Bonus
                            </a></li>
                            <li><a class="dropdown-item" href="banners.php?action=create">
                                <i class="fas fa-image me-2"></i> Yeni Banner
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="media.php">
                                <i class="fas fa-upload me-2"></i> Medya Yükle
                            </a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="../" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i> Siteyi Görüntüle
                        </a>
                    </li>
                </ul>
                
                <!-- Right Side -->
                <ul class="navbar-nav">
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                3
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <li class="dropdown-header">
                                <h6 class="mb-0">Bildirimler</h6>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="notification-item">
                                <a class="dropdown-item" href="#">
                                    <div class="notification-content">
                                        <small class="text-muted">5 dakika önce</small>
                                        <p class="mb-0">Yeni bonus eklendi: Casino Metropol</p>
                                    </div>
                                </a>
                            </li>
                            <li class="notification-item">
                                <a class="dropdown-item" href="#">
                                    <div class="notification-content">
                                        <small class="text-muted">15 dakika önce</small>
                                        <p class="mb-0">Yeni site başvurusu alındı</p>
                                    </div>
                                </a>
                            </li>
                            <li class="notification-item">
                                <a class="dropdown-item" href="#">
                                    <div class="notification-content">
                                        <small class="text-muted">1 saat önce</small>
                                        <p class="mb-0">Banner tıklama sayısı yüksek</p>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="text-center">
                                <a class="dropdown-item text-primary" href="#">
                                    Tüm Bildirimleri Gör
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Admin Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="admin-avatar me-2">
                                <?php echo strtoupper(substr($currentAdmin['username'], 0, 2)); ?>
                            </div>
                            <span class="d-none d-md-inline"><?php echo htmlspecialchars($currentAdmin['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <div class="admin-info">
                                    <strong><?php echo htmlspecialchars($currentAdmin['full_name'] ?: $currentAdmin['username']); ?></strong>
                                    <small class="text-muted d-block"><?php echo ucfirst($currentAdmin['role']); ?></small>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i> Profil
                            </a></li>
                            <li><a class="dropdown-item" href="settings.php">
                                <i class="fas fa-cog me-2"></i> Ayarlar
                            </a></li>
                            <li><a class="dropdown-item" href="analytics.php">
                                <i class="fas fa-chart-bar me-2"></i> İstatistikler
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-content">
            <!-- Main Navigation -->
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" 
                           href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Content Management -->
                    <li class="nav-section">
                        <span class="nav-section-title">İçerik Yönetimi</span>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'sites.php' ? 'active' : ''; ?>" 
                           href="sites.php">
                            <i class="fas fa-globe"></i>
                            <span>Casino Siteleri</span>
                            <?php
                            $pendingSites = $db->fetchColumn("SELECT COUNT(*) FROM sites WHERE status = 0");
                            if ($pendingSites > 0):
                            ?>
                            <span class="badge bg-warning ms-auto"><?php echo $pendingSites; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bonuses.php' ? 'active' : ''; ?>" 
                           href="bonuses.php">
                            <i class="fas fa-gift"></i>
                            <span>Bonuslar</span>
                            <?php
                            $pendingBonuses = $db->fetchColumn("SELECT COUNT(*) FROM bonuses WHERE status = 0");
                            if ($pendingBonuses > 0):
                            ?>
                            <span class="badge bg-warning ms-auto"><?php echo $pendingBonuses; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>" 
                           href="categories.php">
                            <i class="fas fa-tags"></i>
                            <span>Kategoriler</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'pages.php' ? 'active' : ''; ?>" 
                           href="pages.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Sayfalar</span>
                        </a>
                    </li>
                    
                    <!-- Media & Design -->
                    <li class="nav-section">
                        <span class="nav-section-title">Medya & Tasarım</span>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'banners.php' ? 'active' : ''; ?>" 
                           href="banners.php">
                            <i class="fas fa-image"></i>
                            <span>Bannerlar</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'media.php' ? 'active' : ''; ?>" 
                           href="media.php">
                            <i class="fas fa-folder-open"></i>
                            <span>Medya Galerisi</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'live-streams.php' ? 'active' : ''; ?>" 
                           href="live-streams.php">
                            <i class="fas fa-broadcast-tower"></i>
                            <span>Canlı Yayınlar</span>
                            <?php
                            $liveStreams = $db->fetchColumn("SELECT COUNT(*) FROM live_streams WHERE is_live = 1");
                            if ($liveStreams > 0):
                            ?>
                            <span class="badge bg-success ms-auto"><?php echo $liveStreams; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- Analytics & Reports -->
                    <li class="nav-section">
                        <span class="nav-section-title">Analiz & Raporlar</span>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'active' : ''; ?>" 
                           href="analytics.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>İstatistikler</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'logs.php' ? 'active' : ''; ?>" 
                           href="logs.php">
                            <i class="fas fa-list-alt"></i>
                            <span>Sistem Logları</span>
                        </a>
                    </li>
                    
                    <!-- System -->
                    <?php if (hasAdminPermission('manage_system')): ?>
                    <li class="nav-section">
                        <span class="nav-section-title">Sistem</span>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" 
                           href="users.php">
                            <i class="fas fa-users"></i>
                            <span>Kullanıcılar</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" 
                           href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Sistem Ayarları</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        
        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="admin-info-card">
                <div class="admin-avatar-large">
                    <?php echo strtoupper(substr($currentAdmin['username'], 0, 2)); ?>
                </div>
                <div class="admin-details">
                    <strong><?php echo htmlspecialchars($currentAdmin['username']); ?></strong>
                    <small class="text-muted"><?php echo ucfirst($currentAdmin['role']); ?></small>
                </div>
            </div>
            
            <div class="system-info">
                <small class="text-muted">
                    v1.0.0 | <a href="../" target="_blank" class="text-decoration-none">Siteyi Gör</a>
                </small>
            </div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Content will be loaded here -->

<!-- Custom Admin CSS -->
<style>
:root {
    --admin-primary: #6366f1;
    --admin-sidebar-width: 280px;
    --admin-navbar-height: 60px;
}

body.admin-panel {
    font-family: 'Inter', sans-serif;
    background-color: #f8fafc;
    padding-top: var(--admin-navbar-height);
}

/* Admin Navbar */
.admin-navbar {
    height: var(--admin-navbar-height);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1030;
}

.admin-navbar .navbar-brand {
    font-size: 1.25rem;
}

.admin-avatar {
    width: 32px;
    height: 32px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

.notification-badge {
    font-size: 0.6rem;
}

.notification-dropdown {
    width: 320px;
    max-height: 400px;
    overflow-y: auto;
}

.notification-item .dropdown-item {
    padding: 12px 20px;
    border-bottom: 1px solid #f0f0f0;
}

.notification-item:last-child .dropdown-item {
    border-bottom: none;
}

.notification-content p {
    font-size: 0.85rem;
    line-height: 1.4;
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: var(--admin-navbar-height);
    left: 0;
    width: var(--admin-sidebar-width);
    height: calc(100vh - var(--admin-navbar-height));
    background: white;
    border-right: 1px solid #e2e8f0;
    overflow-y: auto;
    z-index: 1020;
}

.sidebar-content {
    padding: 20px 0;
}

.sidebar-nav .nav-item {
    margin-bottom: 2px;
}

.sidebar-nav .nav-link {
    display: flex;
    align-items: center;
    padding: 12px 24px;
    color: #64748b;
    text-decoration: none;
    border-radius: 0;
    transition: all 0.2s ease;
}

.sidebar-nav .nav-link:hover {
    background-color: #f1f5f9;
    color: var(--admin-primary);
}

.sidebar-nav .nav-link.active {
    background-color: #f0f4ff;
    color: var(--admin-primary);
    border-right: 3px solid var(--admin-primary);
}

.sidebar-nav .nav-link i {
    width: 20px;
    margin-right: 12px;
    font-size: 1rem;
}

.nav-section {
    margin-top: 24px;
    margin-bottom: 8px;
}

.nav-section-title {
    display: block;
    padding: 8px 24px;
    color: #94a3b8;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sidebar-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    border-top: 1px solid #e2e8f0;
    background: white;
}

.admin-info-card {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.admin-avatar-large {
    width: 40px;
    height: 40px;
    background: var(--admin-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

.admin-details strong {
    display: block;
    font-size: 0.9rem;
    color: #1e293b;
}

.admin-details small {
    font-size: 0.75rem;
}

.system-info {
    text-align: center;
}

.system-info a {
    color: var(--admin-primary);
}

/* Main Content */
.main-content {
    margin-left: var(--admin-sidebar-width);
    min-height: calc(100vh - var(--admin-navbar-height));
    padding: 30px;
}

.page-header {
    margin-bottom: 30px;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.page-subtitle {
    color: #64748b;
    font-size: 1rem;
    margin: 0;
}

/* Cards */
.card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card-header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 20px 24px;
}

.card-body {
    padding: 24px;
}

/* Responsive */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
        padding: 20px 15px;
    }
    
    .notification-dropdown {
        width: 280px;
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: 1.5rem;
    }
    
    .admin-navbar .navbar-brand span {
        display: none;
    }
}
</style>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->
<script>
$(document).ready(function() {
    // Mobile sidebar toggle
    $('.navbar-toggler').on('click', function() {
        $('.sidebar').toggleClass('show');
    });
    
    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.sidebar, .navbar-toggler').length) {
            $('.sidebar').removeClass('show');
        }
    });
    
    // Auto-hide notifications after read
    $('.notification-item .dropdown-item').on('click', function() {
        $(this).parent().fadeOut();
    });
});

// Global admin functions
function showToast(message, type = 'info') {
    // Toast notification system
    const toast = $(`
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);
    
    $('.toast-container').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();
    
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

function confirmDelete(message = 'Bu işlemi geri alamazsınız!') {
    return confirm('Silmek istediğinizden emin misiniz?\n\n' + message);
}
</script>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11;"></div>