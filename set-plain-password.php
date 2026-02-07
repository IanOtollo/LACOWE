<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'includes/Database.php';

echo "<h2>Setting Plain Text Password (Development Only)</h2>";

$db = new Database();
$db->getConnection();

// Set plain text password
$password = 'Admin@123';

$sql = "UPDATE users SET password_hash = :password WHERE username = 'admin'";
$db->query($sql)->bind(':password', $password)->execute();

echo "<p style='color: green; font-size: 20px;'>✅ Plain Password Set!</p>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> Admin@123</p>";
echo "<br>";
echo "<a href='login.php' style='background: #1e40af; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px;'>Go to Login</a>";
?>