<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * İletişim Sayfası
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Sayfa değişkenleri
$pageTitle = 'İletişim';
$metaDescription = 'BonusBoss ile iletişime geçin. Sorularınız, önerileriniz veya işbirliği teklifleriniz için bizimle iletişime geçebilirsiniz.';
$metaKeywords = 'iletişim, bonusboss, casino bonus, destek, müşteri hizmetleri';
$bodyClass = 'contact-page';

// Include necessary files
require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/security.php';

// Header dahil et
include 'includes/header.php';

// Form gönderildi mi?
$formSent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!validateCSRFToken($_POST['_token'] ?? '')) {
        $errors[] = 'Güvenlik hatası. Lütfen sayfayı yenileyin.';
    } else {
        // Form verilerini al ve temizle
        $name = trim(sanitizeInput($_POST['name'] ?? ''));
        $email = trim(sanitizeInput($_POST['email'] ?? ''));
        $subject = trim(sanitizeInput($_POST['subject'] ?? ''));
        $message = trim(sanitizeInput($_POST['message'] ?? ''));
        $phone = trim(sanitizeInput($_POST['phone'] ?? ''));
        
        // Validasyon
        if (empty($name)) {
            $errors[] = 'Ad Soyad zorunludur.';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi giriniz.';
        }
        
        if (empty($subject)) {
            $errors[] = 'Konu zorunludur.';
        }
        
        if (empty($message)) {
            $errors[] = 'Mesaj zorunludur.';
        }
        
        if (strlen($message) < 10) {
            $errors[] = 'Mesaj en az 10 karakter olmalıdır.';
        }
        
        // Spam kontrolü
        if (!empty($_POST['website'])) { // Honeypot field
            $errors[] = 'Spam tespit edildi.';
        }
        
        // Rate limiting
        $clientIP = $_SERVER['REMOTE_ADDR'];
        $recentSubmissions = $db->fetchColumn(
            "SELECT COUNT(*) FROM logs 
             WHERE action = 'contact_form' AND ip_address = ? 
             AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$clientIP]
        );
        
        if ($recentSubmissions >= 3) {
            $errors[] = 'Çok fazla mesaj gönderildi. Lütfen bir saat sonra tekrar deneyin.';
        }
        
        // Hata yoksa kaydet ve e-posta gönder
        if (empty($errors)) {
            try {
                // Veritabanına kaydet (logs tablosunu kullanıyoruz)
                $db->insert('logs', [
                    'action' => 'contact_form',
                    'data' => json_encode([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'subject' => $subject,
                        'message' => $message
                    ]),
                    'ip_address' => $clientIP,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                // E-posta gönder (gerçek uygulamada mail() veya PHPMailer kullanın)
                $adminEmail = getSetting('contact_email', 'info@bonusboss.com');
                $emailSubject = "İletişim Formu: " . $subject;
                $emailBody = "
                    Yeni İletişim Formu Mesajı
                    
                    Ad Soyad: {$name}
                    E-posta: {$email}
                    Telefon: {$phone}
                    Konu: {$subject}
                    
                    Mesaj:
                    {$message}
                    
                    IP Adresi: {$clientIP}
                    Tarih: " . date('d.m.Y H:i:s') . "
                ";
                
                // mail($adminEmail, $emailSubject, $emailBody);
                
                $formSent = true;
                
                // Analytics kaydet
                trackEvent('contact_form_submit', 'form', 'contact');
                
            } catch (Exception $e) {
                $errors[] = 'Mesaj gönderilemedi. Lütfen tekrar deneyin.';
                error_log("Contact form error: " . $e->getMessage());
            }
        }
    }
}
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <?php echo createBreadcrumb([
            ['title' => 'Ana Sayfa', 'url' => seoUrl('/')],
            ['title' => 'İletişim', 'url' => '']
        ]); ?>
    </div>
</div>

<!-- Page Header -->
<section class="page-header py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title mb-3">
                    <i class="fas fa-envelope"></i>
                    İletişim
                </h1>
                <p class="page-description lead">
                    Bizimle iletişime geçin. Sorularınızı yanıtlamaktan mutluluk duyarız.
                </p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="contact-stats">
                    <div class="stat-item">
                        <i class="fas fa-clock fa-2x"></i>
                        <div class="stat-text">
                            <strong>7/24</strong><br>
                            <small>Destek Hizmeti</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info -->
<section class="contact-info-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-info-card text-center">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h5>E-posta</h5>
                    <p><?php echo getSetting('contact_email', 'info@bonusboss.com'); ?></p>
                    <a href="mailto:<?php echo getSetting('contact_email'); ?>" class="btn btn-primary btn-sm">
                        E-posta Gönder
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-info-card text-center">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h5>Telefon</h5>
                    <p><?php echo getSetting('contact_phone', '+90 (555) 123 45 67'); ?></p>
                    <a href="tel:<?php echo getSetting('contact_phone'); ?>" class="btn btn-primary btn-sm">
                        Ara
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-info-card text-center">
                    <div class="contact-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h5>WhatsApp</h5>
                    <p>+90 (555) 123 45 67</p>
                    <a href="https://wa.me/905551234567" class="btn btn-success btn-sm" target="_blank">
                        WhatsApp
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-info-card text-center">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5>Çalışma Saatleri</h5>
                    <p>7/24 Online Destek</p>
                    <span class="badge bg-success">Şu anda Aktif</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form -->
