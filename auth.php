<?php
/**
 * Unified authentication entry point.
 *
 * One page serves every authentication action, so credentials, roles and
 * access rules are handled in a single place:
 *
 *   auth.php?a=login     sign in with an existing account
 *   auth.php?a=register  submit a new student registration
 *   auth.php?a=forgot    request a password reset link
 *   auth.php?a=reset     choose a new password from a reset link
 *   auth.php?a=logout    end the current session (POST only)
 */

// Load the database connection and session/authentication helpers.
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Read the requested action from the query string or a submitted form.
$action = $_GET['a'] ?? $_POST['a'] ?? 'login';

$actions = array('login', 'register', 'forgot', 'reset', 'logout');
if (!in_array($action, $actions, true)) {
    $action = 'login';
}

$is_post = ($_SERVER['REQUEST_METHOD'] === 'POST');

/*
   Logout
   Logging out is a state change, so it only accepts a POST and never a
   plain link, which stops other sites from signing people out.
*/
if ($action === 'logout') {
    if (!$is_post) {
        header('Location: ' . AUTH_ENTRY);
        exit;
    }
    logout_user();
    header('Location: ' . AUTH_ENTRY . '?a=login');
    exit;
}

// Every other action is a form, so signed-in users go straight to their dashboard.
if (!$is_post) {
    redirect_if_authenticated();
}

// Keep submitted values and validation messages so a failed submit can redisplay.
$errors = array();
$values = array(
    'name' => '',
    'email' => '',
    'department' => '',
    'marks' => ''
);

// True when a password reset token is present, matching and still unexpired.
function reset_token_is_valid($token) {
    return isset($_SESSION['reset_token'], $_SESSION['reset_expires'], $_SESSION['reset_role'], $_SESSION['reset_user_id'])
        && hash_equals($_SESSION['reset_token'], (string) $token)
        && strtotime($_SESSION['reset_expires']) > time();
}

/*
   Login
   One form serves both roles: the account is looked up by email and the role
   stored on the matched row decides where the visitor is sent.
*/
if ($action === 'login' && $is_post) {
    $values['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($values['email'] === '' || $password === '') {
        set_flash_message('error', 'Please enter both email and password.');
    } else {
        $account = find_account_by_email($pdo, $values['email']);

        if ($account && password_verify($password, $account['row']['Password'])) {
            $blocked = false;

            // Only approved students may enter the student portal.
            if ($account['role'] === ROLE_STUDENT) {
                $status = $account['row']['Status'];
                if ($status === 'Pending') {
                    set_flash_message('warning', 'Your account is pending admin approval. Please check back later.');
                    $blocked = true;
                } elseif ($status === 'Not Approved') {
                    set_flash_message('error', 'Your registration was not approved. Please contact the administrator.');
                    $blocked = true;
                }
            }

            if (!$blocked) {
                login_user($account['role'], $account['id'], $account['name'], $account['row']['Email']);
                header('Location: ' . dashboard_path($account['role']));
                exit;
            }
        } else {
            set_flash_message('error', 'Invalid email or password. Please try again.');
        }
    }
}

/*
   Register
   New accounts always belong to the student role and stay pending until an
   administrator approves them.
*/
if ($action === 'register' && $is_post) {
    $values['name'] = trim($_POST['name'] ?? '');
    $values['email'] = trim($_POST['email'] ?? '');
    $values['department'] = trim($_POST['department'] ?? '');
    $values['marks'] = trim($_POST['marks'] ?? '0');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($values['name'] === '')
        $errors['name'] = 'Name is required.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'A valid email address is required.';
    if ($values['department'] === '')
        $errors['department'] = 'Department is required.';
    if ($values['marks'] === '' || !is_numeric($values['marks']) || (float) $values['marks'] < 0 || (float) $values['marks'] > 999.99) {
        $errors['marks'] = 'Marks must be a number between 0 and 999.99.';
    }
    if (strlen($password) < 8)
        $errors['password'] = 'Password must be at least 8 characters.';
    if ($password !== $confirm_password)
        $errors['confirm_password'] = 'Passwords do not match.';

    // The email must be free across both role tables.
    if (!$errors && email_is_taken($pdo, $values['email'])) {
        $errors['email'] = 'Email is already registered.';
    }

    if (!$errors) {
        try {
            $insert = $pdo->prepare("INSERT INTO student (Name, Email, Password, Department, Marks, Status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $insert->execute(array(
                $values['name'],
                $values['email'],
                password_hash($password, PASSWORD_DEFAULT),
                $values['department'],
                (float) $values['marks']
            ));
            set_flash_message('success', 'Registration submitted. You can sign in after admin approval.');
            header('Location: ' . AUTH_ENTRY . '?a=login');
            exit;
        } catch (PDOException $exception) {
            $errors['form'] = 'Registration could not be completed. Please try again.';
        }
    }
}

