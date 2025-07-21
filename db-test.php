<?php
/**
 * Veritabanı Bağlantı Test Dosyası
 */

echo "<h1>🔍 BonusBoss Veritabanı Bağlantı Testi</h1>";

// Config dosyasını dahil et
try {
    require_once 'config/config.php';
    echo "<p>✅ Config dosyası başarıyla yüklendi</p>";
    
    // Database sabitleri kontrol et
    echo "<h2>📋 Veritabanı Ayarları:</h2>";
    echo "<ul>";
    echo "<li><strong>Host:</strong> " . DB_HOST . "</li>";
    echo "<li><strong>Database:</strong> " . DB_NAME . "</li>";
    echo "<li><strong>User:</strong> " . DB_USER . "</li>";
    echo "<li><strong>Password:</strong> " . (empty(DB_PASS) ? '(boş)' : '***') . "</li>";
    echo "<li><strong>Charset:</strong> " . DB_CHARSET . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Config yükleme hatası: " . $e->getMessage() . "</p>";
    exit;
}

// Database dosyasını dahil et
try {
    require_once 'includes/database.php';
    echo "<p>✅ Database dosyası başarıyla yüklendi</p>";
} catch (Exception $e) {
    echo "<p>❌ Database dosyası yükleme hatası: " . $e->getMessage() . "</p>";
    exit;
}

// Bağlantı testi
echo "<h2>🔗 Bağlantı Testi:</h2>";

if (isset($pdo) && $pdo instanceof PDO) {
    echo "<p>✅ PDO bağlantısı başarılı</p>";
    
    try {
        $stmt = $pdo->query('SELECT VERSION() as mysql_version');
        $result = $stmt->fetch();
        echo "<p>✅ MySQL Versiyonu: " . $result['mysql_version'] . "</p>";
    } catch (Exception $e) {
        echo "<p>❌ MySQL versiyon sorgusu hatası: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p>❌ PDO bağlantısı başarısız</p>";
}

if (isset($db) && $db instanceof Database) {
    echo "<p>✅ Database sınıfı başarılı</p>";
    
    if ($db->isConnected()) {
        echo "<p>✅ Database bağlantısı aktif</p>";
    } else {
        echo "<p>❌ Database bağlantısı aktif değil</p>";
    }
} else {
    echo "<p>❌ Database sınıfı yüklenemedi</p>";
}

// Tabloları kontrol et
echo "<h2>📊 Tablo Kontrolü:</h2>";

try {
    $tables = $db->fetchAll("SHOW TABLES");
    
    if (empty($tables)) {
        echo "<p>⚠️ Hiç tablo bulunamadı. Install.sql dosyasını çalıştırmanız gerekiyor.</p>";
        echo "<p><a href='install.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📥 Veritabanını Kur</a></p>";
    } else {
        echo "<p>✅ " . count($tables) . " tablo bulundu:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "<li>📋 " . $tableName . "</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p>❌ Tablo kontrol hatası: " . $e->getMessage() . "</p>";
}

// Fonksiyonları test et
echo "<h2>⚙️ Fonksiyon Testi:</h2>";

try {
    require_once 'includes/functions.php';
    echo "<p>✅ Functions dosyası yüklendi</p>";
    
    // Test fonksiyonları
    $money = formatMoney(1500, 'TL');
    echo "<p>✅ formatMoney(1500, 'TL') = " . $money . "</p>";
    
    $slug = createSlug("Test Başlık");
    echo "<p>✅ createSlug('Test Başlık') = " . $slug . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Fonksiyon test hatası: " . $e->getMessage() . "</p>";
}

echo "<h2>🎯 Sonuç:</h2>";
echo "<p>Tüm testler tamamlandı. Eğer yukarıda ❌ işaretli hatalar varsa, bunları düzeltmeniz gerekiyor.</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #2c3e50; }
h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
p { margin: 10px 0; }
ul { margin: 10px 0 10px 20px; }
li { margin: 5px 0; }
</style>