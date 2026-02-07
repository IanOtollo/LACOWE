<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'includes/Session.php';
require_once 'includes/helpers.php';

Auth::requireAuth();
Auth::requireRole([4]); // Members only
Session::start();

$pageTitle = 'My Dashboard';
$db = new Database();

// Initialize Models
require_once 'models/Member.php';
require_once 'models/Account.php';
require_once 'models/Loan.php';
require_once 'models/Transaction.php';

$memberModel = new Member();
$accountModel = new Account();
$loanModel = new Loan();
$transactionModel = new Transaction();

// Get member details
$userId = Session::getUserId();
$member = $memberModel->getByUserId($userId);

if (!$member) {
    Session::flash('error', 'Member record not found');
    redirect('logout.php');
}

$memberId = $member['member_id'];
$memberName = $member['first_name'] . ' ' . $member['last_name']; // Reconstruct full name as getByUserId returns raw cols
$memberNumber = $member['member_number'];

// Get account statistics
$accountStats = $accountModel->getSummaryByMemberId($memberId);
$totalBalance = $accountStats['total_balance'];
$savingsBalance = $accountStats['savings_balance'];
$sharesBalance = $accountStats['shares_balance'];
$accountCount = $accountStats['account_count'];

// Get loan statistics
$loanStats = $loanModel->getMemberStats($memberId);
$activeLoans = $loanStats['active_loans'];
$outstandingBalance = $loanStats['outstanding_balance'];
$pendingApplications = $loanStats['pending_applications'];
$totalBorrowed = $loanStats['total_borrowed'];

// Get my loans
try {
    $myLoans = $loanModel->getLoansByMember($memberId, 5);
}
catch (Exception $e) {
    $myLoans = [];
}

// Get pending loan applications
try {
    $pendingLoans = $loanModel->getAllApplications(['member_id' => $memberId, 'status' => 'Pending'], 5);
}
catch (Exception $e) {
    $pendingLoans = [];
}

// Get my accounts
try {
    $myAccounts = $accountModel->getByMemberId($memberId);
}
catch (Exception $e) {
    $myAccounts = [];
}

// Get recent transactions
try {
    $recentTransactions = $transactionModel->getByMemberId($memberId, 10);
// Map balance_after to balance for view compatibility if needed, but view uses balance_after
}
catch (Exception $e) {
    $recentTransactions = [];
}

include 'views/layouts/header.php';
?>

<div class="card" style="margin-bottom: 2rem; border-left: 4px solid var(--primary);">
    <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; color: var(--gray-900);">Welcome back, <?php echo htmlspecialchars($member['first_name']); ?>!</h2>
            <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Member ID: <span style="font-family: var(--font-mono); font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($memberNumber); ?></span></p>
        </div>
        <div style="text-align: right;">
            <p style="margin: 0; font-size: 0.875rem; color: var(--gray-500);"><?php echo date('l, F j, Y'); ?></p>
        </div>
    </div>
</div>


</style>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h2 style="margin: 0 0 0.25rem 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-hand-wave" style="font-size: 1.25rem;"></i>
                Welcome back, <?php echo htmlspecialchars($memberName); ?>!
            </h2>
            <p style="margin: 0; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span style="display: flex; align-items: center; gap: 0.375rem;">
                    <i class="fas fa-id-card"></i>
                    <strong><?php echo htmlspecialchars($memberNumber); ?></strong>
                </span>
                <span style="display: flex; align-items: center; gap: 0.375rem;">
                    <i class="fas fa-calendar"></i>
                    Member since <?php echo date('M Y', strtotime($member['created_at'])); ?>
                </span>
            </p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row" style="margin-bottom: 2rem;">
    <div class="col col-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <h3>KES <?php echo number_format($totalBalance, 2); ?></h3>
            <p>Total Balance</p>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon">
                <i class="fas fa-piggy-bank"></i>
            </div>
            <h3>KES <?php echo number_format($savingsBalance, 2); ?></h3>
            <p>Savings Balance</p>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <h3><?php echo $activeLoans; ?></h3>
            <p>Active Loans</p>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-card-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3>KES <?php echo number_format($outstandingBalance, 2); ?></h3>
            <p>Loan Balance</p>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row" style="margin-bottom: 2rem;">
    <div class="col col-3">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon">
                <i class="fas fa-university"></i>
            </div>
            <h3>KES <?php echo number_format($sharesBalance, 2); ?></h3>
            <p>Shares Balance</p>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3><?php echo $accountCount; ?></h3>
            <p>Active Accounts</p>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h3><?php echo $pendingApplications; ?></h3>
            <p>Pending Applications</p>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon">
                <i class="fas fa-history"></i>
            </div>
            <h3>KES <?php echo number_format($totalBorrowed, 2); ?></h3>
            <p>Total Borrowed</p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <div class="section-title" style="margin: 0; padding: 0; border: none;">
            <i class="fas fa-bolt"></i>
            <h3>Quick Actions</h3>
        </div>
    </div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="loan-application.php" class="quick-action-btn">
                <i class="fas fa-file-alt"></i>
                <span>Apply for Loan</span>
            </a>
            <a href="my-accounts.php" class="quick-action-btn">
                <i class="fas fa-wallet"></i>
                <span>View Accounts</span>
            </a>
            <a href="my-transactions.php" class="quick-action-btn">
                <i class="fas fa-exchange-alt"></i>
                <span>Transactions</span>
            </a>
            <a href="my-loans.php" class="quick-action-btn">
                <i class="fas fa-money-check-alt"></i>
                <span>My Loans</span>
            </a>
        </div>
    </div>
