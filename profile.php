<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'includes/helpers.php';

Auth::requireAuth();

$pageTitle = 'My Profile';
$db = new Database();

$userId = Session::getUserId();
$roleId = Session::getUserRole();

// Initialize Models
require_once 'models/Member.php';
require_once 'models/User.php';

$memberModel = new Member();
$userModel = new User();
$auth = new Auth(); // For password change

$userId = Session::getUserId();
$roleId = Session::getUserRole();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        try {
            $db->beginTransaction();

            $email = $_POST['email'];

            // For members, update members table
            if ($roleId == 4) {
                // Get current member data to preserve other fields if needed, 
                // but we only update specific fields here.
                // However, Member::update requires all fields? 
                // Let's check Member::update signature.
                // It takes $data array and binds :first_name, :last_name, etc.
                // The form only submits phone, email, address, postal.
                // We need to fetch existing data to merge or create a partial update method.
                // Creating a specific updateContactInfo method in Member model would be cleaner,
                // but simpler here effectively is to fetch current, merge, and update.

                $currentMember = $memberModel->getByUserId($userId);

                $updateData = [
                    'first_name' => $currentMember['first_name'],
                    'last_name' => $currentMember['last_name'],
                    'phone_number' => $_POST['phone_number'],
                    'email' => $email,
                    'date_of_birth' => $currentMember['date_of_birth'],
                    'gender' => $currentMember['gender'],
                    'address' => $_POST['physical_address'],
                    'city' => $currentMember['city'],
                    'postal_code' => $_POST['postal_address'], // Assuming postal_address maps to postal_code
                    'employment_status' => $currentMember['employment_status'],
                    'department' => $currentMember['department'],
                    'payroll_number' => $currentMember['payroll_number'],
                    'membership_status' => $currentMember['membership_status']
                ];

                $memberResult = $memberModel->update($currentMember['member_id'], $updateData);
                if (!$memberResult['success']) {
                    throw new Exception($memberResult['message']);
                }
            }

            // Update users table email
            $userResult = $userModel->updateEmail($userId, $email);
            if (!$userResult['success']) {
                throw new Exception($userResult['message']);
            }

            $db->commit();
            Session::flash('success', 'Profile updated successfully!', 'success');
            redirect('profile.php');

        }
        catch (Exception $e) {
            $db->rollback();
            Session::flash('error', 'Error updating profile: ' . $e->getMessage(), 'danger');
        }
    }
    elseif ($action === 'change_password') {
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New passwords do not match', 'danger');
        }
        else {
            $result = $auth->changePassword($userId, $oldPassword, $newPassword);

            if ($result['success']) {
                Session::flash('success', 'Password changed successfully!', 'success');
                redirect('profile.php');
            }
            else {
                Session::flash('error', $result['message'], 'danger');
            }
        }
    }
}

// Get user data
try {
    if ($roleId == 4) {
        // Member profile
        $profile = $memberModel->getByUserId($userId);
    }
    else {
        // Admin/Staff profile
        $profile = $userModel->getById($userId);
    }

    if (!$profile) {
        Session::flash('error', 'Profile not found');
        redirect('logout.php');
    }

}
catch (Exception $e) {
    Session::flash('error', 'Error loading profile');
    $profile = [];
}

include 'views/layouts/header.php';
?>

<style>
.profile-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary);
    margin-right: 2rem;
}

.profile-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.profile-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.profile-section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--gray-200);
}

.profile-section-header i {
    color: var(--primary);
    font-size: 1.5rem;
}

.profile-section-header h3 {
    margin: 0;
    font-size: 1.25rem;
}

