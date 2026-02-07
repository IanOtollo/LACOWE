<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'includes/Session.php';
require_once 'includes/helpers.php';

Auth::requireAuth();
Auth::requireRole([4]); // Members only
Session::start();

$pageTitle = 'My Transactions';
$db = new Database();

$userId = Session::getUserId();

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

// Filters
$filters = [
    'account_id' => $_GET['account_id'] ?? '',
    'type' => $_GET['type'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Get transactions
try {
    $transactions = $transactionModel->getByMemberId($memberId, $filters);
}
catch (Exception $e) {
    $transactions = [];
}

// Get member accounts for filter
try {
    $accounts = $accountModel->getByMemberId($memberId);
}
catch (Exception $e) {
    $accounts = [];
}

// Calculate statistics
$totalDeposits = 0;
$totalWithdrawals = 0;
// Recalculate based on loaded transactions to match filter view
// OR: Should stats be always total independent of filter? 
// Previous code calculated based on '$transactions' array (which was filtered).
// So we keep that behavior.
$transactionCount = count($transactions);

foreach ($transactions as $trans) {
    if ($trans['transaction_type'] == 'Deposit') {
        $totalDeposits += $trans['amount'];
    }
    else {
        $totalWithdrawals += $trans['amount'];
    }
}

$netChange = $totalDeposits - $totalWithdrawals;

include 'views/layouts/header.php';
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    border-left: 4px solid var(--primary);
    transition: all 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.12);
}

.stat-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.stat-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-card-primary {
    border-left-color: var(--primary);
}

.stat-card-primary .stat-card-icon {
    background: rgba(30, 64, 175, 0.1);
    color: var(--primary);
}

.stat-card-success {
    border-left-color: var(--success);
}

