<?php
require_once 'includes/admin_auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check admin permissions
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] == 'Viewer') {
    header('Location: login.php');
    exit;
}

$page_title = 'Kategori Yönetimi';
$current_page = 'kategori-yonetimi';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_category':
                $name = sanitizeInput($_POST['name']);
                $slug = sanitizeInput($_POST['slug']);
                $description = sanitizeInput($_POST['description']);
                $color = sanitizeInput($_POST['color']);
                $icon = sanitizeInput($_POST['icon']);
                $sort_order = (int)$_POST['sort_order'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Check if slug exists
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
                $check_stmt->execute([$slug]);
                if ($check_stmt->fetchColumn() > 0) {
                    $error_message = "Bu slug zaten kullanılıyor!";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, color, icon, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    if ($stmt->execute([$name, $slug, $description, $color, $icon, $sort_order, $is_active])) {
                        $success_message = "Kategori başarıyla eklendi!";
                    } else {
                        $error_message = "Kategori eklenirken hata oluştu!";
                    }
                }
                break;
                
            case 'update_category':
                $category_id = (int)$_POST['category_id'];
                $name = sanitizeInput($_POST['name']);
                $slug = sanitizeInput($_POST['slug']);
                $description = sanitizeInput($_POST['description']);
                $color = sanitizeInput($_POST['color']);
                $icon = sanitizeInput($_POST['icon']);
                $sort_order = (int)$_POST['sort_order'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Check if slug exists for other categories
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?");
                $check_stmt->execute([$slug, $category_id]);
                if ($check_stmt->fetchColumn() > 0) {
                    $error_message = "Bu slug zaten kullanılıyor!";
                } else {
                    $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, color=?, icon=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?");
                    if ($stmt->execute([$name, $slug, $description, $color, $icon, $sort_order, $is_active, $category_id])) {
                        $success_message = "Kategori başarıyla güncellendi!";
                    } else {
                        $error_message = "Kategori güncellenirken hata oluştu!";
                    }
                }
                break;
                
            case 'delete_category':
                $category_id = (int)$_POST['category_id'];
                // Check if category has bonuses
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM bonuses WHERE category_id = ?");
                $check_stmt->execute([$category_id]);
                $bonus_count = $check_stmt->fetchColumn();
                
                if ($bonus_count > 0) {
                    $error_message = "Bu kategori silinemez çünkü $bonus_count adet bonusu bulunmaktadır!";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    if ($stmt->execute([$category_id])) {
                        $success_message = "Kategori başarıyla silindi!";
                    } else {
                        $error_message = "Kategori silinirken hata oluştu!";
                    }
                }
                break;
                
            case 'toggle_status':
                $category_id = (int)$_POST['category_id'];
                $stmt = $pdo->prepare("UPDATE categories SET is_active = NOT is_active WHERE id = ?");
                if ($stmt->execute([$category_id])) {
                    $success_message = "Kategori durumu güncellendi!";
                } else {
                    $error_message = "Durum güncellenirken hata oluştu!";
                }
                break;
        }
    }
}

