<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'includes/Session.php';
require_once 'includes/helpers.php';

Auth::requireAuth();
Session::start();

// Redirect members to their specific dashboard
if (Session::getUserRole() == 4) {
    header('Location: member-dashboard.php');
    exit();
}

$pageTitle = 'Dashboard';
$db = new Database();

// Get user info
$userId = Session::getUserId();
$userName = Session::getUserName();
$roleId = Session::getUserRole();

// ========================================
// ADMIN DASHBOARD (Roles 1, 2, 3)
// ========================================
if ($roleId <= 3) {
    // Get SYSTEM-WIDE statistics
    try {
                // Total Members
        $sql = "SELECT COUNT(*) FROM members WHERE membership_status = 'Active'";
        $stmt = $db->getConnection()->query($sql);
        $totalMembers = (int)($stmt->fetchColumn() ?? 0);
        
        // Active Members (same as total)
        $activeMembers = $totalMembers;
        
        // Active Loans
        $sql = "SELECT COUNT(*) FROM loans WHERE loan_status = 'Active'";
        $loansStmt = $db->getConnection()->query($sql);
        $activeLoans = (int)($loansStmt->fetchColumn() ?? 0);
        
        // Outstanding Balance
        $sql = "SELECT COALESCE(SUM(balance), 0) FROM loans WHERE loan_status = 'Active'";
        $balanceStmt = $db->getConnection()->query($sql);
        $outstandingBalance = (float)($balanceStmt->fetchColumn() ?? 0);
        
        $stats = [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'active_loans' => $activeLoans,
            'outstanding_balance' => $outstandingBalance
        ];

        $stats = [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'active_loans' => $activeLoans,
            'outstanding_balance' => $outstandingBalance
        ];
        
    } catch (Exception $e) {
        $stats = [
            'total_members' => 0,
            'active_members' => 0,
            'active_loans' => 0,
            'outstanding_balance' => 0
        ];
    }

    // Get recent members
    try {
        $sql = "SELECT m.*, CONCAT(m.first_name, ' ', m.last_name) as full_name
                FROM members m
                ORDER BY m.created_at DESC
                LIMIT 5";
        $recentMembers = $db->getConnection()->query($sql)->fetchAll();
    } catch (Exception $e) {
        $recentMembers = [];
    }

    // Get recent loan applications
    try {
        $sql = "SELECT la.*, CONCAT(m.first_name, ' ', m.last_name) as member_name, m.member_number
                FROM loan_applications la
                INNER JOIN members m ON la.member_id = m.member_id
                ORDER BY la.application_date DESC
                LIMIT 5";
        $recentApplications = $db->getConnection()->query($sql)->fetchAll();
    } catch (Exception $e) {
        $recentApplications = [];
    }

    include 'views/layouts/header.php';
    ?>

    <!-- ADMIN DASHBOARD -->
    <div class="row">
        <div class="col col-3">
            <div class="card">
                <div class="card-body" style="text-align: center;"><div style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"><i class="fas fa-users"></i></div>
                
                    <h3 style="margin: 0; font-size: 1.75rem; color: var(--gray-900);"><?php echo number_format($stats['total_members']); ?></h3><p style="margin: 0.5rem 0 0; color: var(--gray-600); font-size: 0.875rem;">Total Members</p></div>
            </div>
        </div>
        
        <div class="col col-3">
            <div class="card">
                <div class="card-body" style="text-align: center;"><div style="font-size: 2rem; color: var(--success); margin-bottom: 0.5rem;"><i class="fas fa-user-check"></i></div>
                
                    <h3 style="margin: 0; font-size: 1.75rem; color: var(--gray-900);"><?php echo number_format($stats['active_members']); ?></h3><p style="margin: 0.5rem 0 0; color: var(--gray-600); font-size: 0.875rem;">Active Members</p></div>
            </div>
        </div>
        
        <div class="col col-3">
            <div class="card">
                <div class="card-body" style="text-align: center;"><div style="font-size: 2rem; color: var(--warning); margin-bottom: 0.5rem;"><i class="fas fa-hand-holding-usd"></i></div>
                
                    <h3 style="margin: 0; font-size: 1.75rem; color: var(--gray-900);"><?php echo number_format($stats['active_loans']); ?></h3><p style="margin: 0.5rem 0 0; color: var(--gray-600); font-size: 0.875rem;">Active Loans</p></div>
            </div>
        </div>
        
        <div class="col col-3">
            <div class="card">
                <div class="card-body" style="text-align: center;"><div style="font-size: 2rem; color: var(--danger); margin-bottom: 0.5rem;"><i class="fas fa-money-bill-wave"></i></div>
                
                    <h3 style="margin: 0; font-size: 1.75rem; color: var(--gray-900);">KES <?php echo number_format($stats['outstanding_balance'], 2); ?></h3><p style="margin: 0.5rem 0 0; color: var(--gray-600); font-size: 0.875rem;">Outstanding Balance</p></div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 2rem;">
        <div class="col col-6">
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Recent Members</h3>
                    <a href="members.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentMembers)): ?>
                        <p style="text-align: center; color: var(--gray-500); padding: 2rem;">No members yet</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Member #</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMembers as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member['member_number']); ?></td>
                                        <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($member['phone_number']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $member['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo htmlspecialchars($member['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col col-6">
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Recent Loan Applications</h3>
                    <a href="loans.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentApplications)): ?>
                        <p style="text-align: center; color: var(--gray-500); padding: 2rem;">No applications yet</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentApplications as $app): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($app['member_name']); ?></td>
                                        <td>KES <?php echo number_format($app['amount_requested'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($app['loan_type']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $app['status'] == 'Approved' ? 'success' : ($app['status'] == 'Rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo htmlspecialchars($app['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    include 'views/layouts/footer.php';
    
// ========================================
// MEMBER DASHBOARD (Role 4)
// ========================================
} else {
    // Get MEMBER'S OWN data only
    try {
        // Get member_id from user_id
        $sql = "SELECT member_id, member_number, first_name, last_name 
                FROM members WHERE user_id = :user_id";
        $member = $db->query($sql)->bind(':user_id', $userId)->fetch();
        
        if (!$member) {
            throw new Exception("Member record not found");
        }
        
        $memberId = $member['member_id'];
        $memberName = $member['first_name'] . ' ' . $member['last_name'];
        $memberNumber = $member['member_number'];
        
        // MY Total Account Balance
        $sql = "SELECT COALESCE(SUM(balance), 0) FROM accounts WHERE member_id = :member_id AND status = 'Active'";
        $totalBalance = (float)$db->query($sql)->bind(':member_id', $memberId)->fetch(PDO::FETCH_COLUMN);
        
        // MY Savings Balance
        $sql = "SELECT COALESCE(SUM(balance), 0) FROM accounts WHERE member_id = :member_id AND account_type = 'Savings' AND status = 'Active'";
        $savingsBalance = (float)$db->query($sql)->bind(':member_id', $memberId)->fetch(PDO::FETCH_COLUMN);
        
        // MY Active Loans
        $sql = "SELECT COUNT(*) FROM loans WHERE member_id = :member_id AND status = 'Active'";
        $myActiveLoans = (int)$db->query($sql)->bind(':member_id', $memberId)->fetch(PDO::FETCH_COLUMN);
        
        // MY Outstanding Loan Balance
        $sql = "SELECT COALESCE(SUM(balance), 0) FROM loans WHERE member_id = :member_id AND status = 'Active'";
        $myOutstanding = (float)$db->query($sql)->bind(':member_id', $memberId)->fetch(PDO::FETCH_COLUMN);
        
        $memberStats = [
            'total_balance' => $totalBalance,
            'savings_balance' => $savingsBalance,
            'active_loans' => $myActiveLoans,
            'outstanding_balance' => $myOutstanding
        ];
        
    } catch (Exception $e) {
        $memberStats = [
            'total_balance' => 0,
            'savings_balance' => 0,
            'active_loans' => 0,
            'outstanding_balance' => 0
        ];
        $memberId = 0;
    }

    // Get MY accounts
    try {
        $sql = "SELECT * FROM accounts WHERE member_id = :member_id ORDER BY created_at DESC LIMIT 5";
        $myAccounts = $db->query($sql)->bind(':member_id', $memberId)->fetchAll();
    } catch (Exception $e) {
        $myAccounts = [];
    }

    // Get MY recent transactions
    try {
        $sql = "SELECT t.*, a.account_number, a.account_type
                FROM transactions t
                INNER JOIN accounts a ON t.account_id = a.account_id
                WHERE a.member_id = :member_id
                ORDER BY t.transaction_date DESC
                LIMIT 5";
        $myTransactions = $db->query($sql)->bind(':member_id', $memberId)->fetchAll();
    } catch (Exception $e) {
        $myTransactions = [];
    }

    // Get MY loans
    try {
        $sql = "SELECT * FROM loans WHERE member_id = :member_id ORDER BY created_at DESC LIMIT 5";
        $myLoans = $db->query($sql)->bind(':member_id', $memberId)->fetchAll();
    } catch (Exception $e) {
        $myLoans = [];
    }

    include 'views/layouts/header.php';
    ?>

    <!-- MEMBER DASHBOARD -->
    <div style="margin-bottom: 2rem;">
        <h2>Welcome back, <?php echo htmlspecialchars($memberName); ?>!</h2>
        <p style="color: var(--gray-600);">Member Number: <strong><?php echo htmlspecialchars($memberNumber); ?></strong></p>
    </div>

    <div class="row">
        <div class="col col-3">
            <div class="card">
                <div class="card-body" style="text-align: center;"><div style="font-size: 2rem; color: var(--danger); margin-bottom: 0.5rem;"><i class="fas fa-money-bill-wave"></i></div>
                
                    <h3 style="margin: 0; font-size: 1.75rem; color: var(--gray-900);">KES <?php echo number_format($memberStats['total_balance'], 2); ?></h3><p style="margin: 0.5rem 0 0; color: var(--gray-600); font-size: 0.875rem;">Total Balance</div>
                </div>
            </div>
        </div>
        
        <div class="col col-3">
            <div class="card">
                <div class="stat-icon">🏦</div>
                
                    <h3 style="margin: 0; font-size: 1.75rem; color: var(--gray-900);">KES <?php echo number_format($memberStats['savings_balance'], 2); ?></h3><p style="margin: 0.5rem 0 0; color: var(--gray-600); font-size: 0.875rem;">Savings Balance</div>
                </div>
            </div>
        </div>
        
        <div class="col col-3">
            <div class="card">
                <div class="card-body" style="text-align: center;"><div style="font-size: 2rem; color: var(--warning); margin-bottom: 0.5rem;"><i class="fas fa-hand-holding-usd"></i></div>
                
                    <h3 style="margin: 0; font-size: 1.75rem; color: var(--gray-900);"><?php echo number_format($memberStats['active_loans']); ?></h3><p style="margin: 0.5rem 0 0; color: var(--gray-600); font-size: 0.875rem;">My Active Loans</p></div>
            </div>
        </div>
        
        <div class="col col-3">
            <div class="card">
                <div class="stat-icon">📊</div>
                
                    <h3 style="margin: 0; font-size: 1.75rem; color: var(--gray-900);">KES <?php echo number_format($memberStats['outstanding_balance'], 2); ?></h3><p style="margin: 0.5rem 0 0; color: var(--gray-600); font-size: 0.875rem;">Loan Balance</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row" style="margin-top: 2rem;">
        <div class="col col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="loan-application.php" class="btn btn-primary">
                            📄 Apply for Loan
                        </a>
                        <a href="my-accounts.php" class="btn btn-success">
                            💰 View My Accounts
                        </a>
                        <a href="my-transactions.php" class="btn btn-info">
                            📝 View Transactions
                        </a>
                        <a href="my-loans.php" class="btn btn-warning">
                            💳 My Loans
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 2rem;">
        <!-- My Accounts -->
        <div class="col col-6">
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>My Accounts</h3>
                    <a href="my-accounts.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($myAccounts)): ?>
                        <p style="text-align: center; color: var(--gray-500); padding: 2rem;">No accounts yet</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Account #</th>
                                    <th>Type</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myAccounts as $account): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($account['account_number']); ?></td>
                                        <td><?php echo htmlspecialchars($account['account_type']); ?></td>
                                        <td>KES <?php echo number_format($account['balance'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $account['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo htmlspecialchars($account['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col col-6">
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Recent Transactions</h3>
                    <a href="my-transactions.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($myTransactions)): ?>
                        <p style="text-align: center; color: var(--gray-500); padding: 2rem;">No transactions yet</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myTransactions as $trans): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($trans['transaction_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($trans['transaction_type']); ?></td>
                                        <td>KES <?php echo number_format($trans['amount'], 2); ?></td>
                                        <td>KES <?php echo number_format($trans['balance_after'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    include 'views/layouts/footer.php';
}
?>


