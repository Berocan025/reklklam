<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Site Detay Sayfası
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// URL'den site slug'ını al
$siteSlug = sanitizeInput($_GET['site'] ?? '');

if (empty($siteSlug)) {
    header('Location: ' . seoUrl('/'));
    exit;
}

// Site bilgilerini getir
$site = $db->fetchOne(
    "SELECT s.*, c.name as category_name, c.color as category_color, c.slug as category_slug
     FROM sites s
     JOIN categories c ON s.category_id = c.id
     WHERE s.slug = ? AND s.status = 1",
    [$siteSlug]
);

if (!$site) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

// Sayfa değişkenleri
$pageTitle = $site['name'] . ' - Casino Deneme Bonusları';
$metaDescription = $site['meta_description'] ?: ($site['name'] . ' casino sitesi bonusları. ' . $site['description']);
$metaKeywords = $site['meta_keywords'] ?: ($site['name'] . ', casino bonus, deneme bonusu');
$bodyClass = 'site-detail-page';

// Header dahil et
include 'includes/header.php';

// Site bonuslarını getir
$bonuses = $db->fetchAll(
    "SELECT * FROM bonuses 
     WHERE site_id = ? AND status = 1 
     ORDER BY is_featured DESC, amount DESC",
    [$site['id']]
);

// Benzer siteleri getir
$similarSites = $db->fetchAll(
    "SELECT s.*, c.name as category_name, c.color as category_color
     FROM sites s
     JOIN categories c ON s.category_id = c.id
     WHERE s.category_id = ? AND s.id != ? AND s.status = 1
     ORDER BY s.rating DESC, s.created_at DESC
     LIMIT 4",
    [$site['category_id'], $site['id']]
);

// Site click sayısını arttır
$db->execute(
    "UPDATE sites SET click_count = click_count + 1 WHERE id = ?",
    [$site['id']]
);

// Analytics kaydet
trackEvent('site_view', 'site', $site['slug']);

// Bonus türleri
$bonusTypes = [
    'money' => 'Para Bonusu',
    'free_spin' => 'Bedava Spin',
    'no_deposit' => 'Yatırımsız Bonus',
    'first_deposit' => 'İlk Yatırım Bonusu'
];
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <?php echo createBreadcrumb([
            ['title' => 'Ana Sayfa', 'url' => seoUrl('/')],
            ['title' => 'Casino Siteleri', 'url' => seoUrl('casino-siteleri')],
            ['title' => $site['category_name'], 'url' => seoUrl('kategori/' . $site['category_slug'])],
            ['title' => $site['name'], 'url' => '']
        ]); ?>
    </div>
</div>

