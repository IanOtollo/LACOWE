<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
require_once 'includes/Session.php';
require_once 'includes/helpers.php';

Session::start();
if (Session::isLoggedIn()) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $auth = new Auth();
        $result = $auth->login($username, $password);
        if ($result['success']) {
            redirect('dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1e40af">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="LACOWE MIS">
    <title>Login - LACOWE Welfare MIS</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="assets/images/icon-192.png">
    <link rel="apple-touch-icon" href="assets/images/icon-192.png">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/mobile.css">
    
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            padding: 2rem;
        }
        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .system-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            font-weight: 700;
            color: #1e40af;
        }
        .install-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <!-- Splash Screen -->
    <div class="splash-screen" id="splashScreen">
        <div class="splash-logo">L</div>
        <div class="splash-title">LACOWE MIS</div>
        <div class="splash-subtitle">Loading...</div>
        <div class="spinner"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="system-logo">L</div>
                <h1>LACOWE Welfare MIS</h1>
                <p>Jomo Kenyatta University of Agriculture and Technology</p>
                <div class="install-badge">📱 Installable App</div>
            </div>
            
            <div class="login-body">
                <h2 class="text-center mb-4" style="color: var(--gray-800);">Welcome Back</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <span class="alert-icon">✕</span>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Username or Email</label>
                        <input type="text" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                               required autofocus autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" 
                               required autocomplete="current-password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary haptic-feedback" style="width: 100%;">
                        Sign In
                    </button>
                </form>
                
                <div style="margin-top: 1.5rem; text-align: center; color: var(--gray-600); font-size: 0.875rem;">
                    <p><strong>Default Admin:</strong></p>
                    <p>Username: <code>admin</code> | Password: <code>Admin@123</code></p>
                </div>
            </div>
        </div>
    </div>

    <!-- PWA Install Script -->
    <script src="assets/js/pwa-install.js"></script>
    
    <script>
        // Hide splash screen after page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('splashScreen').classList.add('hide');
            }, 1000);
        });

        // Add haptic feedback to buttons (iOS)
        document.querySelectorAll('.haptic-feedback').forEach(el => {
            el.addEventListener('click', () => {
                if (navigator.vibrate) {
                    navigator.vibrate(10);
                }
            });
        });
    </script>
</body>
</html>
