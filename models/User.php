<?php
/**
 * User Model
 */

require_once __DIR__ . '/../includes/Database.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getById($userId)
    {
        $sql = "SELECT u.*, r.role_name 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.role_id
                WHERE u.user_id = :user_id";
        return $this->db->query($sql)->bind(':user_id', $userId)->fetch();
    }

    public function updateEmail($userId, $email)
    {
        try {
            // Check if email exists for another user
            $sql = "SELECT user_id FROM users WHERE email = :email AND user_id != :user_id";
            $existing = $this->db->query($sql)
                ->bind(':email', $email)
                ->bind(':user_id', $userId)
                ->fetch();

            if ($existing) {
                return ['success' => false, 'message' => 'Email already in use'];
            }

            $sql = "UPDATE users SET email = :email WHERE user_id = :user_id";
            $this->db->query($sql)
                ->bind(':email', $email)
                ->bind(':user_id', $userId)
                ->execute();

            return ['success' => true, 'message' => 'Email updated successfully'];
        }
        catch (Exception $e) {
            error_log("User Email Update Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update email'];
        }
    }
}
?>
