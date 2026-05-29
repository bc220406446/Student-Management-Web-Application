<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $dob = trim($_POST['dob'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $class_grade = trim($_POST['class_grade'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $section = trim($_POST['section'] ?? '');
    $roll_number = trim($_POST['roll_number'] ?? '');
    $admission_date = trim($_POST['admission_date'] ?? '');
    $status = $_POST['status'] ?? 'Approved';

    if (empty($name)) $errors['name'] = "Full Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Valid Email is required.";
    if (strlen($password) < 8) $errors['password'] = "Password must be at least 8 chars.";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT StudentID FROM student WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors['email'] = "Email already exists.";
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $approved_by = ($status === 'Approved') ? $_SESSION['admin_id'] : null;
        
        // Handle profile picture
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/profile-pictures/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $file_name)) {
                $profile_picture = 'uploads/profile-pictures/' . $file_name;
            }
        }

        $insert = $pdo->prepare("INSERT INTO student 
            (Name, Email, Password, ProfilePicture, DateOfBirth, ContactNo, Address, ClassGrade, AcademicYear, Section, RollNumber, AdmissionDate, Status, ApprovedBy) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        if ($insert->execute([$name, $email, $hashed, $profile_picture, $dob ?: null, $contact_no, $address, $class_grade, $academic_year, $section ?: null, $roll_number ?: null, $admission_date ?: null, $status, $approved_by])) {
            set_flash_message('success', 'Student record created successfully.');
            header("Location: all-students.php");
            exit;
        } else {
            set_flash_message('error', 'Server error while creating student.');
        }
    }
}

$page_title = "Add New Student";
include '../includes/header_admin.php';
?>

<div style="margin-bottom: 1rem; color: var(--text-muted); font-size: 0.875rem;">
    <a href="dashboard.php" class="text-muted">Dashboard</a> › <a href="all-students.php" class="text-muted">All Students</a> › Add Student
</div>

<div class="card" style="padding: 0;">
    <form method="POST" action="" enctype="multipart/form-data">
        <div style="padding: 2rem;">
            <h3 class="mb-3" style="color: var(--primary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Section 1 — Personal Information</h3>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required>
                    <?php if(isset($errors['name'])) echo "<span class='form-error'>{$errors['name']}</span>"; ?>
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    <?php if(isset($errors['email'])) echo "<span class='form-error'>{$errors['email']}</span>"; ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Password *</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" class="form-control" required>
                        <span class="password-toggle">Show</span>
                    </div>
                    <?php if(isset($errors['password'])) echo "<span class='form-error'>{$errors['password']}</span>"; ?>
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Confirm Password *</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" class="form-control" required>
                        <span class="password-toggle">Show</span>
                    </div>
                    <?php if(isset($errors['confirm_password'])) echo "<span class='form-error'>{$errors['confirm_password']}</span>"; ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($dob ?? '') ?>">
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="tel" name="contact_no" class="form-control" value="<?= htmlspecialchars($contact_no ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control"><?= htmlspecialchars($address ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Profile Picture (Optional)</label>
                <input type="file" name="profile_picture" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            </div>
            
            <h3 class="mb-3 mt-2" style="color: var(--primary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Section 2 — Academic Information</h3>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Class/Grade</label>
                    <select name="class_grade" class="form-control">
                        <option value="">Select...</option>
                        <?php
                        $grades = ['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
                        foreach ($grades as $g) {
                            $sel = (isset($class_grade) && $class_grade == $g) ? 'selected' : '';
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
                            $sel = (isset($academic_year) && $academic_year == $y) ? 'selected' : '';
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
                        <option value="A" <?= (isset($section) && $section == 'A') ? 'selected' : '' ?>>A</option>
                        <option value="B" <?= (isset($section) && $section == 'B') ? 'selected' : '' ?>>B</option>
                        <option value="C" <?= (isset($section) && $section == 'C') ? 'selected' : '' ?>>C</option>
                        <option value="D" <?= (isset($section) && $section == 'D') ? 'selected' : '' ?>>D</option>
                    </select>
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Roll Number</label>
                    <input type="text" name="roll_number" class="form-control" value="<?= htmlspecialchars($roll_number ?? '') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Admission Date</label>
                    <input type="date" name="admission_date" class="form-control" value="<?= htmlspecialchars($admission_date ?? '') ?>">
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Initial Status</label>
                    <select name="status" class="form-control">
                        <option value="Approved" <?= (isset($status) && $status == 'Approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="Pending" <?= (isset($status) && $status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Rejected" <?= (isset($status) && $status == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="card-header" style="border-bottom: none; border-top: 1px solid var(--border-color); padding: 1.5rem 2rem; margin: 0; background: #fafafa; border-radius: 0 0 var(--radius-lg) var(--radius-lg); display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="all-students.php" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Student</button>
        </div>
    </form>
</div>

</div> <!-- Close content-area -->
</div> <!-- Close dashboard-layout -->
<script src="../assets/js/main.js"></script>
</body>
</html>
