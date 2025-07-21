<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Ana Sayfa
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Include necessary files
require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/security.php';

// Sayfa değişkenleri
$pageTitle = '';
$metaDescription = getSetting('site_description', 'En iyi casino deneme bonusları');
$metaKeywords = getSetting('site_keywords', 'deneme bonusu, casino bonusu, bedava bonus');
$bodyClass = 'homepage';

// Header dahil et
include 'includes/header.php';

// Cache kontrol
$cacheKey = 'homepage_data';
$cachedData = getCache($cacheKey);

if (!$cachedData) {
    // Ana sayfa verilerini çek
    $featuredBonuses = $db->fetchAll(
        "SELECT b.*, s.name as site_name, s.logo as site_logo, s.slug as site_slug, s.rating, c.name as category_name, c.color as category_color
         FROM bonuses b 
         JOIN sites s ON b.site_id = s.id 
         JOIN categories c ON s.category_id = c.id
         WHERE b.status = 1 AND b.is_featured = 1 AND s.status = 1 
         ORDER BY b.amount DESC 
         LIMIT 12"
    );
    
    $categories = $db->fetchAll(
        "SELECT c.*, COUNT(s.id) as site_count 
         FROM categories c 
         LEFT JOIN sites s ON c.id = s.category_id AND s.status = 1
         WHERE c.status = 1 
         GROUP BY c.id 
         ORDER BY c.sort_order ASC"
    );
    
    $latestBonuses = $db->fetchAll(
        "SELECT b.*, s.name as site_name, s.logo as site_logo, s.slug as site_slug, s.rating
         FROM bonuses b 
         JOIN sites s ON b.site_id = s.id 
         WHERE b.status = 1 AND s.status = 1 
         ORDER BY b.created_at DESC 
         LIMIT 8"
    );
    
    $topSites = $db->fetchAll(
        "SELECT s.*, c.name as category_name, c.color as category_color,
                COUNT(b.id) as bonus_count,
                MAX(b.amount) as max_bonus
         FROM sites s 
         JOIN categories c ON s.category_id = c.id
         LEFT JOIN bonuses b ON s.id = b.site_id AND b.status = 1
         WHERE s.status = 1 
         GROUP BY s.id
         ORDER BY s.rating DESC, s.click_count DESC 
         LIMIT 8"
    );
    
    $liveStreams = $db->fetchAll(
        "SELECT * FROM live_streams 
         WHERE status = 1 
         ORDER BY is_live DESC, sort_order ASC 
         LIMIT 4"
    );
    
    $cachedData = [
        'featured_bonuses' => $featuredBonuses,
        'categories' => $categories,
        'latest_bonuses' => $latestBonuses,
        'top_sites' => $topSites,
        'live_streams' => $liveStreams
    ];
    
    setCache($cacheKey, $cachedData, 1800); // 30 dakika cache
}

