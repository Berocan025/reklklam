-- =====================================================
-- BonusBoss Casino Deneme Bonusu Sitesi
-- Veritabanı Şeması
-- Geliştirici: BERAT K
-- Tarih: 2024
-- =====================================================

CREATE DATABASE IF NOT EXISTS `bonusboss` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bonusboss`;

-- Sistem ayarları tablosu
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `setting_type` enum('text','textarea','select','file','number') DEFAULT 'text',
  `category` varchar(100) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin kullanıcıları tablosu  
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','editor','moderator') DEFAULT 'admin',
  `status` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_ip` varchar(45) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sayfa içerikleri tablosu
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `meta_keywords` text,
  `status` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_pages_admin` (`created_by`),
  CONSTRAINT `fk_pages_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site kategorileri tablosu
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Casino siteleri tablosu
CREATE TABLE `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text,
  `url` varchar(500) NOT NULL,
  `affiliate_link` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `features` json DEFAULT NULL,
  `pros` json DEFAULT NULL,
  `cons` json DEFAULT NULL,
  `min_deposit` decimal(10,2) DEFAULT NULL,
  `license` varchar(255) DEFAULT NULL,
  `established_year` int(4) DEFAULT NULL,
  `payment_methods` json DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_vip` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `click_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `idx_status_featured` (`status`, `is_featured`),
  CONSTRAINT `fk_sites_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deneme bonusları tablosu
CREATE TABLE `bonuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'TL',
  `bonus_type` enum('money','free_spin','no_deposit','first_deposit') DEFAULT 'money',
  `description` text,
  `terms_conditions` text,
  `min_deposit` decimal(10,2) DEFAULT NULL,
  `wagering_requirement` varchar(50) DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `claim_link` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_hot` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `click_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`),
  KEY `idx_status_featured` (`status`, `is_featured`),
  KEY `idx_bonus_type` (`bonus_type`),
  CONSTRAINT `fk_bonuses_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Banner reklamları tablosu
CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gif_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `position` enum('header','sidebar','footer','popup','between_content') NOT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `click_count` int(11) DEFAULT 0,
  `impression_count` int(11) DEFAULT 0,
  `target_blank` tinyint(1) DEFAULT 1,
  `is_popup` tinyint(1) DEFAULT 0,
  `popup_delay` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_position_status` (`position`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medya dosyaları tablosu
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_type` enum('image','video','gif','document') DEFAULT 'image',
  `alt_text` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_file_type` (`file_type`),
  CONSTRAINT `fk_media_admin` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Canlı yayın bilgileri tablosu
CREATE TABLE `live_streams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `platform` enum('twitch','youtube','kick') NOT NULL,
  `channel_url` varchar(500) NOT NULL,
  `embed_code` text,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` text,
  `schedule_start` datetime DEFAULT NULL,
  `schedule_end` datetime DEFAULT NULL,
  `is_live` tinyint(1) DEFAULT 0,
  `viewer_count` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_platform_status` (`platform`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sistem logları tablosu
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `idx_action_table` (`action`, `table_name`),
  CONSTRAINT `fk_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analitik tablosu
CREATE TABLE `analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(500) NOT NULL,
  `visitor_ip` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `referer` varchar(500) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet') DEFAULT 'desktop',
  `browser` varchar(100) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visit_date` (`visit_date`),
  KEY `idx_page_url` (`page_url`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Banner tıklama takibi tablosu
CREATE TABLE `banner_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_id` int(11) NOT NULL,
  `visitor_ip` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `referer` varchar(500) DEFAULT NULL,
  `click_date` date NOT NULL,
  `click_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `banner_id` (`banner_id`),
  KEY `idx_click_date` (`click_date`),
  CONSTRAINT `fk_banner_clicks` FOREIGN KEY (`banner_id`) REFERENCES `banners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan admin kullanıcısı ekleme
INSERT INTO `admins` (`username`, `email`, `password`, `role`, `status`) VALUES 
('admin', 'admin@bonusboss.com', '$2y$10$YourHashedPasswordHere', 'super_admin', 1);

-- Varsayılan ayarlar ekleme
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `category`) VALUES
('site_title', 'BonusBoss - En İyi Casino Deneme Bonusları', 'text', 'general'),
('site_description', 'Türkiye\'nin en güvenilir casino deneme bonusu platformu. Hilesiz, anında bonus fırsatları.', 'textarea', 'general'),
('site_keywords', 'deneme bonusu, casino bonus, bedava bonus, casino siteleri', 'textarea', 'seo'),
('site_logo', '', 'file', 'general'),
('contact_email', 'info@bonusboss.com', 'text', 'contact'),
('contact_phone', '+90 (555) 123 45 67', 'text', 'contact'),
('social_facebook', 'https://facebook.com/bonusboss', 'text', 'social'),
('social_twitter', 'https://twitter.com/bonusboss', 'text', 'social'),
('social_instagram', 'https://instagram.com/bonusboss', 'text', 'social'),
('social_youtube', 'https://youtube.com/bonusboss', 'text', 'social'),
('popup_enabled', '1', 'select', 'popup'),
('popup_delay', '3', 'number', 'popup'),
('popup_title', 'Özel Deneme Bonusu Fırsatı!', 'text', 'popup'),
('popup_content', 'Sadece bugün geçerli özel deneme bonusu fırsatını kaçırma!', 'textarea', 'popup'),
('analytics_enabled', '1', 'select', 'analytics'),
('cache_enabled', '1', 'select', 'performance'),
('maintenance_mode', '0', 'select', 'general');

-- Varsayılan kategoriler ekleme
INSERT INTO `categories` (`name`, `slug`, `description`, `color`, `sort_order`) VALUES
('Özel Önerilen', 'ozel-onerilen', 'En çok tercih edilen casino siteleri', '#ff6b35', 1),
('VIP', 'vip', 'VIP kullanıcılar için özel siteler', '#gold', 2),
('Güvenilir', 'guvenilir', 'Lisanslı ve güvenilir casino siteleri', '#28a745', 3),
('Diğer Siteler', 'diger-siteler', 'Diğer casino siteleri', '#6c757d', 4);

-- Varsayılan sayfalar ekleme
INSERT INTO `pages` (`title`, `slug`, `content`, `meta_title`, `meta_description`, `status`) VALUES
('Hakkımızda', 'hakkimizda', '<h1>Hakkımızda</h1><p>BonusBoss, Türkiye\'nin en güvenilir casino deneme bonusu platformudur.</p>', 'Hakkımızda - BonusBoss', 'BonusBoss hakkında bilgi edinin.', 1),
('İletişim', 'iletisim', '<h1>İletişim</h1><p>Bizimle iletişime geçin.</p>', 'İletişim - BonusBoss', 'BonusBoss ile iletişime geçin.', 1),
('Gizlilik Politikası', 'gizlilik-politikasi', '<h1>Gizlilik Politikası</h1><p>Gizlilik politikamız.</p>', 'Gizlilik Politikası - BonusBoss', 'BonusBoss gizlilik politikası.', 1),
('Kullanım Koşulları', 'kullanim-kosullari', '<h1>Kullanım Koşulları</h1><p>Kullanım koşullarımız.</p>', 'Kullanım Koşulları - BonusBoss', 'BonusBoss kullanım koşulları.', 1);