<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'models/Member.php';
require_once 'models/BankAccount.php';

Auth::requireAuth();
Auth::requireRole([4]); // Members only

$pageTitle = 'Link Bank Account';
$db = new Database();
$memberModel = new Member();
$bankAccountModel = new BankAccount();

$userId = Session::getUserId();
$member = $memberModel->getByUserId($userId);
$memberId = $member['member_id'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bankName = trim($_POST['bank_name'] ?? '');
    $accountName = trim($_POST['account_name'] ?? '');
    $accountNumber = trim($_POST['account_number'] ?? '');
    $branchName = trim($_POST['branch_name'] ?? '');
    $swiftCode = trim($_POST['swift_code'] ?? '');

    if (empty($bankName))
        $errors[] = "Bank name is required";
    if (empty($accountName))
        $errors[] = "Account name is required";
    if (empty($accountNumber))
        $errors[] = "Account number is required";

    if (empty($errors)) {
        $data = [
            'member_id' => $memberId,
            'bank_name' => $bankName,
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'branch_name' => $branchName,
            'swift_code' => $swiftCode
        ];

        $result = $bankAccountModel->create($data);
        if ($result['success']) {
            Session::flash('success', 'Bank account linked successfully!');
            redirect('member-dashboard.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}

include 'views/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col col-8">
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;"><i class="fas fa-university"></i> Link Bank Account</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Provide your bank details to link your account to your welfare profile. This
                    will be used for disbursements and transfers.</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            <?php foreach ($errors as $error): ?>
                                <li>
                                    <?php echo htmlspecialchars($error); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="row">
                        <div class="col col-12">
                            <div class="form-group">
                                <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" name="bank_name" class="form-control"
                                    placeholder="e.g. KCB Bank, Equity Bank"
                                    value="<?php echo htmlspecialchars($_POST['bank_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-12">
                            <div class="form-group">
                                <label class="form-label">Account Name <span class="text-danger">*</span></label>
                                <input type="text" name="account_name" class="form-control"
                                    placeholder="Name as it appears on your bank statement"
                                    value="<?php echo htmlspecialchars($_POST['account_name'] ?? ''); ?>" required>
                                <small class="text-muted">Ensure this matches your registered member name to avoid
                                    delays in verification.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-12">
                            <div class="form-group">
                                <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" name="account_number" class="form-control"
                                    placeholder="Your bank account number"
                                    value="<?php echo htmlspecialchars($_POST['account_number'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">Branch Name</label>
                                <input type="text" name="branch_name" class="form-control"
                                    placeholder="e.g. Westlands Branch"
                                    value="<?php echo htmlspecialchars($_POST['branch_name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col col-6">
                            <div class="form-group">
                                <label class="form-label">SWIFT / BIC Code</label>
                                <input type="text" name="swift_code" class="form-control" placeholder="Optional"
                                    value="<?php echo htmlspecialchars($_POST['swift_code'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions"
                        style="margin-top: 2rem; border-top: 1px solid var(--gray-200); padding-top: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end;">
                        <a href="member-dashboard.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-link"></i> Link Bank Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>