<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

redirect_if_logged_in();

$step = $_GET['step'] ?? 'email';
$token_valid = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT StudentID, Name FROM student WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $_SESSION['reset_token'] = $token;
        $_SESSION['reset_student_id'] = $user['StudentID'];
        $_SESSION['reset_expires'] = $expires;
        set_flash_message('success', 'A reset link has been generated. <a href="forgot-password.php?step=reset&token=' . $token . '" class="link-light">Click here to reset your password</a>', 20000);
    } else {
        set_flash_message('error', 'No account found with that email address.');
    }
    header("Location: forgot-password.php");
    exit;
}

if ($step === 'reset') {
    $token = $_GET['token'] ?? '';
    if (
        isset($_SESSION['reset_token']) &&
        $_SESSION['reset_token'] === $token &&
        strtotime($_SESSION['reset_expires']) > time()
    ) {
        $token_valid = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (
        isset($_SESSION['reset_token']) &&
        $_SESSION['reset_token'] === $token &&
        strtotime($_SESSION['reset_expires']) > time()
    ) {
        if (strlen($new_password) < 8) {
            set_flash_message('error', 'Password must be at least 8 characters.');
            header("Location: forgot-password.php?step=reset&token=$token");
            exit;
        }
        if ($new_password !== $confirm_password) {
            set_flash_message('error', 'Passwords do not match.');
            header("Location: forgot-password.php?step=reset&token=$token");
            exit;
        }
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE student SET Password = ? WHERE StudentID = ?");
        $update->execute([$hashed, $_SESSION['reset_student_id']]);

        unset($_SESSION['reset_token'], $_SESSION['reset_student_id'], $_SESSION['reset_expires']);
        set_flash_message('success', 'Password reset successfully! You can now log in.');
        header("Location: login.php");
        exit;
    } else {
        set_flash_message('error', 'Reset link is invalid or has expired.');
        header("Location: forgot-password.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Student Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php display_flash_message(); ?>
<div class="split-layout">
    <div class="split-left">
        <div class="portal-brand">
            <span>Student Management System</span>
        </div>
        <span class="tag">Password Recovery</span>
        <h1 class="mb-2">Reset your password</h1>
        <p class="split-note">Enter your registered email and we'll help you regain access to your account.</p>

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
            <?php if ($step === 'reset' && $token_valid): ?>
                <h2 class="text-center mb-1">Set New Password</h2>
                <p class="text-center text-muted mb-3">Choose a strong password for your account.</p>
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                    <div class="form-group">
                        <label class="form-label">New Password (min 8 chars)</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" class="form-control" required>
                            <span class="password-toggle">Show</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" class="form-control" required>
                            <span class="password-toggle">Show</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-2">Reset Password</button>
                </form>
            <?php elseif ($step === 'reset' && !$token_valid): ?>
                <div class="text-center center-pad">
                    <svg width="48" height="48" fill="none" stroke="var(--danger)" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:1rem;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <h3 class="title-tight">Invalid or Expired Link</h3>
                    <p class="text-muted mb-3">This reset link is invalid or has expired. Please request a new one.</p>
                    <a href="forgot-password.php" class="btn btn-primary">Request New Link</a>
                </div>
            <?php else: ?>
                <h2 class="text-center mb-1">Forgot Password?</h2>
                <p class="text-center text-muted mb-3">Enter your registered email address below.</p>
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-2">Send Reset Link</button>
                    <div class="text-center mt-2">
                        <a href="login.php" class="text-sm">Back to Login</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
