<?php
// Debug file to check server errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Information</h1>";
echo "<h2>PHP Version: " . phpversion() . "</h2>";
echo "<h2>Current Time: " . date('Y-m-d H:i:s') . "</h2>";

// Check if required directories exist
$dirs_to_check = [
    'includes',
    'admin', 
    'assets',
    'config',
    'uploads',
    'cache',
    'logs'
];

echo "<h2>Directory Check:</h2>";
foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        echo "✅ $dir - EXISTS<br>";
    } else {
        echo "❌ $dir - MISSING<br>";
    }
}

// Check if required files exist
$files_to_check = [
    'includes/database.php',
    'includes/functions.php',
    'includes/security.php',
    'config/config.php',
    'install.sql'
];

echo "<h2>File Check:</h2>";
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS<br>";
    } else {
        echo "❌ $file - MISSING<br>";
    }
}

// Test database connection
echo "<h2>Database Test:</h2>";
try {
    if (file_exists('includes/database.php')) {
        require_once 'includes/database.php';
        echo "✅ Database file included successfully<br>";
        
        if (isset($pdo)) {
            echo "✅ PDO connection exists<br>";
            $stmt = $pdo->query("SELECT 1");
            echo "✅ Database connection working<br>";
        } else {
            echo "❌ PDO connection not found<br>";
        }
    } else {
        echo "❌ Database file missing<br>";
    }
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

// Test basic functions
echo "<h2>Functions Test:</h2>";
try {
    if (file_exists('includes/functions.php')) {
        require_once 'includes/functions.php';
        echo "✅ Functions file included successfully<br>";
    } else {
        echo "❌ Functions file missing<br>";
    }
} catch (Exception $e) {
    echo "❌ Functions Error: " . $e->getMessage() . "<br>";
}

// Check PHP extensions
echo "<h2>PHP Extensions:</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext - LOADED<br>";
    } else {
        echo "❌ $ext - MISSING<br>";
    }
}

// Check permissions
echo "<h2>Permissions Check:</h2>";
$dirs_permission = ['uploads', 'cache', 'logs'];
foreach ($dirs_permission as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✅ $dir - WRITABLE<br>";
        } else {
            echo "❌ $dir - NOT WRITABLE<br>";
        }
    }
}

echo "<h2>Complete!</h2>";
echo "<p>If you see any ❌ marks above, those are likely causing the 500 error.</p>";
?>