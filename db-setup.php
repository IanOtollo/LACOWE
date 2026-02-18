<?php
/**
 * Database Setup / Repair Script
 * Run this once to create missing tables on Supabase/PostgreSQL
 */
require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h2>LACOWE Database Repair Utility</h2>";

try {
    $db = new Database();
    $driver = DB_DRIVER;

    echo "Current Driver: <strong>$driver</strong><br><br>";

    if ($driver === 'pgsql') {
        $sql = "CREATE TABLE IF NOT EXISTS bank_accounts (
            bank_account_id SERIAL PRIMARY KEY,
            member_id INT NOT NULL REFERENCES members(member_id) ON DELETE CASCADE,
            bank_name VARCHAR(100) NOT NULL,
            account_name VARCHAR(100) NOT NULL,
            account_number VARCHAR(50) NOT NULL,
            branch_name VARCHAR(100),
            swift_code VARCHAR(20),
            is_verified BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );";
    } else {
        $sql = "CREATE TABLE IF NOT EXISTS bank_accounts (
            bank_account_id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT NOT NULL,
            bank_name VARCHAR(100) NOT NULL,
            account_name VARCHAR(100) NOT NULL,
            account_number VARCHAR(50) NOT NULL,
            branch_name VARCHAR(100),
            swift_code VARCHAR(20),
            is_verified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE
        ) ENGINE=InnoDB;";
    }

    echo "Executing SQL...<br>";
    $db->getConnection()->exec($sql);

    echo "<h3 style='color: green;'>SUCCESS: 'bank_accounts' table is now ready!</h3>";
    echo "<p>You can now go back to your <a href='dashboard.php'>Dashboard</a>.</p>";
    echo "<p><strong>Security Note:</strong> Please delete this file (<code>db-setup.php</code>) after use.</p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>FAILURE: " . $e->getMessage() . "</h3>";
    echo "<p>Please ensure your database credentials in <code>config/config.php</code> are correct.</p>";
}
