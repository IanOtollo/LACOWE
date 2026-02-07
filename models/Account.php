<?php
/**
 * Account Model
 * Handles all account-related database operations
 * LACOWE Welfare MIS
 */

require_once __DIR__ . '/../includes/Database.php';

class Account
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getByMemberId($memberId)
    {
        $sql = "SELECT * FROM accounts WHERE member_id = :member_id ORDER BY created_at";
        return $this->db->query($sql)->bind(':member_id', $memberId)->fetchAll();
    }

    public function getById($accountId)
    {
        $sql = "SELECT a.*, m.member_number, CONCAT(m.first_name, ' ', m.last_name) as member_name
                FROM accounts a
                INNER JOIN members m ON a.member_id = m.member_id
                WHERE a.account_id = :account_id";
        return $this->db->query($sql)->bind(':account_id', $accountId)->fetch();
    }

    public function getSummaryByMemberId($memberId)
    {
        $stats = [
            'total_balance' => 0,
            'savings_balance' => 0,
            'shares_balance' => 0,
            'account_count' => 0
        ];

        $accounts = $this->getByMemberId($memberId);
        $stats['account_count'] = count($accounts);

        foreach ($accounts as $acc) {
            $stats['total_balance'] += $acc['balance'];
            if ($acc['account_type'] == 'Savings') {
                $stats['savings_balance'] += $acc['balance'];
            }
            elseif ($acc['account_type'] == 'Shares') {
                $stats['shares_balance'] += $acc['balance'];
            }
        }

        return $stats;
    }

    public function create($data)
    {
        try {
            $accountNumber = 'ACC' . date('Y') . rand(100000, 999999);

            $sql = "INSERT INTO accounts (member_id, account_number, account_type, balance, interest_rate, date_opened, account_status)
                    VALUES (:member_id, :account_number, :account_type, :balance, :interest_rate, :date_opened, 'Active')";

            $this->db->query($sql)
                ->bind(':member_id', $data['member_id'])
                ->bind(':account_number', $accountNumber)
                ->bind(':account_type', $data['account_type'])
                ->bind(':balance', $data['balance'] ?? 0)
                ->bind(':interest_rate', $data['interest_rate'] ?? 0)
                ->bind(':date_opened', $data['date_opened'] ?? date('Y-m-d'))
                ->execute();

            return ['success' => true, 'message' => 'Account created successfully', 'account_id' => $this->db->lastInsertId()];

        }
        catch (Exception $e) {
            error_log("Account Creation Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create account'];
        }
    }

    public function updateBalance($accountId, $newBalance)
    {
        $sql = "UPDATE accounts SET balance = :balance WHERE account_id = :account_id";
        return $this->db->query($sql)
            ->bind(':balance', $newBalance)
            ->bind(':account_id', $accountId)
            ->execute();
    }

    public function getBalance($accountId)
    {
        $sql = "SELECT balance FROM accounts WHERE account_id = :account_id";
        $result = $this->db->query($sql)->bind(':account_id', $accountId)->fetch();
        return $result ? $result['balance'] : 0;
    }
}
?>
