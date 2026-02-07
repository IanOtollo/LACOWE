<?php
if (!defined('APP_NAME')) die('Direct access not allowed');
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$userName = Session::getUserName();
$roleName = Session::get('role_name');
$roleId = Session::getUserRole();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#1e40af">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="LACOWE MIS">
    <meta name="description" content="Welfare Management System for LACOWE - JKUAT">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="assets/images/icon-192.png">
    <link rel="apple-touch-icon" href="assets/images/icon-192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/images/icon-152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/icon-192.png">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/mobile.css">
</head>
<body>
    <!-- Offline Indicator -->
    <div class="offline-indicator" id="offlineIndicator">
        📡 You're offline. Some features may be limited.
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" style="display: none;">
        ☰
    </button>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 style="margin: 0; color: white; font-size: 1.25rem;">LACOWE MIS</h2>
            <p style="margin: 0.5rem 0 0; font-size: 0.813rem; opacity: 0.9;">Welfare Management</p>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="dashboard.php" class="sidebar-menu-link <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
                    <span>📊</span><span>Dashboard</span>
                </a>
            </li>
            
            <?php if ($roleId <= 3): ?>
                <li class="sidebar-menu-item">
                    <a href="members.php" class="sidebar-menu-link <?php echo $currentPage == 'members' ? 'active' : ''; ?>">
                        <span>👥</span><span>Members</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="accounts.php" class="sidebar-menu-link <?php echo $currentPage == 'accounts' ? 'active' : ''; ?>">
                        <span>💰</span><span>Accounts</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="loans.php" class="sidebar-menu-link <?php echo $currentPage == 'loans' ? 'active' : ''; ?>">
                        <span>💳</span><span>Loans</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="transactions.php" class="sidebar-menu-link <?php echo $currentPage == 'transactions' ? 'active' : ''; ?>">
                        <span>📝</span><span>Transactions</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="reports.php" class="sidebar-menu-link <?php echo $currentPage == 'reports' ? 'active' : ''; ?>">
                        <span>📊</span><span>Reports</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="sidebar-menu-item">
                    <a href="my-accounts.php" class="sidebar-menu-link <?php echo $currentPage == 'my-accounts' ? 'active' : ''; ?>">
                        <span>💰</span><span>My Accounts</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="my-loans.php" class="sidebar-menu-link <?php echo $currentPage == 'my-loans' ? 'active' : ''; ?>">
                        <span>💳</span><span>My Loans</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="loan-application.php" class="sidebar-menu-link <?php echo $currentPage == 'loan-application' ? 'active' : ''; ?>">
                        <span>📄</span><span>Apply for Loan</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="my-transactions.php" class="sidebar-menu-link <?php echo $currentPage == 'my-transactions' ? 'active' : ''; ?>">
                        <span>📝</span><span>Transactions</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if ($roleId <= 2): ?>
                <li class="sidebar-menu-item">
                    <a href="users.php" class="sidebar-menu-link <?php echo $currentPage == 'users' ? 'active' : ''; ?>">
                        <span>⚙️</span><span>Users</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="sidebar-menu-item">
                <a href="profile.php" class="sidebar-menu-link <?php echo $currentPage == 'profile' ? 'active' : ''; ?>">
                    <span>👤</span><span>Profile</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="logout.php" class="sidebar-menu-link" onclick="return confirm('Logout?');">
                    <span>🚪</span><span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="bottom-nav-item <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
            <span>📊</span><span>Home</span>
        </a>
        <?php if ($roleId <= 3): ?>
            <a href="members.php" class="bottom-nav-item <?php echo $currentPage == 'members' ? 'active' : ''; ?>">
                <span>👥</span><span>Members</span>
            </a>
            <a href="loans.php" class="bottom-nav-item <?php echo $currentPage == 'loans' ? 'active' : ''; ?>">
                <span>💳</span><span>Loans</span>
            </a>
        <?php else: ?>
            <a href="my-accounts.php" class="bottom-nav-item <?php echo $currentPage == 'my-accounts' ? 'active' : ''; ?>">
                <span>💰</span><span>Accounts</span>
            </a>
            <a href="loan-application.php" class="bottom-nav-item <?php echo $currentPage == 'loan-application' ? 'active' : ''; ?>">
                <span>📄</span><span>Apply</span>
            </a>
        <?php endif; ?>
        <a href="profile.php" class="bottom-nav-item <?php echo $currentPage == 'profile' ? 'active' : ''; ?>">
            <span>👤</span><span>Profile</span>
        </a>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="navbar" style="position: static; margin: -2rem -2rem 2rem; padding: 1rem 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin: 0; font-size: 1.5rem; color: var(--gray-800);"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="color: var(--gray-600);">
                        <strong><?php echo htmlspecialchars($userName); ?></strong> 
                        <span style="font-size: 0.813rem;">(<?php echo htmlspecialchars($roleName); ?>)</span>
                    </span>
                </div>
            </div>
        </div>
        
        <?php
        $flash = Session::getFlash('success');
        if ($flash):
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <span class="alert-icon"><?php echo $flash['type'] === 'success' ? '✓' : 'ℹ'; ?></span>
                <span><?php echo htmlspecialchars($flash['message']); ?></span>
            </div>
        <?php endif; ?>
        
        <?php
        $errorFlash = Session::getFlash('error');
        if ($errorFlash):
        ?>
            <div class="alert alert-danger">
                <span class="alert-icon">✕</span>
                <span><?php echo htmlspecialchars($errorFlash['message']); ?></span>
            </div>
        <?php endif; ?>