.stat-card-success .stat-card-icon {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.stat-card-danger {
    border-left-color: var(--danger);
}

.stat-card-danger .stat-card-icon {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

.stat-card-info {
    border-left-color: #3b82f6;
}

.stat-card-info .stat-card-icon {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.stat-card-title {
    font-size: 0.813rem;
    color: var(--gray-600);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card-amount {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--gray-800);
    line-height: 1;
}

.filters-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.transaction-table-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.transaction-row {
    transition: background 0.2s ease;
}

.transaction-row:hover {
    background: var(--gray-50);
}

.transaction-deposit {
    border-left: 3px solid var(--success);
}

.transaction-withdrawal {
    border-left: 3px solid var(--danger);
}

.amount-positive {
    color: var(--success);
    font-weight: 700;
}

.amount-negative {
    color: var(--danger);
    font-weight: 700;
}

.transaction-reference {
    font-family: var(--font-mono);
    font-size: 0.75rem;
    color: var(--gray-600);
    background: var(--gray-100);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.empty-state i {
    font-size: 4rem;
    color: var(--gray-300);
    margin-bottom: 1.5rem;
}
</style>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card stat-card-success">
        <div class="stat-card-header">
            <div class="stat-card-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-card-title">Total Deposits</div>
        </div>
        <div class="stat-card-amount">KES <?php echo number_format($totalDeposits, 2); ?></div>
    </div>
    
    <div class="stat-card stat-card-danger">
        <div class="stat-card-header">
            <div class="stat-card-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-card-title">Total Withdrawals</div>
        </div>
        <div class="stat-card-amount">KES <?php echo number_format($totalWithdrawals, 2); ?></div>
    </div>
    
    <div class="stat-card stat-card-info">
        <div class="stat-card-header">
            <div class="stat-card-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-card-title">Net Change</div>
        </div>
        <div class="stat-card-amount" style="color: <?php echo $netChange >= 0 ? 'var(--success)' : 'var(--danger)'; ?>">
            <?php echo $netChange >= 0 ? '+' : ''; ?>KES <?php echo number_format($netChange, 2); ?>
        </div>
    </div>
    
    <div class="stat-card stat-card-primary">
        <div class="stat-card-header">
            <div class="stat-card-icon">
                <i class="fas fa-list"></i>
            </div>
            <div class="stat-card-title">Total Transactions</div>
        </div>
        <div class="stat-card-amount"><?php echo $transactionCount; ?></div>
    </div>
</div>

<!-- Filters -->
<div class="filters-card">
    <h3 style="margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-filter"></i> Filter Transactions
    </h3>
    <form method="GET" action="">
        <div class="filters-grid">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Account</label>
                <select name="account_id" class="form-control">
                    <option value="">All Accounts</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?php echo $acc['account_id']; ?>" <?php echo $filters['account_id'] == $acc['account_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($acc['account_type'] . ' - ' . $acc['account_number']); ?>
                        </option>
                    <?php
endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Transaction Type</label>
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="Deposit" <?php echo $filters['type'] == 'Deposit' ? 'selected' : ''; ?>>Deposits</option>
                    <option value="Withdrawal" <?php echo $filters['type'] == 'Withdrawal' ? 'selected' : ''; ?>>Withdrawals</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Reference Number</label>
                <input type="text" name="search" class="form-control" placeholder="Search reference..." value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>
        </div>
        
        <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Apply Filters
            </button>
            <a href="my-transactions.php" class="btn btn-outline">
                <i class="fas fa-redo"></i> Clear Filters
            </a>
            <button type="button" class="btn btn-success" onclick="window.print()" style="margin-left: auto;">
                <i class="fas fa-print"></i> Print Statement
            </button>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<?php if (empty($transactions)): ?>
    <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <h3>No Transactions Found</h3>
        <p>No transactions match your filters or you haven't made any transactions yet.</p>
        <a href="my-accounts.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Accounts
        </a>
    </div>
<?php
else: ?>
    <div class="transaction-table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar table-icon"></i> Date & Time</th>
                        <th><i class="fas fa-hashtag table-icon"></i> Reference</th>
                        <th><i class="fas fa-credit-card table-icon"></i> Account</th>
                        <th><i class="fas fa-exchange-alt table-icon"></i> Type</th>
                        <th><i class="fas fa-money-bill-wave table-icon"></i> Amount</th>
                        <th><i class="fas fa-balance-scale table-icon"></i> Balance After</th>
                        <th><i class="fas fa-info-circle table-icon"></i> Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trans): ?>
                        <tr class="transaction-row transaction-<?php echo strtolower($trans['transaction_type']); ?>">
                            <td>
                                <div style="font-weight: 600; font-size: 0.875rem;">
                                    <?php echo date('d M Y', strtotime($trans['transaction_date'])); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">
                                    <?php echo date('h:i A', strtotime($trans['transaction_date'])); ?>
                                </div>
                            </td>
                            <td>
                                <span class="transaction-reference">
                                    <?php echo htmlspecialchars($trans['reference_number']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary" style="font-size: 0.75rem;">
                                    <?php echo htmlspecialchars($trans['account_type']); ?>
                                </span>
                                <div style="font-size: 0.7rem; color: var(--gray-500); margin-top: 0.25rem;">
                                    <?php echo htmlspecialchars($trans['account_number']); ?>
                                </div>
                            </td>
                            <td>
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-<?php echo $trans['transaction_type'] == 'Deposit' ? 'arrow-down' : 'arrow-up'; ?>" 
                                       style="color: <?php echo $trans['transaction_type'] == 'Deposit' ? 'var(--success)' : 'var(--danger)'; ?>"></i>
                                    <?php echo htmlspecialchars($trans['transaction_type']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="<?php echo $trans['transaction_type'] == 'Deposit' ? 'amount-positive' : 'amount-negative'; ?>">
                                    <?php echo $trans['transaction_type'] == 'Deposit' ? '+' : '-'; ?>
                                    KES <?php echo number_format($trans['amount'], 2); ?>
                                </span>
                            </td>
                            <td>
                                <strong>KES <?php echo number_format($trans['balance_after'], 2); ?></strong>
                            </td>
                            <td style="max-width: 200px;">
                                <?php echo !empty($trans['description']) ? htmlspecialchars($trans['description']) : '<span style="color: var(--gray-400);">No description</span>'; ?>
                            </td>
                        </tr>
                    <?php
    endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 1.5rem; color: var(--gray-600);">
        <p>Showing <?php echo count($transactions); ?> transaction(s)</p>
    </div>
<?php
endif; ?>

<?php include 'views/layouts/footer.php'; ?>