// Get categories with bonus count
$stmt = $pdo->query("SELECT c.*, 
    (SELECT COUNT(*) FROM bonuses b WHERE b.category_id = c.id) as bonus_count
    FROM categories c 
    ORDER BY c.sort_order ASC, c.name ASC");
$categories = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tags text-primary"></i> Kategori Yönetimi
        </h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus"></i> Yeni Kategori Ekle
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
            COUNT(*) as total_categories,
            SUM(is_active) as active_categories
            FROM categories");
        $stats = $stats_stmt->fetch();
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Kategori</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_categories']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aktif Kategori</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_categories']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Card -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Kategoriler (<?php echo count($categories); ?> adet)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sıra</th>
                            <th>Kategori</th>
                            <th>Slug</th>
                            <th>Açıklama</th>
                            <th>Bonus Sayısı</th>
                            <th>Durum</th>
                            <th>Oluşturulma</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Henüz kategori eklenmemiş.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $category['sort_order']; ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($category['icon']): ?>
                                    <i class="<?php echo htmlspecialchars($category['icon']); ?> me-2" style="color: <?php echo htmlspecialchars($category['color']); ?>"></i>
                                    <?php endif; ?>
                                    <span style="color: <?php echo htmlspecialchars($category['color']); ?>">
                                        <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <code><?php echo htmlspecialchars($category['slug']); ?></code>
                            </td>
                            <td>
                                <?php if ($category['description']): ?>
                                <span title="<?php echo htmlspecialchars($category['description']); ?>">
                                    <?php echo substr(htmlspecialchars($category['description']), 0, 50); ?>
                                    <?php if (strlen($category['description']) > 50): ?>...<?php endif; ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo $category['bonus_count']; ?></span>
                            </td>
                            <td>
                                <?php if ($category['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($category['created_at'])); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo $category['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Durumu değiştirmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $category['is_active'] ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                                            <i class="fas <?php echo $category['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
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
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-primary"></i> Yeni Kategori Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_category">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Kategori Adı *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Sıra *</label>
                                <input type="number" name="sort_order" class="form-control" value="0" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug *</label>
                        <input type="text" name="slug" class="form-control" required>
                        <div class="form-text">URL'de kullanılacak kısa isim (örn: deneme-bonusu)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Renk</label>
                                <input type="color" name="color" class="form-control form-control-color" value="#6c757d">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">İkon (FontAwesome)</label>
                                <input type="text" name="icon" class="form-control" placeholder="fas fa-gift">
                                <div class="form-text">Örn: fas fa-gift, far fa-star</div>
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

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning"></i> Kategori Düzenle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_category">
                <input type="hidden" name="category_id" id="edit_category_id">
                <div class="modal-body" id="editCategoryContent">
                    <!-- Content will be loaded via JavaScript -->
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
// Auto-generate slug from name
document.querySelector('input[name="name"]').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .replace(/ş/g, 's')
        .replace(/ğ/g, 'g')
        .replace(/ı/g, 'i')
        .replace(/ö/g, 'o')
        .replace(/ü/g, 'u')
        .replace(/ç/g, 'c')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    
    document.querySelector('input[name="slug"]').value = slug;
});

function editCategory(categoryId) {
    // Find category data from the current page
    const categories = <?php echo json_encode($categories); ?>;
    const category = categories.find(c => c.id == categoryId);
    
    if (category) {
        document.getElementById('edit_category_id').value = category.id;
        document.getElementById('editCategoryContent').innerHTML = generateEditForm(category);
        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        
        // Add event listener for edit form slug generation
        document.querySelector('#editCategoryContent input[name="name"]').addEventListener('input', function() {
            const slug = this.value
                .toLowerCase()
                .replace(/ş/g, 's')
                .replace(/ğ/g, 'g')
                .replace(/ı/g, 'i')
                .replace(/ö/g, 'o')
                .replace(/ü/g, 'u')
                .replace(/ç/g, 'c')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            
            document.querySelector('#editCategoryContent input[name="slug"]').value = slug;
        });
    }
}

function generateEditForm(category) {
    return `
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label">Kategori Adı *</label>
                    <input type="text" name="name" class="form-control" value="${category.name}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Sıra *</label>
                    <input type="number" name="sort_order" class="form-control" value="${category.sort_order}" min="0" required>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Slug *</label>
            <input type="text" name="slug" class="form-control" value="${category.slug}" required>
            <div class="form-text">URL'de kullanılacak kısa isim (örn: deneme-bonusu)</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Açıklama</label>
            <textarea name="description" class="form-control" rows="3">${category.description || ''}</textarea>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Renk</label>
                    <input type="color" name="color" class="form-control form-control-color" value="${category.color || '#6c757d'}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">İkon (FontAwesome)</label>
                    <input type="text" name="icon" class="form-control" value="${category.icon || ''}" placeholder="fas fa-gift">
                    <div class="form-text">Örn: fas fa-gift, far fa-star</div>
                </div>
            </div>
        </div>
        <div class="form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active" ${category.is_active ? 'checked' : ''}>
            <label class="form-check-label" for="edit_is_active">Aktif</label>
        </div>
    `;
}
</script>

<?php include 'includes/admin_footer.php'; ?>