</div>

<!-- Pending Loan Applications Alert -->
<?php if ($pendingApplications > 0): ?>
<div class="alert alert-warning" style="margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem;"></i>
        <div>
            <strong>Pending Loan Applications</strong>
            <p style="margin: 0.25rem 0 0;">You have <?php echo $pendingApplications; ?> loan application(s) pending approval. <a href="my-loans.php" style="color: var(--warning); text-decoration: underline;">View details</a></p>
        </div>
    </div>
</div>
<?php
endif; ?>

<!-- Main Content -->
<div class="row">
    <!-- My Accounts -->
    <div class="col col-6">
        <div class="card">
            <div class="card-header">
                <div class="section-title" style="margin: 0; padding: 0; border: none;">
                    <i class="fas fa-credit-card"></i>
                    <h3>My Accounts</h3>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($myAccounts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p><strong>No accounts found</strong></p>
                        <p>Your accounts will appear here</p>
                    </div>
                <?php
else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag table-icon"></i> Account #</th>
                                    <th><i class="fas fa-tag table-icon"></i> Type</th>
                                    <th><i class="fas fa-money-bill-wave table-icon"></i> Balance</th>
                                    <th><i class="fas fa-check-circle table-icon"></i> Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myAccounts as $account): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($account['account_number']); ?></td>
                                        <td><?php echo htmlspecialchars($account['account_type']); ?></td>
                                        <td><strong>KES <?php echo number_format($account['balance'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo $account['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                <i class="fas fa-<?php echo $account['status'] == 'Active' ? 'check' : 'times'; ?>"></i>
                                                <?php echo htmlspecialchars($account['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php
    endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="my-accounts.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-right"></i> View All Accounts
                        </a>
                    </div>
                <?php
endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col col-6">
        <div class="card">
            <div class="card-header">
                <div class="section-title" style="margin: 0; padding: 0; border: none;">
                    <i class="fas fa-receipt"></i>
                    <h3>Recent Transactions</h3>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($recentTransactions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p><strong>No transactions yet</strong></p>
                        <p>Your transaction history will appear here</p>
                    </div>
                <?php
else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-calendar table-icon"></i> Date</th>
                                    <th><i class="fas fa-exchange-alt table-icon"></i> Type</th>
                                    <th><i class="fas fa-money-bill table-icon"></i> Amount</th>
                                    <th><i class="fas fa-balance-scale table-icon"></i> Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentTransactions, 0, 5) as $trans): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($trans['transaction_date'])); ?></td>
                                        <td>
                                            <i class="fas fa-<?php echo $trans['transaction_type'] == 'Deposit' ? 'arrow-down' : 'arrow-up'; ?>" 
                                               style="color: <?php echo $trans['transaction_type'] == 'Deposit' ? 'var(--success)' : 'var(--danger)'; ?>"></i>
                                            <?php echo htmlspecialchars($trans['transaction_type']); ?>
                                        </td>
                                        <td><strong>KES <?php echo number_format($trans['amount'], 2); ?></strong></td>
                                        <td>KES <?php echo number_format($trans['balance_after'], 2); ?></td>
                                    </tr>
                                <?php
    endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="my-transactions.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-right"></i> View All Transactions
                        </a>
                    </div>
                <?php
endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- My Loans Section -->
<div class="row" style="margin-top: 2rem;">
    <div class="col col-12">
        <div class="card">
            <div class="card-header">
                <div class="section-title" style="margin: 0; padding: 0; border: none;">
                    <i class="fas fa-hand-holding-usd"></i>
                    <h3>My Loans</h3>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($myLoans)): ?>
                    <div class="empty-state">
                        <i class="fas fa-hand-holding-usd"></i>
                        <p><strong>No loans yet</strong></p>
                        <p>Apply for a loan to get started</p>
                        <a href="loan-application.php" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-plus"></i> Apply for Loan
                        </a>
                    </div>
                <?php
else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag table-icon"></i> Loan #</th>
                                    <th><i class="fas fa-tag table-icon"></i> Type</th>
                                    <th><i class="fas fa-money-bill-wave table-icon"></i> Amount</th>
                                    <th><i class="fas fa-chart-line table-icon"></i> Outstanding</th>
                                    <th><i class="fas fa-calendar table-icon"></i> Due Date</th>
                                    <th><i class="fas fa-info-circle table-icon"></i> Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myLoans as $loan): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($loan['loan_number']); ?></td>
                                        <td><?php echo htmlspecialchars($loan['loan_type']); ?></td>
                                        <td><strong>KES <?php echo number_format($loan['loan_amount'], 2); ?></strong></td>
                                        <td>KES <?php echo number_format($loan['outstanding_balance'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($loan['maturity_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php
        echo $loan['status'] == 'Active' ? 'warning' :
            ($loan['status'] == 'Fully Paid' ? 'success' : 'danger');
?>">
                                                <i class="fas fa-<?php
        echo $loan['status'] == 'Active' ? 'clock' :
            ($loan['status'] == 'Fully Paid' ? 'check' : 'times');
?>"></i>
                                                <?php echo htmlspecialchars($loan['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php
    endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="my-loans.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-right"></i> View All Loans
                        </a>
                    </div>
                <?php
endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>