/*
   Forgot password
   A reset token is generated for any account, admin or student, and is only
   valid for one hour.
*/
if ($action === 'forgot' && $is_post) {
    $values['email'] = trim($_POST['email'] ?? '');
    $account = find_account_by_email($pdo, $values['email']);

    if ($account) {
        $token = bin2hex(random_bytes(32));
        $_SESSION['reset_token'] = $token;
        $_SESSION['reset_role'] = $account['role'];
        $_SESSION['reset_user_id'] = $account['id'];
        $_SESSION['reset_expires'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $link = AUTH_ENTRY . '?a=reset&token=' . urlencode($token);
        set_flash_message('success', 'A reset link has been generated. <a href="' . $link . '" class="link-light">Click here to reset your password</a>', 20000);
    } else {
        set_flash_message('error', 'No account found with that email address.');
    }

    header('Location: ' . AUTH_ENTRY . '?a=forgot');
    exit;
}

/*
   Reset password
   The token decides which account is updated, so the form never accepts an
   account id directly.
*/
$reset_token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$token_valid = $reset_token !== '' && reset_token_is_valid($reset_token);

if ($action === 'reset' && $is_post) {
    if (!$token_valid) {
        set_flash_message('error', 'Reset link is invalid or has expired.');
        header('Location: ' . AUTH_ENTRY . '?a=forgot');
        exit;
    }

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 8) {
        set_flash_message('error', 'Password must be at least 8 characters.');
    } elseif ($new_password !== $confirm_password) {
        set_flash_message('error', 'Passwords do not match.');
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $role = $_SESSION['reset_role'];
        $user_id = $_SESSION['reset_user_id'];

        if ($role === ROLE_ADMIN) {
            $update = $pdo->prepare('UPDATE admin SET Password = ? WHERE AdminID = ?');
        } else {
            $update = $pdo->prepare('UPDATE student SET Password = ? WHERE StudentID = ?');
        }
        $update->execute(array($hashed, $user_id));

        // Remove reset data so the same link cannot be used twice.
        unset($_SESSION['reset_token'], $_SESSION['reset_role'], $_SESSION['reset_user_id'], $_SESSION['reset_expires']);
        set_flash_message('success', 'Password reset successfully! You can now sign in.');
        header('Location: ' . AUTH_ENTRY . '?a=login');
        exit;
    }
}

// Copy the left panel text for the screen currently being shown.
$panels = array(
    'login' => array(
        'tag' => 'Role Based Access',
        'title' => 'Sign in to your portal',
        'note' => 'Your account type is detected automatically after you sign in.'
    ),
    'register' => array(
        'tag' => 'Student Registration',
        'title' => 'Create your student account',
        'note' => 'Submit your student details for administrator review and approval.'
    ),
    'forgot' => array(
        'tag' => 'Password Recovery',
        'title' => 'Reset your password',
        'note' => "Enter your registered email and we'll help you regain access to your account."
    ),
    'reset' => array(
        'tag' => 'Password Recovery',
        'title' => 'Choose a new password',
        'note' => 'This link works once and expires one hour after it was issued.'
    )
);
$panel = $panels[$action];

