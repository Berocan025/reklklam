<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Database Connection
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Config dosyasını dahil et
if (!defined('BONUSBOSS_LOADED')) {
    require_once dirname(__FILE__) . '/../config/config.php';
}

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global değişkenler
$pdo = null;
$db = null;

try {
    // Önce veritabanının var olup olmadığını kontrol et (sadece veritabanı adı olmadan)
    $dsn_check = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
    $pdo_check = new PDO($dsn_check, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Veritabanını oluştur (eğer yoksa)
    $pdo_check->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci");
    $pdo_check = null;
    
    // Şimdi gerçek bağlantıyı kur
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
        PDO::ATTR_PERSISTENT => false
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Zaman dilimini ayarla
    $pdo->exec("SET time_zone = '+03:00'");
    $pdo->exec("SET sql_mode = ''");
    
} catch (PDOException $e) {
    // Geliştirme ortamında hatayı göster
    if (defined('DEVELOPMENT') && DEVELOPMENT) {
        die('<h1>Veritabanı Bağlantı Hatası</h1><p><strong>Hata:</strong> ' . $e->getMessage() . '</p><p><strong>Host:</strong> ' . DB_HOST . '</p><p><strong>Database:</strong> ' . DB_NAME . '</p><p><strong>User:</strong> ' . DB_USER . '</p>');
    }
    
    // Üretim ortamında genel mesaj göster
    error_log('Database Connection Error: ' . $e->getMessage());
    die('Veritabanı bağlantı hatası oluştu. Lütfen daha sonra tekrar deneyin.');
}

/**
 * Database Helper Class
 */
class Database {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Query çalıştır
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (defined('DEVELOPMENT') && DEVELOPMENT) {
                echo '<h3>SQL Hatası:</h3><p>' . $e->getMessage() . '</p><p><strong>SQL:</strong> ' . $sql . '</p>';
            }
            error_log('Database Query Error: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw $e;
        }
    }
    
    /**
     * Tüm sonuçları getir
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Tek satır getir
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    /**
     * Tek satır getir (eski method adı ile uyumluluk)
     */
    public function fetchOne($sql, $params = []) {
        return $this->fetch($sql, $params);
    }
    
    /**
     * Tek kolon getir
     */
    public function fetchColumn($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }
    
    /**
     * Insert ve son ID döndür
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update/Delete ve etkilenen satır sayısını döndür
     */
    public function execute($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Transaction başlat
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Transaction commit
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Transaction rollback
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Son insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * PDO instance döndür
     */
    public function getPdo() {
        return $this->pdo;
    }
    
    /**
     * Bağlantı durumunu kontrol et
     */
    public function isConnected() {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Global database instance oluştur
$db = new Database($pdo);

// Hızlı erişim fonksiyonları
function dbQuery($sql, $params = []) {
    global $db;
    return $db->query($sql, $params);
}

function dbFetchAll($sql, $params = []) {
    global $db;
    return $db->fetchAll($sql, $params);
}

function dbFetch($sql, $params = []) {
    global $db;
    return $db->fetch($sql, $params);
}

function dbFetchOne($sql, $params = []) {
    global $db;
    return $db->fetchOne($sql, $params);
}

function dbFetchColumn($sql, $params = []) {
    global $db;
    return $db->fetchColumn($sql, $params);
}

function dbInsert($sql, $params = []) {
    global $db;
    return $db->insert($sql, $params);
}

function dbExecute($sql, $params = []) {
    global $db;
    return $db->execute($sql, $params);
}

// Veritabanı bağlantı durumunu kontrol et
if (!$db->isConnected()) {
    if (defined('DEVELOPMENT') && DEVELOPMENT) {
        die('Veritabanı bağlantısı başarısız!');
    } else {
        die('Sistem bakımda. Lütfen daha sonra tekrar deneyin.');
    }
}
?>