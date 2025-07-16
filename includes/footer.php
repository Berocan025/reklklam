<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Footer Template
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */
?>
</main>
<!-- Main Content End -->

<!-- Sidebar Banner Area -->
<div class="sidebar-banner-area">
    <?php showBanner('sidebar'); ?>
</div>

<!-- Between Content Banner Area -->
<div class="between-content-banner">
    <?php showBanner('between_content'); ?>
</div>

<!-- Footer -->
<footer class="main-footer">
    <div class="footer-top">
        <div class="container">
            <div class="row">
                <!-- Site Bilgileri -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="widget-title">
                            <?php if (getSetting('site_logo')): ?>
                                <img src="<?php echo uploadUrl(getSetting('site_logo')); ?>" alt="<?php echo getSetting('site_title'); ?>" class="footer-logo">
                            <?php else: ?>
                                BonusBoss
                            <?php endif; ?>
                        </h5>
                        <p class="footer-description">
                            <?php echo getSetting('site_description', 'Türkiye\'nin en güvenilir casino deneme bonusu platformu. Hilesiz, anında bonus fırsatları.'); ?>
                        </p>
                        
                        <!-- Sosyal Medya -->
                        <div class="footer-social">
                            <?php if (getSetting('social_facebook')): ?>
                            <a href="<?php echo getSetting('social_facebook'); ?>" target="_blank" class="social-link">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (getSetting('social_twitter')): ?>
                            <a href="<?php echo getSetting('social_twitter'); ?>" target="_blank" class="social-link">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (getSetting('social_instagram')): ?>
                            <a href="<?php echo getSetting('social_instagram'); ?>" target="_blank" class="social-link">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (getSetting('social_youtube')): ?>
                            <a href="<?php echo getSetting('social_youtube'); ?>" target="_blank" class="social-link">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Hızlı Linkler -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="widget-title">Hızlı Linkler</h5>
                        <ul class="footer-links">
                            <li><a href="<?php echo seoUrl('/'); ?>">Ana Sayfa</a></li>
                            <li><a href="<?php echo seoUrl('deneme-bonusu'); ?>">Deneme Bonusları</a></li>
                            <li><a href="<?php echo seoUrl('canli-yayin'); ?>">Canlı Yayın</a></li>
                            <li><a href="<?php echo seoUrl('hakkimizda'); ?>">Hakkımızda</a></li>
                            <li><a href="<?php echo seoUrl('iletisim'); ?>">İletişim</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Kategoriler -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="widget-title">Kategoriler</h5>
                        <ul class="footer-links">
                            <?php
                            $footerCategories = $db->fetchAll("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC LIMIT 5");
                            foreach ($footerCategories as $category):
                            ?>
                            <li><a href="<?php echo seoUrl('kategori/' . $category['slug']); ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- En Popüler Bonuslar -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="widget-title">En Popüler Bonuslar</h5>
                        <div class="footer-bonuses">
                            <?php
                            $footerBonuses = $db->fetchAll(
                                "SELECT b.*, s.name as site_name, s.logo as site_logo 
                                 FROM bonuses b 
                                 JOIN sites s ON b.site_id = s.id 
                                 WHERE b.status = 1 AND s.status = 1 
                                 ORDER BY b.click_count DESC 
                                 LIMIT 3"
                            );
                            
                            foreach ($footerBonuses as $bonus):
                            ?>
                            <div class="footer-bonus-item">
                                <div class="bonus-info">
                                    <span class="bonus-amount"><?php echo formatMoney($bonus['amount'], $bonus['currency']); ?></span>
                                    <span class="bonus-site"><?php echo htmlspecialchars($bonus['site_name']); ?></span>
                                </div>
                                <a href="<?php echo $bonus['claim_link'] ?: '#'; ?>" class="btn btn-primary btn-xs" target="_blank">
                                    AL
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer Banner Area -->
    <div class="footer-banner-area">
        <?php showBanner('footer'); ?>
    </div>
    
    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="footer-copyright">
                        <p>&copy; <?php echo date('Y'); ?> <?php echo getSetting('site_title'); ?>. Tüm hakları saklıdır.</p>
                        <p class="developer-credit">Geliştirici: <strong>BERAT K</strong></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="footer-legal">
                        <ul class="legal-links">
                            <li><a href="<?php echo seoUrl('gizlilik-politikasi'); ?>">Gizlilik Politikası</a></li>
                            <li><a href="<?php echo seoUrl('kullanim-kosullari'); ?>">Kullanım Koşulları</a></li>
                            <li><a href="<?php echo seoUrl('sitemap'); ?>">Site Haritası</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- İletişim Bilgileri -->
