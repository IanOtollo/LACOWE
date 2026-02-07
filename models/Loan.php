<?php
/**
 * Loan Model
 * Handles all loan-related database operations
 */

require_once __DIR__ . '/../includes/Database.php';

class Loan
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function createApplication($data)
    {
        try {
            $sql = "INSERT INTO loan_applications (member_id, loan_type, amount_requested, loan_purpose, repayment_period, 
                                                   application_date, guarantor1_id, guarantor2_id, collateral_description, application_status)
                    VALUES (:member_id, :loan_type, :amount_requested, :loan_purpose, :repayment_period, :application_date,
                           :guarantor1_id, :guarantor2_id, :collateral_description, 'Pending')";

            $this->db->query($sql)
                ->bind(':member_id', $data['member_id'])
                ->bind(':loan_type', $data['loan_type'])
                ->bind(':amount_requested', $data['amount_requested'])
                ->bind(':loan_purpose', $data['loan_purpose'])
                ->bind(':repayment_period', $data['repayment_period'])
                ->bind(':application_date', date('Y-m-d'))
                ->bind(':guarantor1_id', $data['guarantor1_id'] ?? null)
                ->bind(':guarantor2_id', $data['guarantor2_id'] ?? null)
                ->bind(':collateral_description', $data['collateral_description'] ?? null)
                ->execute();

            return ['success' => true, 'message' => 'Loan application submitted successfully', 'application_id' => $this->db->lastInsertId()];

        }
        catch (Exception $e) {
            error_log("Loan Application Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to submit loan application'];
        }
    }

    public function approveApplication($applicationId, $userId, $comments = '')
    {
        try {
            $this->db->beginTransaction();

            // Update application status
            $sql = "UPDATE loan_applications SET application_status = 'Approved', reviewed_by = :reviewed_by, 
                    review_date = :review_date, review_comments = :comments WHERE application_id = :application_id";

            $this->db->query($sql)
                ->bind(':application_id', $applicationId)
                ->bind(':reviewed_by', $userId)
                ->bind(':review_date', date('Y-m-d'))
                ->bind(':comments', $comments)
                ->execute();

            // Get application details
            $app = $this->getApplicationById($applicationId);

            // Create loan record
            $loanNumber = 'LOAN' . date('Y') . str_pad($applicationId, 6, '0', STR_PAD_LEFT);
            $interestRate = 12.00; // Default rate
            $totalAmount = $app['amount_requested'] * (1 + ($interestRate / 100));
            $monthlyInstallment = $totalAmount / $app['repayment_period'];
            $maturityDate = date('Y-m-d', strtotime('+' . $app['repayment_period'] . ' months'));

            $loanSql = "INSERT INTO loans (application_id, member_id, loan_number, principal_amount, interest_rate, 
                                          total_amount, monthly_installment, repayment_period, disbursement_date, 
                                          maturity_date, balance, disbursed_by, loan_status)
                        VALUES (:application_id, :member_id, :loan_number, :principal_amount, :interest_rate,
                               :total_amount, :monthly_installment, :repayment_period, :disbursement_date,
                               :maturity_date, :balance, :disbursed_by, 'Active')";

            $this->db->query($loanSql)
                ->bind(':application_id', $applicationId)
                ->bind(':member_id', $app['member_id'])
                ->bind(':loan_number', $loanNumber)
                ->bind(':principal_amount', $app['amount_requested'])
                ->bind(':interest_rate', $interestRate)
                ->bind(':total_amount', $totalAmount)
                ->bind(':monthly_installment', $monthlyInstallment)
                ->bind(':repayment_period', $app['repayment_period'])
                ->bind(':disbursement_date', date('Y-m-d'))
                ->bind(':maturity_date', $maturityDate)
                ->bind(':balance', $totalAmount)
                ->bind(':disbursed_by', $userId)
                ->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Loan approved and disbursed successfully'];

        }
        catch (Exception $e) {
            $this->db->rollback();
            error_log("Loan Approval Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to approve loan'];
        }
    }

    public function rejectApplication($applicationId, $userId, $comments)
    {
        $sql = "UPDATE loan_applications SET application_status = 'Rejected', reviewed_by = :reviewed_by, 
                review_date = :review_date, review_comments = :comments WHERE application_id = :application_id";

        $this->db->query($sql)
            ->bind(':application_id', $applicationId)
            ->bind(':reviewed_by', $userId)
            ->bind(':review_date', date('Y-m-d'))
            ->bind(':comments', $comments)
            ->execute();

        return ['success' => true, 'message' => 'Loan application rejected'];
    }

    public function getApplicationById($applicationId)
    {
        $sql = "SELECT la.*, CONCAT(m.first_name, ' ', m.last_name) as member_name, m.member_number
                FROM loan_applications la
                INNER JOIN members m ON la.member_id = m.member_id
                WHERE la.application_id = :application_id";
        return $this->db->query($sql)->bind(':application_id', $applicationId)->fetch();
    }

    public function getAllApplications($filters = [], $limit = null, $offset = 0)
    {
        $sql = "SELECT la.*, CONCAT(m.first_name, ' ', m.last_name) as member_name, m.member_number
                FROM loan_applications la
                INNER JOIN members m ON la.member_id = m.member_id
                WHERE 1=1";

        if (!empty($filters['status'])) {
            $sql .= " AND la.application_status = :status";
        }
        if (!empty($filters['member_id'])) {
            $sql .= " AND la.member_id = :member_id";
        }

        $sql .= " ORDER BY la.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $query = $this->db->query($sql);
        if (!empty($filters['status']))
            $query->bind(':status', $filters['status']);
        if (!empty($filters['member_id']))
            $query->bind(':member_id', $filters['member_id']);
        if ($limit) {
            $query->bind(':limit', $limit, PDO::PARAM_INT);
            $query->bind(':offset', $offset, PDO::PARAM_INT);
        }

        return $query->fetchAll();
    }

    public function getLoanById($loanId)
    {
        $sql = "SELECT * FROM vw_loan_portfolio WHERE loan_id = :loan_id";
        return $this->db->query($sql)->bind(':loan_id', $loanId)->fetch();
    }

    public function getActiveLoans($memberId = null)
    {
        $sql = "SELECT * FROM vw_loan_portfolio WHERE loan_status = 'Active'";

        if ($memberId) {
            $sql .= " AND member_id = :member_id";
        }

        $query = $this->db->query($sql);
        if ($memberId)
            $query->bind(':member_id', $memberId);

        return $query->fetchAll();
    }

    public function processRepayment($loanId, $amount, $paymentMethod, $processedBy)
    {
        try {
            // Get loan details
            $loan = $this->getLoanById($loanId);
            if (!$loan || $loan['loan_status'] != 'Active') {
                return ['success' => false, 'message' => 'Loan not found or not active'];
            }

            // Calculate payment distribution
            $interestPaid = $amount * 0.3;
            $principalPaid = $amount - $interestPaid;
            $balanceAfter = max(0, $loan['balance'] - $amount);
            $receiptNumber = 'LRP-' . date('Ymd') . '-' . uniqid();

            $this->db->beginTransaction();

            // Insert repayment record
            $sql = "INSERT INTO loan_repayments (loan_id, payment_date, amount_paid, principal_paid, interest_paid, 
                                                balance_after, payment_method, receipt_number, processed_by)
                    VALUES (:loan_id, :payment_date, :amount_paid, :principal_paid, :interest_paid,
                           :balance_after, :payment_method, :receipt_number, :processed_by)";

            $this->db->query($sql)
                ->bind(':loan_id', $loanId)
                ->bind(':payment_date', date('Y-m-d'))
                ->bind(':amount_paid', $amount)
                ->bind(':principal_paid', $principalPaid)
                ->bind(':interest_paid', $interestPaid)
                ->bind(':balance_after', $balanceAfter)
                ->bind(':payment_method', $paymentMethod)
                ->bind(':receipt_number', $receiptNumber)
                ->bind(':processed_by', $processedBy)
                ->execute();

            // Update loan
            $newStatus = $balanceAfter == 0 ? 'Fully Paid' : 'Active';
            $updateSql = "UPDATE loans SET balance = :balance, amount_paid = amount_paid + :amount_paid, 
                         loan_status = :loan_status WHERE loan_id = :loan_id";

            $this->db->query($updateSql)
                ->bind(':balance', $balanceAfter)
                ->bind(':amount_paid', $amount)
                ->bind(':loan_status', $newStatus)
                ->bind(':loan_id', $loanId)
                ->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Repayment processed successfully', 'receipt_number' => $receiptNumber];

        }
        catch (Exception $e) {
            $this->db->rollback();
            error_log("Loan Repayment Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process repayment'];
        }
    }

    public function getRepayments($loanId)
    {
        $sql = "SELECT lr.*, CONCAT(u.username) as processed_by_name
                FROM loan_repayments lr
                INNER JOIN users u ON lr.processed_by = u.user_id
                WHERE lr.loan_id = :loan_id
                ORDER BY lr.payment_date DESC";
        return $this->db->query($sql)->bind(':loan_id', $loanId)->fetchAll();
    }

    public function getLoanStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_loans,
                    SUM(CASE WHEN loan_status = 'Active' THEN 1 ELSE 0 END) as active_loans,
                    SUM(CASE WHEN loan_status = 'Fully Paid' THEN 1 ELSE 0 END) as paid_loans,
                    SUM(CASE WHEN loan_status = 'Defaulted' THEN 1 ELSE 0 END) as defaulted_loans,
                    SUM(principal_amount) as total_disbursed,
                    SUM(amount_paid) as total_collected,
                    SUM(balance) as total_outstanding
                FROM loans";

        return $this->db->query($sql)->fetch();
    }
    public function getLoansByMember($memberId, $limit = null)
    {
        $sql = "SELECT l.*, la.loan_type 
                FROM loans l 
                INNER JOIN loan_applications la ON l.application_id = la.application_id 
                WHERE l.member_id = :member_id 
                ORDER BY l.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $query = $this->db->query($sql)->bind(':member_id', $memberId);

        if ($limit) {
            $query->bind(':limit', $limit, PDO::PARAM_INT);
        }

        return $query->fetchAll();
    }
    public function getMemberStats($memberId)
    {
        $stats = [
            'active_loans' => 0,
            'outstanding_balance' => 0,
            'pending_applications' => 0,
            'total_borrowed' => 0
        ];

        // Active loans stats
        $sql = "SELECT COUNT(*) as count, SUM(balance) as balance 
                FROM loans WHERE member_id = :member_id AND loan_status = 'Active'";
        $active = $this->db->query($sql)->bind(':member_id', $memberId)->fetch();

        $stats['active_loans'] = $active['count'] ?? 0;
        $stats['outstanding_balance'] = $active['balance'] ?? 0;

        // Pending applications
        $sql = "SELECT COUNT(*) as count FROM loan_applications 
                WHERE member_id = :member_id AND application_status = 'Pending'";
        $pending = $this->db->query($sql)->bind(':member_id', $memberId)->fetch();
        $stats['pending_applications'] = $pending['count'] ?? 0;

        // Total borrowed (all time)
        $sql = "SELECT SUM(principal_amount) as total FROM loans WHERE member_id = :member_id";
        $borrowed = $this->db->query($sql)->bind(':member_id', $memberId)->fetch();
        $stats['total_borrowed'] = $borrowed['total'] ?? 0;

        return $stats;
    }
}
?>
