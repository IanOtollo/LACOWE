<?php
/**
 * Member Model
 * Handles all member-related database operations
 * LACOWE Welfare MIS
 */

require_once __DIR__ . '/../includes/Database.php';

class Member {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create new member
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            // Generate member number
            $memberNumber = $this->generateMemberNumber();

            // Insert member
            $sql = "INSERT INTO members (user_id, member_number, first_name, last_name, id_number, phone_number, 
                                        email, date_of_birth, gender, address, city, postal_code, 
                                        employment_status, department, payroll_number, date_joined, membership_status)
                    VALUES (:user_id, :member_number, :first_name, :last_name, :id_number, :phone_number,
                           :email, :date_of_birth, :gender, :address, :city, :postal_code,
                           :employment_status, :department, :payroll_number, :date_joined, :membership_status)";

            $this->db->query($sql)
                    ->bind(':user_id', $data['user_id'])
                    ->bind(':member_number', $memberNumber)
                    ->bind(':first_name', $data['first_name'])
                    ->bind(':last_name', $data['last_name'])
                    ->bind(':id_number', $data['id_number'])
                    ->bind(':phone_number', $data['phone_number'])
                    ->bind(':email', $data['email'] ?? null)
                    ->bind(':date_of_birth', $data['date_of_birth'] ?? null)
                    ->bind(':gender', $data['gender'] ?? null)
                    ->bind(':address', $data['address'] ?? null)
                    ->bind(':city', $data['city'] ?? null)
                    ->bind(':postal_code', $data['postal_code'] ?? null)
                    ->bind(':employment_status', $data['employment_status'] ?? 'Active')
                    ->bind(':department', $data['department'] ?? null)
                    ->bind(':payroll_number', $data['payroll_number'] ?? null)
                    ->bind(':date_joined', $data['date_joined'] ?? date('Y-m-d'))
                    ->bind(':membership_status', 'Active')
                    ->execute();

            $memberId = $this->db->lastInsertId();

            // Create default savings account
            $accountNumber = $this->generateAccountNumber();
            $accountSql = "INSERT INTO accounts (member_id, account_number, account_type, date_opened, account_status)
                          VALUES (:member_id, :account_number, 'Savings', :date_opened, 'Active')";
            
            $this->db->query($accountSql)
                    ->bind(':member_id', $memberId)
                    ->bind(':account_number', $accountNumber)
                    ->bind(':date_opened', date('Y-m-d'))
                    ->execute();

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Member registered successfully',
                'member_id' => $memberId,
                'member_number' => $memberNumber
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Member Creation Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to register member: ' . $e->getMessage()];
        }
    }

    /**
     * Update member
     */
    public function update($memberId, $data) {
        try {
            $sql = "UPDATE members SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    phone_number = :phone_number,
                    email = :email,
                    date_of_birth = :date_of_birth,
                    gender = :gender,
                    address = :address,
                    city = :city,
                    postal_code = :postal_code,
                    employment_status = :employment_status,
                    department = :department,
                    payroll_number = :payroll_number,
                    membership_status = :membership_status
                    WHERE member_id = :member_id";

            $this->db->query($sql)
                    ->bind(':member_id', $memberId)
                    ->bind(':first_name', $data['first_name'])
                    ->bind(':last_name', $data['last_name'])
                    ->bind(':phone_number', $data['phone_number'])
                    ->bind(':email', $data['email'] ?? null)
                    ->bind(':date_of_birth', $data['date_of_birth'] ?? null)
                    ->bind(':gender', $data['gender'] ?? null)
                    ->bind(':address', $data['address'] ?? null)
                    ->bind(':city', $data['city'] ?? null)
                    ->bind(':postal_code', $data['postal_code'] ?? null)
                    ->bind(':employment_status', $data['employment_status'])
                    ->bind(':department', $data['department'] ?? null)
                    ->bind(':payroll_number', $data['payroll_number'] ?? null)
                    ->bind(':membership_status', $data['membership_status'])
                    ->execute();

            return ['success' => true, 'message' => 'Member updated successfully'];

        } catch (Exception $e) {
            error_log("Member Update Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update member'];
        }
    }

    /**
     * Get member by ID
     */
    public function getById($memberId) {
        $sql = "SELECT m.*, u.username, u.email as user_email, u.is_active as user_active
                FROM members m
                INNER JOIN users u ON m.user_id = u.user_id
                WHERE m.member_id = :member_id";
        
        return $this->db->query($sql)->bind(':member_id', $memberId)->fetch();
    }

    /**
     * Get member by user ID
     */
    public function getByUserId($userId) {
        $sql = "SELECT m.*, u.username, u.email as user_email
                FROM members m
                INNER JOIN users u ON m.user_id = u.user_id
                WHERE m.user_id = :user_id";
        
        return $this->db->query($sql)->bind(':user_id', $userId)->fetch();
    }

    /**
     * Get member by member number
     */
    public function getByMemberNumber($memberNumber) {
        $sql = "SELECT * FROM members WHERE member_number = :member_number";
        return $this->db->query($sql)->bind(':member_number', $memberNumber)->fetch();
    }

    /**
     * Get all members
     */
    public function getAll($filters = [], $limit = null, $offset = 0) {
        $sql = "SELECT m.*, u.username,
                       (SELECT SUM(balance) FROM accounts WHERE member_id = m.member_id) as total_balance
                FROM members m
                INNER JOIN users u ON m.user_id = u.user_id
                WHERE 1=1";

        if (!empty($filters['membership_status'])) {
            $sql .= " AND m.membership_status = :membership_status";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (m.first_name LIKE :search OR m.last_name LIKE :search OR m.member_number LIKE :search OR m.id_number LIKE :search)";
        }

        $sql .= " ORDER BY m.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $query = $this->db->query($sql);

        if (!empty($filters['membership_status'])) {
            $query->bind(':membership_status', $filters['membership_status']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->bind(':search', $searchTerm);
        }

        if ($limit) {
            $query->bind(':limit', $limit, PDO::PARAM_INT);
            $query->bind(':offset', $offset, PDO::PARAM_INT);
        }

        return $query->fetchAll();
    }

    /**
     * Count members
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM members WHERE 1=1";

        if (!empty($filters['membership_status'])) {
            $sql .= " AND membership_status = :membership_status";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE :search OR last_name LIKE :search OR member_number LIKE :search OR id_number LIKE :search)";
        }

        $query = $this->db->query($sql);

        if (!empty($filters['membership_status'])) {
            $query->bind(':membership_status', $filters['membership_status']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->bind(':search', $searchTerm);
        }

        $result = $query->fetch();
        return $result['total'];
    }

    /**
     * Get member summary
     */
    public function getSummary($memberId) {
        $sql = "SELECT * FROM vw_member_account_summary WHERE member_id = :member_id";
        return $this->db->query($sql)->bind(':member_id', $memberId)->fetch();
    }

    /**
     * Delete member
     */
    public function delete($memberId) {
        try {
            // Check if member has active loans
            $sql = "SELECT COUNT(*) as count FROM loans WHERE member_id = :member_id AND loan_status = 'Active'";
            $result = $this->db->query($sql)->bind(':member_id', $memberId)->fetch();

            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete member with active loans'];
            }

            // Delete member (will cascade to accounts)
            $sql = "DELETE FROM members WHERE member_id = :member_id";
            $this->db->query($sql)->bind(':member_id', $memberId)->execute();

            return ['success' => true, 'message' => 'Member deleted successfully'];

        } catch (Exception $e) {
            error_log("Member Delete Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete member'];
        }
    }

    /**
     * Suspend member
     */
    public function suspend($memberId, $reason) {
        try {
            $sql = "UPDATE members SET membership_status = 'Suspended' WHERE member_id = :member_id";
            $this->db->query($sql)->bind(':member_id', $memberId)->execute();

            return ['success' => true, 'message' => 'Member suspended successfully'];

        } catch (Exception $e) {
            error_log("Member Suspend Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to suspend member'];
        }
    }

    /**
     * Activate member
     */
    public function activate($memberId) {
        try {
            $sql = "UPDATE members SET membership_status = 'Active' WHERE member_id = :member_id";
            $this->db->query($sql)->bind(':member_id', $memberId)->execute();

            return ['success' => true, 'message' => 'Member activated successfully'];

        } catch (Exception $e) {
            error_log("Member Activate Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to activate member'];
        }
    }

    /**
     * Generate unique member number
     */
    private function generateMemberNumber() {
        $year = date('Y');
        $sql = "SELECT COUNT(*) as count FROM members WHERE YEAR(created_at) = :year";
        $result = $this->db->query($sql)->bind(':year', $year)->fetch();
        $count = $result['count'] + 1;
        return 'LCW' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber() {
        return 'ACC' . date('Y') . rand(100000, 999999);
    }

    /**
     * Check if ID number exists
     */
    public function idNumberExists($idNumber, $excludeMemberId = null) {
        $sql = "SELECT member_id FROM members WHERE id_number = :id_number";
        
        if ($excludeMemberId) {
            $sql .= " AND member_id != :member_id";
        }

        $query = $this->db->query($sql)->bind(':id_number', $idNumber);
        
        if ($excludeMemberId) {
            $query->bind(':member_id', $excludeMemberId);
        }

        return $query->fetch() !== false;
    }

    /**
     * Get member statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_members,
                    SUM(CASE WHEN membership_status = 'Active' THEN 1 ELSE 0 END) as active_members,
                    SUM(CASE WHEN membership_status = 'Suspended' THEN 1 ELSE 0 END) as suspended_members,
                    SUM(CASE WHEN membership_status = 'Inactive' THEN 1 ELSE 0 END) as inactive_members
                FROM members";
        
        return $this->db->query($sql)->fetch();
    }
}
?>
