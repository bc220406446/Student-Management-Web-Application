<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirect_if_student_logged_in();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $class_grade = trim($_POST['class_grade'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    if (empty($name)) $errors['name'] = "Full Name is required.";
    if (empty($dob)) $errors['dob'] = "Date of Birth is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Valid Email Address is required.";
    if (strlen($password) < 8) $errors['password'] = "Password must be at least 8 characters.";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";
    if (empty($class_grade)) $errors['class_grade'] = "Class/Grade is required.";
    if (empty($academic_year)) $errors['academic_year'] = "Academic Year is required.";

    // Check if email exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT StudentID FROM student WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = "Email is already registered.";
        }
    }

    // Insert if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO student 
            (Name, Email, Password, DateOfBirth, ContactNo, Address, ClassGrade, AcademicYear, Status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");
        
        if ($stmt->execute([$name, $email, $hashed_password, $dob, $contact_no, $address, $class_grade, $academic_year])) {
            set_flash_message('success', 'Registration submitted. Awaiting admin approval.');
            // Do not redirect to dashboard, stay here or redirect to login (specs say: Do not redirect to dashboard - student must wait for approval. Let's show flash and clear form)
            $name = $dob = $email = $class_grade = $academic_year = $contact_no = $address = ''; // reset
        } else {
            set_flash_message('error', 'Registration failed due to a server error.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php display_flash_message(); ?>
    <div class="split-layout">
        <div class="split-left">
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:2rem; font-family:var(--font-heading); font-size:1.5rem;">
                <span>Student Management System</span>
            </div>
            <span class="tag">Academic Portal</span>
            <h1 class="mb-2">Manage student records with ease</h1>
            <p style="color: rgba(255,255,255,0.8);" class="mb-3">Join our platform to access your academic journey and track your progress in real-time.</p>
            
            <ul style="list-style: none; color: rgba(255,255,255,0.8);">
                <li style="margin-bottom: 0.5rem;">• Easy online registration</li>
                <li style="margin-bottom: 0.5rem;">• Access to academic records</li>
                <li style="margin-bottom: 0.5rem;">• Seamless communication</li>
            </ul>
        </div>
        
        <div class="split-right">
            <div class="register-form-container card">
                <div style="display:flex; justify-content:center; gap:0.5rem; margin-bottom:1.5rem;">
                    <div style="width:8px; height:8px; border-radius:50%; background:var(--accent);"></div>
                    <div style="width:8px; height:8px; border-radius:50%; background:var(--border-color);"></div>
                    <div style="width:8px; height:8px; border-radius:50%; background:var(--border-color);"></div>
                </div>
                
                <h2 class="text-center mb-1">Create your account</h2>
                <p class="text-center text-muted mb-3">Fill in the details below to register as a student</p>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required>
                            <?php if(isset($errors['name'])) echo "<span class='form-error'>{$errors['name']}</span>"; ?>
                        </div>
                        <div class="col-6 form-group">
                            <label class="form-label">Date of Birth *</label>
                            <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($dob ?? '') ?>" required>
                            <?php if(isset($errors['dob'])) echo "<span class='form-error'>{$errors['dob']}</span>"; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required>
                        <?php if(isset($errors['email'])) echo "<span class='form-error'>{$errors['email']}</span>"; ?>
                    </div>
                    
                    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">
                    
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" class="form-control" required>
                            <span class="password-toggle">Show</span>
                        </div>
                        <?php if(isset($errors['password'])) echo "<span class='form-error'>{$errors['password']}</span>"; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" class="form-control" required>
                            <span class="password-toggle">Show</span>
                        </div>
                        <?php if(isset($errors['confirm_password'])) echo "<span class='form-error'>{$errors['confirm_password']}</span>"; ?>
                    </div>
                    
                    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">
                    
                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="form-label">Class / Grade *</label>
                            <select name="class_grade" class="form-control" required>
                                <option value="">Select...</option>
                                <?php
                                $grades = ['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
                                foreach ($grades as $g) {
                                    $sel = (isset($class_grade) && $class_grade == $g) ? 'selected' : '';
                                    echo "<option value='$g' $sel>$g</option>";
                                }
                                ?>
                            </select>
                            <?php if(isset($errors['class_grade'])) echo "<span class='form-error'>{$errors['class_grade']}</span>"; ?>
                        </div>
                        <div class="col-6 form-group">
                            <label class="form-label">Academic Year *</label>
                            <select name="academic_year" class="form-control" required>
                                <option value="">Select...</option>
                                <?php
                                $years = ['2023-2024', '2024-2025', '2025-2026'];
                                foreach ($years as $y) {
                                    $sel = (isset($academic_year) && $academic_year == $y) ? 'selected' : '';
                                    echo "<option value='$y' $sel>$y</option>";
                                }
                                ?>
                            </select>
                            <?php if(isset($errors['academic_year'])) echo "<span class='form-error'>{$errors['academic_year']}</span>"; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" name="contact_no" class="form-control" value="<?= htmlspecialchars($contact_no ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control"><?= htmlspecialchars($address ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block mt-2">Create Account</button>
                    
                    <div class="text-center mt-2">
                        <a href="login.php" class="text-sm">Already have an account? Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
