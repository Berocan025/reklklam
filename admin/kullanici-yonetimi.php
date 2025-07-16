<?php
require_once 'includes/admin_auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check admin permissions - only Super Admin and Admin can manage users
if (!isset($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'], ['Super Admin', 'Admin'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Kullanıcı Yönetimi';
$current_page = 'kullanici-yonetimi';

// User roles
$roles = [
    'Super Admin' => 'Süper Admin',
    'Admin' => 'Admin', 
    'Editor' => 'Editör',
    'Viewer' => 'Görüntüleyici'
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $username = sanitizeInput($_POST['username']);
                $email = sanitizeInput($_POST['email']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $full_name = sanitizeInput($_POST['full_name']);
                $role = sanitizeInput($_POST['role']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Check if username or email exists
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ? OR email = ?");
                $check_stmt->execute([$username, $email]);
                if ($check_stmt->fetchColumn() > 0) {
                    $error_message = "Bu kullanıcı adı veya e-posta zaten kullanılıyor!";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password, full_name, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    if ($stmt->execute([$username, $email, $password, $full_name, $role, $is_active])) {
                        $success_message = "Kullanıcı başarıyla eklendi!";
                    } else {
                        $error_message = "Kullanıcı eklenirken hata oluştu!";
                    }
                }
                break;
                
            case 'update_user':
                $user_id = (int)$_POST['user_id'];
                $username = sanitizeInput($_POST['username']);
                $email = sanitizeInput($_POST['email']);
                $full_name = sanitizeInput($_POST['full_name']);
                $role = sanitizeInput($_POST['role']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Check if username or email exists for other users
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE (username = ? OR email = ?) AND id != ?");
                $check_stmt->execute([$username, $email, $user_id]);
                if ($check_stmt->fetchColumn() > 0) {
                    $error_message = "Bu kullanıcı adı veya e-posta zaten kullanılıyor!";
                } else {
                    // Update password if provided
                    if (!empty($_POST['password'])) {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE admin_users SET username=?, email=?, password=?, full_name=?, role=?, is_active=?, updated_at=NOW() WHERE id=?");
                        $params = [$username, $email, $password, $full_name, $role, $is_active, $user_id];
                    } else {
                        $stmt = $pdo->prepare("UPDATE admin_users SET username=?, email=?, full_name=?, role=?, is_active=?, updated_at=NOW() WHERE id=?");
                        $params = [$username, $email, $full_name, $role, $is_active, $user_id];
                    }
                    
                    if ($stmt->execute($params)) {
                        $success_message = "Kullanıcı başarıyla güncellendi!";
                    } else {
                        $error_message = "Kullanıcı güncellenirken hata oluştu!";
                    }
                }
                break;
                
            case 'delete_user':
                $user_id = (int)$_POST['user_id'];
                
                // Prevent deleting own account
                if ($user_id == $_SESSION['admin_id']) {
                    $error_message = "Kendi hesabınızı silemezsiniz!";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
                    if ($stmt->execute([$user_id])) {
                        $success_message = "Kullanıcı başarıyla silindi!";
                    } else {
                        $error_message = "Kullanıcı silinirken hata oluştu!";
                    }
                }
                break;
                
            case 'toggle_status':
                $user_id = (int)$_POST['user_id'];
                
                // Prevent deactivating own account
                if ($user_id == $_SESSION['admin_id']) {
                    $error_message = "Kendi hesabınızın durumunu değiştiremezsiniz!";
                } else {
                    $stmt = $pdo->prepare("UPDATE admin_users SET is_active = NOT is_active WHERE id = ?");
                    if ($stmt->execute([$user_id])) {
                        $success_message = "Kullanıcı durumu güncellendi!";
                    } else {
                        $error_message = "Durum güncellenirken hata oluştu!";
                    }
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

if (isset($_GET['role']) && !empty($_GET['role'])) {
    $where_conditions[] = "role = ?";
    $params[] = $_GET['role'];
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_conditions[] = "is_active = ?";
    $params[] = (int)$_GET['status'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM admin_users $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get users
$sql = "SELECT * FROM admin_users 
        $where_clause 
        ORDER BY created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users text-primary"></i> Kullanıcı Yönetimi
        </h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus"></i> Yeni Kullanıcı Ekle
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
            COUNT(*) as total_users,
            SUM(is_active) as active_users,
            COUNT(DISTINCT role) as roles_count
            FROM admin_users");
        $stats = $stats_stmt->fetch();
        
        $online_stmt = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) AND is_active = 1");
        $online_users = $online_stmt->fetchColumn();
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Kullanıcı</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aktif Kullanıcı</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_users']; ?></div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Çevrimiçi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $online_users; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Rol Sayısı</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['roles_count']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tag fa-2x text-gray-300"></i>
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
                    <select name="role" class="form-select">
                        <option value="">Tüm Roller</option>
                        <?php foreach ($roles as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($_GET['role']) && $_GET['role'] == $key) ? 'selected' : ''; ?>>
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
                    <input type="text" name="search" class="form-control" placeholder="Kullanıcı ara..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Kullanıcılar (<?php echo $total_records; ?> adet)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı Adı</th>
                            <th>Ad Soyad</th>
                            <th>E-posta</th>
                            <th>Rol</th>
                            <th>Son Giriş</th>
                            <th>Durum</th>
                            <th>Kayıt Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Henüz kullanıcı eklenmemiş.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                <span class="badge bg-info ms-1">Ben</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                $role_classes = [
                                    'Super Admin' => 'bg-danger',
                                    'Admin' => 'bg-warning',
                                    'Editor' => 'bg-info',
                                    'Viewer' => 'bg-secondary'
                                ];
                                $role_class = $role_classes[$user['role']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?php echo $role_class; ?>"><?php echo $roles[$user['role']] ?? $user['role']; ?></span>
                            </td>
                            <td>
                                <?php if ($user['last_login_at']): ?>
                                <span title="<?php echo date('d.m.Y H:i:s', strtotime($user['last_login_at'])); ?>">
                                    <?php echo timeAgo($user['last_login_at']); ?>
                                </span>
                                <?php if (strtotime($user['last_login_at']) >= strtotime('-30 minutes')): ?>
                                <i class="fas fa-circle text-success fa-xs ms-1" title="Çevrimiçi"></i>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">Hiç giriş yapmamış</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Durumu değiştirmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $user['is_active'] ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                                            <i class="fas <?php echo $user['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
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
            <nav aria-label="User pagination">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus text-primary"></i> Yeni Kullanıcı Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kullanıcı Adı *</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">E-posta *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Şifre *</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rol *</label>
                                <select name="role" class="form-select" required>
                                    <option value="">Rol Seçin</option>
                                    <?php foreach ($roles as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
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

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning"></i> Kullanıcı Düzenle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body" id="editUserContent">
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
function editUser(userId) {
    // Find user data from the current page
    const users = <?php echo json_encode($users); ?>;
    const user = users.find(u => u.id == userId);
    
    if (user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('editUserContent').innerHTML = generateEditForm(user);
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }
}

function generateEditForm(user) {
    const roles = <?php echo json_encode($roles); ?>;
    
    return `
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Kullanıcı Adı *</label>
                    <input type="text" name="username" class="form-control" value="${user.username}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">E-posta *</label>
                    <input type="email" name="email" class="form-control" value="${user.email}" required>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Ad Soyad *</label>
            <input type="text" name="full_name" class="form-control" value="${user.full_name}" required>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Yeni Şifre</label>
                    <input type="password" name="password" class="form-control" minlength="6">
                    <div class="form-text">Boş bırakırsanız şifre değişmez</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Rol *</label>
                    <select name="role" class="form-select" required>
                        <option value="">Rol Seçin</option>
                        ${Object.entries(roles).map(([key, value]) => 
                            `<option value="${key}" ${key === user.role ? 'selected' : ''}>${value}</option>`
                        ).join('')}
                    </select>
                </div>
            </div>
        </div>
        <div class="form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active" ${user.is_active == 1 ? 'checked' : ''}>
            <label class="form-check-label" for="edit_is_active">Aktif</label>
        </div>
    `;
}
</script>

<?php include 'includes/admin_footer.php'; ?>