<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Hakkımızda Sayfası
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Sayfa değişkenleri
$pageTitle = 'Hakkımızda';
$metaDescription = 'BonusBoss hakkında bilgi edinin. Misyonumuz, vizyonumuz ve neden Türkiye\'nin en güvenilir casino bonus sitesi olduğumuzu öğrenin.';
$metaKeywords = 'hakkımızda, bonusboss, casino bonus, güvenilir site, misyon, vizyon';
$bodyClass = 'about-page';

// Header dahil et
include 'includes/header.php';

// Sayfa içeriği veritabanından al
$page = $db->fetchOne("SELECT * FROM pages WHERE slug = 'hakkimizda' AND status = 1");

// İstatistikler
$stats = [
    'total_bonuses' => $db->fetchColumn("SELECT COUNT(*) FROM bonuses WHERE status = 1"),
    'total_sites' => $db->fetchColumn("SELECT COUNT(*) FROM sites WHERE status = 1"),
    'total_visitors' => $db->fetchColumn("SELECT COUNT(DISTINCT visitor_ip) FROM analytics WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
    'total_clicks' => $db->fetchColumn("SELECT SUM(click_count) FROM bonuses WHERE status = 1")
];
?>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <?php echo createBreadcrumb([
            ['title' => 'Ana Sayfa', 'url' => seoUrl('/')],
            ['title' => 'Hakkımızda', 'url' => '']
        ]); ?>
    </div>
</div>

<!-- Page Header -->
<section class="page-header py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title mb-3">
                    <i class="fas fa-info-circle"></i>
                    Hakkımızda
                </h1>
                <p class="page-description lead">
                    Türkiye'nin en güvenilir casino deneme bonusu platformu
                </p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="header-logo">
                    <i class="fas fa-crown fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="about-content py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="content-area">
                    <?php if ($page): ?>
                        <?php echo $page['content']; ?>
                    <?php else: ?>
                        <h2>BonusBoss Kimdir?</h2>
                        <p class="lead">
                            BonusBoss, 2024 yılında kurulan ve Türkiye'nin en güvenilir casino deneme bonusu platformu olmayı hedefleyen bir teknoloji şirketidir.
                        </p>
                        
                        <h3>Misyonumuz</h3>
                        <p>
                            Casino oyuncularının en iyi deneme bonusu fırsatlarına güvenli ve hızlı bir şekilde ulaşmalarını sağlamak. 
                            Şeffaf, adil ve kullanıcı dostu bir platform sunarak sektörde güven oluşturmak.
                        </p>
                        
                        <h3>Vizyonumuz</h3>
                        <p>
                            Türkiye'de casino bonus alanında lider platform olmak ve uluslararası arenada tanınan bir marka haline gelmek. 
                            Teknoloji ve inovasyonla sektörde yeni standartlar belirlemek.
                        </p>
                        
                        <h3>Değerlerimiz</h3>
                        <ul class="values-list">
                            <li><strong>Güvenilirlik:</strong> Tüm partner sitelerimizi titizlikle seçiyor ve sürekli denetliyoruz.</li>
                            <li><strong>Şeffaflık:</strong> Tüm bonus şartlarını açık ve anlaşılır şekilde sunuyoruz.</li>
                            <li><strong>Kullanıcı Odaklılık:</strong> Kullanıcı deneyimini sürekli geliştirmeye odaklanıyoruz.</li>
                            <li><strong>İnovasyon:</strong> Teknolojinin gücüyle sektörde yenilikçi çözümler üretiyoruz.</li>
                            <li><strong>Sosyal Sorumluluk:</strong> Sorumlu oyun politikalarını destekliyoruz.</li>
                        </ul>
                        
                        <h3>Neden BonusBoss?</h3>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="feature-item">
                                    <i class="fas fa-shield-alt text-success"></i>
                                    <h5>%100 Güvenli</h5>
                                    <p>SSL sertifikalı güvenli platform</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-item">
                                    <i class="fas fa-clock text-primary"></i>
                                    <h5>7/24 Hizmet</h5>
                                    <p>Kesintisiz hizmet garantisi</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-item">
                                    <i class="fas fa-handshake text-warning"></i>
                                    <h5>Güvenilir Partnerler</h5>
                                    <p>Lisanslı ve denetimli siteler</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-item">
                                    <i class="fas fa-mobile-alt text-info"></i>
                                    <h5>Mobil Uyumlu</h5>
                                    <p>Tüm cihazlarda mükemmel deneyim</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="sidebar">
                    <!-- Stats Widget -->
                    <div class="widget stats-widget">
                        <h4 class="widget-title">Platform İstatistikleri</h4>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo number_format($stats['total_bonuses']); ?></div>
                                <div class="stat-label">Aktif Bonus</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo number_format($stats['total_sites']); ?></div>
                                <div class="stat-label">Partner Site</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo number_format($stats['total_visitors']); ?></div>
                                <div class="stat-label">Aylık Ziyaretçi</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo number_format($stats['total_clicks']); ?></div>
                                <div class="stat-label">Toplam Tıklama</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="widget quick-links-widget">
                        <h4 class="widget-title">Hızlı Erişim</h4>
                        <ul class="quick-links">
                            <li><a href="<?php echo seoUrl('deneme-bonusu'); ?>"><i class="fas fa-gift"></i> Deneme Bonusları</a></li>
                            <li><a href="<?php echo seoUrl('canli-yayin'); ?>"><i class="fas fa-play"></i> Canlı Yayınlar</a></li>
                            <li><a href="<?php echo seoUrl('iletisim'); ?>"><i class="fas fa-envelope"></i> İletişim</a></li>
                            <li><a href="<?php echo seoUrl('gizlilik-politikasi'); ?>"><i class="fas fa-shield-alt"></i> Gizlilik Politikası</a></li>
                        </ul>
                    </div>
                    
                    <!-- Contact Widget -->
                    <div class="widget contact-widget">
                        <h4 class="widget-title">İletişim</h4>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo getSetting('contact_email', 'info@bonusboss.com'); ?></span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo getSetting('contact_phone', '+90 (555) 123 45 67'); ?></span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-clock"></i>
                                <span>7/24 Hizmet</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Ekibimiz</h2>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="team-card text-center">
                    <div class="team-avatar">
                        <i class="fas fa-user-tie fa-3x"></i>
                    </div>
                    <h5 class="team-name">BERAT K</h5>
                    <p class="team-role">Kurucu & Geliştirici</p>
                    <p class="team-description">
                        10+ yıl deneyimli full-stack developer. 
                        Modern web teknolojileri ve güvenlik uzmanı.
                    </p>
                    <div class="team-social">
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="team-card text-center">
                    <div class="team-avatar">
                        <i class="fas fa-user-shield fa-3x"></i>
                    </div>
                    <h5 class="team-name">Güvenlik Ekibi</h5>
                    <p class="team-role">Siber Güvenlik Uzmanları</p>
                    <p class="team-description">
                        Platform güvenliğini 7/24 izleyen 
                        deneyimli siber güvenlik uzmanları.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="team-card text-center">
                    <div class="team-avatar">
                        <i class="fas fa-headset fa-3x"></i>
                    </div>
                    <h5 class="team-name">Destek Ekibi</h5>
                    <p class="team-role">Müşteri Hizmetleri</p>
                    <p class="team-description">
                        Kullanıcılarımıza en iyi deneyimi sunmak için 
                        7/24 hizmet veren destek ekibi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="timeline-section py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">Yolculuğumuz</h2>
        
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-date">2024 Q1</div>
                <div class="timeline-content">
                    <h4>Projenin Başlangıcı</h4>
                    <p>BonusBoss fikrinin doğuşu ve geliştirme sürecinin başlaması.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-date">2024 Q2</div>
                <div class="timeline-content">
                    <h4>Beta Versiyonu</h4>
                    <p>Platform beta testlerinin başlaması ve ilk kullanıcı geri bildirimlerinin alınması.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-date">2024 Q3</div>
                <div class="timeline-content">
                    <h4>Resmi Lansmanı</h4>
                    <p>BonusBoss platformunun resmi olarak kullanıma açılması.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-date">2024 Q4</div>
                <div class="timeline-content">
                    <h4>Büyüme ve Gelişim</h4>
                    <p>Yeni özellikler eklenmesi ve partner ağının genişletilmesi.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Certificates Section -->
<section class="certificates-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Sertifikalar ve Güvenlik</h2>
        
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="certificate-item">
                    <i class="fas fa-certificate fa-3x text-warning"></i>
                    <h5 class="mt-3">SSL Sertifikası</h5>
                    <p>256-bit güvenli şifreleme</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="certificate-item">
                    <i class="fas fa-shield-alt fa-3x text-success"></i>
                    <h5 class="mt-3">Güvenlik Taraması</h5>
                    <p>Günlük güvenlik kontrolü</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="certificate-item">
                    <i class="fas fa-lock fa-3x text-primary"></i>
                    <h5 class="mt-3">Veri Koruması</h5>
                    <p>KVKK uyumlu veri işleme</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="certificate-item">
                    <i class="fas fa-cloud-upload-alt fa-3x text-info"></i>
                    <h5 class="mt-3">Backup Sistemi</h5>
                    <p>Günlük otomatik yedekleme</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container text-center">
        <h3 class="mb-3">Bizimle İletişime Geçin</h3>
        <p class="lead mb-4">
            Sorularınız, önerileriniz veya işbirliği teklifleriniz için bizimle iletişime geçebilirsiniz.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?php echo seoUrl('iletisim'); ?>" class="btn btn-light btn-lg">
                <i class="fas fa-envelope"></i> İletişim Formu
            </a>
            <a href="mailto:<?php echo getSetting('contact_email'); ?>" class="btn btn-outline-light btn-lg">
                <i class="fas fa-paper-plane"></i> E-posta Gönder
            </a>
        </div>
    </div>
</section>

<!-- Custom CSS -->
<style>
.content-area {
    font-size: 1.1rem;
    line-height: 1.8;
}

.content-area h2, .content-area h3 {
    color: var(--primary-color);
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.values-list {
    list-style: none;
    padding: 0;
}

.values-list li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 20px;
}

.feature-item i {
    font-size: 1.5rem;
    margin-top: 5px;
}

.feature-item h5 {
    margin-bottom: 5px;
    color: var(--dark-color);
}

.feature-item p {
    margin: 0;
    color: var(--secondary-color);
    font-size: 0.9rem;
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

.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--primary-color);
    display: block;
}

