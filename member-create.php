<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Session.php';
require_once 'includes/helpers.php';
require_once 'models/Member.php';

Auth::requireRole([1, 2, 3]);

$memberModel = new Member();
$auth = new Auth();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required = ['first_name', 'last_name', 'id_number', 'phone_number', 'username', 'email', 'password'];
    $errors = validateRequired($required, $_POST);
    
    if (empty($errors)) {
        // Check if ID number exists
        if ($memberModel->idNumberExists($_POST['id_number'])) {
            $errors[] = 'ID number already exists';
        }
        
        // Validate phone number
        if (!isValidPhone($_POST['phone_number'])) {
            $errors[] = 'Invalid phone number format';
        }
        
        // Validate email
        if (!isValidEmail($_POST['email'])) {
            $errors[] = 'Invalid email format';
        }
        
        if (empty($errors)) {
            try {
                // Create user first
                $userResult = $auth->createUser(
                    sanitize($_POST['username']),
                    sanitize($_POST['email']),
                    $_POST['password'],
                    4 // Member role
                );
                
                if ($userResult['success']) {
                    // Create member
                    $memberData = [
                        'user_id' => $userResult['user_id'],
                        'first_name' => sanitize($_POST['first_name']),
                        'last_name' => sanitize($_POST['last_name']),
                        'id_number' => sanitize($_POST['id_number']),
                        'phone_number' => sanitize($_POST['phone_number']),
                        'email' => sanitize($_POST['email']),
                        'date_of_birth' => $_POST['date_of_birth'] ?? null,
                        'gender' => $_POST['gender'] ?? null,
                        'address' => sanitize($_POST['address'] ?? ''),
                        'city' => sanitize($_POST['city'] ?? ''),
                        'postal_code' => sanitize($_POST['postal_code'] ?? ''),
                        'employment_status' => 'Active',
                        'department' => sanitize($_POST['department'] ?? ''),
                        'payroll_number' => sanitize($_POST['payroll_number'] ?? ''),
                        'date_joined' => date('Y-m-d')
                    ];
                    
                    $result = $memberModel->create($memberData);
                    
                    if ($result['success']) {
                        Session::flash('success', 'Member registered successfully!', 'success');
                        redirect('members.php');
                    } else {
                        $errors[] = $result['message'];
                    }
                } else {
                    $errors[] = $userResult['message'];
                }
            } catch (Exception $e) {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register New Member';
include 'views/layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Member Registration Form</h3>
    </div>
    
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <span class="alert-icon">✕</span>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <h4>Personal Information</h4>
            <div class="row">
                <div class="col" style="flex: 0 0 calc(50% - 0.5rem); margin-right: 1rem;">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col" style="flex: 0 0 calc(50% - 0.5rem);">
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col" style="flex: 0 0 calc(50% - 0.5rem); margin-right: 1rem;">
                    <div class="form-group">
                        <label class="form-label">ID/Passport Number *</label>
                        <input type="text" name="id_number" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['id_number'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col" style="flex: 0 0 calc(50% - 0.5rem);">
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="phone_number" class="form-control" required 
                               placeholder="+254..." value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col" style="flex: 0 0 calc(33.333% - 0.66rem); margin-right: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col" style="flex: 0 0 calc(33.333% - 0.66rem); margin-right: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="col" style="flex: 0 0 calc(33.333% - 0.66rem);">
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>
            
            <h4 class="mt-4">Login Credentials</h4>
            <div class="row">
                <div class="col" style="flex: 0 0 calc(33.333% - 0.66rem); margin-right: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col" style="flex: 0 0 calc(33.333% - 0.66rem); margin-right: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col" style="flex: 0 0 calc(33.333% - 0.66rem);">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                        <span class="form-text">Minimum 8 characters</span>
                    </div>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary btn-lg">Register Member</button>
                <a href="members.php" class="btn btn-outline btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