// Render one labelled input row with its validation message.
// Password fields are wrapped so the shared show/hide toggle can find them.
function render_field($name, $label, $type, $value = '', $extra = '', $group_class = '') {
    $error = $GLOBALS['errors'][$name] ?? null;
    $input = sprintf(
        '<input type="%s" name="%s" class="form-control" value="%s" %s>',
        htmlspecialchars($type),
        htmlspecialchars($name),
        htmlspecialchars($value),
        $extra
    );
    ?>
    <div class="<?= htmlspecialchars(trim('form-group ' . $group_class)) ?>">
        <label class="form-label"><?= htmlspecialchars($label) ?></label>
        <?php if ($type === 'password'): ?>
            <div class="password-wrapper"><?= $input ?><span class="password-toggle">Show</span></div>
        <?php else: ?>
            <?= $input ?>
        <?php endif; ?>
        <?php if ($error !== null): ?>
            <span class="form-error"><?= htmlspecialchars($error) ?></span>
        <?php endif; ?>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System - Authentication</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php display_flash_message(); ?>
    <div class="split-layout">
        <div class="split-left">
            <div class="portal-brand">
                <span>Student Management System</span>
            </div>
            <span class="tag"><?= htmlspecialchars($panel['tag']) ?></span>
            <h1 class="mb-2"><?= htmlspecialchars($panel['title']) ?></h1>
            <p class="split-note mb-3"><?= htmlspecialchars($panel['note']) ?></p>

            <div class="split-hero-icon">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)"
                    stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <rect x="9" y="11" width="6" height="4" rx="1"></rect>
                    <path d="M10 11V9a2 2 0 1 1 4 0v2"></path>
                </svg>
            </div>
        </div>

        <div class="split-right">
            <div class="auth-form-container card card-border">
                <!-- Switch between the login, register and password screens. -->
                <div class="text-center mb-3">
                    <a class="text-sm<?= $action === 'login' ? ' auth-nav-active' : '' ?>"
                        href="<?= AUTH_ENTRY ?>?a=login">Sign In</a>
                    <span class="text-muted"> &middot; </span>
                    <a class="text-sm<?= $action === 'register' ? ' auth-nav-active' : '' ?>"
                        href="<?= AUTH_ENTRY ?>?a=register">Register</a>
                    <span class="text-muted"> &middot; </span>
                    <a class="text-sm<?= in_array($action, array('forgot', 'reset'), true) ? ' auth-nav-active' : '' ?>"
                        href="<?= AUTH_ENTRY ?>?a=forgot">Forgot Password</a>
                </div>

                <?php if ($action === 'register'): ?>
                    <h2 class="text-center mb-1">Create your account</h2>
                    <p class="text-center text-muted mb-3">All fields are required</p>
                    <?php if (isset($errors['form'])): ?>
                        <div class="form-error mb-2"><?= htmlspecialchars($errors['form']) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?= AUTH_ENTRY ?>?a=register">
                        <?php render_field('name', 'Name', 'text', $values['name'], 'required'); ?>
                        <?php render_field('email', 'Email', 'email', $values['email'], 'required'); ?>
                        <div class="row">
                            <?php render_field('department', 'Department', 'text', $values['department'], 'required', 'col-6'); ?>
                            <?php render_field('marks', 'Marks', 'number', $values['marks'], 'min="0" max="999" step="1" required', 'col-6'); ?>
                        </div>
                        <div class="row">
                            <?php render_field('password', 'Password', 'password', '', 'required', 'col-6'); ?>
                            <?php render_field('confirm_password', 'Confirm Password', 'password', '', 'required', 'col-6'); ?>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-2">Register</button>
                    </form>

                <?php elseif ($action === 'forgot'): ?>
                    <h2 class="text-center mb-1">Forgot Password?</h2>
                    <p class="text-center text-muted mb-3">Enter your registered email address below.</p>
                    <form method="POST" action="<?= AUTH_ENTRY ?>?a=forgot">
                        <?php render_field('email', 'Email Address', 'email', $values['email'], 'placeholder="you@example.com" required'); ?>
                        <button type="submit" class="btn btn-primary btn-block mt-2">Send Reset Link</button>
                    </form>

                <?php elseif ($action === 'reset' && $token_valid): ?>
                    <h2 class="text-center mb-1">Set New Password</h2>
                    <p class="text-center text-muted mb-3">Choose a strong password for your account.</p>
                    <form method="POST" action="<?= AUTH_ENTRY ?>?a=reset">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($reset_token) ?>">
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

                <?php elseif ($action === 'reset'): ?>
                    <div class="text-center center-pad">
                        <svg width="48" height="48" fill="none" stroke="var(--danger)" stroke-width="1.5" viewBox="0 0 24 24"
                            style="margin-bottom:1rem;">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="15" y1="9" x2="9" y2="15" />
                            <line x1="9" y1="9" x2="15" y2="15" />
                        </svg>
                        <h3 class="title-tight">Invalid or Expired Link</h3>
                        <p class="text-muted mb-3">This reset link is invalid or has expired. Please request a new one.</p>
                        <a href="<?= AUTH_ENTRY ?>?a=forgot" class="btn btn-primary">Request New Link</a>
                    </div>

                <?php else: ?>
                    <h2 class="text-center mb-1">Sign in to your account</h2>
                    <p class="text-center text-muted mb-3">Enter your credentials to continue</p>
                    <form method="POST" action="<?= AUTH_ENTRY ?>?a=login">
                        <?php render_field('email', 'Email Address', 'email', $values['email'], 'required'); ?>
                        <div class="form-group">
                            <div class="form-label-row">
                                <label class="form-label">Password</label>
                                <a href="<?= AUTH_ENTRY ?>?a=forgot" class="text-sm">Forgot password?</a>
                            </div>
                            <div class="password-wrapper password-wrapper-spaced">
                                <input type="password" name="password" class="form-control" required>
                                <span class="password-toggle">Show</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-2">Sign In</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>

</html>