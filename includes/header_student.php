<?php
// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Fetch current student's password
    $stmt = $pdo->prepare("SELECT Password FROM student WHERE StudentID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $curr_student = $stmt->fetch();
    
    if ($curr_student && password_verify($current_password, $curr_student['Password'])) {
        if (strlen($new_password) >= 8 && $new_password === $confirm_password) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE student SET Password = ? WHERE StudentID = ?");
            $update->execute([$hashed, $_SESSION['user_id']]);
            set_flash_message('success', 'Password updated successfully.');
        } else {
            set_flash_message('error', 'New passwords do not match or are less than 8 characters.');
        }
    } else {
        set_flash_message('error', 'Current password is incorrect.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Student Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php display_flash_message(); ?>
    
    <?php include '../includes/modals/student-change-password.php'; ?>
    
    <div class="student-topbar">
        <div class="logo-area">
            <a href="dashboard.php">
                <div class="logo-icon">                
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            </a>
            <span class="logo-text">
                <a href="dashboard.php">Student Dashboard</a>
            </span>
        </div>
        
        <?php if (isset($show_search) && $show_search): ?>
        <form method="GET" action="dashboard.php" class="search-bar">
            <input type="text" name="search_id" placeholder="Search by Student ID e.g. #STU-2026-00001" value="<?= htmlspecialchars($_GET['search_id'] ?? '') ?>">
            <button type="submit">Search</button>
        </form>
        <?php endif; ?>

        <div class="avatar">
            <div class="avatar-chip" id="userAvatar">
                <?php 
                $avatar_src = '';
                if (isset($student) && !empty($student['ProfilePicture'])) {
                    $avatar_src = $student['ProfilePicture'];
                } elseif (!empty($_SESSION['user_profile_pic'])) {
                    $avatar_src = $_SESSION['user_profile_pic'];
                }
                if ($avatar_src): 
                ?>
                    <img src="../<?= htmlspecialchars($avatar_src) ?>" alt="Profile" class="avatar-chip-img">
                <?php else: ?>
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                <?php endif; ?>
            </div>
            
            <div class="dropdown-menu" id="userDropdown">
                <div class="dropdown-header">
                    <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
                </div>
                <a href="edit-profile.php" class="dropdown-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:0.5rem;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Update Personal Information
                </a>
                <a href="#" class="dropdown-item" data-modal-target="passwordModal">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:0.5rem;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Change Password
                </a>
                <div class="dropdown-separator"></div>
                <a href="logout.php" class="dropdown-item text-danger">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:0.5rem;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
    
    <div class="student-dashboard-content-area">
