<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Admin Dashboard
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Admin kontrolü
require_once 'includes/admin_auth.php';
requireAdminLogin();

// Sayfa değişkenleri
$pageTitle = 'Dashboard';

// İstatistikleri al
$stats = [
    'total_sites' => $db->fetchColumn("SELECT COUNT(*) FROM sites WHERE status = 1"),
    'total_bonuses' => $db->fetchColumn("SELECT COUNT(*) FROM bonuses WHERE status = 1"),
    'total_clicks' => $db->fetchColumn("SELECT SUM(click_count) FROM bonuses"),
    'total_views' => $db->fetchColumn("SELECT COUNT(*) FROM analytics WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
    'pending_sites' => $db->fetchColumn("SELECT COUNT(*) FROM sites WHERE status = 0"),
    'pending_bonuses' => $db->fetchColumn("SELECT COUNT(*) FROM bonuses WHERE status = 0"),
    'active_streams' => $db->fetchColumn("SELECT COUNT(*) FROM live_streams WHERE is_live = 1"),
    'total_banners' => $db->fetchColumn("SELECT COUNT(*) FROM banners WHERE status = 1")
];

// Son aktiviteler
$recentActivities = $db->fetchAll(
    "SELECT * FROM logs 
     WHERE action IN ('login', 'site_create', 'bonus_create', 'banner_create')
     ORDER BY created_at DESC 
     LIMIT 10"
);

// Aylık istatistikler (son 12 ay)
$monthlyStats = $db->fetchAll(
    "SELECT 
        DATE_FORMAT(visit_date, '%Y-%m') as month,
        COUNT(*) as visits,
        COUNT(DISTINCT visitor_ip) as unique_visitors
     FROM analytics 
     WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
     ORDER BY month ASC"
);

// En popüler bonuslar
$topBonuses = $db->fetchAll(
    "SELECT b.*, s.name as site_name, s.logo as site_logo
     FROM bonuses b
     JOIN sites s ON b.site_id = s.id
     WHERE b.status = 1
     ORDER BY b.click_count DESC
     LIMIT 5"
);

// En popüler siteler
$topSites = $db->fetchAll(
    "SELECT s.*, COUNT(b.id) as bonus_count
     FROM sites s
     LEFT JOIN bonuses b ON s.id = b.site_id
     WHERE s.status = 1
     GROUP BY s.id
     ORDER BY s.click_count DESC
     LIMIT 5"
);

// Son eklenen içerikler
$recentContent = [
    'sites' => $db->fetchAll(
        "SELECT * FROM sites WHERE status = 1 ORDER BY created_at DESC LIMIT 3"
    ),
    'bonuses' => $db->fetchAll(
        "SELECT b.*, s.name as site_name FROM bonuses b
         JOIN sites s ON b.site_id = s.id
         WHERE b.status = 1 ORDER BY b.created_at DESC LIMIT 3"
    )
];

include 'includes/admin_header.php';
?>

<div class="dashboard-container">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Sistem genel durumu ve istatistikler</p>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i> Yenile
                        </button>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary dropdown-toggle" 
                                    data-bs-toggle="dropdown">
                                <i class="fas fa-calendar"></i> Bugün
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?period=today">Bugün</a></li>
                                <li><a class="dropdown-item" href="?period=week">Bu Hafta</a></li>
                                <li><a class="dropdown-item" href="?period=month">Bu Ay</a></li>
                                <li><a class="dropdown-item" href="?period=year">Bu Yıl</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-card-body">
                        <div class="stats-icon bg-primary">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="stats-info">
                            <h3 class="stats-number"><?php echo number_format($stats['total_sites']); ?></h3>
                            <p class="stats-label">Aktif Siteler</p>
                            <?php if ($stats['pending_sites'] > 0): ?>
                            <span class="stats-badge bg-warning">
                                <?php echo $stats['pending_sites']; ?> Beklemede
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="stats-footer">
                        <a href="sites.php" class="stats-link">
                            <i class="fas fa-arrow-right"></i> Detayları Gör
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-card-body">
                        <div class="stats-icon bg-success">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="stats-info">
                            <h3 class="stats-number"><?php echo number_format($stats['total_bonuses']); ?></h3>
                            <p class="stats-label">Aktif Bonuslar</p>
                            <?php if ($stats['pending_bonuses'] > 0): ?>
                            <span class="stats-badge bg-warning">
                                <?php echo $stats['pending_bonuses']; ?> Beklemede
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="stats-footer">
                        <a href="bonuses.php" class="stats-link">
                            <i class="fas fa-arrow-right"></i> Detayları Gör
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-card-body">
                        <div class="stats-icon bg-info">
                            <i class="fas fa-mouse-pointer"></i>
                        </div>
                        <div class="stats-info">
                            <h3 class="stats-number"><?php echo number_format($stats['total_clicks']); ?></h3>
                            <p class="stats-label">Toplam Tıklama</p>
                            <span class="stats-badge bg-success">
                                <i class="fas fa-arrow-up"></i> +12%
                            </span>
                        </div>
                    </div>
                    <div class="stats-footer">
                        <a href="analytics.php" class="stats-link">
                            <i class="fas fa-arrow-right"></i> Detayları Gör
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-card-body">
                        <div class="stats-icon bg-warning">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-info">
                            <h3 class="stats-number"><?php echo number_format($stats['total_views']); ?></h3>
                            <p class="stats-label">Aylık Ziyaretçi</p>
                            <span class="stats-badge bg-success">
                                <i class="fas fa-arrow-up"></i> +8%
                            </span>
                        </div>
                    </div>
                    <div class="stats-footer">
                        <a href="analytics.php" class="stats-link">
                            <i class="fas fa-arrow-right"></i> Detayları Gör
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ziyaretçi İstatistikleri</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="visitorChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Hızlı İşlemler</h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="sites.php?action=create" class="quick-action-btn">
                                <i class="fas fa-plus"></i>
                                <span>Yeni Site Ekle</span>
                            </a>
                            
                            <a href="bonuses.php?action=create" class="quick-action-btn">
                                <i class="fas fa-gift"></i>
                                <span>Yeni Bonus Ekle</span>
                            </a>
                            
                            <a href="banners.php?action=create" class="quick-action-btn">
                                <i class="fas fa-image"></i>
                                <span>Yeni Banner Ekle</span>
                            </a>
                            
                            <a href="media.php" class="quick-action-btn">
                                <i class="fas fa-upload"></i>
                                <span>Medya Yükle</span>
                            </a>
                            
                            <a href="settings.php" class="quick-action-btn">
                                <i class="fas fa-cog"></i>
                                <span>Site Ayarları</span>
                            </a>
                            
                            <a href="analytics.php" class="quick-action-btn">
                                <i class="fas fa-chart-bar"></i>
                                <span>İstatistikler</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">En Popüler Bonuslar</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($topBonuses)): ?>
                            <p class="text-muted">Henüz bonus bulunmuyor.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Site</th>
                                            <th>Miktar</th>
                                            <th>Tıklama</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topBonuses as $bonus): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($bonus['site_logo']): ?>
                                                        <img src="../<?php echo $bonus['site_logo']; ?>" 
                                                             alt="<?php echo htmlspecialchars($bonus['site_name']); ?>"
                                                             class="avatar-sm me-2">
                                                    <?php endif; ?>
                                                    <span><?php echo htmlspecialchars($bonus['site_name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo $bonus['amount']; ?> <?php echo $bonus['currency']; ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo number_format($bonus['click_count']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="bonuses.php?action=edit&id=<?php echo $bonus['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">En Popüler Siteler</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($topSites)): ?>
                            <p class="text-muted">Henüz site bulunmuyor.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Site</th>
                                            <th>Bonus</th>
                                            <th>Tıklama</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topSites as $site): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($site['logo']): ?>
                                                        <img src="../<?php echo $site['logo']; ?>" 
                                                             alt="<?php echo htmlspecialchars($site['name']); ?>"
                                                             class="avatar-sm me-2">
                                                    <?php endif; ?>
                                                    <span><?php echo htmlspecialchars($site['name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?php echo $site['bonus_count']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo number_format($site['click_count']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="sites.php?action=edit&id=<?php echo $site['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Son Aktiviteler</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentActivities)): ?>
                            <p class="text-muted">Henüz aktivite bulunmuyor.</p>
                        <?php else: ?>
                            <div class="activity-timeline">
                                <?php foreach ($recentActivities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php
                                        switch ($activity['action']) {
                                            case 'login':
                                                echo '<i class="fas fa-sign-in-alt text-success"></i>';
                                                break;
                                            case 'site_create':
                                                echo '<i class="fas fa-plus text-primary"></i>';
                                                break;
                                            case 'bonus_create':
                                                echo '<i class="fas fa-gift text-warning"></i>';
                                                break;
                                            case 'banner_create':
                                                echo '<i class="fas fa-image text-info"></i>';
                                                break;
                                            default:
                                                echo '<i class="fas fa-circle text-secondary"></i>';
                                        }
                                        ?>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-text">
                                            <?php
                                            switch ($activity['action']) {
                                                case 'login':
                                                    echo 'Admin girişi yapıldı';
                                                    break;
                                                case 'site_create':
                                                    echo 'Yeni site eklendi';
                                                    break;
                                                case 'bonus_create':
                                                    echo 'Yeni bonus eklendi';
                                                    break;
                                                case 'banner_create':
                                                    echo 'Yeni banner eklendi';
                                                    break;
                                                default:
                                                    echo ucfirst($activity['action']);
                                            }
                                            ?>
                                        </p>
                                        <small class="activity-time text-muted">
                                            <?php echo formatDateTurkish($activity['created_at']); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
.stats-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.stats-card-body {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stats-info h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}

.stats-label {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.stats-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-top: 5px;
}

.stats-footer {
    padding: 12px 20px;
    border-top: 1px solid #f0f0f0;
}

.stats-link {
    color: #6c757d;
    text-decoration: none;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.stats-link:hover {
    color: var(--primary-color);
}

.quick-actions {
    display: grid;
    gap: 10px;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.quick-action-btn i {
    width: 20px;
    text-align: center;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.activity-timeline {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-text {
    margin: 0 0 5px 0;
    font-weight: 500;
}

.activity-time {
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .stats-card-body {
        flex-direction: column;
        text-align: center;
    }
    
    .stats-icon {
        margin-bottom: 10px;
    }
}
</style>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Visitor Chart
const ctx = document.getElementById('visitorChart').getContext('2d');
const visitorChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            <?php foreach ($monthlyStats as $stat): ?>
            '<?php echo date('M Y', strtotime($stat['month'] . '-01')); ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Ziyaretçiler',
            data: [
                <?php foreach ($monthlyStats as $stat): ?>
                <?php echo $stat['visits']; ?>,
                <?php endforeach; ?>
            ],
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.4
        }, {
            label: 'Tekil Ziyaretçiler',
            data: [
                <?php foreach ($monthlyStats as $stat): ?>
                <?php echo $stat['unique_visitors']; ?>,
                <?php endforeach; ?>
            ],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function refreshDashboard() {
    location.reload();
}

// Auto refresh every 5 minutes
setInterval(function() {
    refreshDashboard();
}, 300000);
</script>

<?php include 'includes/admin_footer.php'; ?>