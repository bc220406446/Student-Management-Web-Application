<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'Student Dashboard';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Student Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php display_flash_message(); ?>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="logo-area">
                <a href="dashboard.php" class="logo-link">
                    <div class="logo-icon"><svg width="20" height="20" fill="none" stroke="white" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                            <path d="M6 12v5c3 3 9 3 12 0v-5" />
                        </svg></div>
                </a>
                <a href="dashboard.php"><span class="logo-text">Student Portal</span></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php"
                        class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="view-record.php" class="<?= $current_page === 'view-record.php' ? 'active' : '' ?>">My
                        Record</a></li>
                <li><a href="search-record.php"
                        class="<?= $current_page === 'search-record.php' ? 'active' : '' ?>">Search My Record</a></li>
                <li><a href="edit-profile.php"
                        class="<?= $current_page === 'edit-profile.php' ? 'active' : '' ?>">Update Personal Info</a>
                </li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <h2 class="topbar-title"><?= htmlspecialchars($page_title) ?></h2>
                <div class="topbar-actions dropdown-anchor">
                    <div class="avatar-chip" id="userAvatar"
                        title="<?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?>">
                        <?= htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? 'S', 0, 1))) ?></div>
                    <div class="dropdown-menu dropdown-admin" id="userDropdown">
                        <div class="dropdown-header">
                            <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?></strong><br><small
                                class="text-muted"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small></div>
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
            </header>
            <div class="content-area">