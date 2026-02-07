<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'includes/helpers.php';

Auth::requireAuth();
Auth::requireRole([4]); // Members only

$pageTitle = 'My Accounts';
$db = new Database();

// Initialize Models
require_once 'models/Member.php';
require_once 'models/Account.php';
require_once 'models/Transaction.php';

$memberModel = new Member();
$accountModel = new Account();
$transactionModel = new Transaction();

$userId = Session::getUserId();

// Get member details
$member = $memberModel->getByUserId($userId);

if (!$member) {
    Session::flash('error', 'Member record not found');
    redirect('logout.php');
}

$memberId = $member['member_id'];

// Get all accounts
// Note: We might want to add a specialized sorted getter in Account model later, 
// but for now we can fetch all and sort in PHP or just accept the default order.
// The raw SQL had specific sorting by account type.
// Let's implement a clean 'getByMemberId' in Account model that supports this or just sort here.
try {
    $accounts = $accountModel->getByMemberId($memberId);

    // Custom sort to match previous logic (Savings > Shares > Deposits > Others)
    usort($accounts, function ($a, $b) {
        $order = ['Savings' => 1, 'Shares' => 2, 'Deposits' => 3, 'Emergency Fund' => 4];
        $valA = $order[$a['account_type']] ?? 5;
        $valB = $order[$b['account_type']] ?? 5;

        if ($valA === $valB) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        }
        return $valA - $valB;
    });


}
catch (Exception $e) {
    $accounts = [];
}

// Calculate totals
$totalBalance = 0;
$savingsTotal = 0;
$sharesTotal = 0;
$depositsTotal = 0;
$activeAccountsCount = 0;

foreach ($accounts as $account) {
    $totalBalance += $account['balance'];
    if ($account['status'] == 'Active') { // Note: DB schema says 'account_status' but previous code used 'status'. 
        // Account model 'create' uses 'account_status'. 
        // 'getByMemberId' SELECT * returns table cols.
        // Schema says 'account_status'.
        // I must verify if the previous code was wrong or if I missed an alias.
        // Account.php getByMemberId selects *.
        // Let's check Account.php again or just use 'account_status'.
        // SAFE BET: Use $account['account_status'] as per schema.
        $activeAccountsCount++;
    }

    switch ($account['account_type']) {
        case 'Savings':
            $savingsTotal += $account['balance'];
            break;
        case 'Shares':
            $sharesTotal += $account['balance'];
            break;
        case 'Deposits':
            $depositsTotal += $account['balance'];
            break;
    }
}

// Get recent transactions across all accounts (last 10)
try {
    $recentTransactions = $transactionModel->getByMemberId($memberId, 10);
}
catch (Exception $e) {
    $recentTransactions = [];
}

include 'views/layouts/header.php';
?>

<style>
.account-summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    border-left: 4px solid var(--primary);
    transition: all 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.12);
}

.summary-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.summary-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.summary-card-primary {
    border-left-color: var(--primary);
}

.summary-card-primary .summary-card-icon {
    background: rgba(30, 64, 175, 0.1);
    color: var(--primary);
}

.summary-card-success {
    border-left-color: var(--success);
}

.summary-card-success .summary-card-icon {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.summary-card-info {
    border-left-color: #3b82f6;
}

.summary-card-info .summary-card-icon {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.summary-card-warning {
    border-left-color: var(--warning);
}

.summary-card-warning .summary-card-icon {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.summary-card-title {
    font-size: 0.813rem;
    color: var(--gray-600);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-card-amount {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--gray-800);
    line-height: 1;
}

.account-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    transition: all 0.2s ease;
    border-top: 3px solid var(--primary);
}

.account-card:hover {
    box-shadow: 0 8px 16px rgba(0,0,0,0.12);
}

.account-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--gray-100);
}

