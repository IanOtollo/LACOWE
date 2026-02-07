<?php
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

Auth::requireAuth();
Auth::requireRole([4]); // Members only

$pageTitle = 'My Loans';
$db = new Database();

$userId = Session::getUserId();

// Initialize Models
require_once 'models/Member.php';
require_once 'models/Loan.php';

$memberModel = new Member();
$loanModel = new Loan();

$userId = Session::getUserId();

// Get member details
$member = $memberModel->getByUserId($userId);

if (!$member) {
    Session::flash('error', 'Member record not found');
    redirect('logout.php');
}

$memberId = $member['member_id'];

// Get all loans
// Note: Loan::getLoansByMember returns all loans for the member
try {
    $loans = $loanModel->getLoansByMember($memberId);
}
catch (Exception $e) {
    $loans = [];
}

// Get loan applications
try {
    // getAllApplications supports filters. 
    // We pass ['member_id' => $memberId]
    $applications = $loanModel->getAllApplications(['member_id' => $memberId]);
}
catch (Exception $e) {
    $applications = [];
}

// Calculate statistics
$totalLoans = count($loans);
$activeLoans = 0;
$totalBorrowed = 0;
$totalOutstanding = 0;
$totalPaid = 0;
$pendingApplications = 0;

foreach ($loans as $loan) {
    // Schema uses 'principal_amount' and 'balance' and 'loan_status'
    $principal = $loan['principal_amount'];
    $balance = $loan['balance'];

    $totalBorrowed += $principal;
    $totalOutstanding += $balance;

    // Amount paid so far is principal - balance remaining? 
    // Or strictly from repayments table?
    // Using simple math: if fully paid, balance is 0.
    // 'amount_paid' is also in schema `loans` table.
    $totalPaid += $loan['amount_paid'];

    if ($loan['loan_status'] == 'Active') {
        $activeLoans++;
    }
}

foreach ($applications as $app) {
    if ($app['application_status'] == 'Pending') {
        $pendingApplications++;
    }
}

include 'views/layouts/header.php';
?>

<style>
.loan-summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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

.summary-card-warning {
    border-left-color: var(--warning);
}

.summary-card-warning .summary-card-icon {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.summary-card-danger {
    border-left-color: var(--danger);
}

.summary-card-danger .summary-card-icon {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

.summary-card-info {
    border-left-color: #3b82f6;
}

.summary-card-info .summary-card-icon {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
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

.loan-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    transition: all 0.2s ease;
    border-top: 4px solid var(--primary);
}

.loan-card:hover {
    box-shadow: 0 8px 16px rgba(0,0,0,0.12);
}

.loan-card-active {
    border-top-color: var(--warning);
}

.loan-card-paid {
    border-top-color: var(--success);
}

.loan-card-defaulted {
    border-top-color: var(--danger);
}

.loan-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--gray-100);
}

