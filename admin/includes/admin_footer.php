    </main>
    
    <!-- Footer -->
    <footer class="admin-footer">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> <strong>BonusBoss</strong> - Tüm hakları saklıdır.
                        <span class="ms-2">Geliştirici: <strong>BERAT K</strong></span>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        Versiyon 1.0.0 | Son Güncelleme: <?php echo date('d.m.Y'); ?>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Additional Scripts -->
    <script>
    $(document).ready(function() {
        // Auto-save draft functionality
        let autosaveTimer;
        
        function setupAutosave() {
            $('form[data-autosave]').each(function() {
                const form = $(this);
                const inputs = form.find('input, textarea, select').not('[type="hidden"], [type="submit"], [type="button"]');
                
                inputs.on('input change', function() {
                    clearTimeout(autosaveTimer);
                    autosaveTimer = setTimeout(function() {
                        saveDraft(form);
                    }, 3000); // 3 saniye sonra kaydet
                });
            });
        }
        
        function saveDraft(form) {
            const formData = form.serialize();
            const formId = form.attr('id') || 'default';
            
            $.ajax({
                url: 'includes/save_draft.php',
                type: 'POST',
                data: formData + '&form_id=' + formId + '&action=save_draft',
                success: function(response) {
                    if (response.success) {
                        showToast('Taslak kaydedildi', 'info');
                    }
                }
            });
        }
        
        // Initialize autosave
        setupAutosave();
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Confirm delete buttons
        $('.btn-delete, .delete-btn').on('click', function(e) {
            if (!confirmDelete()) {
                e.preventDefault();
                return false;
            }
        });
        
        // AJAX form submissions
        $('form[data-ajax="true"]').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('[type="submit"]');
            const originalText = submitBtn.html();
            
            // Disable submit button
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> İşleniyor...');
            
            $.ajax({
                url: form.attr('action') || '',
                type: form.attr('method') || 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast(response.message || 'İşlem başarılı', 'success');
                        
                        // Redirect if specified
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                        
                        // Reload table if specified
                        if (response.reload_table) {
                            location.reload();
                        }
                    } else {
                        showToast(response.message || 'İşlem başarısız', 'danger');
                    }
                },
                error: function() {
                    showToast('Sistem hatası oluştu', 'danger');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Data tables enhancement
        if ($.fn.DataTable) {
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json'
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                columnDefs: [
                    { targets: 'no-sort', orderable: false },
                    { targets: 'text-center', className: 'text-center' }
                ]
            });
        }
        
        // File upload enhancement
        $('.file-input').on('change', function() {
            const input = $(this);
            const label = input.next('.file-label');
            const files = input[0].files;
            
            if (files.length > 0) {
                if (files.length === 1) {
                    label.text(files[0].name);
                } else {
                    label.text(files.length + ' dosya seçildi');
                }
            } else {
                label.text('Dosya seçin');
            }
        });
        
        // Image preview
        $('.image-input').on('change', function() {
            const input = this;
            const preview = $(input).siblings('.image-preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.html('<img src="' + e.target.result + '" class="img-fluid" style="max-height: 200px;">');
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        });
        
        // Copy to clipboard functionality
        $('.copy-btn').on('click', function() {
            const target = $(this).data('target');
            const text = $(target).val() || $(target).text();
            
            navigator.clipboard.writeText(text).then(function() {
                showToast('Panoya kopyalandı', 'success');
            });
        });
        
        // Quick search functionality
        $('#quickSearch').on('input', function() {
            const query = $(this).val().toLowerCase();
            const searchContainer = $('.search-container');
            
            if (query.length > 2) {
                searchContainer.find('.search-item').each(function() {
                    const item = $(this);
                    const text = item.text().toLowerCase();
                    
                    if (text.includes(query)) {
                        item.show();
                    } else {
                        item.hide();
                    }
                });
            } else {
                searchContainer.find('.search-item').show();
            }
        });
        
        // Bulk actions
        $('.select-all').on('change', function() {
            const checked = $(this).prop('checked');
            $('.select-item').prop('checked', checked);
            updateBulkActions();
        });
        
        $('.select-item').on('change', function() {
            updateBulkActions();
        });
        
        function updateBulkActions() {
            const selected = $('.select-item:checked').length;
            const bulkActions = $('.bulk-actions');
            
            if (selected > 0) {
                bulkActions.removeClass('d-none');
                bulkActions.find('.selected-count').text(selected);
            } else {
                bulkActions.addClass('d-none');
            }
        }
        
        // Status toggle switches
        $('.status-toggle').on('change', function() {
            const toggle = $(this);
            const id = toggle.data('id');
            const table = toggle.data('table');
            const status = toggle.prop('checked') ? 1 : 0;
            
            $.ajax({
                url: 'includes/toggle_status.php',
                type: 'POST',
                data: {
                    id: id,
                    table: table,
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Durum güncellendi', 'success');
                    } else {
                        toggle.prop('checked', !toggle.prop('checked'));
                        showToast('Durum güncellenemedi', 'danger');
                    }
                },
                error: function() {
                    toggle.prop('checked', !toggle.prop('checked'));
                    showToast('Sistem hatası', 'danger');
                }
            });
        });
        
        // Real-time notifications
        function checkNotifications() {
            $.ajax({
                url: 'includes/get_notifications.php',
                type: 'GET',
                success: function(response) {
                    if (response.notifications && response.notifications.length > 0) {
                        updateNotificationBadge(response.count);
                        updateNotificationList(response.notifications);
                    }
                }
            });
        }
        
        function updateNotificationBadge(count) {
            const badge = $('.notification-badge');
            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }
        }
        
        function updateNotificationList(notifications) {
            const container = $('.notification-dropdown .notification-item').parent();
            // Update notification list
        }
        
        // Check notifications every 30 seconds
        setInterval(checkNotifications, 30000);
        
        // Session timeout warning
        let sessionTimeout = 3600; // 1 hour
        let warningShown = false;
        
        function checkSessionTimeout() {
            sessionTimeout--;
            
            if (sessionTimeout <= 300 && !warningShown) { // 5 minutes warning
                warningShown = true;
                showToast('Oturumunuz 5 dakika içinde sona erecek. Lütfen sayfayı yenileyin.', 'warning');
            }
            
            if (sessionTimeout <= 0) {
                alert('Oturumunuz sona erdi. Lütfen tekrar giriş yapın.');
                window.location.href = 'login.php';
            }
        }
        
        setInterval(checkSessionTimeout, 1000);
        
        // Reset session timeout on activity
        $(document).on('click keypress', function() {
            sessionTimeout = 3600;
            warningShown = false;
        });
    });
    
    // Global utility functions
    window.adminUtils = {
        formatNumber: function(num) {
            return new Intl.NumberFormat('tr-TR').format(num);
        },
        
        formatCurrency: function(amount, currency = 'TL') {
            return new Intl.NumberFormat('tr-TR', {
                style: 'currency',
                currency: currency === 'TL' ? 'TRY' : currency
            }).format(amount);
        },
        
        formatDate: function(date) {
            return new Intl.DateTimeFormat('tr-TR').format(new Date(date));
        },
        
        generateSlug: function(text) {
            return text
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
        },
        
        validateEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        validateURL: function(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        }
    };
    </script>

    <!-- Additional CSS -->
    <style>
    .admin-footer {
        margin-left: var(--admin-sidebar-width);
        background: white;
        border-top: 1px solid #e2e8f0;
        padding: 15px 30px;
        margin-top: 50px;
    }
    
    @media (max-width: 992px) {
        .admin-footer {
            margin-left: 0;
            padding: 15px;
        }
    }
    
    /* Loading states */
    .loading {
        position: relative;
        pointer-events: none;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    
    .loading::before {
        content: '⟳';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2rem;
        animation: spin 1s linear infinite;
        z-index: 11;
    }
    
    @keyframes spin {
        from { transform: translate(-50%, -50%) rotate(0deg); }
        to { transform: translate(-50%, -50%) rotate(360deg); }
    }
    
    /* Custom scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .sidebar::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .sidebar::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* Form enhancements */
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        color: var(--admin-primary);
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }
    
    /* Status badges */
    .status-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    
    .status-active { background-color: #10b981; }
    .status-inactive { background-color: #6b7280; }
    .status-pending { background-color: #f59e0b; }
    .status-featured { background-color: #8b5cf6; }
    
    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .action-buttons .btn {
        padding: 4px 8px;
        font-size: 0.8rem;
    }
    
    /* Quick stats */
    .quick-stat {
        text-align: center;
        padding: 15px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .quick-stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--admin-primary);
    }
    
    .quick-stat-label {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 4px;
    }
    </style>

</body>
</html>

<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Admin Panel Footer
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */
?>