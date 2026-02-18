<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

try {
    $db = new Database();
    $sql = "SELECT u.username, r.role_name, u.is_active, u.last_login 
            FROM users u 
            JOIN roles r ON u.role_id = r.role_id";
    $users = $db->query($sql)->fetchAll();

    echo "USER LIST AND STATUS:\n";
    echo "--------------------\n";
    foreach ($users as $user) {
        $status = $user['is_active'] ? 'Active' : 'Inactive';
        $lastLogin = $user['last_login'] ? $user['last_login'] : 'Never';
        echo sprintf(
            "[%s] %-15s | Status: %-8s | Last Login: %s\n",
            $user['role_name'],
            $user['username'],
            $status,
            $lastLogin
        );
    }
    echo "--------------------\n";
    echo "NOTE: Passwords are hashed (bcrypt) and cannot be viewed in plain text for security.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