<section class="contact-form-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="contact-form-card">
                    <h3 class="form-title mb-4">
                        <i class="fas fa-paper-plane"></i>
                        Mesaj Gönder
                    </h3>
                    
                    <?php if ($formSent): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Mesajınız başarıyla gönderildi!</strong><br>
                            En kısa sürede size dönüş yapacağız. Teşekkür ederiz.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Hata:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$formSent): ?>
                    <form method="POST" id="contactForm" class="contact-form">
                        <?php echo generateCSRFInput(); ?>
                        
                        <!-- Honeypot field -->
                        <input type="text" name="website" style="display: none;" tabindex="-1">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ad Soyad *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">E-posta *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefon</label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konu *</label>
                                <select name="subject" class="form-select" required>
                                    <option value="">Konu seçin</option>
                                    <option value="Genel Bilgi" <?php echo ($_POST['subject'] ?? '') === 'Genel Bilgi' ? 'selected' : ''; ?>>
                                        Genel Bilgi
                                    </option>
                                    <option value="Bonus Sorunu" <?php echo ($_POST['subject'] ?? '') === 'Bonus Sorunu' ? 'selected' : ''; ?>>
                                        Bonus Sorunu
                                    </option>
                                    <option value="Site Ekleme" <?php echo ($_POST['subject'] ?? '') === 'Site Ekleme' ? 'selected' : ''; ?>>
                                        Site Ekleme Talebi
                                    </option>
                                    <option value="Teknik Destek" <?php echo ($_POST['subject'] ?? '') === 'Teknik Destek' ? 'selected' : ''; ?>>
                                        Teknik Destek
                                    </option>
                                    <option value="İşbirliği" <?php echo ($_POST['subject'] ?? '') === 'İşbirliği' ? 'selected' : ''; ?>>
                                        İşbirliği Teklifi
                                    </option>
                                    <option value="Şikayet" <?php echo ($_POST['subject'] ?? '') === 'Şikayet' ? 'selected' : ''; ?>>
                                        Şikayet
                                    </option>
                                    <option value="Diğer" <?php echo ($_POST['subject'] ?? '') === 'Diğer' ? 'selected' : ''; ?>>
                                        Diğer
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mesajınız *</label>
                            <textarea name="message" class="form-control" rows="6" 
                                      placeholder="Mesajınızı buraya yazın..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            <div class="form-text">En az 10 karakter olmalıdır.</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="privacyCheck" required>
                                <label class="form-check-label" for="privacyCheck">
                                    <a href="<?php echo seoUrl('gizlilik-politikasi'); ?>" target="_blank">
                                        Gizlilik Politikası
                                    </a>'nı okudum ve kabul ediyorum. *
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i>
                            Mesaj Gönder
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="contact-sidebar">
                    <!-- FAQ Widget -->
                    <div class="widget faq-widget">
                        <h4 class="widget-title">Sık Sorulan Sorular</h4>
                        <div class="faq-list">
                            <div class="faq-item">
                                <h6>Bonusları nasıl alabilirim?</h6>
                                <p>Site sayfalarından ilgili bonusa tıklayarak partner sitesine yönlendirileceksiniz.</p>
                            </div>
                            
                            <div class="faq-item">
                                <h6>Bonuslar gerçek mi?</h6>
                                <p>Evet, tüm bonuslar partner siteler tarafından sağlanmaktadır ve gerçektir.</p>
                            </div>
                            
                            <div class="faq-item">
                                <h6>Kaç tane bonus alabilirim?</h6>
                                <p>Her siteden genellikle bir kez bonus alabilirsiniz. Detaylar site koşullarına bağlıdır.</p>
                            </div>
                            
                            <div class="faq-item">
                                <h6>Site güvenli mi?</h6>
                                <p>Evet, sitemiz SSL sertifikalıdır ve tüm verileriniz şifrelenerek korunmaktadır.</p>
                            </div>
                        </div>
                        
                        <a href="<?php echo seoUrl('sss'); ?>" class="btn btn-outline-primary btn-sm">
                            Tüm SSS'leri Gör
                        </a>
                    </div>
                    
                    <!-- Social Media Widget -->
                    <div class="widget social-widget">
                        <h4 class="widget-title">Sosyal Medya</h4>
                        <p>Bizi sosyal medyada takip edin ve güncel bonus haberlerini kaçırmayın!</p>
                        
                        <div class="social-links">
                            <a href="<?php echo getSetting('social_facebook'); ?>" class="social-link facebook" target="_blank">
                                <i class="fab fa-facebook-f"></i>
                                <span>Facebook</span>
                            </a>
                            
                            <a href="<?php echo getSetting('social_twitter'); ?>" class="social-link twitter" target="_blank">
                                <i class="fab fa-twitter"></i>
                                <span>Twitter</span>
                            </a>
                            
                            <a href="<?php echo getSetting('social_instagram'); ?>" class="social-link instagram" target="_blank">
                                <i class="fab fa-instagram"></i>
                                <span>Instagram</span>
                            </a>
                            
                            <a href="<?php echo getSetting('social_telegram'); ?>" class="social-link telegram" target="_blank">
                                <i class="fab fa-telegram"></i>
                                <span>Telegram</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Contact Stats Widget -->
                    <div class="widget stats-widget">
                        <h4 class="widget-title">İletişim İstatistikleri</h4>
                        <div class="stats-list">
                            <div class="stat-item">
                                <i class="fas fa-envelope"></i>
                                <div class="stat-info">
                                    <strong>Ortalama Yanıt Süresi</strong>
                                    <span>2 saat</span>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <i class="fas fa-star"></i>
                                <div class="stat-info">
                                    <strong>Müşteri Memnuniyeti</strong>
                                    <span>98%</span>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <div class="stat-info">
                                    <strong>Günlük Destek</strong>
                                    <span>7/24</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-4">Ofis Konumumuz</h3>
        <div class="map-container">
            <div class="map-placeholder">
                <i class="fas fa-map-marker-alt fa-3x text-primary"></i>
                <h5 class="mt-3">BonusBoss Ofis</h5>
                <p>İstanbul, Türkiye</p>
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Güvenlik nedeniyle tam adres paylaşılmamaktadır.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Custom CSS -->
<style>
.contact-stats {
    display: flex;
    align-items: center;
    gap: 15px;
}

