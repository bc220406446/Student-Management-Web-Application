<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_student_login();

$stmt = $pdo->prepare('SELECT StudentID, Name, Email, Password, Department, Marks, Status FROM student WHERE StudentID = ?');
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
if (!$student) { session_destroy(); header('Location: ../login.php'); exit; }
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $marks = trim($_POST['marks'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '') $errors['name'] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'A valid email is required.';
    if ($department === '') $errors['department'] = 'Department is required.';
    if ($marks === '' || !is_numeric($marks) || (float) $marks < 0 || (float) $marks > 999.99) {
        $errors['marks'] = 'Marks must be a number between 0 and 999.99.';
    }
    $check = $pdo->prepare('SELECT StudentID FROM student WHERE Email = ? AND StudentID <> ?');
    $check->execute([$email, $_SESSION['user_id']]);
    if ($check->fetch()) $errors['email'] = 'That email is already in use.';

    if ($new_password !== '' || $current_password !== '' || $confirm_password !== '') {
        if (!password_verify($current_password, $student['Password'])) $errors['current_password'] = 'Current password is incorrect.';
        if (strlen($new_password) < 8) $errors['new_password'] = 'New password must be at least 8 characters.';
        if ($new_password !== $confirm_password) $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$errors) {
        $sql = 'UPDATE student SET Name = ?, Email = ?, Department = ?, Marks = ?';
        $params = [$name, $email, $department, (float) $marks];
        if ($new_password !== '') { $sql .= ', Password = ?'; $params[] = password_hash($new_password, PASSWORD_DEFAULT); }
        $sql .= ' WHERE StudentID = ?'; $params[] = $_SESSION['user_id'];
        $pdo->prepare($sql)->execute($params);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        set_flash_message('success', 'Personal information updated successfully.');
        header('Location: edit-profile.php'); exit;
    }
    $student['Name'] = $name; $student['Email'] = $email; $student['Department'] = $department; $student['Marks'] = $marks;
}
$page_title = 'Update Personal Info';
include '../includes/header_student.php';
?>
<div class="dashboard-panel">
<form method="POST">
    <div class="dashboard-panel-body">
        <h3 class="section-title mb-3">Personal Information</h3>
        <div class="row">
            <div class="col-6 form-group"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['Name']) ?>" required><?php if(isset($errors['name'])): ?><span class="form-error"><?= htmlspecialchars($errors['name']) ?></span><?php endif; ?></div>
            <div class="col-6 form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['Email']) ?>" required><?php if(isset($errors['email'])): ?><span class="form-error"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?></div>
        </div>
        <div class="row">
            <div class="col-6 form-group"><label class="form-label">Department</label><input type="text" name="department" class="form-control" value="<?= htmlspecialchars($student['Department']) ?>" required><?php if(isset($errors['department'])): ?><span class="form-error"><?= htmlspecialchars($errors['department']) ?></span><?php endif; ?></div>
            <div class="col-6 form-group"><label class="form-label">Marks</label><input type="number" name="marks" class="form-control" min="0" max="999.99" step="0.01" value="<?= htmlspecialchars($student['Marks']) ?>" required><?php if(isset($errors['marks'])): ?><span class="form-error"><?= htmlspecialchars($errors['marks']) ?></span><?php endif; ?></div>
        </div>
        <h3 class="section-title mb-3 mt-3">Change Password</h3>
        <div class="form-group"><label class="form-label">Current Password</label><div class="password-wrapper"><input type="password" name="current_password" class="form-control"><span class="password-toggle">Show</span></div><?php if(isset($errors['current_password'])): ?><span class="form-error"><?= htmlspecialchars($errors['current_password']) ?></span><?php endif; ?></div>
        <div class="row">
            <div class="col-6 form-group"><label class="form-label">New Password</label><div class="password-wrapper"><input type="password" name="new_password" class="form-control"><span class="password-toggle">Show</span></div><?php if(isset($errors['new_password'])): ?><span class="form-error"><?= htmlspecialchars($errors['new_password']) ?></span><?php endif; ?></div>
            <div class="col-6 form-group"><label class="form-label">Confirm New Password</label><div class="password-wrapper"><input type="password" name="confirm_password" class="form-control"><span class="password-toggle">Show</span></div><?php if(isset($errors['confirm_password'])): ?><span class="form-error"><?= htmlspecialchars($errors['confirm_password']) ?></span><?php endif; ?></div>
        </div>
    </div>
    <div class="dashboard-panel-footer"><a href="dashboard.php" class="btn btn-outline">Cancel</a><button class="btn btn-primary" type="submit">Save Changes</button></div>
</form>
</div>
</div></main></div><script src="../assets/js/main.js"></script></body></html>
