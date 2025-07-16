<?php
/**
 * =====================================================
 * BonusBoss Casino Deneme Bonusu Sitesi
 * Database Connection
 * Geliştirici: BERAT K
 * Tarih: 2024
 * =====================================================
 */

// Prevent direct access
if (!defined('BONUSBOSS_LOADED')) {
    require_once dirname(__FILE__) . '/../config/config.php';
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // PDO Connection
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Set timezone
    $pdo->exec("SET time_zone = '+03:00'");
    
} catch (PDOException $e) {
    // In development, show error
    if (defined('DEVELOPMENT') && DEVELOPMENT) {
        die('Database Connection Error: ' . $e->getMessage());
    }
    
    // In production, log error and show generic message
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
     * Execute a query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database Query Error: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw $e;
        }
    }
    
    /**
     * Fetch all results
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Fetch single row
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    /**
     * Fetch single column
     */
    public function fetchColumn($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }
    
    /**
     * Insert and return last insert ID
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update/Delete and return affected rows
     */
    public function execute($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Get PDO instance
     */
    public function getPdo() {
        return $this->pdo;
    }
}

// Create global database instance
$db = new Database($pdo);

// Global functions for quick access
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
?>