.contact-stats .stat-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
}

.contact-stats .stat-text {
    text-align: left;
}

.contact-info-card {
    background: white;
    padding: 30px 20px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    height: 100%;
}

.contact-info-card:hover {
    transform: translateY(-5px);
}

.contact-icon {
    width: 70px;
    height: 70px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 1.5rem;
}

.contact-icon.whatsapp {
    background: #25d366;
}

.contact-form-card {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.form-title {
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 10px;
}

.contact-sidebar .widget {
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

.faq-item {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.faq-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.faq-item h6 {
    color: var(--dark-color);
    margin-bottom: 8px;
    font-weight: 600;
}

.faq-item p {
    color: var(--secondary-color);
    font-size: 0.9rem;
    margin: 0;
}

.social-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.social-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    color: white;
}

.social-link.facebook { background: #1877f2; }
.social-link.twitter { background: #1da1f2; }
.social-link.instagram { background: #e4405f; }
.social-link.telegram { background: #0088cc; }

.social-link:hover {
    transform: translateX(5px);
    color: white;
}

.stats-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-item i {
    color: var(--primary-color);
    font-size: 1.2rem;
    width: 20px;
}

.stat-info strong {
    display: block;
    color: var(--dark-color);
    font-size: 0.9rem;
}

.stat-info span {
    color: var(--primary-color);
    font-weight: 600;
}

.map-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.map-placeholder {
    height: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.contact-form .form-control,
.contact-form .form-select {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 12px 15px;
    transition: border-color 0.3s ease;
}

.contact-form .form-control:focus,
.contact-form .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
}

@media (max-width: 768px) {
    .contact-form-card {
        padding: 25px 20px;
    }
    
    .contact-stats {
        justify-content: center;
        margin-top: 20px;
    }
    
    .contact-stats .stat-item {
        flex-direction: column;
        text-align: center;
        gap: 5px;
    }
}
</style>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Form validation
    $('#contactForm').on('submit', function(e) {
        let isValid = true;
        
        // Name validation
        if ($('input[name="name"]').val().trim().length < 2) {
            isValid = false;
            showFieldError('name', 'Ad Soyad en az 2 karakter olmalıdır.');
        }
        
        // Email validation
        const email = $('input[name="email"]').val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            isValid = false;
            showFieldError('email', 'Geçerli bir e-posta adresi giriniz.');
        }
        
        // Message validation
        if ($('textarea[name="message"]').val().trim().length < 10) {
            isValid = false;
            showFieldError('message', 'Mesaj en az 10 karakter olmalıdır.');
        }
        
        // Privacy policy check
        if (!$('#privacyCheck').is(':checked')) {
            isValid = false;
            alert('Gizlilik politikasını kabul etmelisiniz.');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Clear error states on input
    $('.form-control, .form-select').on('input change', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });
    
    // Character counter for message
    $('textarea[name="message"]').on('input', function() {
        const length = $(this).val().length;
        const counter = $(this).siblings('.char-counter');
        
        if (counter.length === 0) {
            $(this).after('<div class="char-counter form-text text-end"></div>');
        }
        
        $(this).siblings('.char-counter').text(length + ' / 1000 karakter');
        
        if (length >= 1000) {
            $(this).val($(this).val().substring(0, 1000));
        }
    });
});

function showFieldError(fieldName, message) {
    const field = $(`[name="${fieldName}"]`);
    field.addClass('is-invalid');
    field.siblings('.invalid-feedback').remove();
    field.after(`<div class="invalid-feedback">${message}</div>`);
}
</script>

<?php include 'includes/footer.php'; ?>