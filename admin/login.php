<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirect_if_admin_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        set_flash_message('error', 'Please enter both email and password.');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE Email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['Password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['AdminID'];
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_name'] = $admin['Name'];
            $_SESSION['admin_email'] = $admin['Email'];
            header("Location: dashboard.php");
            exit;
        } else {
            set_flash_message('error', 'Invalid admin credentials.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Student Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php display_flash_message(); ?>
    <div class="split-layout">
        <div class="split-left">
            <div class="portal-brand">
                <span>Student Management System</span>
            </div>
            <span class="tag">Admin Portal</span>
            <h1 class="mb-2">Secure admin access</h1>
            <p class="split-note mb-3">Manage student registrations, academic records, and oversee the entire system.</p>
            
            <div class="split-hero-icon">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <rect x="9" y="11" width="6" height="4" rx="1"></rect>
                    <path d="M10 11V9a2 2 0 1 1 4 0v2"></path>
                </svg>
            </div>
        </div>
        
        <div class="split-right">
            <div class="auth-form-container card admin-login-card">
                <div class="badge-center">
                    <div class="badge badge-neutral">Admin Access</div>
                </div>
                <h2 class="text-center mb-1">Sign in to admin panel</h2>
                <p class="text-center text-muted mb-3">Enter your administrative credentials</p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" class="form-control" required>
                            <span class="password-toggle">Show</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block mt-3">Sign In</button>
                    
                    <div class="text-center text-muted mt-3 fine-print">
                        This portal is restricted to authorized personnel only.
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
