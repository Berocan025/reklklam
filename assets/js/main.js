/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Ana JavaScript Dosyası
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

$(document).ready(function() {
    'use strict';
    
    // Global değişkenler
    let isLoading = false;
    let currentPage = 1;
    
    /**
     * Sayfa yükleme animasyonu
     */
    function initPageLoader() {
        // Sayfa yüklendiğinde loader'ı gizle
        $('.page-loader').fadeOut(500);
        
        // CSS animasyonlarını başlat
        $('.animate-on-scroll').each(function() {
            $(this).addClass('animated');
        });
    }
    
    /**
     * Navbar scroll efekti
     */
    function initNavbarEffects() {
        let lastScrollTop = 0;
        
        $(window).scroll(function() {
            let scrollTop = $(this).scrollTop();
            
            if (scrollTop > 100) {
                $('.main-header').addClass('scrolled');
            } else {
                $('.main-header').removeClass('scrolled');
            }
            
            // Navbar gizleme/gösterme
            if (scrollTop > lastScrollTop && scrollTop > 200) {
                $('.main-header').addClass('nav-hidden');
            } else {
                $('.main-header').removeClass('nav-hidden');
            }
            
            lastScrollTop = scrollTop;
        });
    }
    
    /**
     * Smooth scroll
     */
    function initSmoothScroll() {
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            
            let target = $($(this).attr('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 800);
            }
        });
    }
    
    /**
     * Lazy loading
     */
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            let lazyImages = document.querySelectorAll('img[data-src], .lazy-bg');
            let imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let image = entry.target;
                        
                        if (image.dataset.src) {
                            image.src = image.dataset.src;
                            image.classList.remove('lazy');
                            image.classList.add('loaded');
                        }
                        
                        if (image.classList.contains('lazy-bg')) {
                            image.style.backgroundImage = `url(${image.dataset.bg})`;
                            image.classList.remove('lazy-bg');
                        }
                        
                        imageObserver.unobserve(image);
                    }
                });
            });
            
            lazyImages.forEach(function(image) {
                imageObserver.observe(image);
            });
        }
    }
    
    /**
     * Animation on scroll
     */
    function initScrollAnimations() {
        if ('IntersectionObserver' in window) {
            let animationObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        animationObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });
            
            document.querySelectorAll('.animate-on-scroll').forEach(function(el) {
                animationObserver.observe(el);
            });
        }
    }
    
    /**
     * Form validasyonu
     */
    function initFormValidation() {
        // Bootstrap form validation
        $('.needs-validation').on('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
        
        // Custom validation messages
        $('input[type="email"]').on('input', function() {
            let email = $(this).val();
            let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.setCustomValidity('Geçerli bir e-posta adresi girin');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Phone validation
        $('input[type="tel"]').on('input', function() {
            let phone = $(this).val().replace(/[^\d]/g, '');
            
            if (phone && phone.length < 10) {
                this.setCustomValidity('Telefon numarası en az 10 haneli olmalıdır');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    /**
     * Tooltip ve popover başlatma
     */
    function initTooltipsAndPopovers() {
        // Tooltip
        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover'
            });
        });
        
        // Popover
        let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    /**
     * Copy to clipboard
     */
    function initCopyToClipboard() {
        $('.copy-btn').on('click', function() {
            let text = $(this).data('copy');
            let $this = $(this);
            
            navigator.clipboard.writeText(text).then(function() {
                let originalText = $this.html();
                $this.html('<i class="fas fa-check"></i> Kopyalandı');
                
                setTimeout(function() {
                    $this.html(originalText);
                }, 2000);
            });
        });
    }
    
    /**
     * Search functionality
     */
    function initSearch() {
        let searchTimer;
        
        $('#searchInput').on('input', function() {
            let query = $(this).val();
            
            clearTimeout(searchTimer);
            
            if (query.length >= 2) {
                searchTimer = setTimeout(function() {
                    performSearch(query);
                }, 500);
            } else {
                $('#searchResults').hide();
            }
        });
        
        // Search form submit
        $('.search-form').on('submit', function(e) {
            e.preventDefault();
            let query = $(this).find('input[name="q"]').val();
            if (query.trim()) {
                window.location.href = `/ara?q=${encodeURIComponent(query)}`;
            }
        });
    }
    
    /**
     * Search API
     */
    function performSearch(query) {
        if (isLoading) return;
        
        isLoading = true;
        $('#searchResults').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Aranıyor...</div>').show();
        
        $.post('/api/search', {
            q: query,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                displaySearchResults(response.data);
            } else {
                $('#searchResults').html('<div class="alert alert-warning">Arama yapılırken bir hata oluştu.</div>');
            }
        })
        .fail(function() {
            $('#searchResults').html('<div class="alert alert-danger">Arama hizmeti şu anda kullanılamıyor.</div>');
        })
        .always(function() {
            isLoading = false;
        });
    }
    
    /**
     * Search results display
     */
    function displaySearchResults(results) {
        let html = '';
        
        if (results.length === 0) {
            html = '<div class="alert alert-info">Aramanız için sonuç bulunamadı.</div>';
        } else {
            html = '<div class="search-results-list">';
            
            results.forEach(function(item) {
                html += `
                    <div class="search-result-item">
                        <div class="result-icon">
                            <i class="fas fa-${item.type === 'bonus' ? 'gift' : 'external-link-alt'}"></i>
                        </div>
                        <div class="result-content">
                            <h6 class="result-title">${item.title}</h6>
                            <p class="result-description">${item.description}</p>
                            <span class="result-type badge badge-secondary">${item.type_label}</span>
                        </div>
                        <a href="${item.url}" class="result-link">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                `;
            });
            
            html += '</div>';
        }
        
        $('#searchResults').html(html);
    }
    
    /**
     * Filter functionality
     */
    function initFilters() {
        // Amount filter
        $('#amountFilter').on('change', function() {
            applyFilters();
        });
        
        // Category filter
        $('#categoryFilter').on('change', function() {
            applyFilters();
        });
        
        // Bonus type filter
        $('#bonusTypeFilter').on('change', function() {
            applyFilters();
        });
        
        // Sort filter
        $('#sortFilter').on('change', function() {
            applyFilters();
        });
        
        // Clear filters
        $('#clearFilters').on('click', function() {
            $('.filter-form')[0].reset();
            applyFilters();
        });
    }
    
    /**
     * Apply filters
     */
    function applyFilters() {
        if (isLoading) return;
        
        isLoading = true;
        $('.loading-overlay').show();
        
        let filters = {
            amount: $('#amountFilter').val(),
            category: $('#categoryFilter').val(),
            bonus_type: $('#bonusTypeFilter').val(),
            sort: $('#sortFilter').val(),
            page: currentPage
        };
        
        $.post('/api/filter', {
            filters: filters,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                updateContent(response.data);
            }
        })
        .fail(function() {
            showNotification('Filtreleme sırasında bir hata oluştu.', 'error');
        })
        .always(function() {
            isLoading = false;
            $('.loading-overlay').hide();
        });
    }
    
    /**
     * Content update
     */
    function updateContent(data) {
        $('#contentArea').html(data.html);
        
        // Pagination
        if (data.pagination) {
            $('#paginationArea').html(data.pagination);
        }
        
        // Re-initialize lazy loading for new content
        initLazyLoading();
        
        // Scroll to top of results
        $('html, body').animate({
            scrollTop: $('#contentArea').offset().top - 100
        }, 500);
    }
    
    /**
     * Load more functionality
     */
    function initLoadMore() {
        $('#loadMoreBtn').on('click', function() {
            if (isLoading) return;
            
            isLoading = true;
            let $btn = $(this);
            let originalText = $btn.html();
            
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Yükleniyor...');
            
            currentPage++;
            
            $.post('/api/load-more', {
                page: currentPage,
                filters: getActiveFilters(),
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .done(function(response) {
                if (response.success) {
                    $('#contentArea').append(response.data.html);
                    
                    if (!response.data.has_more) {
                        $btn.hide();
                    }
                }
            })
            .fail(function() {
                currentPage--;
                showNotification('Daha fazla içerik yüklenemedi.', 'error');
            })
            .always(function() {
                isLoading = false;
                $btn.html(originalText);
            });
        });
    }
    
    /**
     * Get active filters
     */
    function getActiveFilters() {
        return {
            amount: $('#amountFilter').val(),
            category: $('#categoryFilter').val(),
            bonus_type: $('#bonusTypeFilter').val(),
            sort: $('#sortFilter').val()
        };
    }
    
    /**
     * Click tracking
     */
    function initClickTracking() {
        // Bonus click tracking
        $(document).on('click', '.btn-claim, .bonus-link', function() {
            let bonusId = $(this).data('bonus-id');
            if (bonusId) {
                trackClick('bonus', bonusId);
            }
        });
        
        // Site click tracking
        $(document).on('click', '.site-link', function() {
            let siteId = $(this).data('site-id');
            if (siteId) {
                trackClick('site', siteId);
            }
        });
        
        // Banner click tracking
        $(document).on('click', '.banner-link', function() {
            let bannerId = $(this).closest('.banner-container').data('banner-id');
            if (bannerId) {
                trackClick('banner', bannerId);
            }
        });
    }
    
    /**
     * Track click
     */
    function trackClick(type, id) {
        $.post('/api/track-click', {
            type: type,
            id: id,
            _token: $('meta[name="csrf-token"]').attr('content')
        }).fail(function() {
            console.log('Click tracking failed');
        });
    }
    
    /**
     * Notification system
     */
    function showNotification(message, type = 'info', duration = 5000) {
        let alertClass = 'alert-info';
        let icon = 'fas fa-info-circle';
        
        switch (type) {
            case 'success':
                alertClass = 'alert-success';
                icon = 'fas fa-check-circle';
                break;
            case 'error':
                alertClass = 'alert-danger';
                icon = 'fas fa-exclamation-circle';
                break;
            case 'warning':
                alertClass = 'alert-warning';
                icon = 'fas fa-exclamation-triangle';
                break;
        }
        
        let notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert">
                <i class="${icon}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('#notificationArea').append(notification);
        
        setTimeout(function() {
            notification.alert('close');
        }, duration);
    }
    
    /**
     * Counter animation
     */
    function initCounterAnimation() {
        $('.counter').each(function() {
            let $this = $(this);
            let target = parseInt($this.text());
            
            $({ value: 0 }).animate({ value: target }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.ceil(this.value));
                }
            });
        });
    }
    
    /**
     * Card hover effects
     */
    function initCardEffects() {
        $('.card-hover').hover(
            function() {
                $(this).addClass('card-hover-effect');
            },
            function() {
                $(this).removeClass('card-hover-effect');
            }
        );
    }
    
    /**
     * Progress bars
     */
    function initProgressBars() {
        $('.progress-bar').each(function() {
            let $bar = $(this);
            let percent = $bar.data('percent');
            
            $bar.animate({
                width: percent + '%'
            }, 1500);
        });
    }
    
    /**
     * Back to top button
     */
    function initBackToTop() {
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('#backToTop').fadeIn();
            } else {
                $('#backToTop').fadeOut();
            }
        });
        
        $('#backToTop').on('click', function() {
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        });
    }
    
    /**
     * Theme switcher
     */
    function initThemeSwitcher() {
        $('#themeSwitcher').on('click', function() {
            $('body').toggleClass('dark-theme');
            
            // Save preference
            localStorage.setItem('theme', $('body').hasClass('dark-theme') ? 'dark' : 'light');
        });
        
        // Load saved theme
        let savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            $('body').addClass('dark-theme');
        }
    }
    
    /**
     * Mobile menu
     */
    function initMobileMenu() {
        $('.mobile-menu-toggle').on('click', function() {
            $('.mobile-menu').toggleClass('active');
            $(this).toggleClass('active');
        });
        
        // Close menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.mobile-menu, .mobile-menu-toggle').length) {
                $('.mobile-menu').removeClass('active');
                $('.mobile-menu-toggle').removeClass('active');
            }
        });
    }
    
    /**
     * Image zoom
     */
    function initImageZoom() {
        $('.zoomable-image').on('click', function() {
            let imageSrc = $(this).attr('src');
            let modal = $(`
                <div class="modal fade image-zoom-modal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <img src="${imageSrc}" class="img-fluid w-100">
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append(modal);
            modal.modal('show');
            
            modal.on('hidden.bs.modal', function() {
                modal.remove();
            });
        });
    }
    
    /**
     * Auto-refresh content
     */
    function initAutoRefresh() {
        if ($('.auto-refresh').length) {
            setInterval(function() {
                $('.auto-refresh').each(function() {
                    let $element = $(this);
                    let url = $element.data('refresh-url');
                    
                    if (url) {
                        $.get(url).done(function(response) {
                            if (response.success) {
                                $element.html(response.data);
                            }
                        });
                    }
                });
            }, 30000); // 30 saniye
        }
    }
    
    /**
     * Cookie consent
     */
    function initCookieConsent() {
        if (!localStorage.getItem('cookieConsent')) {
            setTimeout(function() {
                $('#cookieConsent').fadeIn();
            }, 2000);
        }
        
        $('#acceptCookies').on('click', function() {
            localStorage.setItem('cookieConsent', 'accepted');
            $('#cookieConsent').fadeOut();
        });
    }
    
    /**
     * Error handling
     */
    function initErrorHandling() {
        // Global AJAX error handler
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            if (xhr.status === 419) {
                showNotification('Oturum süresi doldu. Sayfa yenileniyor...', 'warning');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else if (xhr.status === 500) {
                showNotification('Sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.', 'error');
            } else if (xhr.status === 0) {
                showNotification('İnternet bağlantınızı kontrol edin.', 'warning');
            }
        });
    }
    
    /**
     * PWA support
     */
    function initPWASupport() {
        // Service worker registration
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').then(function(registration) {
                console.log('ServiceWorker registration successful');
            }).catch(function(err) {
                console.log('ServiceWorker registration failed');
            });
        }
        
        // Install prompt
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            $('#installApp').show();
        });
        
        $('#installApp').on('click', function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choiceResult) {
                    deferredPrompt = null;
                    $('#installApp').hide();
                });
            }
        });
    }
    
    /**
     * Initialize all functions
     */
    function init() {
        initPageLoader();
        initNavbarEffects();
        initSmoothScroll();
        initLazyLoading();
        initScrollAnimations();
        initFormValidation();
        initTooltipsAndPopovers();
        initCopyToClipboard();
        initSearch();
        initFilters();
        initLoadMore();
        initClickTracking();
        initCounterAnimation();
        initCardEffects();
        initProgressBars();
        initBackToTop();
        initThemeSwitcher();
        initMobileMenu();
        initImageZoom();
        initAutoRefresh();
        initCookieConsent();
        initErrorHandling();
        initPWASupport();
    }
    
    // Initialize everything
    init();
    
    // Make functions available globally
    window.BonusBoss = {
        showNotification: showNotification,
        trackClick: trackClick,
        performSearch: performSearch,
        applyFilters: applyFilters
    };
});

/**
 * Global utility functions
 */

// Format number
function formatNumber(num) {
    return new Intl.NumberFormat('tr-TR').format(num);
}

// Format currency
function formatCurrency(amount, currency = 'TL') {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: currency === 'TL' ? 'TRY' : currency
    }).format(amount);
}

// Debounce function
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        let context = this;
        let args = arguments;
        let later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        let callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function() {
        let args = arguments;
        let context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Check if element is in viewport
function isInViewport(element) {
    let rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Get device type
function getDeviceType() {
    let userAgent = navigator.userAgent;
    if (/iPad/.test(userAgent)) return 'tablet';
    if (/Mobile|Android|iPhone/.test(userAgent)) return 'mobile';
    return 'desktop';
}

// Copy to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        return navigator.clipboard.writeText(text);
    } else {
        // Fallback for older browsers
        let textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            document.body.removeChild(textArea);
            return Promise.resolve();
        } catch (err) {
            document.body.removeChild(textArea);
            return Promise.reject(err);
        }
    }
}

// Share content
function shareContent(title, text, url) {
    if (navigator.share) {
        return navigator.share({
            title: title,
            text: text,
            url: url
        });
    } else {
        // Fallback for browsers that don't support Web Share API
        copyToClipboard(url).then(() => {
            alert('Link kopyalandı!');
        });
    }
}