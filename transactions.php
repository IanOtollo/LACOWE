<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Session.php';
require_once 'includes/helpers.php';
require_once 'models/Transaction.php';
require_once 'models/Account.php';

Auth::requireRole([1, 2, 3]);

$transactionModel = new Transaction();
$accountModel = new Account();

// Handle new transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_type'])) {
    $accountId = (int)$_POST['account_id'];
    $amount = (float)$_POST['amount'];
    $description = sanitize($_POST['description']);
    $userId = Session::getUserId();
    
    if ($_POST['transaction_type'] === 'deposit') {
        $result = $transactionModel->processDeposit($accountId, $amount, $description, $userId);
    } else {
        $result = $transactionModel->processWithdrawal($accountId, $amount, $description, $userId);
    }
    
    Session::flash('success', $result['message'], $result['success'] ? 'success' : 'danger');
    redirect('transactions.php');
}

// Get filters
$filters = [];
if (!empty($_GET['type'])) {
    $filters['transaction_type'] = sanitize($_GET['type']);
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = sanitize($_GET['date_from']);
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = sanitize($_GET['date_to']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$transactions = $transactionModel->getAll($filters, RECORDS_PER_PAGE, ($page - 1) * RECORDS_PER_PAGE);

$pageTitle = 'Transactions';
include 'views/layouts/header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 style="margin: 0;">Process Transaction</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col" style="flex: 0 0 calc(25% - 0.75rem); margin-right: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Account ID</label>
                        <input type="number" name="account_id" class="form-control" required>
                    </div>
                </div>
                <div class="col" style="flex: 0 0 calc(25% - 0.75rem); margin-right: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Transaction Type</label>
                        <select name="transaction_type" class="form-control" required>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                        </select>
                    </div>
                </div>
                <div class="col" style="flex: 0 0 calc(25% - 0.75rem); margin-right: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Amount (KES)</label>
                        <input type="number" name="amount" class="form-control" required min="1" step="0.01">
                    </div>
                </div>
                <div class="col" style="flex: 0 0 calc(25% - 0.75rem);">
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Process</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" required>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Transaction History</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col" style="flex: 0 0 calc(33.333% - 0.66rem); margin-right: 1rem;">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="Deposit" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Deposit') ? 'selected' : ''; ?>>Deposit</option>
                        <option value="Withdrawal" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Withdrawal') ? 'selected' : ''; ?>>Withdrawal</option>
                    </select>
                </div>
                <div class="col" style="flex: 0 0 calc(33.333% - 0.66rem); margin-right: 1rem;">
                    <input type="date" name="date_from" class="form-control" placeholder="From Date" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                </div>
                <div class="col" style="flex: 0 0 calc(33.333% - 0.66rem);">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="transactions.php" class="btn btn-outline">Clear</a>
                </div>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ref No.</th>
                        <th>Member</th>
                        <th>Account</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Processed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><?php echo $txn['reference_number']; ?></td>
                                <td><?php echo htmlspecialchars($txn['member_name']); ?></td>
                                <td><?php echo $txn['account_number']; ?></td>
                                <td><?php echo $txn['transaction_type']; ?></td>
                                <td><?php echo formatCurrency($txn['amount']); ?></td>
                                <td><?php echo formatDateTime($txn['transaction_date']); ?></td>
                                <td><?php echo getStatusBadge($txn['status']); ?></td>
                                <td><?php echo htmlspecialchars($txn['processed_by']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 2rem; color: var(--gray-500);">No transactions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
