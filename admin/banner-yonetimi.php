<?php
require_once 'includes/admin_auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check admin permissions
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] == 'Viewer') {
    header('Location: login.php');
    exit;
}

$page_title = 'Banner Yönetimi';
$current_page = 'banner-yonetimi';

// Banner positions
$positions = [
    'header_top' => 'Header Üst',
    'header_bottom' => 'Header Alt', 
    'sidebar_top' => 'Sidebar Üst',
    'sidebar_middle' => 'Sidebar Orta',
    'sidebar_bottom' => 'Sidebar Alt',
    'content_top' => 'İçerik Üst',
    'content_middle' => 'İçerik Orta',
    'content_bottom' => 'İçerik Alt',
    'footer_top' => 'Footer Üst',
    'footer_bottom' => 'Footer Alt'
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_banner':
                $title = sanitizeInput($_POST['title']);
                $image_url = sanitizeInput($_POST['image_url']);
                $link_url = sanitizeInput($_POST['link_url']);
                $description = sanitizeInput($_POST['description']);
                $position = sanitizeInput($_POST['position']);
                $sort_order = (int)$_POST['sort_order'];
                $start_date = $_POST['start_date'] ?: null;
                $end_date = $_POST['end_date'] ?: null;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                $stmt = $pdo->prepare("INSERT INTO banners (title, image_url, link_url, description, position, sort_order, start_date, end_date, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                if ($stmt->execute([$title, $image_url, $link_url, $description, $position, $sort_order, $start_date, $end_date, $is_active])) {
                    $success_message = "Banner başarıyla eklendi!";
                } else {
                    $error_message = "Banner eklenirken hata oluştu!";
                }
                break;
                
            case 'update_banner':
                $banner_id = (int)$_POST['banner_id'];
                $title = sanitizeInput($_POST['title']);
                $image_url = sanitizeInput($_POST['image_url']);
                $link_url = sanitizeInput($_POST['link_url']);
                $description = sanitizeInput($_POST['description']);
                $position = sanitizeInput($_POST['position']);
                $sort_order = (int)$_POST['sort_order'];
                $start_date = $_POST['start_date'] ?: null;
                $end_date = $_POST['end_date'] ?: null;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                $stmt = $pdo->prepare("UPDATE banners SET title=?, image_url=?, link_url=?, description=?, position=?, sort_order=?, start_date=?, end_date=?, is_active=?, updated_at=NOW() WHERE id=?");
                if ($stmt->execute([$title, $image_url, $link_url, $description, $position, $sort_order, $start_date, $end_date, $is_active, $banner_id])) {
                    $success_message = "Banner başarıyla güncellendi!";
                } else {
                    $error_message = "Banner güncellenirken hata oluştu!";
                }
                break;
                
            case 'delete_banner':
                $banner_id = (int)$_POST['banner_id'];
                $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
                if ($stmt->execute([$banner_id])) {
                    $success_message = "Banner başarıyla silindi!";
                } else {
                    $error_message = "Banner silinirken hata oluştu!";
                }
                break;
                
            case 'toggle_status':
                $banner_id = (int)$_POST['banner_id'];
                $stmt = $pdo->prepare("UPDATE banners SET is_active = NOT is_active WHERE id = ?");
                if ($stmt->execute([$banner_id])) {
                    $success_message = "Banner durumu güncellendi!";
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

if (isset($_GET['position']) && !empty($_GET['position'])) {
    $where_conditions[] = "position = ?";
    $params[] = $_GET['position'];
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_conditions[] = "is_active = ?";
    $params[] = (int)$_GET['status'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM banners $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get banners with click count
$sql = "SELECT b.*, 
        (SELECT COUNT(*) FROM banner_clicks bc WHERE bc.banner_id = b.id) as click_count
        FROM banners b 
        $where_clause 
        ORDER BY b.position ASC, b.sort_order ASC, b.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$banners = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-rectangle-ad text-primary"></i> Banner Yönetimi
        </h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal">
            <i class="fas fa-plus"></i> Yeni Banner Ekle
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
            COUNT(*) as total_banners,
            SUM(is_active) as active_banners,
            COUNT(DISTINCT position) as positions_used
            FROM banners");
        $stats = $stats_stmt->fetch();
        
        $clicks_stmt = $pdo->query("SELECT COUNT(*) as total_clicks FROM banner_clicks");
        $total_clicks = $clicks_stmt->fetchColumn();
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Banner</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_banners']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rectangle-ad fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aktif Banner</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_banners']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Kullanılan Pozisyon</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['positions_used']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Toplam Tıklanma</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_clicks); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mouse-pointer fa-2x text-gray-300"></i>
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
                    <select name="position" class="form-select">
                        <option value="">Tüm Pozisyonlar</option>
                        <?php foreach ($positions as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($_GET['position']) && $_GET['position'] == $key) ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : ''; ?>>Pasif</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Banner ara..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Banners Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Bannerlar (<?php echo $total_records; ?> adet)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Banner</th>
                            <th>Başlık</th>
                            <th>Pozisyon</th>
                            <th>Sıra</th>
                            <th>Tarih Aralığı</th>
                            <th>Tıklanma</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($banners)): ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Henüz banner eklenmemiş.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td><?php echo $banner['id']; ?></td>
                            <td>
                                <?php if ($banner['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($banner['image_url']); ?>" alt="Banner" class="img-thumbnail" style="width: 60px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 40px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($banner['title']); ?></strong>
                                <?php if ($banner['description']): ?>
                                <br><small class="text-muted"><?php echo substr(htmlspecialchars($banner['description']), 0, 50); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $positions[$banner['position']] ?? $banner['position']; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $banner['sort_order']; ?></span>
                            </td>
                            <td>
                                <?php if ($banner['start_date'] || $banner['end_date']): ?>
                                <small>
                                    <?php echo $banner['start_date'] ? date('d.m.Y', strtotime($banner['start_date'])) : '∞'; ?>
                                    -
                                    <?php echo $banner['end_date'] ? date('d.m.Y', strtotime($banner['end_date'])) : '∞'; ?>
                                </small>
                                <?php else: ?>
                                <span class="text-muted">Süresiz</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo number_format($banner['click_count']); ?></span>
                            </td>
                            <td>
                                <?php 
                                $is_active = $banner['is_active'];
                                $now = date('Y-m-d');
                                
                                // Check date restrictions
                                if ($banner['start_date'] && $now < $banner['start_date']) {
                                    $is_active = false;
                                    $status_text = 'Beklemede';
                                    $status_class = 'bg-warning';
                                } elseif ($banner['end_date'] && $now > $banner['end_date']) {
                                    $is_active = false;
                                    $status_text = 'Süresi Dolmuş';
                                    $status_class = 'bg-secondary';
                                } else {
                                    $status_text = $banner['is_active'] ? 'Aktif' : 'Pasif';
                                    $status_class = $banner['is_active'] ? 'bg-success' : 'bg-danger';
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if ($banner['link_url']): ?>
                                    <a href="<?php echo htmlspecialchars($banner['link_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editBanner(<?php echo $banner['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Durumu değiştirmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $banner['is_active'] ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                                            <i class="fas <?php echo $banner['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu bannerı silmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="delete_banner">
                                        <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
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
            <nav aria-label="Banner pagination">
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

<!-- Add Banner Modal -->
<div class="modal fade" id="addBannerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-primary"></i> Yeni Banner Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_banner">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Banner Başlığı *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Sıra *</label>
                                <input type="number" name="sort_order" class="form-control" value="0" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Banner Görseli URL *</label>
                                <input type="url" name="image_url" class="form-control" placeholder="https://example.com/banner.jpg" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Pozisyon *</label>
                                <select name="position" class="form-select" required>
                                    <option value="">Pozisyon Seçin</option>
                                    <?php foreach ($positions as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hedef Link</label>
                        <input type="url" name="link_url" class="form-control" placeholder="https://example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Başlangıç Tarihi</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bitiş Tarihi</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Aktif</label>
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

<!-- Edit Banner Modal -->
<div class="modal fade" id="editBannerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning"></i> Banner Düzenle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_banner">
                <input type="hidden" name="banner_id" id="edit_banner_id">
                <div class="modal-body" id="editBannerContent">
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
function editBanner(bannerId) {
    // Load banner data via AJAX
    fetch(`ajax/get_banner.php?id=${bannerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_banner_id').value = data.banner.id;
                document.getElementById('editBannerContent').innerHTML = generateEditForm(data.banner);
                new bootstrap.Modal(document.getElementById('editBannerModal')).show();
            }
        })
        .catch(error => console.error('Error:', error));
}

function generateEditForm(banner) {
    const positions = <?php echo json_encode($positions); ?>;
    
    return `
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label">Banner Başlığı *</label>
                    <input type="text" name="title" class="form-control" value="${banner.title}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Sıra *</label>
                    <input type="number" name="sort_order" class="form-control" value="${banner.sort_order}" min="0" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label">Banner Görseli URL *</label>
                    <input type="url" name="image_url" class="form-control" value="${banner.image_url}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Pozisyon *</label>
                    <select name="position" class="form-select" required>
                        <option value="">Pozisyon Seçin</option>
                        ${Object.entries(positions).map(([key, value]) => 
                            `<option value="${key}" ${key === banner.position ? 'selected' : ''}>${value}</option>`
                        ).join('')}
                    </select>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Hedef Link</label>
            <input type="url" name="link_url" class="form-control" value="${banner.link_url || ''}">
        </div>
        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <textarea name="description" class="form-control" rows="3">${banner.description || ''}</textarea>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Başlangıç Tarihi</label>
                    <input type="date" name="start_date" class="form-control" value="${banner.start_date || ''}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Bitiş Tarihi</label>
                    <input type="date" name="end_date" class="form-control" value="${banner.end_date || ''}">
                </div>
            </div>
        </div>
        <div class="form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active" ${banner.is_active ? 'checked' : ''}>
            <label class="form-check-label" for="edit_is_active">Aktif</label>
        </div>
    `;
}
</script>

<?php include 'includes/admin_footer.php'; ?>