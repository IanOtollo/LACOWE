<?php
require_once 'config/config.php';
require_once 'includes/Auth.php';
// Redirect if already logged in
if (Session::isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    }
    else {
        $auth = new Auth();
        $result = $auth->login($username, $password);

        if ($result['success']) {
            redirect('dashboard.php');
        }
        else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LACOWE Welfare MIS</title>
    
    <!-- Professional Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="container" style="max-width: 450px; margin-top: 5vh;">
        <div class="card" style="border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
            <div class="card-header" style="background: white; border-bottom: none; text-align: center; padding-top: 2.5rem;">
                <div style="width: 64px; height: 64px; background: var(--primary); color: white; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; font-weight: 700; margin-bottom: 1rem;">
                    L
                </div>
                <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--gray-900);">LACOWE Welfare</h1>
                <p style="color: var(--gray-500); font-size: 0.875rem; margin: 0;">Sign in to your account</p>
            </div>
            
            <div class="card-body" style="padding: 2rem;">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php
endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 1rem; top: 0.75rem; color: var(--gray-400);">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="username" class="form-control" 
                                   style="padding-left: 2.5rem;"
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                   placeholder="Enter your username"
                                   required autofocus>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <label class="form-label" style="margin: 0;">Password</label>
                            <a href="#" style="font-size: 0.813rem; color: var(--primary);">Forgot password?</a>
                        </div>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 1rem; top: 0.75rem; color: var(--gray-400);">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" class="form-control" 
                                   style="padding-left: 2.5rem;"
                                   placeholder="Enter your password"
                                   required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 600;">
                        Sign In
                    </button>
                </form>
            </div>
            
            <div class="card-footer" style="text-align: center; background: white; border-top: 1px solid var(--gray-100); padding: 1.5rem;">
                <p style="font-size: 0.813rem; color: var(--gray-500); margin: 0;">
                    Secure System for LACOWE Welfare Group
                </p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <p style="font-size: 0.813rem; color: var(--gray-500);">
                &copy; <?php echo date('Y'); ?> LACOWE Welfare MIS. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
