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
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Admin Dashboard' ?> - Student Management System
    </title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php display_flash_message(); ?>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-area">
                <a href="dashboard.php" class="logo-link">
                    <div class="logo-icon">
                        <svg width="20" height="20" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                            <path d="M6 12v5c3 3 9 3 12 0v-5" />
                        </svg>
                    </div>
                </a>
                <a href="dashboard.php"><span class="logo-text">Admin Dashboard</span></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard
                        Overview</a></li>
                <li><a href="all-students.php"
                        class="<?= ($current_page == 'all-students.php' || $current_page == 'edit-student.php') ? 'active' : '' ?>">All
                        Students</a></li>
                <li>
                    <a href="pending-registrations.php"
                        class="<?= $current_page == 'pending-registrations.php' ? 'active' : '' ?>">
                        Pending Registrations
                    </a>
                </li>
                <li><a href="add-student.php" class="<?= $current_page == 'add-student.php' ? 'active' : '' ?>">Add
                        Student</a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="topbar">
                <h2 class="topbar-title">
                    <?= isset($page_title) ? ucwords(strtolower(htmlspecialchars($page_title))) : 'Dashboard' ?></h2>
                <div class="topbar-actions">
                    <!-- Admin Icon Top Right -->
                    <div class="dropdown-anchor">
                        <div class="avatar-chip" id="adminAvatar"
                            title="<?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>">
                            A
                        </div>
                        <div class="dropdown-menu dropdown-admin" id="adminDropdown">
                            <div class="dropdown-header">
                                <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></strong><br>
                                <small class="text-muted">Administrator</small>
                            </div>
                            <a href="logout.php" class="dropdown-item text-danger">
                                <svg class="link-icon" width="16" height="16" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-area">