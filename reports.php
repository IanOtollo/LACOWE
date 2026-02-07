<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'includes/Session.php';
require_once 'includes/helpers.php';

Auth::requireAuth();
Auth::requireRole([1, 2, 3]); // Admin only
Session::start();

$pageTitle = 'Reports';
$db = new Database();

// Get date range from filters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Member Statistics
try {
    $sql = "SELECT 
                COUNT(*) as total_members,
                SUM(CASE WHEN membership_status = 'Active' THEN 1 ELSE 0 END) as active_members,
                SUM(CASE WHEN membership_status = 'Suspended' THEN 1 ELSE 0 END) as suspended_members,
                SUM(CASE WHEN membership_status = 'Inactive' THEN 1 ELSE 0 END) as inactive_members
            FROM members";
    $memberStats = $db->getConnection()->query($sql)->fetch();
}
catch (Exception $e) {
    $memberStats = ['total_members' => 0, 'active_members' => 0, 'suspended_members' => 0, 'inactive_members' => 0];
}

// Financial Statistics
try {
    $sql = "SELECT 
                COALESCE(SUM(balance), 0) as total_balance,
                COALESCE(SUM(CASE WHEN account_type = 'Savings' THEN balance ELSE 0 END), 0) as savings_balance,
                COALESCE(SUM(CASE WHEN account_type = 'Shares' THEN balance ELSE 0 END), 0) as shares_balance
            FROM accounts
            WHERE account_status = 'Active'";
    $financialStats = $db->getConnection()->query($sql)->fetch();
}
catch (Exception $e) {
    $financialStats = ['total_balance' => 0, 'savings_balance' => 0, 'shares_balance' => 0];
}

// Loan Statistics
try {
    $sql = "SELECT 
                COUNT(*) as total_loans,
                SUM(CASE WHEN loan_status = 'Active' THEN 1 ELSE 0 END) as active_loans,
                COALESCE(SUM(CASE WHEN loan_status = 'Active' THEN balance ELSE 0 END), 0) as outstanding_balance,
                COALESCE(SUM(principal_amount), 0) as total_disbursed
            FROM loans";
    $loanStats = $db->getConnection()->query($sql)->fetch();
}
catch (Exception $e) {
    $loanStats = ['total_loans' => 0, 'active_loans' => 0, 'outstanding_balance' => 0, 'total_disbursed' => 0];
}

// Transaction Statistics for date range
try {
    $sql = "SELECT 
                COUNT(*) as total_transactions,
                COALESCE(SUM(CASE WHEN transaction_type = 'Deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                COALESCE(SUM(CASE WHEN transaction_type = 'Withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals,
                COALESCE(SUM(CASE WHEN transaction_type = 'Loan Repayment' THEN amount ELSE 0 END), 0) as total_repayments
            FROM transactions
            WHERE DATE(transaction_date) BETWEEN :start_date AND :end_date";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
    $transactionStats = $stmt->fetch();
}
catch (Exception $e) {
    $transactionStats = ['total_transactions' => 0, 'total_deposits' => 0, 'total_withdrawals' => 0, 'total_repayments' => 0];
}

include 'views/layouts/header.php';
?>

<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 style="margin: 0;">Report Filters</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="">
            <div class="row">
                <div class="col col-4">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>
                </div>
                <div class="col col-4">
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                </div>
                <div class="col col-4">
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<h3 style="margin-bottom: 1rem; color: var(--gray-800);">
    <i class="fas fa-users"></i> Membership Report
</h3>
<div class="row" style="margin-bottom: 2rem;">
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"><i class="fas fa-users"></i></div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($memberStats['total_members']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Total Members</p>
            </div>
        </div>
    </div>
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--success); margin-bottom: 0.5rem;"><i class="fas fa-check-circle"></i></div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($memberStats['active_members']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Active</p>
            </div>
        </div>
    </div>
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--warning); margin-bottom: 0.5rem;"><i class="fas fa-pause-circle"></i></div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($memberStats['suspended_members']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Suspended</p>
            </div>
        </div>
    </div>
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--danger); margin-bottom: 0.5rem;"><i class="fas fa-times-circle"></i></div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($memberStats['inactive_members']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Inactive</p>
            </div>
        </div>
    </div>
</div>

<h3 style="margin-bottom: 1rem; color: var(--gray-800);">
    <i class="fas fa-chart-line"></i> Financial Summary
</h3>
<div class="row" style="margin-bottom: 2rem;">
    <div class="col col-4">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"><i class="fas fa-wallet"></i></div>
                <h3 style="margin: 0; font-size: 1.5rem;"><?php echo formatCurrency($financialStats['total_balance']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Total Balance</p>
            </div>
        </div>
    </div>
    <div class="col col-4">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--success); margin-bottom: 0.5rem;"><i class="fas fa-piggy-bank"></i></div>
                <h3 style="margin: 0; font-size: 1.5rem;"><?php echo formatCurrency($financialStats['savings_balance']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Savings</p>
            </div>
        </div>
    </div>
    <div class="col col-4">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--info); margin-bottom: 0.5rem;"><i class="fas fa-chart-pie"></i></div>
                <h3 style="margin: 0; font-size: 1.5rem;"><?php echo formatCurrency($financialStats['shares_balance']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Shares</p>
            </div>
        </div>
    </div>
</div>

<h3 style="margin-bottom: 1rem; color: var(--gray-800);">
    <i class="fas fa-hand-holding-usd"></i> Loan Summary
</h3>
<div class="row" style="margin-bottom: 2rem;">
    <div class="col col-4">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($loanStats['total_loans']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Total Loans</p>
            </div>
        </div>
    </div>
    <div class="col col-4">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--warning); margin-bottom: 0.5rem;"><i class="fas fa-exclamation-triangle"></i></div>
                <h3 style="margin: 0; font-size: 1.5rem;"><?php echo formatCurrency($loanStats['outstanding_balance']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Outstanding</p>
            </div>
        </div>
    </div>
    <div class="col col-4">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--success); margin-bottom: 0.5rem;"><i class="fas fa-money-bill-wave"></i></div>
                <h3 style="margin: 0; font-size: 1.5rem;"><?php echo formatCurrency($loanStats['total_disbursed']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Total Disbursed</p>
            </div>
        </div>
    </div>
</div>

<h3 style="margin-bottom: 1rem; color: var(--gray-800);">
    <i class="fas fa-exchange-alt"></i> Transaction Summary (<?php echo formatDate($startDate); ?> - <?php echo formatDate($endDate); ?>)
</h3>
<div class="row" style="margin-bottom: 2rem;">
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"><i class="fas fa-receipt"></i></div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($transactionStats['total_transactions']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Total Transactions</p>
            </div>
        </div>
    </div>
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--success); margin-bottom: 0.5rem;"><i class="fas fa-arrow-down"></i></div>
                <h3 style="margin: 0; font-size: 1.5rem;"><?php echo formatCurrency($transactionStats['total_deposits']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Deposits</p>
            </div>
        </div>
    </div>
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--danger); margin-bottom: 0.5rem;"><i class="fas fa-arrow-up"></i></div>
                <h3 style="margin: 0; font-size: 1.5rem;"><?php echo formatCurrency($transactionStats['total_withdrawals']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Withdrawals</p>
            </div>
        </div>
    </div>
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--info); margin-bottom: 0.5rem;"><i class="fas fa-undo"></i></div>
                <h3 style="margin: 0; font-size: 1.5rem;"><?php echo formatCurrency($transactionStats['total_repayments']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Loan Repayments</p>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
