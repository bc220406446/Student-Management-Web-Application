<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare('SELECT * FROM student WHERE StudentID=?');
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) {
    set_flash_message('error', 'Student not found.');
    header('Location: all-students.php');
    exit;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $marks = trim($_POST['marks'] ?? '');
    $status = $_POST['status'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($name === '')
        $errors['name'] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'A valid email is required.';
    if ($department === '')
        $errors['department'] = 'Department is required.';
    if (!is_numeric($marks) || $marks < 0 || $marks > 999.99)
        $errors['marks'] = 'Marks must be between 0 and 999.99.';
    if (!in_array($status, ['Approved', 'Not Approved', 'Pending'], true))
        $errors['status'] = 'Invalid status.';
    $check = $pdo->prepare('SELECT StudentID FROM student WHERE Email=? AND StudentID<>?');
    $check->execute([$email, $id]);
    if ($check->fetch())
        $errors['email'] = 'Email already exists.';
    if ($new_password !== '' && strlen($new_password) < 8)
        $errors['new_password'] = 'Password must be at least 8 characters.';
    if ($new_password !== $confirm)
        $errors['confirm_password'] = 'Passwords do not match.';
    if (!$errors) {
        $sql = 'UPDATE student SET Name=?,Email=?,Department=?,Marks=?,Status=?';
        $params = [$name, $email, $department, $marks, $status];
        if ($new_password !== '') {
            $sql .= ',Password=?';
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE StudentID=?';
        $params[] = $id;
        $pdo->prepare($sql)->execute($params);
        set_flash_message('success', 'Student updated.');
        header('Location: all-students.php');
        exit;
    }
    $student = array_merge($student, ['Name' => $name, 'Email' => $email, 'Department' => $department, 'Marks' => $marks, 'Status' => $status]);
}
$page_title = 'Edit Student';
include '../includes/header_admin.php';
?>
<div class="admin-panel">
    <form method="POST">
        <div class="admin-panel-body">
            <div class="row">
                <div class="col-6 form-group"><label class="form-label">Name</label><input name="name"
                        class="form-control" value="<?= htmlspecialchars($student['Name']) ?>" required></div>
                <div class="col-6 form-group"><label class="form-label">Email</label><input type="email" name="email"
                        class="form-control" value="<?= htmlspecialchars($student['Email']) ?>"
                        required><?php if (isset($errors['email'])): ?><span
                            class="form-error"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?></div>
            </div>
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Marks</label><input type="number" step="0.01" min="0" max="999.99"
                    name="marks" class="form-control" value="<?= htmlspecialchars($student['Marks']) ?>"
                    required><?php if (isset($errors['marks'])): ?><span
                        class="form-error"><?= htmlspecialchars($errors['marks']) ?></span><?php endif; ?>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-label">Department</label><input name="department" class="form-control"
                        value="<?= htmlspecialchars($student['Department']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label><select name="status"
                        class="form-control"><?php foreach (['Approved', 'Not Approved', 'Pending'] as $v): ?>
                            <option <?= $student['Status'] === $v ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                    </select>
                </div>
                <h3 class="admin-section-title mb-3">Reset Password <small class="text-muted">(optional)</small></h3>
                <div class="row">
                <div class="col-6 form-group"><label class="form-label">New Password</label>
                    <div class="password-wrapper"><input type="password" name="new_password" class="form-control"><span
                            class="password-toggle">Show</span></div><?php if (isset($errors['new_password'])): ?><span
                            class="form-error"><?= htmlspecialchars($errors['new_password']) ?></span><?php endif; ?>
                </div>
                <div class="col-6 form-group"><label class="form-label">Confirm Password</label>
                    <div class="password-wrapper"><input type="password" name="confirm_password"
                            class="form-control"><span class="password-toggle">Show</span></div>
                    <?php if (isset($errors['confirm_password'])): ?><span
                            class="form-error"><?= htmlspecialchars($errors['confirm_password']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="admin-panel-footer"><a href="all-students.php" class="btn btn-outline">Cancel</a><button
                class="btn btn-primary">Save Changes</button></div>
    </form>
</div>
</div>
</div>
<script src="../assets/js/main.js"></script>
</body>

</html>