<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin_login();
$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$department = trim($_POST['department'] ?? '');
$marks = trim($_POST['marks'] ?? '0');
$status = $_POST['status'] ?? 'Approved';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($name === '')
        $errors['name'] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'A valid email is required.';
    if ($department === '')
        $errors['department'] = 'Department is required.';
    if (!is_numeric($marks) || $marks < 0 || $marks > 999.99)
        $errors['marks'] = 'Marks must be between 0 and 999.99.';
    if (strlen($password) < 8)
        $errors['password'] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)
        $errors['confirm_password'] = 'Passwords do not match.';
    if (!in_array($status, ['Approved', 'Not Approved', 'Pending'], true))
        $errors['status'] = 'Invalid status.';
    $check = $pdo->prepare('SELECT StudentID FROM student WHERE Email=?');
    $check->execute([$email]);
    if ($check->fetch())
        $errors['email'] = 'Email already exists.';
    if (!$errors) {
        $pdo->prepare('INSERT INTO student (Name,Email,Password,Department,Marks,Status) VALUES (?,?,?,?,?,?)')->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $department, $marks, $status]);
        set_flash_message('success', 'Student created.');
        header('Location: all-students.php');
        exit;
    }
}
$page_title = 'Add Student';
include '../includes/header_admin.php';
?>
<div class="dashboard-panel">
    <form method="POST">
        <div class="dashboard-panel-body">
            <div class="row">
                <div class="col-6 form-group"><label class="form-label">Name</label><input name="name"
                        class="form-control" value="<?= htmlspecialchars($name) ?>"
                        required><?php if (isset($errors['name'])): ?><span
                            class="form-error"><?= htmlspecialchars($errors['name']) ?></span><?php endif; ?></div>
                <div class="col-6 form-group"><label class="form-label">Email</label><input type="email" name="email"
                        class="form-control" value="<?= htmlspecialchars($email) ?>"
                        required><?php if (isset($errors['email'])): ?><span
                            class="form-error"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?></div>
            </div>
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Marks</label><input type="number" step="0.01" min="0" max="999.99"
                        name="marks" class="form-control" value="<?= htmlspecialchars($marks) ?>"
                        required><?php if (isset($errors['marks'])): ?><span
                            class="form-error"><?= htmlspecialchars($errors['marks']) ?></span><?php endif; ?>
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Department</label><input name="department" class="form-control"
                        value="<?= htmlspecialchars($department) ?>"
                        required><?php if (isset($errors['department'])): ?><span
                            class="form-error"><?= htmlspecialchars($errors['department']) ?></span><?php endif; ?>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label><select name="status"
                    class="form-control"><?php foreach (['Approved', 'Not Approved', 'Pending'] as $v): ?>
                        <option <?= $status === $v ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                </select>
                <?php if (isset($errors['status'])): ?><span
                    class="form-error"><?= htmlspecialchars($errors['status']) ?></span><?php endif; ?>
            </div>
            <h3 class="section-title mb-3">Set Password</h3>
            <div class="row">
                <div class="col-6 form-group"><label class="form-label">Password</label>
                    <div class="password-wrapper"><input type="password" name="password" class="form-control"
                            required><span class="password-toggle">Show</span></div>
                    <?php if (isset($errors['password'])): ?><span
                            class="form-error"><?= htmlspecialchars($errors['password']) ?></span><?php endif; ?>
                </div>
                <div class="col-6 form-group"><label class="form-label">Confirm Password</label>
                    <div class="password-wrapper"><input type="password" name="confirm_password" class="form-control"
                            required><span class="password-toggle">Show</span></div>
                    <?php if (isset($errors['confirm_password'])): ?><span
                            class="form-error"><?= htmlspecialchars($errors['confirm_password']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="dashboard-panel-footer"><a href="all-students.php" class="btn btn-outline">Cancel</a><button
                class="btn btn-primary">Save Student</button></div>
    </form>
</div>
</div>
</div>
<script src="../assets/js/main.js"></script>
</body>

</html>
