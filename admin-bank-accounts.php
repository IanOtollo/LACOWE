<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'models/BankAccount.php';

Auth::requireAuth();
Auth::requireRole([1, 2]); // Admin and Super Admin

$pageTitle = 'Member Bank Accounts';
$bankAccountModel = new BankAccount();

// Handle status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'verify') {
        $bankAccountModel->updateStatus($id, 1);
        Session::flash('success', 'Bank account verified successfully.');
    } elseif ($action === 'unverify') {
        $bankAccountModel->updateStatus($id, 0);
        Session::flash('info', 'Bank account status set to unverified.');
    } elseif ($action === 'delete') {
        $bankAccountModel->delete($id);
        Session::flash('success', 'Bank account link removed.');
    }
    redirect('admin-bank-accounts.php');
}

$allBankAccounts = $bankAccountModel->getAll();

include 'views/layouts/header.php';
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;"><i class="fas fa-university"></i> Linked Bank Accounts</h3>
    </div>
    <div class="card-body">
        <?php if (empty($allBankAccounts)): ?>
            <div class="empty-state">
                <i class="fas fa-university"></i>
                <p><strong>No bank accounts linked yet</strong></p>
                <p>Linked member bank accounts will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Bank Name</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allBankAccounts as $ba): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($ba['member_name']); ?>
                                    </strong><br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($ba['member_number']); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($ba['bank_name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($ba['account_name']); ?>
                                </td>
                                <td><code><?php echo htmlspecialchars($ba['account_number']); ?></code></td>
                                <td>
                                    <?php echo htmlspecialchars($ba['branch_name'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $ba['is_verified'] ? 'success' : 'warning'; ?>">
                                        <?php echo $ba['is_verified'] ? 'Verified' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <?php if (!$ba['is_verified']): ?>
                                            <a href="?action=verify&id=<?php echo $ba['bank_account_id']; ?>"
                                                class="btn btn-sm btn-success" title="Mark as Verified">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=unverify&id=<?php echo $ba['bank_account_id']; ?>"
                                                class="btn btn-sm btn-warning" title="Mark as Unverified">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $ba['bank_account_id']; ?>"
                                            class="btn btn-sm btn-danger" title="Delete Link"
                                            onclick="return confirm('Are you sure you want to remove this bank account link?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>