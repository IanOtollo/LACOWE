<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Session.php';
require_once 'includes/helpers.php';
require_once 'models/Member.php';

Auth::requireRole([1, 2, 3]); // Admin only

$memberModel = new Member();

// Handle search and filters
$filters = [];
if (!empty($_GET['search'])) {
    $filters['search'] = sanitize($_GET['search']);
}
if (!empty($_GET['status'])) {
    $filters['membership_status'] = sanitize($_GET['status']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalRecords = $memberModel->count($filters);
$pagination = paginate($page, $totalRecords);

// Get members
$members = $memberModel->getAll($filters, $pagination['records_per_page'], $pagination['offset']);

$pageTitle = 'Members Management';
include 'views/layouts/header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 style="margin: 0;">Member Management</h3>
        <a href="member-create.php" class="btn btn-primary">+ Register New Member</a>
    </div>
    
    <div class="card-body">
        <!-- Search and Filter Form -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col" style="flex: 0 0 calc(50% - 0.5rem); margin-right: 1rem;">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name, member number, or ID number..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col" style="flex: 0 0 calc(25% - 0.5rem); margin-right: 1rem;">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="Active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Suspended" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                        <option value="Inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col" style="flex: 0 0 calc(25% - 0.5rem);">
                    <button type="submit" class="btn btn-primary" style="margin-right: 0.5rem;">Search</button>
                    <a href="members.php" class="btn btn-outline">Clear</a>
                </div>
            </div>
        </form>
        
        <!-- Members Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member No.</th>
                        <th>Name</th>
                        <th>ID Number</th>
                        <th>Phone</th>
                        <th>Total Balance</th>
                        <th>Status</th>
                        <th>Date Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($members)): ?>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['member_number']); ?></td>
                                <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($member['id_number']); ?></td>
                                <td><?php echo htmlspecialchars($member['phone_number']); ?></td>
                                <td><?php echo formatCurrency($member['total_balance'] ?? 0); ?></td>
                                <td><?php echo getStatusBadge($member['membership_status']); ?></td>
                                <td><?php echo formatDate($member['date_joined']); ?></td>
                                <td>
                                    <a href="member-view.php?id=<?php echo $member['member_id']; ?>" class="btn btn-sm btn-info">View</a>
                                    <a href="member-edit.php?id=<?php echo $member['member_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 2rem; color: var(--gray-500);">No members found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php echo paginationHTML($pagination, 'members.php'); ?>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
