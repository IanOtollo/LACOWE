<?php
/**
 * Authentication Class - WORKING VERSION
 */

require_once 'Database.php';
require_once 'Session.php';

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function login($username, $password)
    {
        try {
            // Check username
            $sql = "SELECT u.user_id, u.username, u.email, u.password_hash, u.role_id, u.is_active,
                           r.role_name, m.member_id
                    FROM users u
                    INNER JOIN roles r ON u.role_id = r.role_id
                    LEFT JOIN members m ON u.user_id = m.user_id
                    WHERE u.username = :username";

            $user = $this->db->query($sql)
                ->bind(':username', $username)
                ->fetch();

            // If not found by username, try email
            if (!$user) {
                $sql = "SELECT u.user_id, u.username, u.email, u.password_hash, u.role_id, u.is_active,
                               r.role_name, m.member_id
                        FROM users u
                        INNER JOIN roles r ON u.role_id = r.role_id
                        LEFT JOIN members m ON u.user_id = m.user_id
                        WHERE u.email = :email";

                $user = $this->db->query($sql)
                    ->bind(':email', $username)
                    ->fetch();
            }

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }

            if ($user['is_active'] != 1) {
                return ['success' => false, 'message' => 'Account deactivated'];
            }

            // Verify password (Supports Hashed AND Plain Text)
            $isValid = false;
            if (password_verify($password, $user['password_hash'])) {
                $isValid = true;
            }
            elseif ($password === $user['password_hash']) {
                $isValid = true;
            }

            if (!$isValid) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }

            $this->updateLastLogin($user['user_id']);
            Session::setUser($user);

            // Only regenerate if not in a restricted environment 
            // Vercel sometimes loses session on immediate regeneration
            if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                Session::regenerate();
            }

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ];

        }
        catch (Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login error: ' . $e->getMessage()];
        }
    }

    public function logout()
    {
        Session::clearUser();
        Session::destroy();
    }

    public static function isAuthenticated()
    {
        return Session::isLoggedIn();
    }

    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            redirect('login.php');
        }
    }

    public static function hasRole($allowedRoles)
    {
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        return in_array(Session::getUserRole(), $allowedRoles);
    }

    public static function requireRole($allowedRoles)
    {
        self::requireAuth();
        if (!self::hasRole($allowedRoles)) {
            Session::flash('error', 'No permission');
            redirect('dashboard.php');
        }
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        try {
            $sql = "SELECT password_hash FROM users WHERE user_id = :user_id";
            $user = $this->db->query($sql)->bind(':user_id', $userId)->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            if ($oldPassword !== $user['password_hash']) {
                return ['success' => false, 'message' => 'Current password incorrect'];
            }

            $sql = "UPDATE users SET password_hash = :password WHERE user_id = :user_id";
            $this->db->query($sql)
                ->bind(':password', $newPassword)
                ->bind(':user_id', $userId)
                ->execute();

            return ['success' => true, 'message' => 'Password changed'];

        }
        catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to change password'];
        }
    }

    public function createUser($username, $email, $password, $roleId)
    {
        try {
            $sql = "SELECT user_id FROM users WHERE username = :username";
            $existing = $this->db->query($sql)->bind(':username', $username)->fetch();

            if ($existing) {
                return ['success' => false, 'message' => 'Username exists'];
            }

            $sql = "INSERT INTO users (username, email, password_hash, role_id) 
                    VALUES (:username, :email, :password, :role_id)";

            $this->db->query($sql)
                ->bind(':username', $username)
                ->bind(':email', $email)
                ->bind(':password', $password)
                ->bind(':role_id', $roleId)
                ->execute();

            return ['success' => true, 'user_id' => $this->db->lastInsertId()];

        }
        catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create user'];
        }
    }

    private function updateLastLogin($userId)
    {
        try {
            $sql = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
            $this->db->query($sql)->bind(':user_id', $userId)->execute();
        }
        catch (Exception $e) {
        // Ignore error
        }
    }

    public function getUserById($userId)
    {
        $sql = "SELECT u.*, r.role_name 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.role_id
                WHERE u.user_id = :user_id";

        return $this->db->query($sql)->bind(':user_id', $userId)->fetch();
    }

    public function getRoles()
    {
        $sql = "SELECT * FROM roles ORDER BY role_name";
        return $this->db->query($sql)->fetchAll();
    }
}
?>