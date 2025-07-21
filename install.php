<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Otomatik Kurulum Dosyası
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Config dosyasını dahil et
require_once 'config/config.php';

echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>BonusBoss - Kurulum</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .container { max-width: 800px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; }
        h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px 10px 0; }
        .btn:hover { background: #005a87; }
        .step { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .step-title { font-weight: bold; color: #2c3e50; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>🚀 BonusBoss Kurulum Sihirbazı</h1>";

// Kurulum adımları
$step = $_GET['step'] ?? 1;

if ($step == 1) {
    echo "<div class='step'>
        <div class='step-title'>📋 Adım 1: Sistem Gereksinimleri Kontrolü</div>";
    
    $requirements = [
        'PHP 7.4+' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'JSON Extension' => extension_loaded('json'),
        'mbstring Extension' => extension_loaded('mbstring'),
        'Uploads Directory Writable' => is_writable('uploads') || @mkdir('uploads', 0755, true),
        'Cache Directory Writable' => is_writable('cache') || @mkdir('cache', 0755, true),
        'Logs Directory Writable' => is_writable('logs') || @mkdir('logs', 0755, true)
    ];
    
    echo "<table>";
    echo "<tr><th>Gereksinim</th><th>Durum</th></tr>";
    
    $allOk = true;
    foreach ($requirements as $req => $status) {
        $icon = $status ? '✅' : '❌';
        $color = $status ? 'green' : 'red';
        echo "<tr><td>$req</td><td style='color: $color'>$icon " . ($status ? 'Tamam' : 'Hata') . "</td></tr>";
        if (!$status) $allOk = false;
    }
    echo "</table>";
    
    if ($allOk) {
        echo "<div class='success'>Tüm gereksinimler karşılanıyor! Kuruluma devam edebilirsiniz.</div>";
        echo "<a href='?step=2' class='btn'>Sonraki Adım →</a>";
    } else {
        echo "<div class='error'>Bazı gereksinimler karşılanmıyor. Lütfen bunları düzeltin ve sayfayı yenileyin.</div>";
        echo "<a href='?step=1' class='btn'>Yeniden Kontrol Et</a>";
    }
    
    echo "</div>";
    
} elseif ($step == 2) {
    echo "<div class='step'>
        <div class='step-title'>🔗 Adım 2: Veritabanı Bağlantı Testi</div>";
    
    try {
        // Veritabanı bağlantısını test et
        $dsn_check = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
        $pdo_check = new PDO($dsn_check, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        echo "<div class='success'>✅ MySQL sunucusuna bağlantı başarılı!</div>";
        
        // Veritabanını oluştur
        $pdo_check->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci");
        echo "<div class='success'>✅ Veritabanı oluşturuldu/kontrol edildi: " . DB_NAME . "</div>";
        
        echo "<div class='info'>
            <strong>Bağlantı Bilgileri:</strong><br>
            Host: " . DB_HOST . "<br>
            Veritabanı: " . DB_NAME . "<br>
            Kullanıcı: " . DB_USER . "<br>
            Karakter Seti: " . DB_CHARSET . "
        </div>";
        
        echo "<a href='?step=3' class='btn'>Sonraki Adım →</a>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Veritabanı bağlantı hatası: " . $e->getMessage() . "</div>";
        echo "<div class='info'>
            <strong>Kontrol Edilecekler:</strong><br>
            • MySQL servisi çalışıyor mu?<br>
            • Kullanıcı adı ve şifre doğru mu?<br>
            • config/config.php dosyasındaki ayarlar doğru mu?
        </div>";
        echo "<a href='?step=2' class='btn'>Tekrar Dene</a>";
        echo "<a href='db-test.php' class='btn' target='_blank'>Detaylı Test</a>";
    }
    
    echo "</div>";
    
} elseif ($step == 3) {
    echo "<div class='step'>
        <div class='step-title'>📊 Adım 3: Veritabanı Tablolarını Oluştur</div>";
    
    try {
        // Database bağlantısını dahil et
        require_once 'includes/database.php';
        
        // SQL dosyasını oku
        $sql = file_get_contents('install.sql');
        
        if (!$sql) {
            throw new Exception('install.sql dosyası okunamadı!');
        }
        
        // SQL komutlarını ayır (USE ve CREATE DATABASE komutlarını atla)
        $commands = explode(';', $sql);
        $executed = 0;
        $errors = 0;
        
        foreach ($commands as $command) {
            $command = trim($command);
            if (empty($command) || 
                stripos($command, 'CREATE DATABASE') !== false || 
                stripos($command, 'USE ') !== false) {
                continue;
            }
            
            try {
                $pdo->exec($command);
                $executed++;
            } catch (PDOException $e) {
                $errors++;
                echo "<div class='error'>SQL Hatası: " . $e->getMessage() . "</div>";
            }
        }
        
        echo "<div class='success'>✅ $executed SQL komutu başarıyla çalıştırıldı!</div>";
        
        if ($errors > 0) {
            echo "<div class='error'>⚠️ $errors hata oluştu. Bazı tablolar zaten var olabilir.</div>";
        }
        
        // Tabloları listele
        $tables = $db->fetchAll("SHOW TABLES");
        echo "<div class='info'><strong>Oluşturulan Tablolar (" . count($tables) . "):</strong><br>";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "• $tableName<br>";
        }
        echo "</div>";
        
        echo "<a href='?step=4' class='btn'>Sonraki Adım →</a>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Tablo oluşturma hatası: " . $e->getMessage() . "</div>";
        echo "<a href='?step=3' class='btn'>Tekrar Dene</a>";
    }
    
    echo "</div>";
    
} elseif ($step == 4) {
    echo "<div class='step'>
        <div class='step-title'>👤 Adım 4: Admin Kullanıcısı Oluştur</div>";
    
    if ($_POST) {
        try {
            require_once 'includes/database.php';
            require_once 'includes/functions.php';
            require_once 'includes/security.php';
            
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validasyon
            if (empty($username) || empty($email) || empty($password)) {
                throw new Exception('Tüm alanları doldurun!');
            }
            
            if ($password !== $confirm_password) {
                throw new Exception('Şifreler eşleşmiyor!');
            }
            
            if (strlen($password) < 6) {
                throw new Exception('Şifre en az 6 karakter olmalı!');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Geçersiz email adresi!');
            }
            
            // Admin kullanıcısını oluştur
            $hashedPassword = hashPassword($password);
            $adminId = $db->insert(
                "INSERT INTO admins (username, email, password, role, status, created_at) VALUES (?, ?, ?, 'super_admin', 1, NOW())",
                [$username, $email, $hashedPassword]
            );
            
            if ($adminId) {
                echo "<div class='success'>✅ Admin kullanıcısı başarıyla oluşturuldu!<br>
                    <strong>Kullanıcı Adı:</strong> $username<br>
                    <strong>Email:</strong> $email<br>
                    <strong>Rol:</strong> Super Admin</div>";
                
                echo "<a href='?step=5' class='btn'>Sonraki Adım →</a>";
            } else {
                throw new Exception('Admin kullanıcısı oluşturulamadı!');
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Hata: " . $e->getMessage() . "</div>";
        }
    }
    
    if (!isset($adminId)) {
        echo "<form method='post'>
            <table>
                <tr><td><strong>Kullanıcı Adı:</strong></td><td><input type='text' name='username' required style='width: 200px; padding: 5px;'></td></tr>
                <tr><td><strong>Email:</strong></td><td><input type='email' name='email' required style='width: 200px; padding: 5px;'></td></tr>
                <tr><td><strong>Şifre:</strong></td><td><input type='password' name='password' required style='width: 200px; padding: 5px;'></td></tr>
                <tr><td><strong>Şifre Tekrar:</strong></td><td><input type='password' name='confirm_password' required style='width: 200px; padding: 5px;'></td></tr>
            </table>
            <button type='submit' class='btn'>Admin Oluştur</button>
        </form>";
    }
    
    echo "</div>";
    
} elseif ($step == 5) {
    echo "<div class='step'>
        <div class='step-title'>⚙️ Adım 5: Temel Ayarları Yap</div>";
    
    if ($_POST) {
        try {
            require_once 'includes/database.php';
            
            $site_title = trim($_POST['site_title']);
            $site_description = trim($_POST['site_description']);
            $site_keywords = trim($_POST['site_keywords']);
            $contact_email = trim($_POST['contact_email']);
            
            // Ayarları veritabanına kaydet
            $settings = [
                'site_title' => $site_title,
                'site_description' => $site_description,
                'site_keywords' => $site_keywords,
                'contact_email' => $contact_email,
                'site_status' => '1',
                'maintenance_mode' => '0',
                'analytics_enabled' => '1'
            ];
            
            foreach ($settings as $key => $value) {
                $db->query(
                    "INSERT INTO settings (setting_key, setting_value, created_at) VALUES (?, ?, NOW()) 
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
                    [$key, $value]
                );
            }
            
            echo "<div class='success'>✅ Temel ayarlar başarıyla kaydedildi!</div>";
            echo "<a href='?step=6' class='btn'>Kurulumu Tamamla →</a>";
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Ayar kaydetme hatası: " . $e->getMessage() . "</div>";
        }
    }
    
    if (!$_POST) {
        echo "<form method='post'>
            <table>
                <tr><td><strong>Site Başlığı:</strong></td><td><input type='text' name='site_title' value='BonusBoss' style='width: 300px; padding: 5px;'></td></tr>
                <tr><td><strong>Site Açıklaması:</strong></td><td><textarea name='site_description' style='width: 300px; height: 60px; padding: 5px;'>En iyi casino deneme bonusları ve güvenilir casino siteleri</textarea></td></tr>
                <tr><td><strong>Anahtar Kelimeler:</strong></td><td><input type='text' name='site_keywords' value='deneme bonusu, casino bonus, bedava bonus, casino siteleri' style='width: 300px; padding: 5px;'></td></tr>
                <tr><td><strong>İletişim Email:</strong></td><td><input type='email' name='contact_email' value='admin@bonusboss.com' style='width: 300px; padding: 5px;'></td></tr>
            </table>
            <button type='submit' class='btn'>Ayarları Kaydet</button>
        </form>";
    }
    
    echo "</div>";
    
} elseif ($step == 6) {
    echo "<div class='step'>
        <div class='step-title'>🎉 Kurulum Tamamlandı!</div>";
    
    echo "<div class='success'>
        <h3>Tebrikler! BonusBoss kurulumu başarıyla tamamlandı.</h3>
        <p><strong>Şimdi neler yapabilirsiniz?</strong></p>
        <ul>
            <li>🏠 <a href='index.php'>Ana sayfayı ziyaret edin</a></li>
            <li>🛡️ <a href='admin/login.php'>Admin paneline giriş yapın</a></li>
            <li>⚙️ <a href='admin/settings.php'>Site ayarlarını düzenleyin</a></li>
            <li>🎰 Casino sitelerini ve bonusları ekleyin</li>
        </ul>
    </div>";
    
    echo "<div class='info'>
        <strong>Güvenlik İpuçları:</strong><br>
        • install.php ve db-test.php dosyalarını silin<br>
        • config/config.php dosyasında DEVELOPMENT mod'unu false yapın<br>
        • Güçlü şifreler kullanın<br>
        • Düzenli yedekleme yapın
    </div>";
    
    echo "<a href='index.php' class='btn'>🏠 Ana Sayfaya Git</a>";
    echo "<a href='admin/login.php' class='btn'>🛡️ Admin Paneli</a>";
    echo "<a href='#' onclick='if(confirm(\"install.php dosyasını silmek istediğinizden emin misiniz?\")) { window.location.href=\"?delete_install=1\"; }' class='btn' style='background: #dc3545;'>🗑️ install.php Dosyasını Sil</a>";
    
    echo "</div>";
}

// install.php dosyasını sil
if (isset($_GET['delete_install'])) {
    if (unlink(__FILE__)) {
        echo "<script>alert('install.php dosyası başarıyla silindi!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('install.php dosyası silinemedi. Manuel olarak silin.');</script>";
    }
}

echo "<div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;'>
    <p>BonusBoss Casino Deneme Bonusu Sitesi<br>
    Geliştirici: <strong>BERAT K</strong> | 2024</p>
</div>";

echo "</div></body></html>";
?>