<!-- Site Header -->
<section class="site-header py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-3 text-center mb-4 mb-lg-0">
                <div class="site-logo-large">
                    <?php if ($site['logo']): ?>
                        <img src="<?php echo uploadUrl($site['logo']); ?>" 
                             alt="<?php echo htmlspecialchars($site['name']); ?>"
                             class="img-fluid rounded-circle site-logo-img">
                    <?php else: ?>
                        <div class="logo-placeholder-large">
                            <?php echo strtoupper(substr($site['name'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-6">
                <h1 class="site-name mb-3"><?php echo htmlspecialchars($site['name']); ?></h1>
                
                <div class="site-meta mb-3">
                    <div class="meta-item">
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?php echo $i <= $site['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                            <?php endfor; ?>
                            <span class="rating-value"><?php echo $site['rating']; ?>/5</span>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <span class="category-badge" style="background-color: <?php echo $site['category_color']; ?>">
                            <?php echo htmlspecialchars($site['category_name']); ?>
                        </span>
                    </div>
                    
                    <?php if ($site['license']): ?>
                    <div class="meta-item">
                        <span class="license-badge">
                            <i class="fas fa-certificate"></i>
                            <?php echo htmlspecialchars($site['license']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <p class="site-description lead">
                    <?php echo htmlspecialchars($site['description']); ?>
                </p>
            </div>
            
            <div class="col-lg-3 text-center">
                <div class="site-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($bonuses); ?></div>
                        <div class="stat-label">Bonus Sayısı</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($site['click_count']); ?></div>
                        <div class="stat-label">Toplam Tıklama</div>
                    </div>
                </div>
                
                <div class="site-actions mt-3">
                    <a href="<?php echo $site['url']; ?>" 
                       class="btn btn-warning btn-lg w-100 mb-2"
                       target="_blank"
                       onclick="trackSiteClick(<?php echo $site['id']; ?>)">
                        <i class="fas fa-external-link-alt"></i>
                        SİTEYE GİT
                    </a>
                    
                    <?php if ($site['review_url']): ?>
                    <a href="<?php echo $site['review_url']; ?>" 
                       class="btn btn-outline-light btn-sm"
                       target="_blank">
                        <i class="fas fa-star"></i>
                        İnceleme Oku
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Site Details -->
<section class="site-details py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Bonuses -->
                <div class="site-section mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-gift text-primary"></i>
                        Mevcut Bonuslar
                    </h3>
                    
                    <?php if (empty($bonuses)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Bu site için henüz bonus eklenmemiş.
                        </div>
                    <?php else: ?>
                        <div class="bonuses-list">
                            <?php foreach ($bonuses as $bonus): ?>
                            <div class="bonus-item-detailed">
                                <div class="bonus-header">
                                    <div class="bonus-type-badge">
                                        <?php echo $bonusTypes[$bonus['bonus_type']] ?? ucfirst($bonus['bonus_type']); ?>
                                    </div>
                                    
                                    <?php if ($bonus['is_featured']): ?>
                                    <span class="featured-badge">
                                        <i class="fas fa-star"></i> ÖNE ÇIKAN
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($bonus['is_hot']): ?>
                                    <span class="hot-badge">
                                        <i class="fas fa-fire"></i> HOT
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="bonus-content">
                                    <div class="bonus-amount">
                                        <span class="amount"><?php echo formatMoney($bonus['amount'], $bonus['currency']); ?></span>
                                        <span class="label"><?php echo $bonusTypes[$bonus['bonus_type']] ?? 'Bonus'; ?></span>
                                    </div>
                                    
                                    <div class="bonus-details">
                                        <?php if ($bonus['min_deposit']): ?>
                                        <div class="detail-row">
                                            <span class="label">Minimum Yatırım:</span>
                                            <span class="value"><?php echo formatMoney($bonus['min_deposit'], $bonus['currency']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($bonus['wagering_requirement']): ?>
                                        <div class="detail-row">
                                            <span class="label">Çevrim Şartı:</span>
                                            <span class="value"><?php echo htmlspecialchars($bonus['wagering_requirement']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($bonus['valid_until']): ?>
                                        <div class="detail-row">
                                            <span class="label">Son Tarih:</span>
                                            <span class="value"><?php echo formatDate($bonus['valid_until']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($bonus['bonus_code']): ?>
                                        <div class="detail-row">
                                            <span class="label">Bonus Kodu:</span>
                                            <span class="value bonus-code" onclick="copyBonusCode('<?php echo $bonus['bonus_code']; ?>')">
                                                <?php echo htmlspecialchars($bonus['bonus_code']); ?>
                                                <i class="fas fa-copy"></i>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($bonus['description']): ?>
                                    <div class="bonus-description">
                                        <?php echo $bonus['description']; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="bonus-action">
                                    <a href="<?php echo $bonus['claim_link'] ?: $site['url']; ?>" 
                                       class="btn btn-primary btn-lg"
                                       target="_blank"
                                       onclick="trackBonusClick(<?php echo $bonus['id']; ?>)">
                                        <i class="fas fa-gift"></i>
                                        BONUSU AL
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Site Info -->
                <div class="site-section mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle text-primary"></i>
                        Site Hakkında
                    </h3>
                    
                    <div class="site-info-grid">
                        <div class="info-card">
                            <h5><i class="fas fa-calendar-alt"></i> Kuruluş Yılı</h5>
                            <p><?php echo $site['established_year'] ?: 'Belirtilmemiş'; ?></p>
                        </div>
                        
                        <div class="info-card">
                            <h5><i class="fas fa-certificate"></i> Lisans</h5>
                            <p><?php echo $site['license'] ?: 'Belirtilmemiş'; ?></p>
                        </div>
                        
                        <div class="info-card">
                            <h5><i class="fas fa-globe"></i> Dil Desteği</h5>
                            <p><?php echo $site['supported_languages'] ?: 'Türkçe'; ?></p>
                        </div>
                        
                        <div class="info-card">
                            <h5><i class="fas fa-mobile-alt"></i> Mobil Uygulamalar</h5>
                            <p><?php echo $site['mobile_support'] ? 'Mevcut' : 'Mevcut Değil'; ?></p>
                        </div>
                    </div>
                    
                    <?php if ($site['content']): ?>
                    <div class="site-content mt-4">
                        <?php echo $site['content']; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Methods -->
                <?php if ($site['payment_methods']): ?>
                <div class="site-section mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-credit-card text-primary"></i>
                        Ödeme Yöntemleri
                    </h3>
                    
                    <div class="payment-methods">
                        <?php 
                        $paymentMethods = json_decode($site['payment_methods'], true);
                        if ($paymentMethods):
                        ?>
                            <?php foreach ($paymentMethods as $method): ?>
                            <span class="payment-method">
                                <i class="fas fa-credit-card"></i>
                                <?php echo htmlspecialchars($method); ?>
                            </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Kredi Kartı, Banka Havalesi, E-Cüzdan</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="sidebar">
                    <!-- Quick Stats -->
                    <div class="widget">
                        <h4 class="widget-title">Hızlı Bilgiler</h4>
                        <div class="quick-stats">
                            <div class="stat-row">
                                <span class="label">Puan:</span>
                                <span class="value">
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?php echo $i <= $site['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </span>
                            </div>
                            
                            <div class="stat-row">
                                <span class="label">Kategori:</span>
                                <span class="value">
                                    <a href="<?php echo seoUrl('kategori/' . $site['category_slug']); ?>">
                                        <?php echo htmlspecialchars($site['category_name']); ?>
                                    </a>
                                </span>
                            </div>
                            
                            <div class="stat-row">
                                <span class="label">Bonus Sayısı:</span>
                                <span class="value"><?php echo count($bonuses); ?></span>
                            </div>
                            
                            <div class="stat-row">
                                <span class="label">Toplam Tıklama:</span>
                                <span class="value"><?php echo number_format($site['click_count']); ?></span>
                            </div>
                            
                            <?php if ($site['min_deposit']): ?>
                            <div class="stat-row">
                                <span class="label">Min. Yatırım:</span>
                                <span class="value"><?php echo formatMoney($site['min_deposit'], 'TL'); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="widget">
                        <div class="action-buttons">
                            <a href="<?php echo $site['url']; ?>" 
                               class="btn btn-primary btn-lg w-100 mb-3"
                               target="_blank"
                               onclick="trackSiteClick(<?php echo $site['id']; ?>)">
                                <i class="fas fa-external-link-alt"></i>
                                SİTEYE GİT
                            </a>
                            
                            <button class="btn btn-outline-primary w-100 mb-2" 
                                    onclick="shareContent()">
                                <i class="fas fa-share"></i>
                                Paylaş
                            </button>
                            
                            <button class="btn btn-outline-secondary w-100" 
                                    onclick="addToFavorites(<?php echo $site['id']; ?>)">
                                <i class="fas fa-heart"></i>
                                Favorilere Ekle
                            </button>
                        </div>
                    </div>
                    
                    <!-- Similar Sites -->
                    <?php if (!empty($similarSites)): ?>
                    <div class="widget">
                        <h4 class="widget-title">Benzer Siteler</h4>
                        <div class="similar-sites">
                            <?php foreach ($similarSites as $similarSite): ?>
                            <div class="similar-site">
                                <div class="site-logo">
                                    <?php if ($similarSite['logo']): ?>
                                        <img src="<?php echo uploadUrl($similarSite['logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($similarSite['name']); ?>">
                                    <?php else: ?>
                                        <div class="logo-placeholder">
                                            <?php echo strtoupper(substr($similarSite['name'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="site-info">
                                    <h6>
                                        <a href="<?php echo seoUrl('site/' . $similarSite['slug']); ?>">
                                            <?php echo htmlspecialchars($similarSite['name']); ?>
                                        </a>
                                    </h6>
                                    <div class="site-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?php echo $i <= $similarSite['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="site-action">
                                    <a href="<?php echo seoUrl('site/' . $similarSite['slug']); ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        Detay
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Custom CSS -->
<style>
.site-logo-large {
    width: 120px;
    height: 120px;
    margin: 0 auto;
    border: 3px solid rgba(255,255,255,0.2);
    border-radius: 50%;
    overflow: hidden;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.site-logo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.logo-placeholder-large {
    font-size: 2rem;
    font-weight: bold;
    color: white;
}

.site-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
}

.rating-stars {
    display: inline-flex;
    gap: 2px;
    color: #ffc107;
}

.rating-value {
    margin-left: 5px;
    color: white;
    font-weight: 600;
}

.category-badge {
    padding: 5px 12px;
    border-radius: 15px;
    color: white;
    font-size: 0.85rem;
    font-weight: 600;
}

.license-badge {
    background: rgba(255,255,255,0.2);
    padding: 5px 12px;
    border-radius: 15px;
    color: white;
    font-size: 0.85rem;
}

.site-stats {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
}

.site-stats .stat-item {
    text-align: center;
}

.site-stats .stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
    display: block;
}

.site-stats .stat-label {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.8);
}

.bonus-item-detailed {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border: 1px solid #f0f0f0;
}

.bonus-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.bonus-type-badge {
    background: var(--primary-color);
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.featured-badge {
    background: linear-gradient(45deg, #ffd700, #ffed4a);
    color: #333;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.hot-badge {
    background: linear-gradient(45deg, #ff4757, #ff6b7a);
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.bonus-content {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 20px;
    align-items: start;
}

.bonus-amount {
    text-align: center;
    min-width: 120px;
}

.bonus-amount .amount {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
    line-height: 1;
}

.bonus-amount .label {
    font-size: 0.85rem;
    color: var(--secondary-color);
    margin-top: 5px;
}

.bonus-details {
    display: grid;
    gap: 8px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row .label {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

.detail-row .value {
    font-weight: 600;
    color: var(--dark-color);
}

.bonus-code {
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s ease;
    font-family: 'Courier New', monospace;
}

.bonus-code:hover {
    background: #e9ecef;
}

.bonus-description {
    grid-column: 1 / -1;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
    color: var(--secondary-color);
    line-height: 1.6;
}

.bonus-action {
    grid-column: 1 / -1;
    text-align: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.site-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.info-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.info-card h5 {
    color: var(--primary-color);
    margin-bottom: 10px;
    font-size: 1rem;
}

.info-card i {
    margin-right: 5px;
}

.payment-methods {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
}

.payment-method {
    background: #f8f9fa;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    color: var(--dark-color);
    border: 1px solid #e9ecef;
}

.sidebar .widget {
    background: white;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.widget-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 10px;
}

.quick-stats {
    display: grid;
    gap: 12px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
}

.stat-row:last-child {
    border-bottom: none;
}

.stat-row .label {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

.stat-row .value {
    font-weight: 600;
    color: var(--dark-color);
}

.similar-sites {
    display: grid;
    gap: 15px;
}

.similar-site {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.similar-site .site-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.similar-site .site-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.similar-site .logo-placeholder {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
    font-weight: bold;
}

.similar-site .site-info {
    flex: 1;
}

.similar-site h6 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
}

.similar-site h6 a {
    color: var(--dark-color);
    text-decoration: none;
}

.similar-site h6 a:hover {
    color: var(--primary-color);
}

.similar-site .site-rating {
    font-size: 0.75rem;
    color: #ffc107;
}

@media (max-width: 768px) {
    .bonus-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .bonus-amount {
        min-width: auto;
    }
    
    .site-info-grid {
        grid-template-columns: 1fr;
    }
    
    .site-stats {
        gap: 15px;
    }
    
    .site-meta {
        justify-content: center;
    }
}
</style>

<!-- JavaScript -->
<script>
function trackSiteClick(siteId) {
    $.post('<?php echo seoUrl('api/track-click'); ?>', {
        type: 'site',
        id: siteId,
        _token: '<?php echo generateCSRFToken(); ?>'
    });
}

function trackBonusClick(bonusId) {
    $.post('<?php echo seoUrl('api/track-click'); ?>', {
        type: 'bonus',
        id: bonusId,
        _token: '<?php echo generateCSRFToken(); ?>'
    });
}

function copyBonusCode(code) {
    copyToClipboard(code).then(() => {
        showToast('Bonus kodu kopyalandı: ' + code, 'success');
    });
}

function shareContent() {
    const title = '<?php echo addslashes($site['name']); ?> - Casino Deneme Bonusları';
    const url = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        copyToClipboard(url).then(() => {
            showToast('Sayfa linki kopyalandı!', 'success');
        });
    }
}

function addToFavorites(siteId) {
    // Favorites functionality
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    
    if (favorites.includes(siteId)) {
        const index = favorites.indexOf(siteId);
        favorites.splice(index, 1);
        showToast('Favorilerden kaldırıldı', 'info');
    } else {
        favorites.push(siteId);
        showToast('Favorilere eklendi', 'success');
    }
    
    localStorage.setItem('favorites', JSON.stringify(favorites));
    updateFavoriteButton(siteId, favorites.includes(siteId));
}

function updateFavoriteButton(siteId, isFavorite) {
    const button = $('button[onclick="addToFavorites(' + siteId + ')"]');
    const icon = button.find('i');
    
    if (isFavorite) {
        icon.removeClass('far').addClass('fas');
        button.removeClass('btn-outline-secondary').addClass('btn-danger');
        button.html('<i class="fas fa-heart"></i> Favorilerden Çıkar');
    } else {
        icon.removeClass('fas').addClass('far');
        button.removeClass('btn-danger').addClass('btn-outline-secondary');
        button.html('<i class="far fa-heart"></i> Favorilere Ekle');
    }
}

$(document).ready(function() {
    // Check if site is in favorites
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const siteId = <?php echo $site['id']; ?>;
    
    if (favorites.includes(siteId)) {
        updateFavoriteButton(siteId, true);
    }
    
    // Bonus card animations
    $('.bonus-item-detailed').hover(
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