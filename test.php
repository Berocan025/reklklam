<?php
// Simple test file
echo "<h1>PHP Test</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

if (extension_loaded('pdo')) {
    echo "<p>✅ PDO Extension: Available</p>";
} else {
    echo "<p>❌ PDO Extension: Missing</p>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<p>✅ PDO MySQL: Available</p>";
} else {
    echo "<p>❌ PDO MySQL: Missing</p>";
}

echo "<p>Test completed successfully!</p>";
?>