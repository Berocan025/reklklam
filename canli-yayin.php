<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Canlı Yayın Sayfası
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Sayfa değişkenleri
$pageTitle = 'Canlı Yayınlar';
$metaDescription = 'Casino oyunlarını canlı izleyin! Twitch, YouTube ve Kick platformlarında en popüler casino yayınları.';
$metaKeywords = 'canlı yayın, casino yayını, twitch casino, youtube casino, kick casino';
$bodyClass = 'live-streams-page';

// Include necessary files
require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/security.php';

// Header dahil et
include 'includes/header.php';

// Platform filter
$platformFilter = sanitizeInput($_GET['platform'] ?? '');
$statusFilter = sanitizeInput($_GET['status'] ?? '');

// WHERE koşulları
$whereConditions = ['status = 1'];
$params = [];

if (!empty($platformFilter)) {
    $whereConditions[] = 'platform = ?';
    $params[] = $platformFilter;
}

if ($statusFilter === 'live') {
    $whereConditions[] = 'is_live = 1';
} elseif ($statusFilter === 'scheduled') {
    $whereConditions[] = 'schedule_start > NOW()';
}

$whereClause = implode(' AND ', $whereConditions);

// Canlı yayınları getir
$streams = $db->fetchAll(
    "SELECT * FROM live_streams 
     WHERE {$whereClause}
     ORDER BY is_live DESC, viewer_count DESC, sort_order ASC",
    $params
);

// Platform istatistikleri
$platformStats = $db->fetchAll(
    "SELECT platform, COUNT(*) as total_count, SUM(is_live) as live_count, SUM(viewer_count) as total_viewers
     FROM live_streams 
     WHERE status = 1 
     GROUP BY platform"
);

// Platform bilgileri
$platforms = [
    'twitch' => [
        'name' => 'Twitch',
        'color' => '#9146ff',
        'icon' => 'fab fa-twitch',
        'description' => 'Dünyanın en popüler oyun yayın platformu'
    ],
    'youtube' => [
        'name' => 'YouTube',
        'color' => '#ff0000',
        'icon' => 'fab fa-youtube',
        'description' => 'Video paylaşım platformunda canlı yayınlar'
    ],
    'kick' => [
        'name' => 'Kick',
        'color' => '#53fc18',
        'icon' => 'fas fa-play',
        'description' => 'Yeni nesil yayın platformu'
    ]
];
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <?php echo createBreadcrumb([
            ['title' => 'Ana Sayfa', 'url' => seoUrl('/')],
            ['title' => 'Canlı Yayınlar', 'url' => '']
        ]); ?>
    </div>
</div>

<!-- Page Header -->
<section class="page-header py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title mb-3">
                    <i class="fas fa-broadcast-tower"></i>
                    Canlı Yayınlar
                </h1>
                <p class="page-description lead">
                    Casino oyunlarını profesyonel yayıncılardan canlı izleyin. 
                    Stratejileri öğrenin, eğlenceli vakit geçirin!
                </p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="live-indicator-large">
                    <span class="live-dot-large"></span>
                    <span class="live-text">CANLI YAYIN</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Platform Stats -->
<section class="platform-stats py-4 bg-light">
    <div class="container">
        <div class="row">
            <?php foreach ($platformStats as $stat): ?>
            <?php $platform = $platforms[$stat['platform']] ?? null; ?>
            <?php if ($platform): ?>
            <div class="col-md-4 mb-3">
                <div class="stat-card text-center">
                    <div class="stat-icon" style="color: <?php echo $platform['color']; ?>">
                        <i class="<?php echo $platform['icon']; ?>"></i>
                    </div>
                    <h5 class="stat-title"><?php echo $platform['name']; ?></h5>
                    <div class="stat-numbers">
                        <div class="stat-number">
                            <span class="number"><?php echo $stat['live_count']; ?></span>
                            <span class="label">Canlı</span>
                        </div>
                        <div class="stat-number">
                            <span class="number"><?php echo number_format($stat['total_viewers']); ?></span>
                            <span class="label">İzleyici</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Filters -->
