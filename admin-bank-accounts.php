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
        $bankAccountModel->updateStatus($id, true);
        Session::flash('success', 'Bank account verified successfully.');
    } elseif ($action === 'unverify') {
        $bankAccountModel->updateStatus($id, false);
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
            <div class="empty-state" style="padding: 3rem 1.5rem;">
                <div class="logo-icon-container" style="margin: 0 auto 1.5rem; width: 64px; height: 64px; font-size: 2rem;">
                    <i class="fas fa-university"></i>
                </div>
                <h3 style="margin-bottom: 0.5rem;">Bank Integration Active</h3>
                <p style="color: var(--gray-500); max-width: 500px; margin: 0 auto 2rem;">
                    The banking plugin is successfully initialized. Linked member accounts will appear here for
                    verification.
                </p>

                <div style="text-align: left; max-width: 800px; margin: 0 auto;">
                    <h5
                        style="color: var(--gray-700); border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-plug"></i> Supported Gateway Integrations
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
                        <div class="bank-provider-card">
                            <i class="fas fa-credit-card bank-provider-icon" style="color: #FF5F00;"></i>
                            <div class="bank-provider-name">Mastercard</div>
                        </div>
                        <div class="bank-provider-card" style="opacity: 0.6; cursor: default;">
                            <i class="fas fa-plus bank-provider-icon"></i>
                            <div class="bank-provider-name">Add More...</div>
                        </div>
                    </div>
                </div>
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