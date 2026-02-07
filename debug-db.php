<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

$db = new Database();

echo "=== DATABASE DIAGNOSTIC ===\n\n";

// Check members table
echo "1. MEMBERS TABLE:\n";
$sql = "SELECT member_id, first_name, last_name, membership_status FROM members LIMIT 5";
$members = $db->getConnection()->query($sql)->fetchAll();
echo "Total rows returned: " . count($members) . "\n";
foreach ($members as $m) {
    echo "  - ID: {$m['member_id']}, Name: {$m['first_name']} {$m['last_name']}, Status: {$m['membership_status']}\n";
}

echo "\n2. COUNT WITH membership_status = 'Active':\n";
$sql = "SELECT COUNT(*) as total FROM members WHERE membership_status = 'Active'";
$result = $db->getConnection()->query($sql)->fetch();
echo "  Count: " . $result['total'] . "\n";

echo "\n3. COUNT ALL MEMBERS:\n";
$sql = "SELECT COUNT(*) as total FROM members";
$result = $db->getConnection()->query($sql)->fetch();
echo "  Count: " . $result['total'] . "\n";

echo "\n4. GROUP BY membership_status:\n";
$sql = "SELECT membership_status, COUNT(*) as count FROM members GROUP BY membership_status";
$results = $db->getConnection()->query($sql)->fetchAll();
foreach ($results as $r) {
    echo "  - Status: '{$r['membership_status']}', Count: {$r['count']}\n";
}

echo "\n5. LOANS TABLE:\n";
$sql = "SELECT COUNT(*) as total FROM loans";
$result = $db->getConnection()->query($sql)->fetch();
echo "  Total loans: " . $result['total'] . "\n";

echo "\n=== END DIAGNOSTIC ===\n";
?>