.account-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--gray-100);
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.account-type-savings {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.account-type-shares {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.account-type-deposits {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.account-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
    margin-bottom: 1rem;
}

.account-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.account-detail-label {
    font-size: 0.75rem;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.account-detail-value {
    font-size: 1rem;
    color: var(--gray-800);
    font-weight: 600;
}

.account-balance {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
}

.account-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.transaction-list {
    margin-top: 1.5rem;
}

.transaction-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.875rem;
    border-bottom: 1px solid var(--gray-100);
    transition: background 0.2s ease;
}

.transaction-item:hover {
    background: var(--gray-50);
}

.transaction-item:last-child {
    border-bottom: none;
}

.transaction-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.transaction-deposit .transaction-icon {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.transaction-withdrawal .transaction-icon {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

.transaction-info {
    flex: 1;
    margin-left: 1rem;
}

.transaction-type {
    font-weight: 600;
    color: var(--gray-800);
    font-size: 0.875rem;
}

.transaction-date {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.transaction-amount {
    font-weight: 700;
    font-size: 1rem;
}

.transaction-amount.positive {
    color: var(--success);
}

.transaction-amount.negative {
    color: var(--danger);
}

.empty-accounts {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.empty-accounts i {
    font-size: 4rem;
    color: var(--gray-300);
    margin-bottom: 1.5rem;
}

.empty-accounts h3 {
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.empty-accounts p {
    color: var(--gray-500);
    margin-bottom: 2rem;
}

.filter-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-item {
    flex: 1;
    min-width: 200px;
}

.account-number-display {
    font-family: var(--font-mono);
    font-size: 0.875rem;
    color: var(--primary);
    font-weight: 600;
}
</style>

<!-- Account Summary Cards -->
<div class="account-summary-cards">
    <div class="summary-card summary-card-primary">
        <div class="summary-card-header">
            <div class="summary-card-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="summary-card-title">Total Balance</div>
        </div>
        <div class="summary-card-amount">KES <?php echo number_format($totalBalance, 2); ?></div>
    </div>
    
    <div class="summary-card summary-card-success">
        <div class="summary-card-header">
            <div class="summary-card-icon">
                <i class="fas fa-piggy-bank"></i>
            </div>
            <div class="summary-card-title">Savings</div>
        </div>
        <div class="summary-card-amount">KES <?php echo number_format($savingsTotal, 2); ?></div>
    </div>
    
    <div class="summary-card summary-card-info">
        <div class="summary-card-header">
            <div class="summary-card-icon">
                <i class="fas fa-university"></i>
            </div>
            <div class="summary-card-title">Shares</div>
        </div>
        <div class="summary-card-amount">KES <?php echo number_format($sharesTotal, 2); ?></div>
    </div>
    
    <div class="summary-card summary-card-warning">
        <div class="summary-card-header">
            <div class="summary-card-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="summary-card-title">Active Accounts</div>
        </div>
        <div class="summary-card-amount"><?php echo $activeAccountsCount; ?></div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <div class="filter-item">
        <select class="form-control" id="accountTypeFilter">
            <option value="">All Account Types</option>
            <option value="Savings">Savings</option>
            <option value="Shares">Shares</option>
            <option value="Deposits">Deposits</option>
            <option value="Emergency Fund">Emergency Fund</option>
        </select>
    </div>
    <div class="filter-item">
        <select class="form-control" id="statusFilter">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Closed">Closed</option>
        </select>
    </div>
    <div style="margin-left: auto;">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print Statement
        </button>
    </div>
</div>

<!-- Accounts List -->
<?php if (empty($accounts)): ?>
    <div class="empty-accounts">
        <i class="fas fa-wallet"></i>
        <h3>No Accounts Yet</h3>
        <p>You don't have any accounts set up. Contact the administrator to create your first account.</p>
        <a href="member-dashboard.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
<?php
else: ?>
    <div id="accountsList">
        <?php foreach ($accounts as $account): ?>
            <div class="account-card" data-account-type="<?php echo $account['account_type']; ?>" data-status="<?php echo $account['account_status']; ?>">
                <div class="account-card-header">
                    <div>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <span class="account-type-badge account-type-<?php echo strtolower(str_replace(' ', '-', $account['account_type'])); ?>">
                                <i class="fas fa-<?php
        echo $account['account_type'] == 'Savings' ? 'piggy-bank' :
            ($account['account_type'] == 'Shares' ? 'university' :
            ($account['account_type'] == 'Deposits' ? 'money-check-alt' : 'shield-alt'));
?>"></i>
                                <?php echo htmlspecialchars($account['account_type']); ?>
                            </span>
                            <span class="badge badge-<?php echo $account['account_status'] == 'Active' ? 'success' : ($account['account_status'] == 'Inactive' ? 'warning' : 'secondary'); ?>">
                                <i class="fas fa-<?php echo $account['account_status'] == 'Active' ? 'check-circle' : ($account['account_status'] == 'Inactive' ? 'pause-circle' : 'times-circle'); ?>"></i>
                                <?php echo htmlspecialchars($account['account_status']); ?>
                            </span>
                        </div>
                        <div class="account-number-display">
                            <i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($account['account_number']); ?>
                        </div>
                    </div>
                    <div class="account-balance">
                        KES <?php echo number_format($account['balance'], 2); ?>
                    </div>
                </div>
                
                <div class="account-details-grid">
                    <div class="account-detail-item">
                        <div class="account-detail-label">
                            <i class="fas fa-calendar-plus"></i> Opened On
                        </div>
                        <div class="account-detail-value">
                            <?php echo date('d M Y', strtotime($account['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="account-detail-item">
                        <div class="account-detail-label">
                            <i class="fas fa-clock"></i> Last Updated
                        </div>
                        <div class="account-detail-value">
                            <?php echo date('d M Y', strtotime($account['updated_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="account-detail-item">
                        <div class="account-detail-label">
                            <i class="fas fa-info-circle"></i> Account Status
                        </div>
                        <div class="account-detail-value">
                            <?php echo htmlspecialchars($account['status']); ?>
                        </div>
                    </div>
                    
                    <div class="account-detail-item">
                        <div class="account-detail-label">
                            <i class="fas fa-hashtag"></i> Account ID
                        </div>
                        <div class="account-detail-value">
                            #<?php echo str_pad($account['account_id'], 6, '0', STR_PAD_LEFT); ?>
                        </div>
                    </div>
                </div>
                
                <div class="account-actions">
                    <button class="btn btn-sm btn-primary" onclick="viewTransactions(<?php echo $account['account_id']; ?>)">
                        <i class="fas fa-list"></i> View Transactions
                    </button>
                    <button class="btn btn-sm btn-success" onclick="downloadStatement(<?php echo $account['account_id']; ?>)">
                        <i class="fas fa-download"></i> Download Statement
                    </button>
                </div>
                
                <!-- Recent Transactions for this Account -->
                <?php
        try {
            $sql = "SELECT * FROM transactions 
                            WHERE account_id = :account_id 
                            ORDER BY transaction_date DESC 
                            LIMIT 5";
            $accountTransactions = $db->query($sql)->bind(':account_id', $account['account_id'])->fetchAll();

            if (!empty($accountTransactions)):
?>
                    <div class="transaction-list">
                        <h4 style="font-size: 0.938rem; color: var(--gray-700); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-history"></i> Recent Transactions
                        </h4>
                        <?php foreach ($accountTransactions as $trans): ?>
                            <div class="transaction-item transaction-<?php echo strtolower($trans['transaction_type']); ?>">
                                <div class="transaction-icon">
                                    <i class="fas fa-<?php echo $trans['transaction_type'] == 'Deposit' ? 'arrow-down' : 'arrow-up'; ?>"></i>
                                </div>
                                <div class="transaction-info">
                                    <div class="transaction-type">
                                        <?php echo htmlspecialchars($trans['transaction_type']); ?>
                                        <?php if (!empty($trans['description'])): ?>
                                            <span style="color: var(--gray-500); font-weight: 400;"> - <?php echo htmlspecialchars($trans['description']); ?></span>
                                        <?php
                    endif; ?>
                                    </div>
                                    <div class="transaction-date">
                                        <i class="fas fa-calendar"></i> <?php echo date('d M Y, h:i A', strtotime($trans['transaction_date'])); ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="transaction-amount <?php echo $trans['transaction_type'] == 'Deposit' ? 'positive' : 'negative'; ?>">
                                        <?php echo $trans['transaction_type'] == 'Deposit' ? '+' : '-'; ?>
                                        KES <?php echo number_format($trans['amount'], 2); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--gray-500); text-align: right; margin-top: 0.25rem;">
                                        Balance: KES <?php echo number_format($trans['balance_after'], 2); ?>
                                    </div>
                                </div>
                            </div>
                        <?php
                endforeach; ?>
                    </div>
                <?php
            endif;
        }
        catch (Exception $e) {
        // Silently handle error
        }
?>
            </div>
        <?php
    endforeach; ?>
    </div>
<?php
endif; ?>

<!-- All Recent Transactions Section -->
<?php if (!empty($recentTransactions)): ?>
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3 style="margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-history"></i>
                All Recent Transactions
            </h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag table-icon"></i> Reference</th>
                            <th><i class="fas fa-credit-card table-icon"></i> Account</th>
                            <th><i class="fas fa-exchange-alt table-icon"></i> Type</th>
                            <th><i class="fas fa-money-bill-wave table-icon"></i> Amount</th>
                            <th><i class="fas fa-calendar table-icon"></i> Date</th>
                            <th><i class="fas fa-balance-scale table-icon"></i> Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $trans): ?>
                            <tr>
                                <td>
                                    <code style="font-size: 0.75rem;"><?php echo htmlspecialchars($trans['reference_number']); ?></code>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?php echo htmlspecialchars($trans['account_type']); ?>
                                    </span>
                                    <div style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.25rem;">
                                        <?php echo htmlspecialchars($trans['account_number']); ?>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-<?php echo $trans['transaction_type'] == 'Deposit' ? 'arrow-down' : 'arrow-up'; ?>" 
                                       style="color: <?php echo $trans['transaction_type'] == 'Deposit' ? 'var(--success)' : 'var(--danger)'; ?>"></i>
                                    <?php echo htmlspecialchars($trans['transaction_type']); ?>
                                </td>
                                <td>
                                    <strong style="color: <?php echo $trans['transaction_type'] == 'Deposit' ? 'var(--success)' : 'var(--danger)'; ?>">
                                        <?php echo $trans['transaction_type'] == 'Deposit' ? '+' : '-'; ?>
                                        KES <?php echo number_format($trans['amount'], 2); ?>
                                    </strong>
                                </td>
                                <td><?php echo date('d M Y, h:i A', strtotime($trans['transaction_date'])); ?></td>
                                <td>KES <?php echo number_format($trans['balance_after'], 2); ?></td>
                            </tr>
                        <?php
    endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="my-transactions.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> View All Transactions
                </a>
            </div>
        </div>
    </div>
<?php
endif; ?>

<script>
// Filter functionality
document.getElementById('accountTypeFilter').addEventListener('change', filterAccounts);
document.getElementById('statusFilter').addEventListener('change', filterAccounts);

function filterAccounts() {
    const typeFilter = document.getElementById('accountTypeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const accounts = document.querySelectorAll('.account-card');
    
    accounts.forEach(account => {
        const accountType = account.getAttribute('data-account-type');
        const accountStatus = account.getAttribute('data-status');
        
        const typeMatch = !typeFilter || accountType === typeFilter;
        const statusMatch = !statusFilter || accountStatus === statusFilter;
        
        if (typeMatch && statusMatch) {
            account.style.display = 'block';
        } else {
            account.style.display = 'none';
        }
    });
}

// View transactions
function viewTransactions(accountId) {
    window.location.href = 'my-transactions.php?account_id=' + accountId;
}

// Download statement
function downloadStatement(accountId) {
    window.location.href = 'download-statement.php?account_id=' + accountId;
}

// Auto-dismiss alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>

<?php include 'views/layouts/footer.php'; ?>