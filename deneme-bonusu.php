<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Deneme Bonusları Sayfası
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Sayfa değişkenleri
$pageTitle = 'Deneme Bonusları';
$metaDescription = 'En yüksek deneme bonusları burada! Hilesiz, anında çekim garantili casino bonusları.';
$metaKeywords = 'deneme bonusu, casino bonus, bedava bonus, deneme bonusu veren siteler';
$bodyClass = 'bonuses-page';

// Header dahil et
include 'includes/header.php';

// Filtreleme parametreleri
$categoryFilter = sanitizeInput($_GET['category'] ?? '');
$bonusTypeFilter = sanitizeInput($_GET['bonus_type'] ?? '');
$minAmount = (int)($_GET['min_amount'] ?? 0);
$maxAmount = (int)($_GET['max_amount'] ?? 0);
$sortBy = sanitizeInput($_GET['sort'] ?? 'amount_desc');
$page = (int)($_GET['page'] ?? 1);

// Sayfa başına bonus sayısı
$perPage = 12;
$offset = ($page - 1) * $perPage;

// WHERE koşulları oluştur
$whereConditions = ['b.status = 1', 's.status = 1'];
$params = [];

if (!empty($categoryFilter)) {
    $whereConditions[] = 'c.slug = ?';
    $params[] = $categoryFilter;
}

if (!empty($bonusTypeFilter)) {
    $whereConditions[] = 'b.bonus_type = ?';
    $params[] = $bonusTypeFilter;
}

if ($minAmount > 0) {
    $whereConditions[] = 'b.amount >= ?';
    $params[] = $minAmount;
}

if ($maxAmount > 0) {
    $whereConditions[] = 'b.amount <= ?';
    $params[] = $maxAmount;
}

$whereClause = implode(' AND ', $whereConditions);

// Sıralama
$orderBy = 'b.amount DESC';
switch ($sortBy) {
    case 'amount_asc':
        $orderBy = 'b.amount ASC';
        break;
    case 'amount_desc':
        $orderBy = 'b.amount DESC';
        break;
    case 'newest':
        $orderBy = 'b.created_at DESC';
        break;
    case 'popular':
        $orderBy = 'b.click_count DESC';
        break;
    case 'rating':
        $orderBy = 's.rating DESC';
        break;
}

// Bonusları getir
$bonusQuery = "
    SELECT b.*, s.name as site_name, s.logo as site_logo, s.slug as site_slug, s.rating, 
           c.name as category_name, c.color as category_color, c.slug as category_slug
    FROM bonuses b 
    JOIN sites s ON b.site_id = s.id 
    JOIN categories c ON s.category_id = c.id
    WHERE {$whereClause}
    ORDER BY b.is_featured DESC, {$orderBy}
    LIMIT {$perPage} OFFSET {$offset}
";

$bonuses = $db->fetchAll($bonusQuery, $params);

// Toplam bonus sayısı
$totalQuery = "
    SELECT COUNT(*) as total
    FROM bonuses b 
    JOIN sites s ON b.site_id = s.id 
    JOIN categories c ON s.category_id = c.id
    WHERE {$whereClause}
";

$totalResult = $db->fetchOne($totalQuery, $params);
$totalBonuses = $totalResult['total'];
$totalPages = ceil($totalBonuses / $perPage);

// Kategorileri getir
$categories = $db->fetchAll("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC");

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
            ['title' => 'Deneme Bonusları', 'url' => '']
        ]); ?>
    </div>
</div>

