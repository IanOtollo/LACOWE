<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'includes/helpers.php';

Auth::requireRole([1, 2, 3]); // Admin only

$pageTitle = 'Accounts Management';
$db = new Database();

// Get all accounts with member information
try {
    $sql = "SELECT a.*, 
                   CONCAT(m.first_name, ' ', m.last_name) as member_name,
                   m.member_number,
                   m.membership_status
            FROM accounts a
            INNER JOIN members m ON a.member_id = m.member_id
            ORDER BY a.created_at DESC";
    $accounts = $db->getConnection()->query($sql)->fetchAll();
}
catch (Exception $e) {
    $accounts = [];
    Session::flash('error', 'Error loading accounts: ' . $e->getMessage());
}

// Get summary statistics
try {
    $sql = "SELECT 
                COUNT(*) as total_accounts,
                SUM(CASE WHEN account_status = 'Active' THEN 1 ELSE 0 END) as active_accounts,
                SUM(balance) as total_balance,
                SUM(CASE WHEN account_type = 'Savings' THEN balance ELSE 0 END) as savings_total,
                SUM(CASE WHEN account_type = 'Shares' THEN balance ELSE 0 END) as shares_total
            FROM accounts";
    $stats = $db->getConnection()->query($sql)->fetch();
}
catch (Exception $e) {
    $stats = [
        'total_accounts' => 0,
        'active_accounts' => 0,
        'total_balance' => 0,
        'savings_total' => 0,
        'shares_total' => 0
    ];
}

include 'views/layouts/header.php';
?>

<div class="row" style="margin-bottom: 2rem;">
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($stats['total_accounts']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Total Accounts</p>
            </div>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--success); margin-bottom: 0.5rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($stats['active_accounts']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Active Accounts</p>
            </div>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo formatCurrency($stats['savings_total']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Total Savings</p>
            </div>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--secondary); margin-bottom: 0.5rem;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo formatCurrency($stats['shares_total']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Total Shares</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">All Accounts</h3>
    </div>
    
    <div class="card-body">
        <?php if (!empty($accounts)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Account #</th>
                            <th>Member</th>
                            <th>Type</th>
                            <th>Balance</th>
                            <th>Interest Rate</th>
                            <th>Date Opened</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td style="font-family: var(--font-mono); font-weight: 600;">
                                    <?php echo htmlspecialchars($account['account_number']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($account['member_name']); ?><br>
                                    <small style="color: var(--gray-500);"><?php echo htmlspecialchars($account['member_number']); ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $account['account_type'] == 'Savings' ? 'primary' : 'success'; ?>">
                                        <?php echo htmlspecialchars($account['account_type']); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 600; color: var(--gray-900);">
                                    <?php echo formatCurrency($account['balance']); ?>
                                </td>
                                <td><?php echo number_format($account['interest_rate'], 2); ?>%</td>
                                <td><?php echo formatDate($account['date_opened']); ?></td>
                                <td><?php echo getStatusBadge($account['account_status']); ?></td>
                            </tr>
                        <?php
    endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php
else: ?>
            <div style="text-align: center; padding: 3rem; color: var(--gray-500);">
                <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.5; margin-bottom: 1rem;"></i>
                <p>No accounts found</p>
            </div>
        <?php
endif; ?>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