<section class="filters-section py-3 border-bottom">
    <div class="container">
        <form method="GET" class="filter-form">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Platform</label>
                    <select name="platform" class="form-select" onchange="this.form.submit()">
                        <option value="">Tüm Platformlar</option>
                        <?php foreach ($platforms as $key => $platform): ?>
                            <option value="<?php echo $key; ?>" <?php echo $platformFilter === $key ? 'selected' : ''; ?>>
                                <?php echo $platform['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Durum</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Tüm Yayınlar</option>
                        <option value="live" <?php echo $statusFilter === 'live' ? 'selected' : ''; ?>>
                            🔴 Canlı Yayınlar
                        </option>
                        <option value="scheduled" <?php echo $statusFilter === 'scheduled' ? 'selected' : ''; ?>>
                            📅 Programlanmış
                        </option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <?php if (!empty($platformFilter) || !empty($statusFilter)): ?>
                    <a href="<?php echo seoUrl('canli-yayin'); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Filtreleri Temizle
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Featured Live Stream -->
<?php 
$featuredStream = null;
foreach ($streams as $stream) {
    if ($stream['is_live'] && $stream['viewer_count'] > 0) {
        $featuredStream = $stream;
        break;
    }
}
?>

<?php if ($featuredStream): ?>
<section class="featured-stream py-5">
    <div class="container">
        <h2 class="section-title text-center mb-4">
            <i class="fas fa-star text-warning"></i> Öne Çıkan Canlı Yayın
        </h2>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="featured-stream-card">
                    <div class="stream-embed">
                        <?php if ($featuredStream['embed_code']): ?>
                            <?php echo $featuredStream['embed_code']; ?>
                        <?php else: ?>
                            <div class="embed-placeholder">
                                <i class="fab fa-<?php echo $featuredStream['platform']; ?> fa-4x"></i>
                                <h4><?php echo htmlspecialchars($featuredStream['title']); ?></h4>
                                <a href="<?php echo $featuredStream['channel_url']; ?>" 
                                   class="btn btn-primary btn-lg" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> Yayını İzle
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="stream-info">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h3 class="stream-title"><?php echo htmlspecialchars($featuredStream['title']); ?></h3>
                                <div class="stream-meta">
                                    <span class="platform-badge platform-<?php echo $featuredStream['platform']; ?>">
                                        <i class="fab fa-<?php echo $featuredStream['platform']; ?>"></i>
                                        <?php echo ucfirst($featuredStream['platform']); ?>
                                    </span>
                                    <span class="live-badge">
                                        <span class="live-dot"></span> CANLI
                                    </span>
                                    <span class="viewer-count">
                                        <i class="fas fa-eye"></i>
                                        <?php echo number_format($featuredStream['viewer_count']); ?> izleyici
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Streams Grid -->
<section class="streams-grid py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">
                <?php if ($statusFilter === 'live'): ?>
                    🔴 Canlı Yayınlar
                <?php elseif ($statusFilter === 'scheduled'): ?>
                    📅 Programlanmış Yayınlar
                <?php else: ?>
                    Tüm Yayınlar
                <?php endif; ?>
            </h2>
            <div class="view-toggle">
                <button class="btn btn-outline-secondary btn-sm active" onclick="toggleView('grid')">
                    <i class="fas fa-th"></i> Grid
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="toggleView('list')">
                    <i class="fas fa-list"></i> Liste
                </button>
            </div>
        </div>
        
        <?php if (empty($streams)): ?>
            <div class="text-center py-5">
                <i class="fas fa-video-slash fa-3x text-muted mb-3"></i>
                <h3>Yayın Bulunamadı</h3>
                <p class="text-muted">Seçtiğiniz kriterlere uygun yayın bulunamadı.</p>
                <a href="<?php echo seoUrl('canli-yayin'); ?>" class="btn btn-primary">
                    Tüm Yayınları Gör
                </a>
            </div>
        <?php else: ?>
            <div class="streams-container" id="streamsContainer">
                <div class="row" id="streamsGrid">
                    <?php foreach ($streams as $stream): ?>
                    <div class="col-lg-4 col-md-6 mb-4 stream-item">
                        <div class="stream-card">
                            <div class="stream-thumbnail">
                                <?php if ($stream['thumbnail']): ?>
                                    <img src="<?php echo uploadUrl($stream['thumbnail']); ?>" 
                                         alt="<?php echo htmlspecialchars($stream['title']); ?>"
                                         loading="lazy" class="img-fluid">
                                <?php else: ?>
                                    <div class="thumbnail-placeholder">
                                        <i class="fab fa-<?php echo $stream['platform']; ?> fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Overlay Info -->
                                <div class="stream-overlay">
                                    <?php if ($stream['is_live']): ?>
                                    <div class="live-indicator">
                                        <span class="live-dot"></span>
                                        CANLI
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="platform-badge platform-<?php echo $stream['platform']; ?>">
                                        <i class="fab fa-<?php echo $stream['platform']; ?>"></i>
                                        <?php echo ucfirst($stream['platform']); ?>
                                    </div>
                                </div>
                                
                                <!-- Play Button -->
                                <div class="play-overlay">
                                    <a href="<?php echo $stream['channel_url']; ?>" 
                                       class="play-button" target="_blank">
                                        <i class="fas fa-play"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="stream-content">
                                <h5 class="stream-title">
                                    <?php echo htmlspecialchars($stream['title']); ?>
                                </h5>
                                
                                <?php if ($stream['description']): ?>
                                <p class="stream-description">
                                    <?php echo truncateText(strip_tags($stream['description']), 80); ?>
                                </p>
                                <?php endif; ?>
                                
                                <div class="stream-stats">
                                    <?php if ($stream['is_live'] && $stream['viewer_count']): ?>
                                    <div class="stat-item">
                                        <i class="fas fa-eye"></i>
                                        <span><?php echo number_format($stream['viewer_count']); ?> izleyici</span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!$stream['is_live'] && $stream['schedule_start']): ?>
                                    <div class="stat-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo formatDateTurkish($stream['schedule_start']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="stream-actions">
                                    <a href="<?php echo $stream['channel_url']; ?>" 
                                       class="btn btn-primary btn-sm" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> İzle
                                    </a>
                                    
                                    <button class="btn btn-outline-secondary btn-sm" 
                                            onclick="shareStream('<?php echo htmlspecialchars($stream['title']); ?>', '<?php echo $stream['channel_url']; ?>')">
                                        <i class="fas fa-share"></i> Paylaş
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Platform Info -->
<section class="platform-info py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Yayın Platformları</h2>
        
        <div class="row">
            <?php foreach ($platforms as $key => $platform): ?>
            <div class="col-md-4 mb-4">
                <div class="platform-card text-center">
                    <div class="platform-icon" style="color: <?php echo $platform['color']; ?>">
                        <i class="<?php echo $platform['icon']; ?> fa-3x"></i>
                    </div>
                    <h4 class="platform-name mt-3"><?php echo $platform['name']; ?></h4>
                    <p class="platform-description"><?php echo $platform['description']; ?></p>
                    <a href="?platform=<?php echo $key; ?>" class="btn btn-outline-primary">
                        Yayınları Gör
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container text-center">
        <h3 class="mb-3">Siz de Yayıncı mısınız?</h3>
        <p class="lead mb-4">
            Casino yayını yapıyorsanız sitemizde yer almak için bizimle iletişime geçin!
        </p>
        <a href="<?php echo seoUrl('iletisim'); ?>" class="btn btn-light btn-lg">
            <i class="fas fa-plus"></i> Yayın Ekle
        </a>
    </div>
