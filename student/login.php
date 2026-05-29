<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirect_if_student_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        set_flash_message('error', 'Please enter both email and password.');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM student WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['Password'])) {
            if ($user['Status'] === 'Pending') {
                set_flash_message('warning', 'Your account is pending admin approval. Please check back later.');
            } elseif ($user['Status'] === 'Rejected') {
                set_flash_message('error', 'Your registration has been rejected. Please contact the school.');
            } else {
                // Approved
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['StudentID'];
                $_SESSION['role'] = 'student';
                $_SESSION['user_name'] = $user['Name'];
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['user_profile_pic'] = $user['ProfilePicture'];
                header("Location: dashboard.php");
                exit;
            }
        } else {
            set_flash_message('error', 'Invalid email or password. Please try again.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php display_flash_message(); ?>
    <div class="split-layout">
        <div class="split-left">
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:2rem; font-family:var(--font-heading); font-size:1.5rem;">
                <span>Student Management System</span>
            </div>
            <span class="tag">Student Portal</span>
            <h1 class="mb-2">Welcome back to your portal</h1>
            <p style="color: rgba(255,255,255,0.8);" class="mb-3">Log in to view your academic records, update your profile, and stay connected.</p>
            
            <div style="background: rgba(255,255,255,0.1); padding: 1.5rem; border-radius: var(--radius-md);">
                <h3 style="color: white; margin-bottom: 1rem; font-size: 1rem; font-family: var(--font-body);">Getting Started</h3>
                <div style="display:flex; flex-direction:column; gap: 0.75rem; color: rgba(255,255,255,0.8); font-size: 0.875rem;">
                    <div><strong>1. Register</strong> - Create your account</div>
                    <div><strong>2. Wait for approval</strong> - Admin verification</div>
                    <div><strong>3. Login</strong> - Access your dashboard</div>
                </div>
            </div>
        </div>
        
        <div class="split-right">
            <div class="auth-form-container card">
                <div style="display:flex; justify-content:center; margin-bottom: 1rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; background: #eff6ff; color: var(--accent); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">
                        <span style="width:6px; height:6px; background:var(--accent); border-radius:50%; display:inline-block; animation: pulse 2s infinite;"></span>
                        Student Access
                    </div>
                </div>
                <h2 class="text-center mb-1">Sign in to your account</h2>
                <p class="text-center text-muted mb-3">Enter your credentials to continue</p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <label class="form-label" style="margin:0;">Password</label>
                            <a href="forgot-password.php" class="text-sm">Forgot password?</a>
                        </div>
                        <div class="password-wrapper" style="margin-top: 0.5rem;">
                            <input type="password" name="password" class="form-control" required>
                            <span class="password-toggle">Show</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block mt-2">Sign In</button>
                    
                    <div style="display:flex; align-items:center; margin: 1.5rem 0; color: var(--text-muted); font-size: 0.875rem;">
                        <div style="flex:1; height:1px; background:var(--border-color);"></div>
                        <div style="padding: 0 1rem;">or</div>
                        <div style="flex:1; height:1px; background:var(--border-color);"></div>
                    </div>
                    
                    <div class="text-center mb-3">
                        <a href="register.php" class="text-sm">Don't have an account? Register here</a>
                    </div>
                    
                    <div class="text-center text-muted" style="font-size: 0.75rem; background: var(--bg-color); padding: 0.75rem; border-radius: var(--radius-sm);">
                        Your account must be approved by an admin before you can log in.
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(59, 130, 246, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
    </style>
    <script src="../assets/js/main.js"></script>
</body>
</html>