<!-- Page Header -->
<section class="page-header py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title mb-3">
                    <i class="fas fa-gift text-primary"></i>
                    Deneme Bonusları
                </h1>
                <p class="page-description lead">
                    En yüksek miktarlı ve güvenilir casino deneme bonusları burada! 
                    <?php echo $totalBonuses; ?> farklı bonus seçeneği.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="page-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalBonuses; ?></div>
                        <div class="stat-label">Toplam Bonus</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="filters-section py-4 bg-light">
    <div class="container">
        <form method="GET" class="filter-form" id="bonusFilters">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Kategori</label>
                    <select name="category" class="form-select" id="categoryFilter">
                        <option value="">Tüm Kategoriler</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['slug']; ?>" 
                                    <?php echo $categoryFilter === $category['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Bonus Türü</label>
                    <select name="bonus_type" class="form-select" id="bonusTypeFilter">
                        <option value="">Tüm Türler</option>
                        <?php foreach ($bonusTypes as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                    <?php echo $bonusTypeFilter === $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">Min. Tutar</label>
                    <input type="number" name="min_amount" class="form-control" 
                           placeholder="0 TL" value="<?php echo $minAmount ?: ''; ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">Max. Tutar</label>
                    <input type="number" name="max_amount" class="form-control" 
                           placeholder="1000 TL" value="<?php echo $maxAmount ?: ''; ?>">
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrele
                    </button>
                </div>
            </div>
            
            <!-- Sort Options -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <label class="form-label fw-bold mb-0">Sıralama:</label>
                        <select name="sort" class="form-select form-select-sm" style="width: auto;" id="sortFilter">
                            <option value="amount_desc" <?php echo $sortBy === 'amount_desc' ? 'selected' : ''; ?>>
                                En Yüksek Tutar
                            </option>
                            <option value="amount_asc" <?php echo $sortBy === 'amount_asc' ? 'selected' : ''; ?>>
                                En Düşük Tutar
                            </option>
                            <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>
                                En Yeni
                            </option>
                            <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>
                                En Popüler
                            </option>
                            <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>
                                En Yüksek Puan
                            </option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6 text-end">
                    <?php if (!empty(array_filter([$categoryFilter, $bonusTypeFilter, $minAmount, $maxAmount]))): ?>
                    <a href="<?php echo seoUrl('deneme-bonusu'); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i> Filtreleri Temizle
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Bonuses Grid -->
<section class="bonuses-grid py-5">
    <div class="container">
        <?php if (empty($bonuses)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>Bonus Bulunamadı</h3>
                <p class="text-muted">Arama kriterlerinize uygun bonus bulunamadı. Filtreleri değiştirmeyi deneyin.</p>
                <a href="<?php echo seoUrl('deneme-bonusu'); ?>" class="btn btn-primary">
                    Tüm Bonusları Görüntüle
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($bonuses as $bonus): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="bonus-card h-100">
                        <div class="bonus-header">
                            <?php if ($bonus['is_featured']): ?>
                            <span class="bonus-badge featured">
                                <i class="fas fa-star"></i> ÖNE ÇIKAN
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($bonus['is_hot']): ?>
                            <span class="bonus-badge hot">
                                <i class="fas fa-fire"></i> HOT
                            </span>
                            <?php endif; ?>
                            
                            <div class="site-logo">
                                <?php if ($bonus['site_logo']): ?>
                                    <img src="<?php echo uploadUrl($bonus['site_logo']); ?>" 
                                         alt="<?php echo htmlspecialchars($bonus['site_name']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="logo-placeholder">
                                        <?php echo strtoupper(substr($bonus['site_name'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="bonus-content">
                            <h5 class="site-name"><?php echo htmlspecialchars($bonus['site_name']); ?></h5>
                            
                            <div class="bonus-amount">
                                <span class="amount"><?php echo formatMoney($bonus['amount'], $bonus['currency']); ?></span>
                                <span class="bonus-type"><?php echo $bonusTypes[$bonus['bonus_type']] ?? ucfirst($bonus['bonus_type']); ?></span>
                            </div>
                            
                            <div class="bonus-details">
                                <div class="detail-item">
                                    <i class="fas fa-star"></i>
                                    <span>Puan: <?php echo $bonus['rating']; ?>/5</span>
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
                                    <span>Çevrim: <?php echo htmlspecialchars($bonus['wagering_requirement']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($bonus['valid_until']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>Son Tarih: <?php echo formatDate($bonus['valid_until']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="category-tag" style="background-color: <?php echo $bonus['category_color']; ?>15; color: <?php echo $bonus['category_color']; ?>">
                                <?php echo htmlspecialchars($bonus['category_name']); ?>
                            </div>
                            
                            <?php if ($bonus['description']): ?>
                            <p class="bonus-description">
                                <?php echo truncateText(strip_tags($bonus['description']), 100); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="bonus-footer">
                            <a href="<?php echo $bonus['claim_link'] ?: '#'; ?>" 
                               class="btn btn-primary btn-claim flex-fill"
                               target="_blank" 
                               onclick="trackBonusClick(<?php echo $bonus['id']; ?>)"
                               data-bonus-id="<?php echo $bonus['id']; ?>">
                                <i class="fas fa-gift"></i> HEMEN AL
                            </a>
                            <a href="<?php echo seoUrl('site/' . $bonus['site_slug']); ?>" 
                               class="btn btn-outline-secondary btn-sm ms-2">
                                <i class="fas fa-info-circle"></i> Detay
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Bonus sayfaları">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo seoUrl('deneme-bonusu?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))); ?>">
                            <i class="fas fa-chevron-left"></i> Önceki
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo seoUrl('deneme-bonusu?' . http_build_query(array_merge($_GET, ['page' => $i]))); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo seoUrl('deneme-bonusu?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))); ?>">
                            Sonraki <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="text-center mt-3 text-muted">
                Sayfa <?php echo $page; ?> / <?php echo $totalPages; ?> 
                (Toplam <?php echo $totalBonuses; ?> bonus)
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container text-center">
        <h3 class="mb-3">Bonus Alamadınız mı?</h3>
        <p class="lead mb-4">
            Size özel bonus teklifleri için bizimle iletişime geçin. 
            Uzman ekibimiz size en uygun bonusu bulacak!
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?php echo seoUrl('iletisim'); ?>" class="btn btn-light btn-lg">
                <i class="fas fa-envelope"></i> İletişime Geç
            </a>
            <a href="https://wa.me/905551234567" class="btn btn-success btn-lg" target="_blank">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
        </div>
    </div>
</section>

<!-- JavaScript -->
<script>
function trackBonusClick(bonusId) {
    $.post('<?php echo seoUrl('api/track-click'); ?>', {
        type: 'bonus',
        id: bonusId,
        _token: '<?php echo generateCSRFToken(); ?>'
    });
}

$(document).ready(function() {
    // Auto-submit on sort change
    $('#sortFilter').on('change', function() {
        $('#bonusFilters').submit();
    });
    
    // Filter form enhancements
    $('#categoryFilter, #bonusTypeFilter').on('change', function() {
        setTimeout(function() {
            $('#bonusFilters').submit();
        }, 100);
    });
    
    // Smooth scroll to results after filter
    if (window.location.search) {
        $('html, body').animate({
            scrollTop: $('.bonuses-grid').offset().top - 100
        }, 500);
    }
    
    // Bonus card hover effects
    $('.bonus-card').hover(
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