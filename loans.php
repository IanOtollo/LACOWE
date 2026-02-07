<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Session.php';
require_once 'includes/helpers.php';
require_once 'models/Loan.php';

Auth::requireRole([1, 2, 3]);

$loanModel = new Loan();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $applicationId = (int)$_POST['application_id'];
    $userId = Session::getUserId();
    
    if ($_POST['action'] === 'approve') {
        $result = $loanModel->approveApplication($applicationId, $userId, $_POST['comments'] ?? '');
    } else {
        $result = $loanModel->rejectApplication($applicationId, $userId, $_POST['comments'] ?? '');
    }
    
    Session::flash('success', $result['message'], $result['success'] ? 'success' : 'danger');
    redirect('loans.php');
}

// Get filters
$filters = [];
if (!empty($_GET['status'])) {
    $filters['status'] = sanitize($_GET['status']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$applications = $loanModel->getAllApplications($filters, RECORDS_PER_PAGE, ($page - 1) * RECORDS_PER_PAGE);

$pageTitle = 'Loan Applications';
include 'views/layouts/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 style="margin: 0;">Loan Applications</h3>
        <div class="d-flex gap-2">
            <a href="?status=Pending" class="btn btn-sm btn-outline <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'btn-primary' : ''; ?>">Pending</a>
            <a href="?status=Approved" class="btn btn-sm btn-outline <?php echo (isset($_GET['status']) && $_GET['status'] == 'Approved') ? 'btn-success' : ''; ?>">Approved</a>
            <a href="?status=Rejected" class="btn btn-sm btn-outline <?php echo (isset($_GET['status']) && $_GET['status'] == 'Rejected') ? 'btn-danger' : ''; ?>">Rejected</a>
            <a href="loans.php" class="btn btn-sm btn-outline">All</a>
        </div>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Loan Type</th>
                        <th>Amount</th>
                        <th>Period</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($applications)): ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?php echo $app['application_id']; ?></td>
                                <td><?php echo htmlspecialchars($app['member_name']); ?><br>
                                    <small><?php echo $app['member_number']; ?></small></td>
                                <td><?php echo $app['loan_type']; ?></td>
                                <td><?php echo formatCurrency($app['amount_requested']); ?></td>
                                <td><?php echo $app['repayment_period']; ?> months</td>
                                <td><?php echo formatDate($app['application_date']); ?></td>
                                <td><?php echo getStatusBadge($app['application_status']); ?></td>
                                <td>
                                    <?php if ($app['application_status'] == 'Pending'): ?>
                                        <button onclick="showApprovalModal(<?php echo $app['application_id']; ?>, 'approve')" 
                                                class="btn btn-sm btn-success">Approve</button>
                                        <button onclick="showApprovalModal(<?php echo $app['application_id']; ?>, 'reject')" 
                                                class="btn btn-sm btn-danger">Reject</button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-info" onclick="viewApplication(<?php echo $app['application_id']; ?>)">View</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 2rem; color: var(--gray-500);">No loan applications found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; width: 90%; margin: 0;">
        <div class="card-header">
            <h3 id="modalTitle" style="margin: 0;">Approve/Reject Loan</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="application_id" id="modalApplicationId">
                <input type="hidden" name="action" id="modalAction">
                
                <div class="form-group">
                    <label class="form-label">Comments</label>
                    <textarea name="comments" class="form-control" rows="4"></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" onclick="closeModal()" class="btn btn-outline">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showApprovalModal(applicationId, action) {
    document.getElementById('modalApplicationId').value = applicationId;
    document.getElementById('modalAction').value = action;
    document.getElementById('modalTitle').textContent = action === 'approve' ? 'Approve Loan Application' : 'Reject Loan Application';
    document.getElementById('approvalModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('approvalModal').style.display = 'none';
}

function viewApplication(applicationId) {
    window.location.href = 'loan-view.php?id=' + applicationId;
}
</script>

<?php include 'views/layouts/footer.php'; ?>
