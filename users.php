<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';
require_once 'includes/helpers.php';

Auth::requireAuth();
Auth::requireRole([1]); // Super Admin only

$pageTitle = 'User Management';
$db = new Database();

// Get all users with role information
try {
    $sql = "SELECT u.*, r.role_name,
                   CASE 
                       WHEN m.member_id IS NOT NULL THEN CONCAT(m.first_name, ' ', m.last_name)
                       ELSE 'N/A'
                   END as full_name
            FROM users u
            INNER JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN members m ON u.user_id = m.user_id
            ORDER BY u.created_at DESC";
    $users = $db->getConnection()->query($sql)->fetchAll();
}
catch (Exception $e) {
    $users = [];
    Session::flash('error', 'Error loading users: ' . $e->getMessage());
}

// Get statistics
try {
    $sql = "SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN role_id = 4 THEN 1 ELSE 0 END) as member_users,
                SUM(CASE WHEN role_id <= 3 THEN 1 ELSE 0 END) as admin_users
            FROM users";
    $stats = $db->getConnection()->query($sql)->fetch();
}
catch (Exception $e) {
    $stats = [
        'total_users' => 0,
        'active_users' => 0,
        'member_users' => 0,
        'admin_users' => 0
    ];
}

include 'views/layouts/header.php';
?>

<div class="row" style="margin-bottom: 2rem;">
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;">
                    <i class="fas fa-users"></i>
                </div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($stats['total_users']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Total Users</p>
            </div>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--success); margin-bottom: 0.5rem;">
                    <i class="fas fa-user-check"></i>
                </div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($stats['active_users']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Active Users</p>
            </div>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--info); margin-bottom: 0.5rem;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($stats['member_users']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Members</p>
            </div>
        </div>
    </div>
    
    <div class="col col-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 2rem; color: var(--warning); margin-bottom: 0.5rem;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 style="margin: 0; font-size: 1.75rem;"><?php echo number_format($stats['admin_users']); ?></h3>
                <p style="margin: 0.5rem 0 0; color: var(--gray-600);">Administrators</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">All Users</h3>
    </div>
    
    <div class="card-body">
        <?php if (!empty($users)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td style="font-family: var(--font-mono); font-weight: 600;">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role_id'] <= 3 ? 'warning' : 'primary'; ?>">
                                        <?php echo htmlspecialchars($user['role_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php
        else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php
        endif; ?>
                                </td>
                                <td>
                                    <?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                            </tr>
                        <?php
    endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php
else: ?>
            <div style="text-align: center; padding: 3rem; color: var(--gray-500);">
                <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.5; margin-bottom: 1rem;"></i>
                <p>No users found</p>
            </div>
        <?php
endif; ?>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
