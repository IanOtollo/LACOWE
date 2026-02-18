<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

Auth::requireAuth();
Auth::requireRole([4]); // Members only

$pageTitle = 'My Dashboard';
$db = new Database();

// Initialize Models
require_once 'models/Member.php';
require_once 'models/Account.php';
require_once 'models/Loan.php';
require_once 'models/Transaction.php';
require_once 'models/BankAccount.php';

$memberModel = new Member();
$accountModel = new Account();
$loanModel = new Loan();
$transactionModel = new Transaction();
$bankAccountModel = new BankAccount();

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
} catch (Exception $e) {
    $myLoans = [];
}

// Get pending loan applications
try {
    $pendingLoans = $loanModel->getAllApplications(['member_id' => $memberId, 'status' => 'Pending'], 5);
} catch (Exception $e) {
    $pendingLoans = [];
}

// Get my accounts
try {
    $myAccounts = $accountModel->getByMemberId($memberId);
} catch (Exception $e) {
    $myAccounts = [];
}

// Get recent transactions
try {
    $recentTransactions = $transactionModel->getByMemberId($memberId, 10);
    // Map balance_after to balance for view compatibility if needed, but view uses balance_after
} catch (Exception $e) {
    $recentTransactions = [];
}

// Get linked bank accounts
try {
    $myBankAccounts = $bankAccountModel->getByMemberId($memberId);
} catch (Exception $e) {
    $myBankAccounts = [];
}

include 'views/layouts/header.php';
?>

<!-- Premium Welcome Banner -->
<div class="welcome-banner" style="margin-bottom: 2rem; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; padding: 2.5rem; border-radius: 16px; box-shadow: var(--shadow-lg); position: relative; overflow: hidden;">
    <div style="position: absolute; right: -20px; top: -20px; font-size: 10rem; opacity: 0.1; transform: rotate(-15deg);">
        <i class="fas fa-university"></i>
    </div>
    <div style="position: relative; z-index: 1;">
        <h2 style="margin: 0 0 0.5rem 0; color: white; font-size: 2rem;">Welcome back, <?php echo htmlspecialchars($member['first_name']); ?>!</h2>
        <p style="margin: 0; opacity: 0.9; display: flex; align-items: center; gap: 1.5rem; font-weight: 500;">
            <span><i class="fas fa-id-card"></i> ID: <?php echo htmlspecialchars($memberNumber); ?></span>
            <span><i class="fas fa-calendar-alt"></i> Joined <?php echo date('M Y', strtotime($member['created_at'])); ?></span>
            <span class="badge" style="background: rgba(255,255,255,0.2); color: white; border: none;"><?php echo htmlspecialchars($member['membership_status']); ?></span>
        </p>
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
            <a href="link-bank-account.php" class="quick-action-btn"
                style="background-color: var(--info); color: white;">
                <i class="fas fa-university"></i>
                <span>Link Bank Account</span>
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
                    <p style="margin: 0.25rem 0 0;">You have <?php echo $pendingApplications; ?> loan application(s) pending
                        approval. <a href="my-loans.php" style="color: var(--warning); text-decoration: underline;">View
                            details</a></p>
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
                                                    <span
                                                        class="badge badge-<?php echo $account['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                        <i
                                                            class="fas fa-<?php echo $account['status'] == 'Active' ? 'check' : 'times'; ?>"></i>
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
                <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Loan #</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Outstanding</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
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
                                                        <?php echo htmlspecialchars($loan['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- My Linked Bank Accounts Section -->
<div class="row" style="margin-top: 2rem;">
    <div class="col col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="section-title" style="margin: 0; padding: 0; border: none;">
                    <i class="fas fa-university"></i>
                    <h3>My Linked Bank Accounts</h3>
                </div>
                <a href="link-bank-account.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Link New Account
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($myBankAccounts)): ?>
                        <div class="empty-state" style="padding: 3rem 1.5rem;">
                            <div class="logo-icon-container"
                                style="margin: 0 auto 1.5rem; width: 64px; height: 64px; font-size: 2rem; background: var(--gray-100); color: var(--gray-400); box-shadow: none;">
                                <i class="fas fa-plug"></i>
                            </div>
                            <h3 style="margin-bottom: 0.5rem;">Connect Your Bank</h3>
                            <p style="color: var(--gray-500); max-width: 500px; margin: 0 auto 2rem;">
                                Link your bank account or mobile money to enable automated savings and instant withdrawals to
                                your preferred platform.
                            </p>

                            <div style="text-align: left; max-width: 600px; margin: 0 auto;">
                                <h5
                                    style="color: var(--gray-700); font-size: 0.875rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                                    <i class="fas fa-shield-alt"></i> Available Secure Integrations
                                </h5>
                                <div class="bank-hub-grid">
                                    <div class="bank-provider-card">
                                        <i class="fas fa-mobile-alt bank-provider-icon" style="color: #4CAF50;"></i>
                                        <div class="bank-provider-name">M-PESA G2</div>
                                    </div>
                                    <div class="bank-provider-card">
                                        <i class="fas fa-landmark bank-provider-icon" style="color: #003399;"></i>
                                        <div class="bank-provider-name">KCB Group</div>
                                    </div>
                                    <div class="bank-provider-card">
                                        <i class="fas fa-landmark bank-provider-icon" style="color: #630000;"></i>
                                        <div class="bank-provider-name">Equity Bank</div>
                                    </div>
                                    <div class="bank-provider-card">
                                        <i class="fas fa-landmark bank-provider-icon" style="color: #1e40af;"></i>
                                        <div class="bank-provider-name">Co-op Bank</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Bank Name</th>
                                        <th>Account Name</th>
                                        <th>Account Number</th>
                                        <th>Branch</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myBankAccounts as $ba): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ba['bank_name']); ?></td>
                                                <td><?php echo htmlspecialchars($ba['account_name']); ?></td>
                                                <td><code><?php echo htmlspecialchars($ba['account_number']); ?></code></td>
                                                <td><?php echo htmlspecialchars($ba['branch_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $ba['is_verified'] ? 'success' : 'warning'; ?>">
                                                        <i
                                                            class="fas fa-<?php echo $ba['is_verified'] ? 'check-circle' : 'clock'; ?>"></i>
                                                        <?php echo $ba['is_verified'] ? 'Verified' : 'Pending'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>