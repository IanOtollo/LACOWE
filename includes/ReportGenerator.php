<?php
/**
 * ReportGenerator Utility
 * Handles data fetching and CSV conversion for various system reports
 * LACOWE Welfare MIS
 */

require_once __DIR__ . '/Database.php';

class ReportGenerator
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
        $this->ensureTableExists();
    }

    private function ensureTableExists()
    {
        try {
            $driver = DB_DRIVER;
            if ($driver === 'pgsql') {
                $sql = "CREATE TABLE IF NOT EXISTS bank_accounts (
                    bank_account_id SERIAL PRIMARY KEY,
                    member_id INT NOT NULL REFERENCES members(member_id) ON DELETE CASCADE,
                    bank_name VARCHAR(100) NOT NULL,
                    account_name VARCHAR(100) NOT NULL,
                    account_number VARCHAR(50) NOT NULL,
                    branch_name VARCHAR(100),
                    swift_code VARCHAR(20),
                    is_verified BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );";
            } else {
                $sql = "CREATE TABLE IF NOT EXISTS bank_accounts (
                    bank_account_id INT AUTO_INCREMENT PRIMARY KEY,
                    member_id INT NOT NULL,
                    bank_name VARCHAR(100) NOT NULL,
                    account_name VARCHAR(100) NOT NULL,
                    account_number VARCHAR(50) NOT NULL,
                    branch_name VARCHAR(100),
                    swift_code VARCHAR(20),
                    is_verified TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE
                ) ENGINE=InnoDB;";
            }
            $this->db->getConnection()->exec($sql);
        } catch (Exception $e) {
            error_log("Auto-table creation failed in ReportGenerator: " . $e->getMessage());
        }
    }

    /**
     * Generate CSV from an array of data
     */
    public function arrayToCsv(array $headers, array $data, $filename = "report.csv")
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, $headers);

        // Write data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit();
    }

    /**
     * Get Member Report Data
     */
    public function getMemberReport($status = null)
    {
        $sql = "SELECT m.member_number, m.first_name, m.last_name, m.id_number, m.phone_number, 
                       m.email, m.membership_status, m.date_joined,
                       COALESCE(SUM(a.balance), 0) as total_savings
                FROM members m
                LEFT JOIN accounts a ON m.member_id = a.member_id
                WHERE 1=1";

        $params = [];
        if ($status) {
            $sql .= " AND m.membership_status = :status";
            $params['status'] = $status;
        }

        $sql .= " GROUP BY m.member_id ORDER BY m.member_number ASC";

        return $this->db->query($sql)->bindArray($params)->fetchAll();
    }

    /**
     * Get Loan Report Data
     */
    public function getLoanReport($startDate = null, $endDate = null)
    {
        $sql = "SELECT l.loan_number, m.member_number, CONCAT(m.first_name, ' ', m.last_name) as member_name,
                       la.loan_type, l.principal_amount, l.interest_rate, l.total_amount, 
                       l.amount_paid, l.balance as outstanding_balance, l.loan_status, 
                       l.disbursement_date, l.maturity_date
                FROM loans l
                INNER JOIN members m ON l.member_id = m.member_id
                INNER JOIN loan_applications la ON l.application_id = la.application_id
                WHERE 1=1";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND l.disbursement_date BETWEEN :start AND :end";
            $params['start'] = $startDate;
            $params['end'] = $endDate;
        }

        $sql .= " ORDER BY l.disbursement_date DESC";

        return $this->db->query($sql)->bindArray($params)->fetchAll();
    }

    /**
     * Get Transaction Report Data
     */
    public function getTransactionReport($startDate = null, $endDate = null)
    {
        $sql = "SELECT t.transaction_date, t.reference_number, m.member_number, 
                       CONCAT(m.first_name, ' ', m.last_name) as member_name,
                       a.account_number, a.account_type, t.transaction_type, t.amount, t.status
                FROM transactions t
                INNER JOIN accounts a ON t.account_id = a.account_id
                INNER JOIN members m ON a.member_id = m.member_id
                WHERE 1=1";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND DATE(t.transaction_date) BETWEEN :start AND :end";
            $params['start'] = $startDate;
            $params['end'] = $endDate;
        }

        $sql .= " ORDER BY t.transaction_date DESC";

        return $this->db->query($sql)->bindArray($params)->fetchAll();
    }

    /**
     * Get Bank Account Report Data
     */
    public function getBankAccountReport()
    {
        $sql = "SELECT m.member_number, CONCAT(m.first_name, ' ', m.last_name) as member_name,
                       b.bank_name, b.account_name, b.account_number, b.branch_name, 
                       CASE WHEN b.is_verified = 1 THEN 'Verified' ELSE 'Pending' END as status
                FROM bank_accounts b
                INNER JOIN members m ON b.member_id = m.member_id
                ORDER BY m.member_number ASC";

        return $this->db->query($sql)->fetchAll();
    }
}
