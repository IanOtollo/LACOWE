<?php
require_once 'includes/Auth.php';

Auth::requireAuth();
Auth::requireRole([4]); // Members only

$pageTitle = 'Apply for Loan';

// Initialize Models
require_once 'models/Member.php';
require_once 'models/Loan.php';

$memberModel = new Member();
$loanModel = new Loan();

// Get member details
$userId = Session::getUserId();
$member = $memberModel->getByUserId($userId);

if (!$member) {
    Session::flash('error', 'Member record not found');
    redirect('logout.php');
}

$memberId = $member['member_id'];
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanType = sanitize($_POST['loan_type'] ?? '');
    $amount = sanitize($_POST['amount'] ?? '');
    $purpose = sanitize($_POST['purpose'] ?? '');
    $repaymentPeriod = sanitize($_POST['repayment_period'] ?? '');

    // Validation
    if (empty($loanType)) {
        $errors[] = 'Please select a loan type';
    }

    if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $errors[] = 'Please enter a valid loan amount';
    }

    if (empty($purpose)) {
        $errors[] = 'Please provide the purpose of the loan';
    }

    if (empty($repaymentPeriod) || !is_numeric($repaymentPeriod) || $repaymentPeriod <= 0) {
        $errors[] = 'Please enter a valid repayment period';
    }

    if (empty($errors)) {
        try {
            $interestRate = 10; // Default 10% interest rate

            $result = $loanModel->createApplication([
                'member_id' => $memberId,
                'loan_type' => $loanType,
                'amount' => $amount,
                'purpose' => $purpose,
                'repayment_period' => $repaymentPeriod,
                'interest_rate' => $interestRate,
                'status' => 'Pending'
            ]);

            if ($result) {
                Session::flash('success', 'Loan application submitted successfully! You will be notified once it is reviewed.');
                redirect('my-loans.php');
            }
            else {
                $errors[] = 'Failed to submit loan application. Please try again.';
            }
        }
        catch (Exception $e) {
            $errors[] = 'An error occurred: ' . $e->getMessage();
        }
    }
}

include 'views/layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Loan Application Form</h3>
    </div>
    
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php
    endforeach; ?>
                </div>
            </div>
        <?php
endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php
endif; ?>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col col-6">
                    <div class="form-group">
                        <label class="form-label required">Loan Type</label>
                        <select name="loan_type" class="form-control" required>
                            <option value="">Select Loan Type</option>
                            <option value="Emergency" <?php echo(isset($_POST['loan_type']) && $_POST['loan_type'] == 'Emergency') ? 'selected' : ''; ?>>Emergency Loan</option>
                            <option value="Development" <?php echo(isset($_POST['loan_type']) && $_POST['loan_type'] == 'Development') ? 'selected' : ''; ?>>Development Loan</option>
                            <option value="School Fees" <?php echo(isset($_POST['loan_type']) && $_POST['loan_type'] == 'School Fees') ? 'selected' : ''; ?>>School Fees Loan</option>
                            <option value="Business" <?php echo(isset($_POST['loan_type']) && $_POST['loan_type'] == 'Business') ? 'selected' : ''; ?>>Business Loan</option>
                            <option value="Personal" <?php echo(isset($_POST['loan_type']) && $_POST['loan_type'] == 'Personal') ? 'selected' : ''; ?>>Personal Loan</option>
                        </select>
                    </div>
                </div>
                
                <div class="col col-6">
                    <div class="form-group">
                        <label class="form-label required">Loan Amount (KES)</label>
                        <input type="number" name="amount" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>" 
                               placeholder="Enter amount" 
                               min="1000" 
                               step="100" 
                               required>
                        <small class="form-text">Minimum: KES 1,000</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col col-6">
                    <div class="form-group">
                        <label class="form-label required">Repayment Period (Months)</label>
                        <input type="number" name="repayment_period" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['repayment_period'] ?? ''); ?>" 
                               placeholder="Enter period in months" 
                               min="1" 
                               max="60" 
                               required>
                        <small class="form-text">Maximum: 60 months (5 years)</small>
                    </div>
                </div>
                
                <div class="col col-6">
                    <div class="form-group">
                        <label class="form-label">Interest Rate</label>
                        <input type="text" class="form-control" value="10% per annum" readonly disabled>
                        <small class="form-text">Standard interest rate for all loans</small>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label required">Purpose of Loan</label>
                <textarea name="purpose" class="form-control" rows="4" 
                          placeholder="Please describe the purpose of this loan..." 
                          required><?php echo htmlspecialchars($_POST['purpose'] ?? ''); ?></textarea>
                <small class="form-text">Provide detailed information about how you plan to use this loan</small>
            </div>
            
            <div class="alert alert-info">
                <span class="alert-icon"><i class="fas fa-info-circle"></i></span>
                <div>
                    <strong>Important Information:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        <li>Your application will be reviewed by the management committee</li>
                        <li>Approval typically takes 3-5 business days</li>
                        <li>You will be notified via SMS/Email once your application is processed</li>
                        <li>Ensure you have sufficient shares to qualify for the loan</li>
                    </ul>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="my-loans.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
