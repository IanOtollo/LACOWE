<?php
/**
 * Server Health Check Script
 * Upload this to your InfinityFree /htdocs folder to test your connection.
 */

require_once 'config/config.php';

echo "<h1>LACOWE MIS Server Health Check</h1>";

// 1. Check PHP Version
echo "<h2>1. PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// 2. Check Database Connection
echo "<h2>2. Database Connection</h2>";
echo "Attempting to connect to: " . DB_HOST . " (Database: " . DB_NAME . ")<br>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "<p style='color: green;'>✅ Success: Connected to the database!</p>";

    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
        echo "Found " . count($tables) . " tables:<br>";
        echo "<ul><li>" . implode("</li><li>", $tables) . "</li></ul>";
    }
    else {
        echo "<p style='color: orange;'>⚠️ Warning: Connection successful but no tables found. Did you import schema.sql?</p>";
    }


}
catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: Could not connect to the database.</p>";
    echo "Error Message: " . $e->getMessage() . "<br>";
    echo "<p><strong>Troubleshooting:</strong><br>";
    echo "1. Check if DB_HOST, DB_NAME, DB_USER, and DB_PASS in <code>config/config.php</code> are correct.<br>";
    echo "2. Ensure you are using the credentials provided by InfinityFree (not your local root/root).<br>";
    echo "3. Make sure your database name starts with <code>if0_</code> or similar as assigned by the host.</p>";
}

// 3. Check PWA Files
echo "<h2>3. PWA Assets</h2>";
$files = ['manifest.json', 'sw.js', 'assets/css/style.css'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ Found: $file<br>";
    }
    else {
        echo "❌ Missing: $file<br>";
    }
}

echo "<br><hr><p><em>Delete this file after verification for security!</em></p>";
?>
