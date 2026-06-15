<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin_login();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM student WHERE StudentID = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    set_flash_message('error', 'Student not found.');
    header("Location: all-students.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $class_grade = trim($_POST['class_grade'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $section = trim($_POST['section'] ?? '');
    $roll_number = trim($_POST['roll_number'] ?? '');
    $admission_date = trim($_POST['admission_date'] ?? '');
    $status = $_POST['status'] ?? 'Approved';
    
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name)) $errors['name'] = "Full Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Valid Email is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT StudentID FROM student WHERE Email = ? AND StudentID != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) $errors['email'] = "Email already in use by another student.";
    }
    
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) $errors['new_password'] = "Password must be at least 8 chars.";
        if ($new_password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $approved_by = ($status === 'Approved') ? $_SESSION['admin_id'] : null;
        if ($status === $student['Status']) {
            $approved_by = $student['ApprovedBy']; // Keep existing if not changed
        }
        
        $profile_picture = $student['ProfilePicture'];
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/profile-pictures/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $file_name)) {
                $profile_picture = 'uploads/profile-pictures/' . $file_name;
            }
        }

        $update_sql = "UPDATE student SET Name=?, Email=?, ProfilePicture=?, DateOfBirth=?, ContactNo=?, Address=?, ClassGrade=?, AcademicYear=?, Section=?, RollNumber=?, AdmissionDate=?, Status=?, ApprovedBy=?";
        $params = [$name, $email, $profile_picture, $dob ?: null, $contact_no, $address, $class_grade, $academic_year, $section ?: null, $roll_number ?: null, $admission_date ?: null, $status, $approved_by];
        
        if (!empty($new_password)) {
            $update_sql .= ", Password=?";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $update_sql .= " WHERE StudentID=?";
        $params[] = $id;

        $update = $pdo->prepare($update_sql);
            
        if ($update->execute($params)) {
            set_flash_message('success', 'Student record updated successfully.');
            header("Location: all-students.php");
            exit;
        } else {
            set_flash_message('error', 'Server error while updating student.');
        }
    }
}

$page_title = "Edit Student";
include '../includes/header_admin.php';

// Prefill values
$name = $_POST['name'] ?? $student['Name'];
$email = $_POST['email'] ?? $student['Email'];
$dob = $_POST['dob'] ?? $student['DateOfBirth'];
$contact_no = $_POST['contact_no'] ?? $student['ContactNo'];
$address = $_POST['address'] ?? $student['Address'];
$class_grade = $_POST['class_grade'] ?? $student['ClassGrade'];
$academic_year = $_POST['academic_year'] ?? $student['AcademicYear'];
$section = $_POST['section'] ?? $student['Section'];
$roll_number = $_POST['roll_number'] ?? $student['RollNumber'];
$admission_date = $_POST['admission_date'] ?? $student['AdmissionDate'];
$status = $_POST['status'] ?? $student['Status'];
?>

<div class="breadcrumb">
    <a href="dashboard.php" class="text-muted">Dashboard</a> › <a href="all-students.php" class="text-muted">All Students</a> › Edit Student
</div>

<div class="admin-panel">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="admin-panel-body">
            <h3 class="mb-3 admin-section-title">Section 1 - Personal Information</h3>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                    <?php if(isset($errors['name'])) echo "<span class='form-error'>{$errors['name']}</span>"; ?>
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                    <?php if(isset($errors['email'])) echo "<span class='form-error'>{$errors['email']}</span>"; ?>
                </div>
            </div>
            
            <div class="soft-box">
                <details>
                    <summary>Reset Password (Optional)</summary>
                    <div class="row mt-2">
                        <div class="col-6 form-group">
                            <label class="form-label">New Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="new_password" class="form-control">
                                <span class="password-toggle">Show</span>
                            </div>
                            <?php if(isset($errors['new_password'])) echo "<span class='form-error'>{$errors['new_password']}</span>"; ?>
                        </div>
                        <div class="col-6 form-group">
                            <label class="form-label">Confirm New Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" class="form-control">
                                <span class="password-toggle">Show</span>
                            </div>
                            <?php if(isset($errors['confirm_password'])) echo "<span class='form-error'>{$errors['confirm_password']}</span>"; ?>
                        </div>
                    </div>
                </details>
            </div>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($dob) ?>">
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="tel" name="contact_no" class="form-control" value="<?= htmlspecialchars($contact_no) ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control"><?= htmlspecialchars($address) ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Profile Picture (Leave empty to keep current)</label>
                <?php if ($student['ProfilePicture']): ?>
                    <div class="profile-picture-wrap">
                        <img src="../<?= htmlspecialchars($student['ProfilePicture']) ?>" alt="Current Profile" class="current-profile-picture">
                    </div>
                <?php endif; ?>
                <input type="file" name="profile_picture" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            </div>
            
            <h3 class="mb-3 mt-2 admin-section-title">Section 2 - Academic Information</h3>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Class/Grade</label>
                    <select name="class_grade" class="form-control">
                        <option value="">Select...</option>
                        <?php
                        $grades = ['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
                        foreach ($grades as $g) {
                            $sel = ($class_grade == $g) ? 'selected' : '';
                            echo "<option value='$g' $sel>$g</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year" class="form-control">
                        <option value="">Select...</option>
                        <?php
                        $years = ['2023-2024', '2024-2025', '2025-2026'];
                        foreach ($years as $y) {
                            $sel = ($academic_year == $y) ? 'selected' : '';
                            echo "<option value='$y' $sel>$y</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Section</label>
                    <select name="section" class="form-control">
                        <option value="">Not Assigned</option>
                        <option value="A" <?= ($section == 'A') ? 'selected' : '' ?>>A</option>
                        <option value="B" <?= ($section == 'B') ? 'selected' : '' ?>>B</option>
                        <option value="C" <?= ($section == 'C') ? 'selected' : '' ?>>C</option>
                        <option value="D" <?= ($section == 'D') ? 'selected' : '' ?>>D</option>
                    </select>
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Roll Number</label>
                    <input type="text" name="roll_number" class="form-control" value="<?= htmlspecialchars($roll_number) ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Admission Date</label>
                    <input type="date" name="admission_date" class="form-control" value="<?= htmlspecialchars($admission_date) ?>">
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="Approved" <?= ($status == 'Approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="Pending" <?= ($status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Rejected" <?= ($status == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="admin-panel-footer">
            <a href="all-students.php" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

</div> <!-- Close content-area -->
</div> <!-- Close dashboard-layout -->
<script src="../assets/js/main.js"></script>
</body>
</html>