.loan-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--gray-100);
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.loan-type-emergency {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

.loan-type-development {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.loan-type-school-fees {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.loan-type-business {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.loan-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}

.loan-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.loan-detail-label {
    font-size: 0.75rem;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.loan-detail-value {
    font-size: 1rem;
    color: var(--gray-800);
    font-weight: 600;
}

.loan-amount {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
}

.progress-container {
    margin: 1.5rem 0;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-700);
}

.progress-bar-container {
    width: 100%;
    height: 12px;
    background: var(--gray-200);
    border-radius: 6px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--success) 0%, #059669 100%);
    border-radius: 6px;
    transition: width 0.5s ease;
}

.progress-bar-warning {
    background: linear-gradient(90deg, var(--warning) 0%, #d97706 100%);
}

.progress-bar-danger {
    background: linear-gradient(90deg, var(--danger) 0%, #dc2626 100%);
}

.loan-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 1.25rem;
}

.payment-history {
    margin-top: 1.5rem;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.875rem;
    border-bottom: 1px solid var(--gray-100);
    transition: background 0.2s ease;
}

.payment-item:hover {
    background: var(--gray-50);
}

.payment-item:last-child {
    border-bottom: none;
}

.payment-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.payment-info {
    flex: 1;
    margin-left: 1rem;
}

.payment-type {
    font-weight: 600;
    color: var(--gray-800);
    font-size: 0.875rem;
}

.payment-date {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.payment-amount {
    font-weight: 700;
    font-size: 1rem;
    color: var(--success);
}

.application-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    border-left: 4px solid var(--warning);
}

.application-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
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

.empty-state h3 {
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--gray-500);
    margin-bottom: 2rem;
}

.loan-number-display {
    font-family: var(--font-mono);
    font-size: 0.875rem;
    color: var(--primary);
    font-weight: 600;
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

.tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--gray-200);
}

.tab {
    padding: 0.75rem 1.5rem;
    border: none;
    background: transparent;
    color: var(--gray-600);
    font-weight: 500;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
}

.tab:hover {
    color: var(--primary);
}

.tab.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>

<!-- Loan Summary Cards -->
<div class="loan-summary-cards">
    <div class="summary-card summary-card-primary">
        <div class="summary-card-header">
            <div class="summary-card-icon">
                <i class="fas fa-list"></i>
            </div>
            <div class="summary-card-title">Total Loans</div>
        </div>
        <div class="summary-card-amount"><?php echo $totalLoans; ?></div>
    </div>
    
    <div class="summary-card summary-card-warning">
        <div class="summary-card-header">
            <div class="summary-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="summary-card-title">Active Loans</div>
        </div>
        <div class="summary-card-amount"><?php echo $activeLoans; ?></div>
    </div>
    
    <div class="summary-card summary-card-info">
        <div class="summary-card-header">
            <div class="summary-card-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="summary-card-title">Total Borrowed</div>
        </div>
        <div class="summary-card-amount">KES <?php echo number_format($totalBorrowed, 2); ?></div>
    </div>
    
    <div class="summary-card summary-card-danger">
        <div class="summary-card-header">
            <div class="summary-card-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="summary-card-title">Outstanding</div>
        </div>
        <div class="summary-card-amount">KES <?php echo number_format($totalOutstanding, 2); ?></div>
    </div>
    
    <div class="summary-card summary-card-success">
        <div class="summary-card-header">
            <div class="summary-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="summary-card-title">Total Paid</div>
        </div>
        <div class="summary-card-amount">KES <?php echo number_format($totalPaid, 2); ?></div>
    </div>
</div>

<!-- Tabs -->
<div class="tabs">
    <button class="tab active" onclick="switchTab('active-loans')">
        <i class="fas fa-clock"></i> Active Loans (<?php echo $activeLoans; ?>)
    </button>
    <button class="tab" onclick="switchTab('all-loans')">
        <i class="fas fa-list"></i> All Loans (<?php echo $totalLoans; ?>)
    </button>
    <button class="tab" onclick="switchTab('applications')">
        <i class="fas fa-file-alt"></i> Applications (<?php echo count($applications); ?>)
    </button>
</div>

<!-- Pending Applications Alert -->
<?php if ($pendingApplications > 0): ?>
<div class="alert alert-warning">
    <span class="alert-icon">
        <i class="fas fa-exclamation-triangle"></i>
    </span>
    <span>
        You have <strong><?php echo $pendingApplications; ?></strong> pending loan application(s) awaiting approval.
    </span>
</div>
<?php
endif; ?>

<!-- Active Loans Tab -->
<div id="active-loans" class="tab-content active">
    <?php
$activeLoansFiltered = array_filter($loans, function ($loan) {
    return $loan['loan_status'] == 'Active';
});

if (empty($activeLoansFiltered)):
?>
        <div class="empty-state">
            <i class="fas fa-hand-holding-usd"></i>
            <h3>No Active Loans</h3>
            <p>You don't have any active loans at the moment.</p>
            <a href="loan-application.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Apply for a Loan
            </a>
        </div>
    <?php
else: ?>
        <?php foreach ($activeLoansFiltered as $loan):
        $totalAmount = $loan['total_amount'];
        $paid = $loan['amount_paid'];
        $paidPercentage = $totalAmount > 0 ? ($paid / $totalAmount) * 100 : 0;
?>
            <div class="loan-card loan-card-active">
                <div class="loan-card-header">
                    <div>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <span class="loan-type-badge loan-type-<?php echo strtolower(str_replace(' ', '-', $loan['loan_type'])); ?>">
                                <i class="fas fa-briefcase"></i>
                                <?php echo htmlspecialchars($loan['loan_type']); ?>
                            </span>
                            <span class="badge badge-warning">
                                <i class="fas fa-clock"></i> Active
                            </span>
                        </div>
                        <div class="loan-number-display">
                            <i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($loan['loan_number']); ?>
                        </div>
                    </div>
                    <div class="loan-amount">
                        KES <?php echo number_format($loan['principal_amount'], 2); ?>
                    </div>
                </div>
                
                <div class="loan-details-grid">
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">
                            <i class="fas fa-percentage"></i> Interest Rate
                        </div>
                        <div class="loan-detail-value">
                            <?php echo number_format($loan['interest_rate'], 2); ?>%
                        </div>
                    </div>
                    
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">
                            <i class="fas fa-calendar"></i> Duration
                        </div>
                        <div class="loan-detail-value">
                            <?php echo $loan['repayment_period']; ?> months
                        </div>
                    </div>
                    
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">
                            <i class="fas fa-money-check-alt"></i> Monthly Payment
                        </div>
                        <div class="loan-detail-value">
                            KES <?php echo number_format($loan['monthly_installment'], 2); ?>
                        </div>
                    </div>
                    
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">
                            <i class="fas fa-chart-line"></i> Outstanding
                        </div>
                        <div class="loan-detail-value" style="color: var(--danger);">
                            KES <?php echo number_format($loan['balance'], 2); ?>
                        </div>
                    </div>
                    
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">
                            <i class="fas fa-calendar-check"></i> Disbursed
                        </div>
                        <div class="loan-detail-value">
                            <?php echo date('d M Y', strtotime($loan['disbursement_date'])); ?>
                        </div>
                    </div>
                    
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">
                            <i class="fas fa-calendar-times"></i> Due Date
                        </div>
                        <div class="loan-detail-value">
                            <?php echo date('d M Y', strtotime($loan['maturity_date'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="progress-container">
                    <div class="progress-label">
                        <span><strong>Repayment Progress</strong></span>
                        <span><?php echo number_format($paidPercentage, 1); ?>% Paid</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar <?php echo $paidPercentage < 30 ? 'progress-bar-danger' : ($paidPercentage < 70 ? 'progress-bar-warning' : ''); ?>" 
                             style="width: <?php echo $paidPercentage; ?>%"></div>
                    </div>
                </div>
                
                <div class="loan-actions">
                    <button class="btn btn-sm btn-primary" onclick="viewLoanDetails(<?php echo $loan['loan_id']; ?>)">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                    <button class="btn btn-sm btn-success" onclick="makePayment(<?php echo $loan['loan_id']; ?>)">
                        <i class="fas fa-money-bill"></i> Make Payment
                    </button>
                    <button class="btn btn-sm btn-info" onclick="downloadSchedule(<?php echo $loan['loan_id']; ?>)">
                        <i class="fas fa-download"></i> Payment Schedule
                    </button>
                </div>
                
                <!-- Recent Payments -->
                <?php
        try {
            $sql = "SELECT * FROM loan_repayments 
                            WHERE loan_id = :loan_id 
                            ORDER BY payment_date DESC 
                            LIMIT 3";
            $payments = $db->query($sql)->bind(':loan_id', $loan['loan_id'])->fetchAll();

            if (!empty($payments)):
?>
                    <div class="payment-history">
                        <h4 style="font-size: 0.938rem; color: var(--gray-700); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-history"></i> Recent Payments
                        </h4>
                        <?php foreach ($payments as $payment): ?>
                            <div class="payment-item">
                                <div class="payment-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="payment-info">
                                    <div class="payment-type">
                                        Payment - <?php echo htmlspecialchars($payment['payment_method'] ?? 'Cash'); ?>
                                        <?php if (!empty($payment['reference_number'])): ?>
                                            <span style="color: var(--gray-500); font-weight: 400;"> - Ref: <?php echo htmlspecialchars($payment['reference_number']); ?></span>
                                        <?php
                    endif; ?>
                                    </div>
                                    <div class="payment-date">
                                        <i class="fas fa-calendar"></i> <?php echo date('d M Y, h:i A', strtotime($payment['payment_date'])); ?>
                                    </div>
                                </div>
                                <div class="payment-amount">
                                    KES <?php echo number_format($payment['amount'], 2); ?>
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
    <?php
endif; ?>
</div>

<!-- All Loans Tab -->
<div id="all-loans" class="tab-content">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-item">
            <select class="form-control" id="loanTypeFilter">
                <option value="">All Loan Types</option>
                <option value="Emergency">Emergency</option>
                <option value="Development">Development</option>
                <option value="School Fees">School Fees</option>
                <option value="Business">Business</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control" id="statusFilter">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Fully Paid">Fully Paid</option>
                <option value="Defaulted">Defaulted</option>
            </select>
        </div>
    </div>
    
    <?php if (empty($loans)): ?>
        <div class="empty-state">
            <i class="fas fa-hand-holding-usd"></i>
            <h3>No Loans Yet</h3>
            <p>You haven't taken any loans. Apply for your first loan to get started.</p>
            <a href="loan-application.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Apply for a Loan
            </a>
        </div>
    <?php
else: ?>
        <div id="loansList">
            <?php foreach ($loans as $loan):
        $totalAmount = $loan['total_amount'];
        $paid = $loan['amount_paid'];
        $paidPercentage = $totalAmount > 0 ? ($paid / $totalAmount) * 100 : 0;

        $status = $loan['loan_status'];
        $cardClass = $status == 'Active' ? 'loan-card-active' : ($status == 'Fully Paid' ? 'loan-card-paid' : 'loan-card-defaulted');
?>
                <div class="loan-card <?php echo $cardClass; ?>" data-loan-type="<?php echo htmlspecialchars($loan['loan_type']); ?>" data-status="<?php echo htmlspecialchars($status); ?>">
                    <div class="loan-card-header">
                        <div>
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                <span class="loan-type-badge loan-type-<?php echo strtolower(str_replace(' ', '-', $loan['loan_type'])); ?>">
                                    <i class="fas fa-briefcase"></i>
                                    <?php echo htmlspecialchars($loan['loan_type']); ?>
                                </span>
                                <span class="badge badge-<?php
        echo $status == 'Active' ? 'warning' :
            ($status == 'Fully Paid' ? 'success' : 'danger');
?>">
                                    <i class="fas fa-<?php
        echo $status == 'Active' ? 'clock' :
            ($status == 'Fully Paid' ? 'check-circle' : 'exclamation-circle');
?>"></i>
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </div>
                            <div class="loan-number-display">
                                <i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($loan['loan_number']); ?>
                            </div>
                        </div>
                        <div class="loan-amount">
                            KES <?php echo number_format($loan['principal_amount'], 2); ?>
                        </div>
                    </div>
                    
                    <div class="loan-details-grid">
                        <div class="loan-detail-item">
                            <div class="loan-detail-label">
                                <i class="fas fa-chart-line"></i> Outstanding
                            </div>
                            <div class="loan-detail-value" style="color: <?php echo $loan['balance'] > 0 ? 'var(--danger)' : 'var(--success)'; ?>;">
                                KES <?php echo number_format($loan['balance'], 2); ?>
                            </div>
                        </div>
                        
                        <div class="loan-detail-item">
                            <div class="loan-detail-label">
                                <i class="fas fa-percentage"></i> Interest
                            </div>
                            <div class="loan-detail-value">
                                <?php echo number_format($loan['interest_rate'], 2); ?>%
                            </div>
                        </div>
                        
                        <div class="loan-detail-item">
                            <div class="loan-detail-label">
                                <i class="fas fa-calendar"></i> Duration
                            </div>
                            <div class="loan-detail-value">
                                <?php echo $loan['repayment_period']; ?> months
                            </div>
                        </div>
                        
                        <div class="loan-detail-item">
                            <div class="loan-detail-label">
                                <i class="fas fa-calendar-check"></i> Disbursed
                            </div>
                            <div class="loan-detail-value">
                                <?php echo date('d M Y', strtotime($loan['disbursement_date'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress-container">
                        <div class="progress-label">
                            <span><strong>Repayment Progress</strong></span>
                            <span><?php echo number_format($paidPercentage, 1); ?>% Paid</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar <?php echo $paidPercentage < 30 ? 'progress-bar-danger' : ($paidPercentage < 70 ? 'progress-bar-warning' : ''); ?>" 
                                 style="width: <?php echo $paidPercentage; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="loan-actions">
                        <button class="btn btn-sm btn-primary" onclick="viewLoanDetails(<?php echo $loan['loan_id']; ?>)">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        <?php if ($loan['status'] == 'Active'): ?>
                            <button class="btn btn-sm btn-success" onclick="makePayment(<?php echo $loan['loan_id']; ?>)">
                                <i class="fas fa-money-bill"></i> Make Payment
                            </button>
                        <?php
        endif; ?>
                        <button class="btn btn-sm btn-info" onclick="downloadSchedule(<?php echo $loan['loan_id']; ?>)">
                            <i class="fas fa-download"></i> Payment Schedule
                        </button>
                    </div>
                </div>
            <?php
    endforeach; ?>
        </div>
    <?php
endif; ?>
</div>

<!-- Applications Tab -->
<div id="applications" class="tab-content">
    <?php if (empty($applications)): ?>
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <h3>No Applications</h3>
            <p>You haven't submitted any loan applications yet.</p>
            <a href="loan-application.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Apply for a Loan
            </a>
        </div>
    <?php
else: ?>
        <?php foreach ($applications as $app): ?>
            <div class="application-card">
                <div class="application-header">
                    <div>
                        <h4 style="margin: 0 0 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-file-alt"></i>
                            <?php echo htmlspecialchars($app['loan_type']); ?> Loan Application
                        </h4>
                        <div style="font-size: 0.813rem; color: var(--gray-500);">
                            <i class="fas fa-calendar"></i> Applied on <?php echo date('d M Y', strtotime($app['application_date'])); ?>
                        </div>
                    </div>
                    <span class="badge badge-<?php
        echo $app['status'] == 'Approved' ? 'success' :
            ($app['status'] == 'Rejected' ? 'danger' : 'warning');
?>">
                        <i class="fas fa-<?php
        echo $app['status'] == 'Approved' ? 'check-circle' :
            ($app['status'] == 'Rejected' ? 'times-circle' : 'clock');
?>"></i>
                        <?php echo htmlspecialchars($app['status']); ?>
                    </span>
                </div>
                
                <div class="loan-details-grid">
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">Amount Requested</div>
                        <div class="loan-detail-value">KES <?php echo number_format($app['amount_requested'], 2); ?></div>
                    </div>
                    
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">Period</div>
                        <div class="loan-detail-value"><?php echo $app['repayment_period_months']; ?> months</div>
                    </div>
                    
                    <?php if (!empty($app['approved_amount']) && $app['status'] == 'Approved'): ?>
                        <div class="loan-detail-item">
                            <div class="loan-detail-label">Approved Amount</div>
                            <div class="loan-detail-value" style="color: var(--success);">
                                KES <?php echo number_format($app['approved_amount'], 2); ?>
                            </div>
                        </div>
                    <?php
        endif; ?>
                </div>
                
                <?php if (!empty($app['purpose'])): ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                        <div style="font-size: 0.75rem; color: var(--gray-500); margin-bottom: 0.25rem;">PURPOSE</div>
                        <div style="font-size: 0.875rem; color: var(--gray-700);">
                            <?php echo htmlspecialchars($app['purpose']); ?>
                        </div>
                    </div>
                <?php
        endif; ?>
                
                <?php if (!empty($app['rejection_reason']) && $app['status'] == 'Rejected'): ?>
                    <div class="alert alert-danger" style="margin-top: 1rem; margin-bottom: 0;">
                        <span class="alert-icon"><i class="fas fa-info-circle"></i></span>
                        <span><strong>Rejection Reason:</strong> <?php echo htmlspecialchars($app['rejection_reason']); ?></span>
                    </div>
                <?php
        endif; ?>
            </div>
        <?php
    endforeach; ?>
    <?php
endif; ?>
</div>

<script>
// Tab switching
function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabId).classList.add('active');
    
    // Add active class to clicked tab
    event.target.closest('.tab').classList.add('active');
}

// Filter functionality
document.getElementById('loanTypeFilter')?.addEventListener('change', filterLoans);
document.getElementById('statusFilter')?.addEventListener('change', filterLoans);

function filterLoans() {
    const typeFilter = document.getElementById('loanTypeFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const loans = document.querySelectorAll('#loansList .loan-card');
    
    loans.forEach(loan => {
        const loanType = loan.getAttribute('data-loan-type');
        const loanStatus = loan.getAttribute('data-status');
        
        const typeMatch = !typeFilter || loanType === typeFilter;
        const statusMatch = !statusFilter || loanStatus === statusFilter;
        
        loan.style.display = (typeMatch && statusMatch) ? 'block' : 'none';
    });
}

// Action functions
function viewLoanDetails(loanId) {
    alert('View loan details feature coming soon! Loan ID: ' + loanId);
    // window.location.href = 'loan-details.php?loan_id=' + loanId;
}

function makePayment(loanId) {
    alert('Make payment feature coming soon! Loan ID: ' + loanId);
    // window.location.href = 'make-payment.php?loan_id=' + loanId;
}

function downloadSchedule(loanId) {
    alert('Download schedule feature coming soon! Loan ID: ' + loanId);
    // window.location.href = 'download-schedule.php?loan_id=' + loanId;
}
</script>

<?php include 'views/layouts/footer.php'; ?>