extract($cachedData);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">
                        <span class="highlight">En İyi</span> Casino Deneme Bonusları
                    </h1>
                    <p class="hero-description">
                        Türkiye'nin en güvenilir casino deneme bonusu platformu. Hilesiz, anında bonus fırsatları sizi bekliyor!
                    </p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $db->fetchColumn("SELECT COUNT(*) FROM bonuses WHERE status = 1"); ?></span>
                            <span class="stat-label">Aktif Bonus</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $db->fetchColumn("SELECT COUNT(*) FROM sites WHERE status = 1"); ?></span>
                            <span class="stat-label">Güvenilir Site</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">7/24</span>
                            <span class="stat-label">Hizmet</span>
                        </div>
                    </div>
                    <div class="hero-buttons">
                        <a href="<?php echo seoUrl('deneme-bonusu'); ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-gift"></i> Bonusları İncele
                        </a>
                        <a href="<?php echo seoUrl('canli-yayin'); ?>" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-play"></i> Canlı Yayın
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image">
                    <img src="<?php echo assetUrl('images/hero-casino.png'); ?>" alt="Casino Bonusları" class="img-fluid">
                    <div class="hero-bonus-float">
                        <?php if (!empty($featuredBonuses)): ?>
                        <div class="float-bonus">
                            <span class="bonus-amount"><?php echo formatMoney($featuredBonuses[0]['amount'], $featuredBonuses[0]['currency']); ?></span>
                            <span class="bonus-text">Deneme Bonusu</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hero Background Animation -->
    <div class="hero-bg-animation">
        <div class="coin coin-1"></div>
        <div class="coin coin-2"></div>
        <div class="coin coin-3"></div>
        <div class="card card-1"></div>
        <div class="card card-2"></div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Site Kategorileri</h2>
            <p class="section-subtitle">İhtiyacınıza uygun kategoriyi seçin</p>
        </div>
        
        <div class="row">
            <?php foreach ($categories as $category): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="category-card" style="border-left-color: <?php echo $category['color']; ?>">
                    <div class="category-icon" style="background-color: <?php echo $category['color']; ?>15">
                        <?php if ($category['icon']): ?>
                            <i class="<?php echo $category['icon']; ?>"></i>
                        <?php else: ?>
                            <i class="fas fa-star"></i>
                        <?php endif; ?>
                    </div>
                    <div class="category-content">
                        <h4 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h4>
                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                        <div class="category-count">
                            <span class="count-number"><?php echo $category['site_count']; ?></span>
                            <span class="count-label">Site</span>
                        </div>
                    </div>
                    <a href="<?php echo seoUrl('kategori/' . $category['slug']); ?>" class="category-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Bonuses Section -->