.stat-label {
    font-size: 0.85rem;
    color: var(--secondary-color);
    margin-top: 5px;
}

.quick-links {
    list-style: none;
    padding: 0;
}

.quick-links li {
    margin-bottom: 10px;
}

.quick-links a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--dark-color);
    text-decoration: none;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
    transition: color 0.3s ease;
}

.quick-links a:hover {
    color: var(--primary-color);
}

.contact-info .contact-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

.contact-item i {
    color: var(--primary-color);
    width: 20px;
}

.team-card {
    background: white;
    border-radius: 15px;
    padding: 30px 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.team-card:hover {
    transform: translateY(-5px);
}

.team-avatar {
    width: 80px;
    height: 80px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
}

.team-name {
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--dark-color);
}

.team-role {
    color: var(--primary-color);
    font-weight: 500;
    margin-bottom: 15px;
}

.team-description {
    font-size: 0.9rem;
    color: var(--secondary-color);
    margin-bottom: 20px;
}

.team-social {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.team-social .social-link {
    width: 35px;
    height: 35px;
    background: var(--light-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--secondary-color);
    text-decoration: none;
    transition: all 0.3s ease;
}

.team-social .social-link:hover {
    background: var(--primary-color);
    color: white;
}

.timeline {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 2px;
    height: 100%;
    background: var(--primary-color);
}

.timeline-item {
    position: relative;
    margin-bottom: 50px;
    display: flex;
    align-items: center;
}

.timeline-item:nth-child(odd) {
    flex-direction: row;
}

.timeline-item:nth-child(even) {
    flex-direction: row-reverse;
}

.timeline-date {
    background: var(--primary-color);
    color: white;
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: 600;
    min-width: 120px;
    text-align: center;
}

.timeline-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin: 0 30px;
    flex: 1;
}

.timeline-content h4 {
    color: var(--primary-color);
    margin-bottom: 10px;
}

.certificate-item {
    padding: 20px;
}

.certificate-item i {
    margin-bottom: 15px;
}

.certificate-item h5 {
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--dark-color);
}

.certificate-item p {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .timeline::before {
        left: 20px;
    }
    
    .timeline-item {
        flex-direction: column !important;
        padding-left: 50px;
    }
    
    .timeline-date {
        position: absolute;
        left: -60px;
        min-width: 100px;
        font-size: 0.8rem;
        padding: 8px 12px;
    }
    
    .timeline-content {
        margin: 0;
        margin-top: 20px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>