<?php
/**
 * BankAccount Model
 * Handles database operations for linked bank accounts
 * LACOWE Welfare MIS
 */

require_once __DIR__ . '/../includes/Database.php';

class BankAccount
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get all bank accounts for a member
     */
    public function getByMemberId($memberId)
    {
        $sql = "SELECT * FROM bank_accounts WHERE member_id = :member_id ORDER BY created_at DESC";
        return $this->db->query($sql)->bind(':member_id', $memberId)->fetchAll();
    }

    /**
     * Get a specific bank account by ID
     */
    public function getById($bankAccountId)
    {
        $sql = "SELECT b.*, m.member_number, CONCAT(m.first_name, ' ', m.last_name) as member_name
                FROM bank_accounts b
                INNER JOIN members m ON b.member_id = m.member_id
                WHERE b.bank_account_id = :bank_account_id";
        return $this->db->query($sql)->bind(':bank_account_id', $bankAccountId)->fetch();
    }

    /**
     * Get all bank accounts in the system (for admin)
     */
    public function getAll()
    {
        $sql = "SELECT b.*, m.member_number, CONCAT(m.first_name, ' ', m.last_name) as member_name
                FROM bank_accounts b
                INNER JOIN members m ON b.member_id = m.member_id
                ORDER BY b.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Create a new bank account link
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO bank_accounts (member_id, bank_name, account_name, account_number, branch_name, swift_code)
                    VALUES (:member_id, :bank_name, :account_name, :account_number, :branch_name, :swift_code)";

            $this->db->query($sql)
                ->bind(':member_id', $data['member_id'])
                ->bind(':bank_name', $data['bank_name'])
                ->bind(':account_name', $data['account_name'])
                ->bind(':account_number', $data['account_number'])
                ->bind(':branch_name', $data['branch_name'] ?? null)
                ->bind(':swift_code', $data['swift_code'] ?? null)
                ->execute();

            return ['success' => true, 'message' => 'Bank account linked successfully', 'bank_account_id' => $this->db->lastInsertId()];

        } catch (Exception $e) {
            error_log("Bank Account Creation Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to link bank account: ' . $e->getMessage()];
        }
    }

    /**
     * Update verification status
     */
    public function updateStatus($bankAccountId, $isVerified)
    {
        $sql = "UPDATE bank_accounts SET is_verified = :is_verified WHERE bank_account_id = :bank_account_id";
        return $this->db->query($sql)
            ->bind(':is_verified', $isVerified)
            ->bind(':bank_account_id', $bankAccountId)
            ->execute();
    }

    /**
     * Delete a bank account link
     */
    public function delete($bankAccountId)
    {
        $sql = "DELETE FROM bank_accounts WHERE bank_account_id = :bank_account_id";
        return $this->db->query($sql)->bind(':bank_account_id', $bankAccountId)->execute();
    }
}