<section class="featured-bonuses-section py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Öne Çıkan Deneme Bonusları</h2>
            <p class="section-subtitle">En yüksek miktarlı ve popüler bonuslar</p>
        </div>
        
        <div class="row">
            <?php foreach ($featuredBonuses as $bonus): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="bonus-card">
                    <div class="bonus-header">
                        <?php if ($bonus['is_hot']): ?>
                        <span class="bonus-badge hot">
                            <i class="fas fa-fire"></i> HOT
                        </span>
                        <?php endif; ?>
                        
                        <div class="site-logo">
                            <?php if ($bonus['site_logo']): ?>
                                <img src="<?php echo uploadUrl($bonus['site_logo']); ?>" alt="<?php echo htmlspecialchars($bonus['site_name']); ?>">
                            <?php else: ?>
                                <div class="logo-placeholder"><?php echo substr($bonus['site_name'], 0, 2); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="bonus-content">
                        <h5 class="site-name"><?php echo htmlspecialchars($bonus['site_name']); ?></h5>
                        
                        <div class="bonus-amount">
                            <span class="amount"><?php echo formatMoney($bonus['amount'], $bonus['currency']); ?></span>
                            <span class="bonus-type"><?php echo ucfirst($bonus['bonus_type']); ?></span>
                        </div>
                        
                        <div class="bonus-details">
                            <div class="detail-item">
                                <i class="fas fa-star"></i>
                                <span>Rating: <?php echo $bonus['rating']; ?>/5</span>
                            </div>
                            
                            <?php if ($bonus['min_deposit']): ?>
                            <div class="detail-item">
                                <i class="fas fa-coins"></i>
                                <span>Min. Yatırım: <?php echo formatMoney($bonus['min_deposit'], $bonus['currency']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($bonus['wagering_requirement']): ?>
                            <div class="detail-item">
                                <i class="fas fa-chart-line"></i>
                                <span>Çevrim: <?php echo $bonus['wagering_requirement']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="category-tag" style="background-color: <?php echo $bonus['category_color']; ?>15; color: <?php echo $bonus['category_color']; ?>">
                            <?php echo htmlspecialchars($bonus['category_name']); ?>
                        </div>
                    </div>
                    
                    <div class="bonus-footer">
                        <a href="<?php echo $bonus['claim_link'] ?: '#'; ?>" class="btn btn-primary btn-claim" target="_blank" onclick="trackBonusClick(<?php echo $bonus['id']; ?>)">
                            <i class="fas fa-gift"></i> HEMEN AL
                        </a>
                        <a href="<?php echo seoUrl('site/' . $bonus['site_slug']); ?>" class="btn btn-outline-secondary btn-sm">
                            Detaylar
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo seoUrl('deneme-bonusu'); ?>" class="btn btn-primary btn-lg">
                Tüm Bonusları Görüntüle <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Latest Bonuses Section -->
<section class="latest-bonuses-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Son Eklenen Bonuslar</h2>
            <p class="section-subtitle">Yeni eklenen fırsatları kaçırmayın</p>
        </div>
        
        <div class="latest-bonuses-grid">
            <?php foreach ($latestBonuses as $bonus): ?>
            <div class="latest-bonus-item">
                <div class="bonus-site-info">
                    <div class="site-logo-sm">
                        <?php if ($bonus['site_logo']): ?>
                            <img src="<?php echo uploadUrl($bonus['site_logo']); ?>" alt="<?php echo htmlspecialchars($bonus['site_name']); ?>">
                        <?php else: ?>
                            <div class="logo-placeholder-sm"><?php echo substr($bonus['site_name'], 0, 2); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="site-details">
                        <h6 class="site-name"><?php echo htmlspecialchars($bonus['site_name']); ?></h6>
                        <div class="site-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $bonus['rating'] ? 'active' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                
                <div class="bonus-amount-sm">
                    <span class="amount"><?php echo formatMoney($bonus['amount'], $bonus['currency']); ?></span>
                </div>
                
                <div class="bonus-actions">
                    <a href="<?php echo $bonus['claim_link'] ?: '#'; ?>" class="btn btn-sm btn-primary" target="_blank" onclick="trackBonusClick(<?php echo $bonus['id']; ?>)">
                        AL
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Top Sites Section -->
<section class="top-sites-section py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">En Popüler Casino Siteleri</h2>
            <p class="section-subtitle">Yüksek puanlı ve güvenilir siteler</p>
        </div>
        
        <div class="row">
            <?php foreach ($topSites as $index => $site): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="site-card">
                    <div class="site-rank">
                        <span class="rank-number"><?php echo $index + 1; ?></span>
                    </div>
                    
                    <div class="site-header">
                        <div class="site-logo-lg">
                            <?php if ($site['logo']): ?>
                                <img src="<?php echo uploadUrl($site['logo']); ?>" alt="<?php echo htmlspecialchars($site['name']); ?>">
                            <?php else: ?>
                                <div class="logo-placeholder-lg"><?php echo substr($site['name'], 0, 2); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="site-category" style="background-color: <?php echo $site['category_color']; ?>15; color: <?php echo $site['category_color']; ?>">
                            <?php echo htmlspecialchars($site['category_name']); ?>
                        </div>
                    </div>
                    
                    <div class="site-content">
                        <h5 class="site-name"><?php echo htmlspecialchars($site['name']); ?></h5>
                        
                        <div class="site-rating">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $site['rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text"><?php echo $site['rating']; ?>/5</span>
                        </div>
                        
                        <div class="site-stats">
                            <div class="stat-item">
                                <span class="stat-label">Bonus Sayısı:</span>
                                <span class="stat-value"><?php echo $site['bonus_count']; ?></span>
                            </div>
                            
                            <?php if ($site['max_bonus']): ?>
                            <div class="stat-item">
                                <span class="stat-label">Max Bonus:</span>
                                <span class="stat-value"><?php echo formatMoney($site['max_bonus'], 'TL'); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($site['description']): ?>
                        <p class="site-description"><?php echo truncateText(strip_tags($site['description']), 80); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="site-footer">
                        <a href="<?php echo seoUrl('site/' . $site['slug']); ?>" class="btn btn-outline-primary btn-sm">
                            Detaylar
                        </a>
                        <a href="<?php echo $site['affiliate_link'] ?: $site['url']; ?>" class="btn btn-primary btn-sm" target="_blank" onclick="trackSiteClick(<?php echo $site['id']; ?>)">
                            <i class="fas fa-external-link-alt"></i> Siteyi Ziyaret Et
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Live Streams Section -->
<?php if (!empty($liveStreams)): ?>
<section class="live-streams-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">
                <i class="fas fa-broadcast-tower text-danger"></i> Canlı Yayınlar
            </h2>
            <p class="section-subtitle">Casino oyunlarını canlı izleyin</p>
        </div>
        
        <div class="row">
            <?php foreach ($liveStreams as $stream): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stream-card">
                    <div class="stream-thumbnail">
                        <?php if ($stream['thumbnail']): ?>
                            <img src="<?php echo uploadUrl($stream['thumbnail']); ?>" alt="<?php echo htmlspecialchars($stream['title']); ?>">
                        <?php else: ?>
                            <div class="thumbnail-placeholder">
                                <i class="fab fa-<?php echo $stream['platform']; ?>"></i>
                            </div>
                        <?php endif; ?>
                        
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
                    
                    <div class="stream-content">
                        <h6 class="stream-title"><?php echo htmlspecialchars($stream['title']); ?></h6>
                        
                        <?php if ($stream['viewer_count']): ?>
                        <div class="viewer-count">
                            <i class="fas fa-eye"></i>
                            <?php echo number_format($stream['viewer_count']); ?> izleyici
                        </div>
                        <?php endif; ?>
                        
                        <a href="<?php echo $stream['channel_url']; ?>" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-play"></i> İzle
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo seoUrl('canli-yayin'); ?>" class="btn btn-outline-primary">
                Tüm Yayınları Gör <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter Section -->
<section class="newsletter-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="newsletter-content">
                    <h3 class="newsletter-title">
                        <i class="fas fa-bell"></i> Bonus Fırsatlarından Haberdar Olun
                    </h3>
                    <p class="newsletter-description">
                        Yeni bonus fırsatları, özel teklifler ve casino haberlerini ilk siz öğrenin!
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <form class="newsletter-form" id="newsletterForm">
                    <div class="input-group">
                        <input type="email" class="form-control" name="email" placeholder="E-posta adresiniz..." required>
                        <button class="btn btn-light" type="submit">
                            <i class="fas fa-paper-plane"></i> Abone Ol
                        </button>
                    </div>
                    <small class="form-text mt-2">
                        <i class="fas fa-shield-alt"></i> E-posta adresiniz güvendedir.
                    </small>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript Functions -->
<script>
function trackBonusClick(bonusId) {
    $.post('<?php echo seoUrl('api/track-click'); ?>', {
        type: 'bonus',
        id: bonusId,
        _token: '<?php echo generateCSRFToken(); ?>'
    });
}

function trackSiteClick(siteId) {
    $.post('<?php echo seoUrl('api/track-click'); ?>', {
        type: 'site',
        id: siteId,
        _token: '<?php echo generateCSRFToken(); ?>'
    });
}

$(document).ready(function() {
    // Newsletter form
    $('#newsletterForm').on('submit', function(e) {
        e.preventDefault();
        
        var email = $(this).find('input[name="email"]').val();
        var btn = $(this).find('button[type="submit"]');
        var originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin"></i> Gönderiliyor...');
        btn.prop('disabled', true);
        
        $.post('<?php echo seoUrl('api/newsletter'); ?>', {
            email: email,
            _token: '<?php echo generateCSRFToken(); ?>'
        })
        .done(function(response) {
            if (response.success) {
                alert('Başarıyla abone oldunuz!');
                $('#newsletterForm')[0].reset();
            } else {
                alert('Hata: ' + response.message);
            }
        })
        .fail(function() {
            alert('Bir hata oluştu. Lütfen tekrar deneyin.');
        })
        .always(function() {
            btn.html(originalText);
            btn.prop('disabled', false);
        });
    });
    
    // Bonus kartları hover efekti
    $('.bonus-card').hover(
        function() {
            $(this).addClass('hover-effect');
        },
        function() {
            $(this).removeClass('hover-effect');
        }
    );
    
    // Lazy loading için bonus kartları
    $('.bonus-card img, .site-card img').each(function() {
        var $this = $(this);
        var src = $this.attr('src');
        $this.attr('data-src', src).removeAttr('src').addClass('lazy');
    });
});
</script>

<?php include 'includes/footer.php'; ?>