<?php
/**
 * Transaction Model
 */

require_once __DIR__ . '/../includes/Database.php';

class Transaction
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function processDeposit($accountId, $amount, $description, $processedBy)
    {
        try {
            $this->db->beginTransaction();

            // Get current balance
            $account = $this->db->query("SELECT balance FROM accounts WHERE account_id = :account_id")
                ->bind(':account_id', $accountId)
                ->fetch();

            $balanceBefore = $account['balance'];
            $balanceAfter = $balanceBefore + $amount;
            $reference = 'DEP-' . date('Ymd') . '-' . uniqid();

            // Insert transaction
            $sql = "INSERT INTO transactions (account_id, transaction_type, amount, transaction_date, description, 
                                             reference_number, processed_by, balance_before, balance_after, status)
                    VALUES (:account_id, 'Deposit', :amount, NOW(), :description, :reference_number, 
                           :processed_by, :balance_before, :balance_after, 'Completed')";

            $this->db->query($sql)
                ->bind(':account_id', $accountId)
                ->bind(':amount', $amount)
                ->bind(':description', $description)
                ->bind(':reference_number', $reference)
                ->bind(':processed_by', $processedBy)
                ->bind(':balance_before', $balanceBefore)
                ->bind(':balance_after', $balanceAfter)
                ->execute();

            // Update account balance
            $this->db->query("UPDATE accounts SET balance = :balance WHERE account_id = :account_id")
                ->bind(':balance', $balanceAfter)
                ->bind(':account_id', $accountId)
                ->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Deposit successful', 'reference' => $reference];

        }
        catch (Exception $e) {
            $this->db->rollback();
            error_log("Deposit Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Deposit failed'];
        }
    }

    public function processWithdrawal($accountId, $amount, $description, $processedBy)
    {
        try {
            $this->db->beginTransaction();

            // Get current balance
            $account = $this->db->query("SELECT balance FROM accounts WHERE account_id = :account_id")
                ->bind(':account_id', $accountId)
                ->fetch();

            $balanceBefore = $account['balance'];

            if ($balanceBefore < $amount) {
                return ['success' => false, 'message' => 'Insufficient balance'];
            }

            $balanceAfter = $balanceBefore - $amount;
            $reference = 'WTH-' . date('Ymd') . '-' . uniqid();

            // Insert transaction
            $sql = "INSERT INTO transactions (account_id, transaction_type, amount, transaction_date, description, 
                                             reference_number, processed_by, balance_before, balance_after, status)
                    VALUES (:account_id, 'Withdrawal', :amount, NOW(), :description, :reference_number, 
                           :processed_by, :balance_before, :balance_after, 'Completed')";

            $this->db->query($sql)
                ->bind(':account_id', $accountId)
                ->bind(':amount', $amount)
                ->bind(':description', $description)
                ->bind(':reference_number', $reference)
                ->bind(':processed_by', $processedBy)
                ->bind(':balance_before', $balanceBefore)
                ->bind(':balance_after', $balanceAfter)
                ->execute();

            // Update account balance
            $this->db->query("UPDATE accounts SET balance = :balance WHERE account_id = :account_id")
                ->bind(':balance', $balanceAfter)
                ->bind(':account_id', $accountId)
                ->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Withdrawal successful', 'reference' => $reference];

        }
        catch (Exception $e) {
            $this->db->rollback();
            error_log("Withdrawal Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Withdrawal failed'];
        }
    }

    public function getByAccountId($accountId, $limit = null)
    {
        $sql = "SELECT * FROM transactions WHERE account_id = :account_id ORDER BY transaction_date DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $query = $this->db->query($sql)->bind(':account_id', $accountId);

        if ($limit) {
            $query->bind(':limit', $limit, PDO::PARAM_INT);
        }

        return $query->fetchAll();
    }

    public function getById($transactionId)
    {
        $sql = "SELECT * FROM vw_transaction_summary WHERE transaction_id = :transaction_id";
        return $this->db->query($sql)->bind(':transaction_id', $transactionId)->fetch();
    }

    public function getAll($filters = [], $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM vw_transaction_summary WHERE 1=1";

        if (!empty($filters['transaction_type'])) {
            $sql .= " AND transaction_type = :transaction_type";
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(transaction_date) >= :date_from";
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(transaction_date) <= :date_to";
        }

        $sql .= " ORDER BY transaction_date DESC";

        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $query = $this->db->query($sql);
        if (!empty($filters['transaction_type']))
            $query->bind(':transaction_type', $filters['transaction_type']);
        if (!empty($filters['date_from']))
            $query->bind(':date_from', $filters['date_from']);
        if (!empty($filters['date_to']))
            $query->bind(':date_to', $filters['date_to']);

        if ($limit) {
            $query->bind(':limit', $limit, PDO::PARAM_INT);
            $query->bind(':offset', $offset, PDO::PARAM_INT);
        }

        return $query->fetchAll();
    }

    public function getByMemberId($memberId, $filters = [], $limit = 100)
    {
        $sql = "SELECT t.*, a.account_number, a.account_type
                FROM transactions t
                INNER JOIN accounts a ON t.account_id = a.account_id
                WHERE a.member_id = :member_id";

        if (!empty($filters['account_id'])) {
            $sql .= " AND t.account_id = :account_id";
        }
        if (!empty($filters['type'])) {
            $sql .= " AND t.transaction_type = :type";
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(t.transaction_date) >= :date_from";
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(t.transaction_date) <= :date_to";
        }
        if (!empty($filters['search'])) {
            $sql .= " AND t.reference_number LIKE :search";
        }

        $sql .= " ORDER BY t.transaction_date DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $query = $this->db->query($sql)->bind(':member_id', $memberId);

        if (!empty($filters['account_id']))
            $query->bind(':account_id', $filters['account_id']);
        if (!empty($filters['type']))
            $query->bind(':type', $filters['type']);
        if (!empty($filters['date_from']))
            $query->bind(':date_from', $filters['date_from']);
        if (!empty($filters['date_to']))
            $query->bind(':date_to', $filters['date_to']);
        if (!empty($filters['search']))
            $query->bind(':search', '%' . $filters['search'] . '%');

        if ($limit) {
            $query->bind(':limit', $limit, PDO::PARAM_INT);
        }

        return $query->fetchAll();
    }
}
?>
