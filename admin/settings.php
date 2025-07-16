<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Admin Settings
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Admin kontrolü
require_once 'includes/admin_auth.php';
requireAdminLogin();

// Sayfa değişkenleri
$pageTitle = 'Sistem Ayarları';

// Form gönderildi mi?
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!validateAdminCSRF($_POST['_token'] ?? '')) {
        $message = 'Güvenlik hatası. Lütfen sayfayı yenileyin.';
        $messageType = 'danger';
    } else {
        try {
            // Form verilerini al
            $settings = [
                'site_name' => sanitizeInput($_POST['site_name'] ?? ''),
                'site_description' => sanitizeInput($_POST['site_description'] ?? ''),
                'site_keywords' => sanitizeInput($_POST['site_keywords'] ?? ''),
                'site_url' => sanitizeInput($_POST['site_url'] ?? ''),
                'contact_email' => sanitizeInput($_POST['contact_email'] ?? ''),
                'contact_phone' => sanitizeInput($_POST['contact_phone'] ?? ''),
                'social_facebook' => sanitizeInput($_POST['social_facebook'] ?? ''),
                'social_twitter' => sanitizeInput($_POST['social_twitter'] ?? ''),
                'social_instagram' => sanitizeInput($_POST['social_instagram'] ?? ''),
                'social_telegram' => sanitizeInput($_POST['social_telegram'] ?? ''),
                'analytics_code' => $_POST['analytics_code'] ?? '',
                'popup_enabled' => isset($_POST['popup_enabled']) ? 1 : 0,
                'popup_title' => sanitizeInput($_POST['popup_title'] ?? ''),
                'popup_content' => $_POST['popup_content'] ?? '',
                'popup_delay' => (int)($_POST['popup_delay'] ?? 5),
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
                'maintenance_message' => $_POST['maintenance_message'] ?? '',
                'cache_enabled' => isset($_POST['cache_enabled']) ? 1 : 0,
                'cache_duration' => (int)($_POST['cache_duration'] ?? 3600),
                'smtp_host' => sanitizeInput($_POST['smtp_host'] ?? ''),
                'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
                'smtp_username' => sanitizeInput($_POST['smtp_username'] ?? ''),
                'smtp_password' => sanitizeInput($_POST['smtp_password'] ?? ''),
                'smtp_encryption' => sanitizeInput($_POST['smtp_encryption'] ?? 'tls'),
            ];
            
            // Ayarları güncelle
            foreach ($settings as $key => $value) {
                $existing = $db->fetchOne(
                    "SELECT id FROM settings WHERE setting_key = ?",
                    [$key]
                );
                
                if ($existing) {
                    $db->update('settings',
                        ['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
                        ['setting_key' => $key]
                    );
                } else {
                    $db->insert('settings', [
                        'setting_key' => $key,
                        'setting_value' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            // Log kaydı
            $db->insert('logs', [
                'admin_id' => $_SESSION['admin_id'],
                'action' => 'settings_update',
                'data' => json_encode(['updated_settings' => array_keys($settings)]),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $message = 'Ayarlar başarıyla güncellendi.';
            $messageType = 'success';
            
        } catch (Exception $e) {
            $message = 'Ayarlar güncellenirken hata oluştu: ' . $e->getMessage();
            $messageType = 'danger';
            error_log("Settings update error: " . $e->getMessage());
        }
    }
}

// Mevcut ayarları getir
$currentSettings = [];
$settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsData as $setting) {
    $currentSettings[$setting['setting_key']] = $setting['setting_value'];
}

// Varsayılan değerler
$defaultSettings = [
    'site_name' => 'BonusBoss',
    'site_description' => 'Türkiye\'nin en güvenilir casino deneme bonusu sitesi',
    'site_keywords' => 'casino bonus, deneme bonusu, bedava bonus',
    'site_url' => 'https://bonusboss.com',
    'contact_email' => 'info@bonusboss.com',
    'contact_phone' => '+90 (555) 123 45 67',
    'social_facebook' => '',
    'social_twitter' => '',
    'social_instagram' => '',
    'social_telegram' => '',
    'analytics_code' => '',
    'popup_enabled' => 1,
    'popup_title' => 'Hoş Geldiniz!',
    'popup_content' => 'En yüksek casino bonusları burada!',
    'popup_delay' => 5,
    'maintenance_mode' => 0,
    'maintenance_message' => 'Site bakımda. Kısa süre sonra tekrar deneyin.',
    'cache_enabled' => 1,
    'cache_duration' => 3600,
    'smtp_host' => '',
    'smtp_port' => 587,
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_encryption' => 'tls',
];

// Ayarları birleştir
$settings = array_merge($defaultSettings, $currentSettings);

include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">Sistem Ayarları</h1>
                <p class="page-subtitle">Site genel ayarlarını yönetin</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" onclick="$('#settingsForm').submit()">
                    <i class="fas fa-save"></i> Ayarları Kaydet
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Settings Form -->
    <form method="POST" id="settingsForm" data-autosave="true">
        <?php echo generateCSRFInput(); ?>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- General Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Genel Ayarlar
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Adı</label>
                                <input type="text" name="site_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site URL</label>
                                <input type="url" name="site_url" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['site_url']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Site Açıklaması</label>
                            <textarea name="site_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Anahtar Kelimeler</label>
                            <input type="text" name="site_keywords" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['site_keywords']); ?>"
                                   placeholder="casino bonus, deneme bonusu, bedava bonus">
                            <small class="form-text text-muted">Virgülle ayırın</small>
                        </div>
                    </div>
                </div>

                <!-- Contact Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-address-card me-2"></i>
                            İletişim Bilgileri
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="contact_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['contact_email']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefon</label>
                                <input type="text" name="contact_phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['contact_phone']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-share-alt me-2"></i>
                            Sosyal Medya
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fab fa-facebook text-primary"></i> Facebook
                                </label>
                                <input type="url" name="social_facebook" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['social_facebook']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fab fa-twitter text-info"></i> Twitter
                                </label>
                                <input type="url" name="social_twitter" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['social_twitter']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fab fa-instagram text-danger"></i> Instagram
                                </label>
                                <input type="url" name="social_instagram" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['social_instagram']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fab fa-telegram text-primary"></i> Telegram
                                </label>
                                <input type="url" name="social_telegram" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['social_telegram']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popup Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-window-restore me-2"></i>
                            Popup Ayarları
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" name="popup_enabled" class="form-check-input" 
                                   <?php echo $settings['popup_enabled'] ? 'checked' : ''; ?>>
                            <label class="form-check-label">Popup'ı Etkinleştir</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Popup Başlığı</label>
                            <input type="text" name="popup_title" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['popup_title']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Popup İçeriği</label>
                            <textarea name="popup_content" class="form-control" rows="4"><?php echo htmlspecialchars($settings['popup_content']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gecikme Süresi (saniye)</label>
                            <input type="number" name="popup_delay" class="form-control" min="1" max="60"
                                   value="<?php echo $settings['popup_delay']; ?>">
                        </div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Analytics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Google Analytics Kodu</label>
                            <textarea name="analytics_code" class="form-control" rows="5" 
                                      placeholder="<!-- Google Analytics kodu buraya -->"><?php echo htmlspecialchars($settings['analytics_code']); ?></textarea>
                            <small class="form-text text-muted">Google Analytics veya GTM kodunu buraya ekleyin</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- System Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-server me-2"></i>
                            Sistem Ayarları
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" name="maintenance_mode" class="form-check-input" 
                                   <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                            <label class="form-check-label">Bakım Modu</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bakım Mesajı</label>
                            <textarea name="maintenance_message" class="form-control" rows="3"><?php echo htmlspecialchars($settings['maintenance_message']); ?></textarea>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" name="cache_enabled" class="form-check-input" 
                                   <?php echo $settings['cache_enabled'] ? 'checked' : ''; ?>>
                            <label class="form-check-label">Cache Etkin</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Cache Süresi (saniye)</label>
                            <input type="number" name="cache_duration" class="form-control" min="300" max="86400"
                                   value="<?php echo $settings['cache_duration']; ?>">
                        </div>
                    </div>
                </div>

                <!-- SMTP Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            E-posta Ayarları (SMTP)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['smtp_host']); ?>"
                                   placeholder="smtp.gmail.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Port</label>
                            <input type="number" name="smtp_port" class="form-control" 
                                   value="<?php echo $settings['smtp_port']; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" name="smtp_username" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['smtp_username']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Şifre</label>
                            <input type="password" name="smtp_password" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['smtp_password']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Şifreleme</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="" <?php echo empty($settings['smtp_encryption']) ? 'selected' : ''; ?>>Yok</option>
                            </select>
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="testSMTP()">
                            <i class="fas fa-paper-plane"></i> Test E-postası Gönder
                        </button>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>
                            Hızlı İşlemler
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-danger" onclick="clearCache()">
                                <i class="fas fa-trash"></i> Cache Temizle
                            </button>
                            
                            <button type="button" class="btn btn-outline-warning" onclick="optimizeDatabase()">
                                <i class="fas fa-database"></i> Veritabanı Optimize Et
                            </button>
                            
                            <button type="button" class="btn btn-outline-info" onclick="exportSettings()">
                                <i class="fas fa-download"></i> Ayarları Dışa Aktar
                            </button>
                            
                            <input type="file" id="importFile" accept=".json" style="display: none;" onchange="importSettings()">
                            <button type="button" class="btn btn-outline-success" onclick="document.getElementById('importFile').click()">
                                <i class="fas fa-upload"></i> Ayarları İçe Aktar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript -->
<script>
function testSMTP() {
    $.ajax({
        url: 'includes/test_smtp.php',
        type: 'POST',
        data: $('#settingsForm').serialize(),
        success: function(response) {
            if (response.success) {
                showToast('Test e-postası başarıyla gönderildi', 'success');
            } else {
                showToast('E-posta gönderilemedi: ' + response.message, 'danger');
            }
        },
        error: function() {
            showToast('Test işlemi başarısız', 'danger');
        }
    });
}

function clearCache() {
    if (confirm('Cache temizlensin mi?')) {
        $.ajax({
            url: 'includes/clear_cache.php',
            type: 'POST',
            data: { _token: $('input[name="_token"]').val() },
            success: function(response) {
                if (response.success) {
                    showToast('Cache başarıyla temizlendi', 'success');
                } else {
                    showToast('Cache temizlenemedi', 'danger');
                }
            }
        });
    }
}

function optimizeDatabase() {
    if (confirm('Veritabanı optimize edilsin mi? Bu işlem birkaç dakika sürebilir.')) {
        $.ajax({
            url: 'includes/optimize_database.php',
            type: 'POST',
            data: { _token: $('input[name="_token"]').val() },
            success: function(response) {
                if (response.success) {
                    showToast('Veritabanı başarıyla optimize edildi', 'success');
                } else {
                    showToast('Optimizasyon başarısız', 'danger');
                }
            }
        });
    }
}

function exportSettings() {
    const formData = new FormData();
    formData.append('action', 'export');
    formData.append('_token', $('input[name="_token"]').val());
    
    fetch('includes/export_import_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'bonusboss_settings_' + new Date().toISOString().split('T')[0] + '.json';
        a.click();
        window.URL.revokeObjectURL(url);
    });
}

function importSettings() {
    const file = document.getElementById('importFile').files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('action', 'import');
    formData.append('settings_file', file);
    formData.append('_token', $('input[name="_token"]').val());
    
    fetch('includes/export_import_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Ayarlar başarıyla içe aktarıldı', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('İçe aktarma başarısız: ' + data.message, 'danger');
        }
    });
}

$(document).ready(function() {
    // Form validation
    $('#settingsForm').on('submit', function(e) {
        let isValid = true;
        
        // Required fields check
        $('input[required], textarea[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Email validation
        const email = $('input[name="contact_email"]').val();
        if (email && !adminUtils.validateEmail(email)) {
            isValid = false;
            $('input[name="contact_email"]').addClass('is-invalid');
        }
        
        // URL validation
        $('input[type="url"]').each(function() {
            const url = $(this).val();
            if (url && !adminUtils.validateURL(url)) {
                isValid = false;
                $(this).addClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showToast('Lütfen gerekli alanları doğru şekilde doldurun', 'warning');
        }
    });
    
    // Auto-generate slug from title
    $('input[name="site_name"]').on('input', function() {
        // Auto-update other fields if needed
    });
});
</script>

<?php include 'includes/admin_footer.php'; ?>