<div class="contact-info">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div class="contact-details">
                        <strong>E-posta</strong>
                        <span><?php echo getSetting('contact_email', 'info@bonusboss.com'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div class="contact-details">
                        <strong>Telefon</strong>
                        <span><?php echo getSetting('contact_phone', '+90 (555) 123 45 67'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <div class="contact-details">
                        <strong>Çalışma Saatleri</strong>
                        <span>7/24 Hizmet</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scroll to Top Button -->
<button class="scroll-to-top" id="scrollToTop" title="Yukarı Çık">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- WhatsApp Float Button -->
<div class="whatsapp-float">
    <a href="https://wa.me/905551234567" target="_blank" class="whatsapp-btn">
        <i class="fab fa-whatsapp"></i>
        <span>WhatsApp</span>
    </a>
</div>

<!-- JavaScript Files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?php echo assetUrl('js/main.js'); ?>"></script>

<!-- Analytics Code -->
<?php if (getSetting('analytics_enabled', '1') == '1'): ?>
<script>
// Google Analytics veya diğer analitik kodları buraya eklenebilir
</script>
<?php endif; ?>

<!-- Banner Click Tracking -->
<script>
$(document).ready(function() {
    // Banner tıklama takibi
    $('.banner-link, .banner-container a').on('click', function() {
        var bannerId = $(this).closest('.banner-container').data('banner-id');
        if (bannerId) {
            $.post('<?php echo seoUrl('api/banner-click'); ?>', {
                banner_id: bannerId,
                _token: '<?php echo generateCSRFToken(); ?>'
            });
        }
    });
    
    // Popup göster
    <?php if (shouldShowPopup()): ?>
    setTimeout(function() {
        $('#bonusPopup').modal('show');
        
        // Cookie set et
        document.cookie = "popup_shown=1; expires=" + new Date(Date.now() + 24*60*60*1000).toUTCString() + "; path=/";
    }, <?php echo getSetting('popup_delay', '3') * 1000; ?>);
    <?php endif; ?>
    
    // Scroll to top
    $('#scrollToTop').on('click', function() {
        $('html, body').animate({scrollTop: 0}, 500);
    });
    
    // Scroll to top button görünürlük
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#scrollToTop').fadeIn();
        } else {
            $('#scrollToTop').fadeOut();
        }
    });
    
    // Lazy loading
    if ('IntersectionObserver' in window) {
        let lazyImages = document.querySelectorAll('img[data-src]');
        let imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let image = entry.target;
                    image.src = image.dataset.src;
                    image.classList.remove('lazy');
                    imageObserver.unobserve(image);
                }
            });
        });
        
        lazyImages.forEach(function(image) {
            imageObserver.observe(image);
        });
    }
    
    // Form doğrulama
    $('.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Tooltip ve popover başlat
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>

<!-- Structured Data for Organization -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "<?php echo getSetting('site_title'); ?>",
    "description": "<?php echo getSetting('site_description'); ?>",
    "url": "<?php echo SITE_URL; ?>",
    "logo": "<?php echo getSetting('site_logo') ? uploadUrl(getSetting('site_logo')) : assetUrl('images/logo.png'); ?>",
    "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "<?php echo getSetting('contact_phone'); ?>",
        "contactType": "Customer Service",
        "areaServed": "TR",
        "availableLanguage": "Turkish"
    },
    "sameAs": [
        <?php
        $socialLinks = [];
        if (getSetting('social_facebook')) $socialLinks[] = '"' . getSetting('social_facebook') . '"';
        if (getSetting('social_twitter')) $socialLinks[] = '"' . getSetting('social_twitter') . '"';
        if (getSetting('social_instagram')) $socialLinks[] = '"' . getSetting('social_instagram') . '"';
        if (getSetting('social_youtube')) $socialLinks[] = '"' . getSetting('social_youtube') . '"';
        echo implode(',', $socialLinks);
        ?>
    ]
}
</script>

</body>
</html>