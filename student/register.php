<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirect_if_student_logged_in();

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$department = trim($_POST['department'] ?? '');
$marks = trim($_POST['marks'] ?? '0');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '') $errors['name'] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'A valid email address is required.';
    if ($department === '') $errors['department'] = 'Department is required.';
    if ($marks === '' || !is_numeric($marks) || (float) $marks < 0 || (float) $marks > 999.99) {
        $errors['marks'] = 'Marks must be a number between 0 and 999.99.';
    }
    if (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';
    if ($password !== $confirm_password) $errors['confirm_password'] = 'Passwords do not match.';

    if (!$errors) {
        $check = $pdo->prepare('SELECT StudentID FROM student WHERE Email = ?');
        $check->execute([$email]);
        if ($check->fetch()) $errors['email'] = 'Email is already registered.';
    }

    if (!$errors) {
        try {
            $insert = $pdo->prepare("INSERT INTO student (Name, Email, Password, Department, Marks, Status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $department, (float) $marks]);
            set_flash_message('success', 'Registration submitted. You can log in after admin approval.');
            header('Location: login.php');
            exit;
        } catch (PDOException $exception) {
            $errors['form'] = 'Registration could not be completed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Student Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php display_flash_message(); ?>
<div class="split-layout">
    <div class="split-left">
        <div class="portal-brand">Student Management System</div>
        <span class="tag">Student Portal</span>
        <h1 class="mb-2">Create your student account</h1>
        <p class="split-note">Enter the information held by the student database. Your registration will require admin approval.</p>
    </div>
    <div class="split-right">
        <div class="register-form-container card card-border">
            <div class="access-badge-wrap">
                    <div class="access-badge">
                        Student Registration
                    </div>
                </div>
            <h2 class="text-center mb-1">Create your account</h2>
            <p class="text-center text-muted mb-3">All fields are required</p>
            <form method="POST">
                <?php if (isset($errors['form'])): ?>
                    <div class="form-error mb-2"><?= htmlspecialchars($errors['form']) ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                    <?php if (isset($errors['name'])): ?><span class="form-error"><?= htmlspecialchars($errors['name']) ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                    <?php if (isset($errors['email'])): ?><span class="form-error"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?>
                </div>
                <div class="row">
                    <div class="col-6 form-group">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($department) ?>" required>
                        <?php if (isset($errors['department'])): ?><span class="form-error"><?= htmlspecialchars($errors['department']) ?></span><?php endif; ?>
                </div>
                    <div class="col-6 form-group">
                        <label class="form-label">Marks</label>
                        <input type="number" name="marks" class="form-control" min="0" max="999" step="1" value="<?= htmlspecialchars($marks) ?>" required>
                        <?php if (isset($errors['marks'])): ?><span class="form-error"><?= htmlspecialchars($errors['marks']) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 form-group">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper"><input type="password" name="password" class="form-control" required><span class="password-toggle">Show</span></div>
                        <?php if (isset($errors['password'])): ?><span class="form-error"><?= htmlspecialchars($errors['password']) ?></span><?php endif; ?>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="password-wrapper"><input type="password" name="confirm_password" class="form-control" required><span class="password-toggle">Show</span></div>
                        <?php if (isset($errors['confirm_password'])): ?><span class="form-error"><?= htmlspecialchars($errors['confirm_password']) ?></span><?php endif; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block mt-2">Register</button>
                <div class="text-center mt-2"><a href="login.php">Already registered? Log in</a></div>
            </form>
        </div>
    </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
