<?php
require_once 'includes/admin_auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check admin permissions
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] == 'Viewer') {
    header('Location: login.php');
    exit;
}

$page_title = 'Bonus Yönetimi';
$current_page = 'bonus-yonetimi';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_bonus':
                $site_id = (int)$_POST['site_id'];
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $bonus_type = sanitizeInput($_POST['bonus_type']);
                $bonus_amount = sanitizeInput($_POST['bonus_amount']);
                $category_id = (int)$_POST['category_id'];
                $wager_requirement = sanitizeInput($_POST['wager_requirement']);
                $min_deposit = sanitizeInput($_POST['min_deposit']);
                $max_bonus = sanitizeInput($_POST['max_bonus']);
                $terms = sanitizeInput($_POST['terms']);
                $expiry_date = $_POST['expiry_date'];
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                $stmt = $pdo->prepare("INSERT INTO bonuses (site_id, title, description, bonus_type, bonus_amount, category_id, wager_requirement, min_deposit, max_bonus, terms, expiry_date, is_featured, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                if ($stmt->execute([$site_id, $title, $description, $bonus_type, $bonus_amount, $category_id, $wager_requirement, $min_deposit, $max_bonus, $terms, $expiry_date, $is_featured, $is_active])) {
                    $success_message = "Bonus başarıyla eklendi!";
                } else {
                    $error_message = "Bonus eklenirken hata oluştu!";
                }
                break;
                
            case 'update_bonus':
                $bonus_id = (int)$_POST['bonus_id'];
                $site_id = (int)$_POST['site_id'];
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $bonus_type = sanitizeInput($_POST['bonus_type']);
                $bonus_amount = sanitizeInput($_POST['bonus_amount']);
                $category_id = (int)$_POST['category_id'];
                $wager_requirement = sanitizeInput($_POST['wager_requirement']);
                $min_deposit = sanitizeInput($_POST['min_deposit']);
                $max_bonus = sanitizeInput($_POST['max_bonus']);
                $terms = sanitizeInput($_POST['terms']);
                $expiry_date = $_POST['expiry_date'];
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                $stmt = $pdo->prepare("UPDATE bonuses SET site_id=?, title=?, description=?, bonus_type=?, bonus_amount=?, category_id=?, wager_requirement=?, min_deposit=?, max_bonus=?, terms=?, expiry_date=?, is_featured=?, is_active=?, updated_at=NOW() WHERE id=?");
                if ($stmt->execute([$site_id, $title, $description, $bonus_type, $bonus_amount, $category_id, $wager_requirement, $min_deposit, $max_bonus, $terms, $expiry_date, $is_featured, $is_active, $bonus_id])) {
                    $success_message = "Bonus başarıyla güncellendi!";
                } else {
                    $error_message = "Bonus güncellenirken hata oluştu!";
                }
                break;
                
            case 'delete_bonus':
                $bonus_id = (int)$_POST['bonus_id'];
                $stmt = $pdo->prepare("DELETE FROM bonuses WHERE id = ?");
                if ($stmt->execute([$bonus_id])) {
                    $success_message = "Bonus başarıyla silindi!";
                } else {
                    $error_message = "Bonus silinirken hata oluştu!";
                }
                break;
                
            case 'toggle_status':
                $bonus_id = (int)$_POST['bonus_id'];
                $stmt = $pdo->prepare("UPDATE bonuses SET is_active = NOT is_active WHERE id = ?");
                if ($stmt->execute([$bonus_id])) {
                    $success_message = "Bonus durumu güncellendi!";
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

if (isset($_GET['site_id']) && !empty($_GET['site_id'])) {
    $where_conditions[] = "b.site_id = ?";
    $params[] = (int)$_GET['site_id'];
}

if (isset($_GET['bonus_type']) && !empty($_GET['bonus_type'])) {
    $where_conditions[] = "b.bonus_type = ?";
    $params[] = $_GET['bonus_type'];
}

if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $where_conditions[] = "b.category_id = ?";
    $params[] = (int)$_GET['category_id'];
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_conditions[] = "b.is_active = ?";
    $params[] = (int)$_GET['status'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(b.title LIKE ? OR b.description LIKE ?)";
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM bonuses b $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get bonuses
$sql = "SELECT b.*, s.name as site_name, c.name as category_name 
        FROM bonuses b 
        LEFT JOIN sites s ON b.site_id = s.id 
        LEFT JOIN categories c ON b.category_id = c.id 
        $where_clause 
        ORDER BY b.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bonuses = $stmt->fetchAll();

// Get sites for dropdown
$sites_stmt = $pdo->query("SELECT id, name FROM sites WHERE is_active = 1 ORDER BY name");
$sites = $sites_stmt->fetchAll();

// Get categories for dropdown
$categories_stmt = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $categories_stmt->fetchAll();

include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-gift text-primary"></i> Bonus Yönetimi
        </h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBonusModal">
            <i class="fas fa-plus"></i> Yeni Bonus Ekle
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

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtreler
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-2">
                    <select name="site_id" class="form-select">
                        <option value="">Tüm Siteler</option>
                        <?php foreach ($sites as $site): ?>
                        <option value="<?php echo $site['id']; ?>" <?php echo (isset($_GET['site_id']) && $_GET['site_id'] == $site['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($site['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="bonus_type" class="form-select">
                        <option value="">Tüm Bonus Türleri</option>
                        <option value="Hoşgeldin Bonusu" <?php echo (isset($_GET['bonus_type']) && $_GET['bonus_type'] == 'Hoşgeldin Bonusu') ? 'selected' : ''; ?>>Hoşgeldin Bonusu</option>
                        <option value="Deneme Bonusu" <?php echo (isset($_GET['bonus_type']) && $_GET['bonus_type'] == 'Deneme Bonusu') ? 'selected' : ''; ?>>Deneme Bonusu</option>
                        <option value="Yatırım Bonusu" <?php echo (isset($_GET['bonus_type']) && $_GET['bonus_type'] == 'Yatırım Bonusu') ? 'selected' : ''; ?>>Yatırım Bonusu</option>
                        <option value="Freespin" <?php echo (isset($_GET['bonus_type']) && $_GET['bonus_type'] == 'Freespin') ? 'selected' : ''; ?>>Freespin</option>
                        <option value="Cashback" <?php echo (isset($_GET['bonus_type']) && $_GET['bonus_type'] == 'Cashback') ? 'selected' : ''; ?>>Cashback</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select">
                        <option value="">Tüm Kategoriler</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
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
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Bonus ara..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bonuses Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Bonuslar (<?php echo $total_records; ?> adet)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Site</th>
                            <th>Başlık</th>
                            <th>Tür</th>
                            <th>Miktar</th>
                            <th>Kategori</th>
                            <th>Öne Çıkan</th>
                            <th>Durum</th>
                            <th>Oluşturulma</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bonuses)): ?>
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Henüz bonus eklenmemiş.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($bonuses as $bonus): ?>
                        <tr>
                            <td><?php echo $bonus['id']; ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo htmlspecialchars($bonus['site_name']); ?></span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($bonus['title']); ?></strong>
                                <?php if ($bonus['is_featured']): ?>
                                <i class="fas fa-star text-warning ms-1" title="Öne Çıkan"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($bonus['bonus_type']); ?></td>
                            <td>
                                <span class="badge bg-success"><?php echo htmlspecialchars($bonus['bonus_amount']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($bonus['category_name']); ?></td>
                            <td>
                                <?php if ($bonus['is_featured']): ?>
                                <span class="badge bg-warning">Evet</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Hayır</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($bonus['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($bonus['created_at'])); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editBonus(<?php echo $bonus['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Durumu değiştirmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="bonus_id" value="<?php echo $bonus['id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $bonus['is_active'] ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                                            <i class="fas <?php echo $bonus['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu bonusu silmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="delete_bonus">
                                        <input type="hidden" name="bonus_id" value="<?php echo $bonus['id']; ?>">
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
            <nav aria-label="Bonus pagination">
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

<!-- Add Bonus Modal -->
<div class="modal fade" id="addBonusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-primary"></i> Yeni Bonus Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_bonus">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Site *</label>
                                <select name="site_id" class="form-select" required>
                                    <option value="">Site Seçin</option>
                                    <?php foreach ($sites as $site): ?>
                                    <option value="<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kategori *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Kategori Seçin</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Başlık *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bonus Türü *</label>
                                <select name="bonus_type" class="form-select" required>
                                    <option value="">Tür Seçin</option>
                                    <option value="Hoşgeldin Bonusu">Hoşgeldin Bonusu</option>
                                    <option value="Deneme Bonusu">Deneme Bonusu</option>
                                    <option value="Yatırım Bonusu">Yatırım Bonusu</option>
                                    <option value="Freespin">Freespin</option>
                                    <option value="Cashback">Cashback</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bonus Miktarı *</label>
                                <input type="text" name="bonus_amount" class="form-control" placeholder="ör: 100₺, %100, 50 FS" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Çevrim Şartı</label>
                                <input type="text" name="wager_requirement" class="form-control" placeholder="ör: 40x">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Min. Yatırım</label>
                                <input type="text" name="min_deposit" class="form-control" placeholder="ör: 100₺">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Maks. Bonus</label>
                                <input type="text" name="max_bonus" class="form-control" placeholder="ör: 1000₺">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şartlar ve Koşullar</label>
                        <textarea name="terms" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Son Geçerlilik Tarihi</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured">
                                <label class="form-check-label" for="is_featured">Öne Çıkan Bonus</label>
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

<!-- Edit Bonus Modal -->
<div class="modal fade" id="editBonusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning"></i> Bonus Düzenle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_bonus">
                <input type="hidden" name="bonus_id" id="edit_bonus_id">
                <div class="modal-body" id="editBonusContent">
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
function editBonus(bonusId) {
    // Load bonus data via AJAX
    fetch(`ajax/get_bonus.php?id=${bonusId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_bonus_id').value = data.bonus.id;
                document.getElementById('editBonusContent').innerHTML = generateEditForm(data.bonus, data.sites, data.categories);
                new bootstrap.Modal(document.getElementById('editBonusModal')).show();
            }
        })
        .catch(error => console.error('Error:', error));
}

function generateEditForm(bonus, sites, categories) {
    return `
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Site *</label>
                    <select name="site_id" class="form-select" required>
                        <option value="">Site Seçin</option>
                        ${sites.map(site => 
                            `<option value="${site.id}" ${site.id == bonus.site_id ? 'selected' : ''}>${site.name}</option>`
                        ).join('')}
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Kategori *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Kategori Seçin</option>
                        ${categories.map(category => 
                            `<option value="${category.id}" ${category.id == bonus.category_id ? 'selected' : ''}>${category.name}</option>`
                        ).join('')}
                    </select>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Başlık *</label>
            <input type="text" name="title" class="form-control" value="${bonus.title}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <textarea name="description" class="form-control" rows="3">${bonus.description || ''}</textarea>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Bonus Türü *</label>
                    <select name="bonus_type" class="form-select" required>
                        <option value="">Tür Seçin</option>
                        <option value="Hoşgeldin Bonusu" ${bonus.bonus_type == 'Hoşgeldin Bonusu' ? 'selected' : ''}>Hoşgeldin Bonusu</option>
                        <option value="Deneme Bonusu" ${bonus.bonus_type == 'Deneme Bonusu' ? 'selected' : ''}>Deneme Bonusu</option>
                        <option value="Yatırım Bonusu" ${bonus.bonus_type == 'Yatırım Bonusu' ? 'selected' : ''}>Yatırım Bonusu</option>
                        <option value="Freespin" ${bonus.bonus_type == 'Freespin' ? 'selected' : ''}>Freespin</option>
                        <option value="Cashback" ${bonus.bonus_type == 'Cashback' ? 'selected' : ''}>Cashback</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Bonus Miktarı *</label>
                    <input type="text" name="bonus_amount" class="form-control" value="${bonus.bonus_amount}" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Çevrim Şartı</label>
                    <input type="text" name="wager_requirement" class="form-control" value="${bonus.wager_requirement || ''}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Min. Yatırım</label>
                    <input type="text" name="min_deposit" class="form-control" value="${bonus.min_deposit || ''}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Maks. Bonus</label>
                    <input type="text" name="max_bonus" class="form-control" value="${bonus.max_bonus || ''}">
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Şartlar ve Koşullar</label>
            <textarea name="terms" class="form-control" rows="3">${bonus.terms || ''}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Son Geçerlilik Tarihi</label>
            <input type="date" name="expiry_date" class="form-control" value="${bonus.expiry_date || ''}">
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" name="is_featured" class="form-check-input" id="edit_is_featured" ${bonus.is_featured ? 'checked' : ''}>
                    <label class="form-check-label" for="edit_is_featured">Öne Çıkan Bonus</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active" ${bonus.is_active ? 'checked' : ''}>
                    <label class="form-check-label" for="edit_is_active">Aktif</label>
                </div>
            </div>
        </div>
    `;
}
</script>

<?php include 'includes/admin_footer.php'; ?>