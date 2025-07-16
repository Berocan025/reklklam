<?php
require_once 'includes/admin_auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check admin permissions
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] == 'Viewer') {
    header('Location: login.php');
    exit;
}

$page_title = 'Site Yönetimi';
$current_page = 'site-yonetimi';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_site':
                $name = sanitizeInput($_POST['name']);
                $domain = sanitizeInput($_POST['domain']);
                $logo_url = sanitizeInput($_POST['logo_url']);
                $affiliate_link = sanitizeInput($_POST['affiliate_link']);
                $description = sanitizeInput($_POST['description']);
                $rating = (float)$_POST['rating'];
                $established_year = (int)$_POST['established_year'];
                $license = sanitizeInput($_POST['license']);
                $languages = sanitizeInput($_POST['languages']);
                $currencies = sanitizeInput($_POST['currencies']);
                $payment_methods = sanitizeInput($_POST['payment_methods']);
                $min_deposit = sanitizeInput($_POST['min_deposit']);
                $max_payout = sanitizeInput($_POST['max_payout']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                $stmt = $pdo->prepare("INSERT INTO sites (name, domain, logo_url, affiliate_link, description, rating, established_year, license, languages, currencies, payment_methods, min_deposit, max_payout, is_featured, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                if ($stmt->execute([$name, $domain, $logo_url, $affiliate_link, $description, $rating, $established_year, $license, $languages, $currencies, $payment_methods, $min_deposit, $max_payout, $is_featured, $is_active])) {
                    $success_message = "Site başarıyla eklendi!";
                } else {
                    $error_message = "Site eklenirken hata oluştu!";
                }
                break;
                
            case 'update_site':
                $site_id = (int)$_POST['site_id'];
                $name = sanitizeInput($_POST['name']);
                $domain = sanitizeInput($_POST['domain']);
                $logo_url = sanitizeInput($_POST['logo_url']);
                $affiliate_link = sanitizeInput($_POST['affiliate_link']);
                $description = sanitizeInput($_POST['description']);
                $rating = (float)$_POST['rating'];
                $established_year = (int)$_POST['established_year'];
                $license = sanitizeInput($_POST['license']);
                $languages = sanitizeInput($_POST['languages']);
                $currencies = sanitizeInput($_POST['currencies']);
                $payment_methods = sanitizeInput($_POST['payment_methods']);
                $min_deposit = sanitizeInput($_POST['min_deposit']);
                $max_payout = sanitizeInput($_POST['max_payout']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                $stmt = $pdo->prepare("UPDATE sites SET name=?, domain=?, logo_url=?, affiliate_link=?, description=?, rating=?, established_year=?, license=?, languages=?, currencies=?, payment_methods=?, min_deposit=?, max_payout=?, is_featured=?, is_active=?, updated_at=NOW() WHERE id=?");
                if ($stmt->execute([$name, $domain, $logo_url, $affiliate_link, $description, $rating, $established_year, $license, $languages, $currencies, $payment_methods, $min_deposit, $max_payout, $is_featured, $is_active, $site_id])) {
                    $success_message = "Site başarıyla güncellendi!";
                } else {
                    $error_message = "Site güncellenirken hata oluştu!";
                }
                break;
                
            case 'delete_site':
                $site_id = (int)$_POST['site_id'];
                // Check if site has bonuses
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM bonuses WHERE site_id = ?");
                $check_stmt->execute([$site_id]);
                $bonus_count = $check_stmt->fetchColumn();
                
                if ($bonus_count > 0) {
                    $error_message = "Bu site silinemez çünkü $bonus_count adet bonusu bulunmaktadır!";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM sites WHERE id = ?");
                    if ($stmt->execute([$site_id])) {
                        $success_message = "Site başarıyla silindi!";
                    } else {
                        $error_message = "Site silinirken hata oluştu!";
                    }
                }
                break;
                
            case 'toggle_status':
                $site_id = (int)$_POST['site_id'];
                $stmt = $pdo->prepare("UPDATE sites SET is_active = NOT is_active WHERE id = ?");
                if ($stmt->execute([$site_id])) {
                    $success_message = "Site durumu güncellendi!";
                } else {
                    $error_message = "Durum güncellenirken hata oluştu!";
                }
                break;
                
            case 'toggle_featured':
                $site_id = (int)$_POST['site_id'];
                $stmt = $pdo->prepare("UPDATE sites SET is_featured = NOT is_featured WHERE id = ?");
                if ($stmt->execute([$site_id])) {
                    $success_message = "Site öne çıkarma durumu güncellendi!";
                } else {
                    $error_message = "Durum güncellenirken hata oluştu!";
                }
                break;
        }
    }
}

// Pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$where_conditions = [];
$params = [];

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_conditions[] = "is_active = ?";
    $params[] = (int)$_GET['status'];
}

if (isset($_GET['featured']) && $_GET['featured'] !== '') {
    $where_conditions[] = "is_featured = ?";
    $params[] = (int)$_GET['featured'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(name LIKE ? OR domain LIKE ? OR description LIKE ?)";
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM sites $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get sites with bonus count
$sql = "SELECT s.*, 
        (SELECT COUNT(*) FROM bonuses b WHERE b.site_id = s.id) as bonus_count,
        (SELECT COUNT(*) FROM site_clicks sc WHERE sc.site_id = s.id) as click_count
        FROM sites s 
        $where_clause 
        ORDER BY s.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sites = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building text-primary"></i> Site Yönetimi
        </h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
            <i class="fas fa-plus"></i> Yeni Site Ekle
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <?php
        $stats_stmt = $pdo->query("SELECT 
            COUNT(*) as total_sites,
            SUM(is_active) as active_sites,
            SUM(is_featured) as featured_sites,
            AVG(rating) as avg_rating
            FROM sites");
        $stats = $stats_stmt->fetch();
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Site</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_sites']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aktif Site</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_sites']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Öne Çıkan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['featured_sites']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ort. Puan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtreler
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : ''; ?>>Pasif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="featured" class="form-select">
                        <option value="">Tüm Siteler</option>
                        <option value="1" <?php echo (isset($_GET['featured']) && $_GET['featured'] == '1') ? 'selected' : ''; ?>>Öne Çıkan</option>
                        <option value="0" <?php echo (isset($_GET['featured']) && $_GET['featured'] == '0') ? 'selected' : ''; ?>>Normal</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Site ara..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sites Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Siteler (<?php echo $total_records; ?> adet)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Site Adı</th>
                            <th>Domain</th>
                            <th>Puan</th>
                            <th>Bonus Sayısı</th>
                            <th>Tıklanma</th>
                            <th>Öne Çıkan</th>
                            <th>Durum</th>
                            <th>Oluşturulma</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sites)): ?>
                        <tr>
                            <td colspan="11" class="text-center">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Henüz site eklenmemiş.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($sites as $site): ?>
                        <tr>
                            <td><?php echo $site['id']; ?></td>
                            <td>
                                <?php if ($site['logo_url']): ?>
                                <img src="<?php echo htmlspecialchars($site['logo_url']); ?>" alt="Logo" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($site['name']); ?></strong>
                                <?php if ($site['is_featured']): ?>
                                <i class="fas fa-star text-warning ms-1" title="Öne Çıkan"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo htmlspecialchars($site['domain']); ?>" target="_blank" class="text-decoration-none">
                                    <?php echo htmlspecialchars($site['domain']); ?>
                                    <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-info me-1"><?php echo $site['rating']; ?></span>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $site['rating']): ?>
                                        <i class="fas fa-star text-warning fa-xs"></i>
                                        <?php else: ?>
                                        <i class="far fa-star text-muted fa-xs"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo $site['bonus_count']; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-success"><?php echo $site['click_count']; ?></span>
                            </td>
                            <td>
                                <?php if ($site['is_featured']): ?>
                                <span class="badge bg-warning">Evet</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Hayır</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($site['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($site['created_at'])); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editSite(<?php echo $site['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Öne çıkarma durumunu değiştirmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $site['is_featured'] ? 'btn-warning' : 'btn-outline-warning'; ?>">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Durumu değiştirmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $site['is_active'] ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                                            <i class="fas <?php echo $site['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu siteyi silmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="delete_site">
                                        <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Site pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo buildQueryString(['page']); ?>">Önceki</a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo buildQueryString(['page']); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo buildQueryString(['page']); ?>">Sonraki</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Site Modal -->
<div class="modal fade" id="addSiteModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-primary"></i> Yeni Site Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_site">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Site Adı *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Domain *</label>
                                <input type="url" name="domain" class="form-control" placeholder="https://example.com" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Logo URL</label>
                                <input type="url" name="logo_url" class="form-control" placeholder="https://example.com/logo.png">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Affiliate Link *</label>
                                <input type="url" name="affiliate_link" class="form-control" placeholder="https://affiliate.example.com" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Puan (1-5) *</label>
                                <select name="rating" class="form-select" required>
                                    <option value="">Puan Seçin</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Kuruluş Yılı</label>
                                <input type="number" name="established_year" class="form-control" min="1990" max="<?php echo date('Y'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lisans</label>
                                <input type="text" name="license" class="form-control" placeholder="ör: Malta Gaming Authority">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Diller</label>
                                <input type="text" name="languages" class="form-control" placeholder="Türkçe, İngilizce">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Para Birimleri</label>
                                <input type="text" name="currencies" class="form-control" placeholder="TRY, USD, EUR">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Ödeme Yöntemleri</label>
                                <input type="text" name="payment_methods" class="form-control" placeholder="Visa, Mastercard, Papara">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Min. Yatırım</label>
                                <input type="text" name="min_deposit" class="form-control" placeholder="ör: 50₺">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Maks. Ödeme</label>
                                <input type="text" name="max_payout" class="form-control" placeholder="ör: 100.000₺">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured">
                                <label class="form-check-label" for="is_featured">Öne Çıkan Site</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Site Modal -->
<div class="modal fade" id="editSiteModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning"></i> Site Düzenle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_site">
                <input type="hidden" name="site_id" id="edit_site_id">
                <div class="modal-body" id="editSiteContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSite(siteId) {
    // Load site data via AJAX
    fetch(`ajax/get_site.php?id=${siteId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_site_id').value = data.site.id;
                document.getElementById('editSiteContent').innerHTML = generateEditForm(data.site);
                new bootstrap.Modal(document.getElementById('editSiteModal')).show();
            }
        })
        .catch(error => console.error('Error:', error));
}

function generateEditForm(site) {
    return `
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Site Adı *</label>
                    <input type="text" name="name" class="form-control" value="${site.name}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Domain *</label>
                    <input type="url" name="domain" class="form-control" value="${site.domain}" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Logo URL</label>
                    <input type="url" name="logo_url" class="form-control" value="${site.logo_url || ''}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Affiliate Link *</label>
                    <input type="url" name="affiliate_link" class="form-control" value="${site.affiliate_link}" required>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <textarea name="description" class="form-control" rows="3">${site.description || ''}</textarea>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Puan (1-5) *</label>
                    <select name="rating" class="form-select" required>
                        <option value="">Puan Seçin</option>
                        <option value="1" ${site.rating == 1 ? 'selected' : ''}>1</option>
                        <option value="2" ${site.rating == 2 ? 'selected' : ''}>2</option>
                        <option value="3" ${site.rating == 3 ? 'selected' : ''}>3</option>
                        <option value="4" ${site.rating == 4 ? 'selected' : ''}>4</option>
                        <option value="5" ${site.rating == 5 ? 'selected' : ''}>5</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Kuruluş Yılı</label>
                    <input type="number" name="established_year" class="form-control" value="${site.established_year || ''}" min="1990" max="${new Date().getFullYear()}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Lisans</label>
                    <input type="text" name="license" class="form-control" value="${site.license || ''}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Diller</label>
                    <input type="text" name="languages" class="form-control" value="${site.languages || ''}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Para Birimleri</label>
                    <input type="text" name="currencies" class="form-control" value="${site.currencies || ''}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Ödeme Yöntemleri</label>
                    <input type="text" name="payment_methods" class="form-control" value="${site.payment_methods || ''}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Min. Yatırım</label>
                    <input type="text" name="min_deposit" class="form-control" value="${site.min_deposit || ''}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Maks. Ödeme</label>
                    <input type="text" name="max_payout" class="form-control" value="${site.max_payout || ''}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" name="is_featured" class="form-check-input" id="edit_is_featured" ${site.is_featured ? 'checked' : ''}>
                    <label class="form-check-label" for="edit_is_featured">Öne Çıkan Site</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active" ${site.is_active ? 'checked' : ''}>
                    <label class="form-check-label" for="edit_is_active">Aktif</label>
                </div>
            </div>
        </div>
    `;
}
</script>

<?php include 'includes/admin_footer.php'; ?>