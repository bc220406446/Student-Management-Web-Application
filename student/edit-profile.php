<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_student_login();

// Fetch student
$stmt = $pdo->prepare("SELECT * FROM student WHERE StudentID = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Profile picture upload
    $profile_picture = $student['ProfilePicture'];
    $upload_error = false;
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = dirname(__DIR__) . '/uploads/profile-pictures/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_name = $_SESSION['user_id'] . '_' . time() . '_' . basename($_FILES['profile_picture']['name']);
            $target_path = $upload_dir . $file_name;
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $real_type = $finfo->file($_FILES['profile_picture']['tmp_name']);
            if (in_array($real_type, $allowed_types) && $_FILES['profile_picture']['size'] <= 2 * 1024 * 1024) {
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                    $profile_picture = 'uploads/profile-pictures/' . $file_name;
                } else {
                    $upload_error = true;
                    set_flash_message('error', 'Failed to save the uploaded image. Check folder permissions.');
                }
            } else {
                $upload_error = true;
                set_flash_message('error', 'Invalid file type or size exceeds 2MB.');
            }
        } else {
            $upload_error = true;
            set_flash_message('error', 'Image upload failed. The file might be too large.');
        }
    }

    if (!$upload_error) {
        $update = $pdo->prepare("UPDATE student SET Name = ?, DateOfBirth = ?, ContactNo = ?, Address = ?, ProfilePicture = ? WHERE StudentID = ?");
        if ($update->execute([$name, $dob, $contact_no, $address, $profile_picture, $_SESSION['user_id']])) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_profile_pic'] = $profile_picture;
            set_flash_message('success', 'Your profile has been updated successfully.');
            header("Location: edit-profile.php");
            exit;
        }
    }
}
?>
<?php include '../includes/header_student.php'; ?>

<div class="breadcrumb">
    <a href="dashboard.php" class="text-muted">Dashboard</a> › Update Personal Information
</div>

<div class="card profile-edit-card">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="profile-edit-hero">
            <div class="profile-upload-wrap">
                <div class="profile-avatar-large profile-avatar-edit">
                    <?php if ($student['ProfilePicture']): ?>
                        <img id="profilePreview" src="../<?= htmlspecialchars($student['ProfilePicture']) ?>" alt="Profile" class="profile-image-fill">
                    <?php else: ?>
                        <svg id="profileSvg" width="50%" height="50%" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <img id="profilePreview" src="" alt="Profile" class="profile-image-fill is-hidden">
                    <?php endif; ?>
                </div>
                <!-- Upload Icon Overlay -->
                <label for="profileUpload" class="profile-upload-trigger">
                    <svg width="16" height="16" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </label>
                <input type="file" id="profileUpload" name="profile_picture" accept=".jpg,.jpeg,.png,.webp" class="hidden-file-input">
            </div>
            
            <h2 class="profile-edit-name"><?= htmlspecialchars($student['Name']) ?></h2>
            <p class="profile-edit-email"><?= htmlspecialchars($student['Email']) ?></p>
            <p class="profile-edit-help">Click the upload icon to change profile picture (Max 2MB)</p>
        </div>
        
        <div class="form-card-body">
            <h3 class="mb-3">Account Information</h3>
            <table class="info-table mb-3">
                <tr>
                    <td class="info-table-label">Email</td>
                    <td><?= htmlspecialchars($student['Email']) ?></td>
                </tr>
            </table>

            <h3 class="mb-3">Personal Details</h3>
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['Name'] ?? '') ?>" required>
            </div>
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($student['DateOfBirth'] ?? '') ?>" required>
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="tel" name="contact_no" class="form-control" value="<?= htmlspecialchars($student['ContactNo'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control"><?= htmlspecialchars($student['Address'] ?? '') ?></textarea>
            </div>
        </div>
        
        <div class="card-header form-card-footer">
            <div class="text-muted text-sm footer-note">Changes are saved to your profile immediately</div>
            <div class="footer-actions">
                <a href="dashboard.php" class="btn btn-outline btn-compact">Back</a>
                <button type="submit" class="btn btn-primary btn-compact">Save Changes</button>
            </div>
        </div>
    </form>
</div>

</div> <!-- Close content-area -->
<script src="../assets/js/main.js"></script>
<script>
document.getElementById('profileUpload').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(event) {
            var preview = document.getElementById('profilePreview');
            var svg = document.getElementById('profileSvg');
            if (preview) {
                preview.src = event.target.result;
                preview.classList.remove('is-hidden');
            }
            if (svg) {
                svg.classList.add('is-hidden');
            }
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
</body>
</html>
