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
            <div class="portal-brand">
                <span>Student Management System</span>
            </div>
            <span class="tag">Student Portal</span>
            <h1 class="mb-2">Welcome back to your portal</h1>
            <p class="split-note mb-3">Log in to view your academic records, update your profile, and stay connected.</p>

            <div class="split-hero-icon">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <rect x="9" y="11" width="6" height="4" rx="1"></rect>
                    <path d="M10 11V9a2 2 0 1 1 4 0v2"></path>
                </svg>
            </div>
        </div>
        
        <div class="split-right">
            <div class="auth-form-container card card-border">
                <div class="access-badge-wrap">
                    <div class="access-badge">
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
                        <div class="form-label-row">
                            <label class="form-label">Password</label>
                            <a href="forgot-password.php" class="text-sm">Forgot password?</a>
                        </div>
                        <div class="password-wrapper password-wrapper-spaced">
                            <input type="password" name="password" class="form-control" required>
                            <span class="password-toggle">Show</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block mt-2">Sign In</button>
                    
                    <div class="auth-divider">
                        <div class="auth-divider-line"></div>
                        <div class="auth-divider-text">or</div>
                        <div class="auth-divider-line"></div>
                    </div>
                    
                    <div class="text-center">
                        <a href="register.php" class="text-sm">Don't have an account? Register here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
