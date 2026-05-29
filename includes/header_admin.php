<?php
// Determine active page for sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Count pending registrations for badge
$pending_stmt = $pdo->query("SELECT COUNT(*) FROM student WHERE Status = 'Pending'");
$pending_count = $pending_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Admin Dashboard' ?> - Student Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php display_flash_message(); ?>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-area" style="margin-bottom:2rem;">
                <div class="logo-icon" style="background:rgba(255,255,255,0.15);">
                    <svg width="20" height="20" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <a href="dashboard.php" style="color: var(--text-primary);"><span class="logo-text" style="color:white; font-size:1rem;">Admin Dashboard</span></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:0.5rem;flex-shrink:0;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Dashboard Overview
                </a></li>
                <li><a href="all-students.php" class="<?= ($current_page == 'all-students.php' || $current_page == 'edit-student.php') ? 'active' : '' ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:0.5rem;flex-shrink:0;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    All Students
                </a></li>
                <li>
                    <a href="pending-registrations.php" class="<?= $current_page == 'pending-registrations.php' ? 'active' : '' ?>">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:0.5rem;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Pending Registrations
                        <?php if ($pending_count > 0): ?>
                            <span class="badge badge-warning" style="margin-left:auto;"><?= $pending_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="add-student.php" class="<?= $current_page == 'add-student.php' ? 'active' : '' ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:0.5rem;flex-shrink:0;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Student
                </a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="topbar">
                <h2 style="margin:0; font-family:var(--font-heading); font-size:1.25rem;"><?= isset($page_title) ? ucwords(strtolower(htmlspecialchars($page_title))) : 'Dashboard' ?></h2>
                <div style="display:flex; align-items:center; gap:1rem;">
                    <span class="text-muted text-sm"><?= date('l, F j, Y') ?></span>
                    <!-- Admin Icon Top Right -->
                    <div style="position:relative;">
                        <div class="avatar-chip" id="adminAvatar" style="cursor:pointer;" title="<?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>">
                            A
                        </div>
                        <div class="dropdown-menu" id="adminDropdown" style="top:50px; right:0;">
                            <div class="dropdown-header">
                                <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></strong><br>
                                <small class="text-muted">Administrator</small>
                            </div>
                            <a href="logout.php" class="dropdown-item text-danger">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:0.5rem;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-area">
