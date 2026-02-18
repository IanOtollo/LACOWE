<?php
if (!defined('APP_NAME'))
    die('Direct access not allowed');
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$userName = Session::getUserName();
$roleName = Session::get('role_name');
$roleId = Session::getUserRole();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1e40af">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="LACOWE">
    <link rel="apple-touch-icon" href="assets/images/icon-192.png">

    <!-- Google Fonts: Inter for Professional UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-icon-container">
                    <i class="fas fa-university"></i>
                </div>
                <div class="logo-text">
                    <div class="logo-title">LACOWE</div>
                    <div class="logo-subtitle">Welfare MIS</div>
                </div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="<?php echo $roleId <= 3 ? 'dashboard.php' : 'member-dashboard.php'; ?>"
                    class="sidebar-menu-link <?php echo in_array($currentPage, ['dashboard', 'member-dashboard']) ? 'active' : ''; ?>">
                    <span><i class="fas fa-chart-line"></i></span>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($roleId <= 3): ?>
                <li class="sidebar-menu-item">
                    <a href="members.php"
                        class="sidebar-menu-link <?php echo $currentPage == 'members' ? 'active' : ''; ?>">
                        <span><i class="fas fa-users"></i></span>
                        <span>Members</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="accounts.php"
                        class="sidebar-menu-link <?php echo $currentPage == 'accounts' ? 'active' : ''; ?>">
                        <span><i class="fas fa-wallet"></i></span>
                        <span>Accounts</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="loans.php" class="sidebar-menu-link <?php echo $currentPage == 'loans' ? 'active' : ''; ?>">
                        <span><i class="fas fa-hand-holding-usd"></i></span>
                        <span>Loans</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="transactions.php"
                        class="sidebar-menu-link <?php echo $currentPage == 'transactions' ? 'active' : ''; ?>">
                        <span><i class="fas fa-exchange-alt"></i></span>
                        <span>Transactions</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="reports.php"
                        class="sidebar-menu-link <?php echo $currentPage == 'reports' ? 'active' : ''; ?>">
                        <span><i class="fas fa-chart-bar"></i></span>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="admin-bank-accounts.php"
                        class="sidebar-menu-link <?php echo $currentPage == 'admin-bank-accounts' ? 'active' : ''; ?>">
                        <span><i class="fas fa-university"></i></span>
                        <span>Bank Accounts</span>
                    </a>
                </li>
                <?php
            else: ?>
                <li class="sidebar-menu-item">
                    <a href="my-accounts.php"
                        class="sidebar-menu-link <?php echo $currentPage == 'my-accounts' ? 'active' : ''; ?>">
                        <span><i class="fas fa-wallet"></i></span>
                        <span>My Accounts</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="my-loans.php"
                        class="sidebar-menu-link <?php echo $currentPage == 'my-loans' ? 'active' : ''; ?>">
                        <span><i class="fas fa-money-check-alt"></i></span>
                        <span>My Loans</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="loan-application.php"
                        class="sidebar-menu-link <?php echo $currentPage == 'loan-application' ? 'active' : ''; ?>">
                        <span><i class="fas fa-file-alt"></i></span>
                        <span>Apply for Loan</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="my-transactions.php"
                        class="sidebar-menu-link <?php echo $currentPage == 'my-transactions' ? 'active' : ''; ?>">
                        <span><i class="fas fa-receipt"></i></span>
                        <span>Transactions</span>
                    </a>
                </li>
                <?php
            endif; ?>

            <?php if ($roleId <= 2): ?>
                <li class="sidebar-menu-item">
                    <a href="users.php" class="sidebar-menu-link <?php echo $currentPage == 'users' ? 'active' : ''; ?>">
                        <span><i class="fas fa-user-cog"></i></span>
                        <span>Users</span>
                    </a>
                </li>
                <?php
            endif; ?>

            <li class="sidebar-menu-item">
                <a href="profile.php"
                    class="sidebar-menu-link <?php echo $currentPage == 'profile' ? 'active' : ''; ?>">
                    <span><i class="fas fa-user"></i></span>
                    <span>Profile</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="logout.php" class="sidebar-menu-link"
                    onclick="return confirm('Are you sure you want to logout?');">
                    <span><i class="fas fa-sign-out-alt"></i></span>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="navbar">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button class="mobile-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                </div>
                <div class="user-info" style="display: flex; align-items: center; gap: 0.625rem;">
                    <i class="fas fa-user-circle" style="color: var(--primary); font-size: 1.5rem;"></i>
                    <div style="text-align: right; line-height: 1.3;">
                        <div style="font-weight: 600; color: var(--gray-800); font-size: 0.875rem;">
                            <?php echo htmlspecialchars($userName); ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--gray-500);">
                            <?php echo htmlspecialchars($roleName); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $flash = Session::getFlash('success');
        if ($flash):
            ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <span class="alert-icon">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
                </span>
                <span><?php echo htmlspecialchars($flash['message']); ?></span>
            </div>
            <?php
        endif; ?>

        <?php
        $errorFlash = Session::getFlash('error');
        if ($errorFlash):
            ?>
            <div class="alert alert-danger">
                <span class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </span>
                <span><?php echo htmlspecialchars($errorFlash['message']); ?></span>
            </div>
            <?php
        endif; ?>