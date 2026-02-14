<?php
/**
 * Database Session Setup Script
 * Run this once to create the required sessions table.
 */

require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h1>LACOWE Session Setup</h1>";

try {
    $db = new Database();
    $driver = DB_DRIVER;

    if ($driver === 'pgsql') {
        $sql = "CREATE TABLE IF NOT EXISTS sessions (
            id TEXT PRIMARY KEY,
            data TEXT NOT NULL,
            expiry INTEGER NOT NULL
        )";
    }
    else {
        $sql = "CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(255) PRIMARY KEY,
            data TEXT NOT NULL,
            expiry INT(11) NOT NULL,
            INDEX (expiry)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    $db->getConnection()->exec($sql);
    echo "<p style='color: green;'>✅ Success: 'sessions' table created or already exists.</p>";
    echo "<p>You can now delete this file for security.</p>";
    echo "<a href='index.php'>Go to Login</a>";

}
catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please ensure your database credentials in <code>config/config.php</code> are correct and the user has CREATE TABLE permissions.</p>";
}