</section>

<!-- Custom CSS -->
<style>
.live-indicator-large {
    background: linear-gradient(45deg, #ff0000, #ff4444);
    padding: 15px 25px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-weight: bold;
    animation: pulse 2s infinite;
}

.live-dot-large {
    width: 12px;
    height: 12px;
    background: white;
    border-radius: 50%;
    animation: blink 1s infinite;
}

.live-text {
    font-size: 1.1rem;
    letter-spacing: 1px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 10px;
}

.stat-numbers {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.stat-number {
    text-align: center;
}

.stat-number .number {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
}

.stat-number .label {
    font-size: 0.85rem;
    color: var(--secondary-color);
}

.featured-stream-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stream-embed {
    position: relative;
    width: 100%;
    height: 400px;
    background: #000;
}

.embed-placeholder {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    text-align: center;
}

.stream-info {
    padding: 20px;
}

.stream-thumbnail {
    position: relative;
    height: 200px;
    background: var(--light-color);
    overflow: hidden;
}

.thumbnail-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--secondary-color);
}

.stream-overlay {
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.play-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stream-card:hover .play-overlay {
    opacity: 1;
}

.play-button {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 1.5rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.play-button:hover {
    background: white;
    transform: scale(1.1);
    color: var(--primary-color);
}

.platform-card {
    background: white;
    padding: 30px 20px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.platform-card:hover {
    transform: translateY(-5px);
}

.stream-stats {
    display: flex;
    gap: 15px;
    margin: 10px 0;
    font-size: 0.85rem;
    color: var(--secondary-color);
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.stream-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@media (max-width: 768px) {
    .stat-numbers {
        flex-direction: column;
        gap: 10px;
    }
    
    .stream-embed {
        height: 250px;
    }
    
    .live-indicator-large {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
}
</style>

<!-- JavaScript -->
<script>
function toggleView(view) {
    // View toggle functionality
    $('.view-toggle .btn').removeClass('active');
    $(`button[onclick="toggleView('${view}')"]`).addClass('active');
    
    if (view === 'list') {
        $('#streamsGrid').removeClass('row').addClass('list-view');
        $('.stream-item').removeClass('col-lg-4 col-md-6').addClass('col-12');
    } else {
        $('#streamsGrid').addClass('row').removeClass('list-view');
        $('.stream-item').addClass('col-lg-4 col-md-6').removeClass('col-12');
    }
}

function shareStream(title, url) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // Fallback
        copyToClipboard(url).then(() => {
            alert('Link kopyalandı!');
        });
    }
}

$(document).ready(function() {
    // Auto-refresh live viewer counts every 30 seconds
    setInterval(function() {
        $('.viewer-count').each(function() {
            // Update viewer counts for live streams
        });
    }, 30000);
    
    // Stream card hover effects
    $('.stream-card').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
});
</script>

<?php include 'includes/footer.php'; ?>