.info-grid {
    display: grid;
    gap: 1.25rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-label {
    font-size: 0.75rem;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.info-value {
    font-size: 1rem;
    color: var(--gray-800);
    font-weight: 600;
}

@media (max-width: 992px) {
    .profile-content {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Profile Header -->
<div class="profile-header">
    <div style="display: flex; align-items: center;">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($profile['first_name'] ?? $profile['username'], 0, 1)); ?>
        </div>
        <div>
            <h2 style="margin: 0 0 0.5rem; color: white;">
                <?php
if ($roleId == 4) {
    echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']);
}
else {
    echo htmlspecialchars($profile['username']);
}
?>
            </h2>
            <p style="margin: 0; opacity: 0.9; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <?php if ($roleId == 4): ?>
                    <span><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($profile['member_number']); ?></span>
                <?php
endif; ?>
                <span><i class="fas fa-user-tag"></i> <?php echo htmlspecialchars($profile['role_name'] ?? 'Member'); ?></span>
                <?php if (!empty($profile['last_login'])): ?>
                    <span><i class="fas fa-clock"></i> Last login: <?php echo date('d M Y, h:i A', strtotime($profile['last_login'])); ?></span>
                <?php
endif; ?>
            </p>
        </div>
    </div>
</div>

<div class="profile-content">
    <!-- Personal Information -->
    <div class="profile-section">
        <div class="profile-section-header">
            <i class="fas fa-user"></i>
            <h3>Personal Information</h3>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_profile">
            
            <?php if ($roleId == 4): ?>
                <div class="info-grid">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($profile['first_name']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($profile['last_name']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">ID/Passport Number</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($profile['id_number']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($profile['phone_number']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="text" class="form-control" value="<?php echo date('d M Y', strtotime($profile['date_of_birth'])); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($profile['gender']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Physical Address</label>
                        <textarea name="physical_address" class="form-control" rows="2"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Postal Address</label>
                        <input type="text" name="postal_address" class="form-control" value="<?php echo htmlspecialchars($profile['postal_code'] ?? ''); ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            <?php
else: ?>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['username']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['email']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Role</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['role_name']); ?></div>
                    </div>
                </div>
            <?php
endif; ?>
        </form>
    </div>
    
    <!-- Employment Information (Members Only) -->
    <?php if ($roleId == 4): ?>
    <div class="profile-section">
        <div class="profile-section-header">
            <i class="fas fa-briefcase"></i>
            <h3>Employment Information</h3>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label"><i class="fas fa-building"></i> Department</div>
                <div class="info-value"><?php echo htmlspecialchars($profile['department'] ?? 'Not specified'); ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label"><i class="fas fa-user-tie"></i> Job Title</div>
                <div class="info-value"><?php echo htmlspecialchars($profile['job_title'] ?? 'Not specified'); ?></div>
            </div>
            
            <?php if (!empty($profile['employee_number'])): ?>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-id-badge"></i> Employee Number</div>
                <div class="info-value"><?php echo htmlspecialchars($profile['employee_number']); ?></div>
            </div>
            <?php
    endif; ?>
            
            <?php if (!empty($profile['employment_date'])): ?>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-calendar-check"></i> Employment Date</div>
                <div class="info-value"><?php echo date('d M Y', strtotime($profile['employment_date'])); ?></div>
            </div>
            <?php
    endif; ?>
        </div>
    </div>
    <?php
endif; ?>
    
    <!-- Security Settings -->
    <div class="profile-section">
        <div class="profile-section-header">
            <i class="fas fa-lock"></i>
            <h3>Security Settings</h3>
        </div>
        
        <form method="POST" action="" onsubmit="return validatePasswordForm()">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label class="form-label required">Current Password</label>
                <input type="password" name="old_password" id="old_password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label required">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
                <small class="form-text">Minimum 8 characters</small>
            </div>
            
            <div class="form-group">
                <label class="form-label required">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-key"></i> Change Password
            </button>
        </form>
    </div>
    
    <!-- Account Status -->
    <div class="profile-section">
        <div class="profile-section-header">
            <i class="fas fa-info-circle"></i>
            <h3>Account Information</h3>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Account Status</div>
                <div class="info-value">
                    <span class="badge badge-success">
                        <i class="fas fa-check-circle"></i> Active
                    </span>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Member Since</div>
                <div class="info-value"><?php echo date('d M Y', strtotime($profile['created_at'])); ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Username</div>
                <div class="info-value"><?php echo htmlspecialchars($profile['username']); ?></div>
            </div>
            
            <?php if (!empty($profile['last_login'])): ?>
            <div class="info-item">
                <div class="info-label">Last Login</div>
                <div class="info-value"><?php echo date('d M Y, h:i A', strtotime($profile['last_login'])); ?></div>
            </div>
            <?php
endif; ?>
        </div>
    </div>
</div>

<script>
function validatePasswordForm() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword.length < 8) {
        alert('New password must be at least 8 characters long');
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match');
        return false;
    }
    
    return true;
}
</script>

<?php include 'views/layouts/footer.php'; ?>