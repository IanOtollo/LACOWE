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

    // Start transaction to ensure atomic cleanup
    $conn->beginTransaction();

    // 1. Transactions
    $conn->exec("DELETE FROM transactions");
    echo "<p>✅ Cleared transactions</p>";

    // 2. Loan Repayments (if table exists)
    $conn->exec("DELETE FROM loan_repayments");
    echo "<p>✅ Cleared loan repayments</p>";

    // 3. Loans
    $conn->exec("DELETE FROM loans");
    echo "<p>✅ Cleared loans</p>";

    // 4. Loan Applications
    $conn->exec("DELETE FROM loan_applications");
    echo "<p>✅ Cleared loan applications</p>";

    // 5. Accounts
    $conn->exec("DELETE FROM accounts");
    echo "<p>✅ Cleared accounts</p>";

    // 6. Members (must happen after loans/accounts)
    $conn->exec("DELETE FROM members");
    echo "<p>✅ Cleared members</p>";

    // 7. Users (except for user_id 1 which is the admin)
    $conn->exec("DELETE FROM users WHERE user_id > 1");
    echo "<p>✅ Cleared other users (Admin kept)</p>";

    $conn->commit();
    echo "<br><p style='color: green; font-weight: bold;'>Cleanup complete! Only the admin account remains.</p>";
    echo "<a href='index.php'>Go to Dashboard</a>";

}
catch (Exception $e) {
    if (isset($conn))
        $conn->rollback();
    echo "<p style='color: red;'>❌ Error during cleanup: " . $e->getMessage() . "</p>";
}
