<?php
/**
 * Data Cleanup Script
 * WARNING: This will delete almost all data in your database!
 */

require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h1>LACOWE Data Cleanup</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();

    $tables = [
        'transactions' => 'Transactions',
        'loan_repayments' => 'Loan Repayments',
        'loans' => 'Loans',
        'loan_applications' => 'Loan Applications',
        'accounts' => 'Accounts',
        'members' => 'Members'
    ];

    foreach ($tables as $table => $label) {
        try {
            $conn->exec("DELETE FROM $table");
            echo "<p>✅ Cleared $label</p>";
        }
        catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Skipped $label (Table might not exist)</p>";
        }
    }

    // 7. Users (except for user_id 1 which is the admin)
    try {
        $conn->exec("DELETE FROM users WHERE user_id > 1");
        echo "<p>✅ Cleared other users (Admin kept)</p>";
    }
    catch (Exception $e) {
        echo "<p style='color: red;'>❌ Failed to clear users: " . $e->getMessage() . "</p>";
    }

    echo "<br><p style='color: green; font-weight: bold;'>Cleanup process finished!</p>";
    echo "<a href='index.php'>Go to Dashboard</a>";

}
catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error during cleanup: " . $e->getMessage() . "</p>";
}
