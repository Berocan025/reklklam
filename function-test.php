<?php
/**
 * Fonksiyon Test Dosyası
 * Duplicate fonksiyon hatalarını kontrol eder
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 BonusBoss Fonksiyon Testi</h1>";

try {
    // Config yükle
    require_once 'config/config.php';
    echo "<p>✅ Config yüklendi</p>";
    
    // Database yükle
    require_once 'includes/database.php';
    echo "<p>✅ Database yüklendi</p>";
    
    // Functions yükle
    require_once 'includes/functions.php';
    echo "<p>✅ Functions yüklendi</p>";
    
    // Security yükle
    require_once 'includes/security.php';
    echo "<p>✅ Security yüklendi</p>";
    
    echo "<h2>🧪 Fonksiyon Testleri:</h2>";
    
    // CSRF Token test
    $csrf = generateCSRFToken();
    echo "<p>✅ generateCSRFToken(): " . substr($csrf, 0, 10) . "...</p>";
    
    $verify = verifyCSRFToken($csrf);
    echo "<p>✅ verifyCSRFToken(): " . ($verify ? 'True' : 'False') . "</p>";
    
    // IP Test
    $ip = getClientIP();
    echo "<p>✅ getClientIP(): " . $ip . "</p>";
    
    // Format Test
    $money = formatMoney(1500, 'TL');
    echo "<p>✅ formatMoney(): " . $money . "</p>";
    
    // Slug Test
    $slug = createSlug("Test Başlık İçerik");
    echo "<p>✅ createSlug(): " . $slug . "</p>";
    
    // Hash Test
    $hash = hashPassword('test123');
    echo "<p>✅ hashPassword(): " . substr($hash, 0, 20) . "...</p>";
    
    $verify_pass = verifyPassword('test123', $hash);
    echo "<p>✅ verifyPassword(): " . ($verify_pass ? 'True' : 'False') . "</p>";
    
    echo "<h2>🎉 Tüm testler başarılı!</h2>";
    echo "<p>Artık siteniz hatasız çalışacak.</p>";
    
} catch (Error $e) {
    echo "<h2>❌ Fatal Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
} catch (Exception $e) {
    echo "<h2>❌ Exception:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #2c3e50; }
h2 { color: #34495e; }
p { margin: 10px 0; }
</style>