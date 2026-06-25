<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin_login();
$total_students = $pdo->query('SELECT COUNT(*) FROM student')->fetchColumn();
$pending_students = $pdo->query("SELECT COUNT(*) FROM student WHERE Status = 'Pending'")->fetchColumn();
$approved_students = $pdo->query("SELECT COUNT(*) FROM student WHERE Status = 'Approved'")->fetchColumn();
$not_approved_students = $pdo->query("SELECT COUNT(*) FROM student WHERE Status = 'Not Approved'")->fetchColumn();
$page_title = 'Dashboard Overview';
include '../includes/header_admin.php';
?>
<div class="admin-stats-grid">
    <div class="admin-stat-card"><div><div class="stat-value"><?= number_format($total_students) ?></div><div class="stat-label">Total Students</div></div></div>
    <div class="admin-stat-card"><div><div class="stat-value"><?= number_format($pending_students) ?></div><div class="stat-label">Pending</div></div></div>
    <div class="admin-stat-card"><div><div class="stat-value"><?= number_format($approved_students) ?></div><div class="stat-label">Approved</div></div></div>
    <div class="admin-stat-card"><div><div class="stat-value"><?= number_format($not_approved_students) ?></div><div class="stat-label">Not Approved</div></div></div>
</div>

<div class="review-banner">Choose what you would like to do.</div>

<div class="dashboard-action-list">
    <section class="dashboard-panel dashboard-action-panel">
        <div class="dashboard-panel-header"><h3 class="dashboard-panel-title">Review Pending Registration</h3><a href="pending-registrations.php" class="btn btn-primary">Review Registrations</a></div>
    </section>
    <section class="dashboard-panel dashboard-action-panel">
        <div class="dashboard-panel-header"><h3 class="dashboard-panel-title">Manage Student Records</h3><a href="all-students.php" class="btn btn-primary">Manage Students</a></div>
    </section>
    <section class="dashboard-panel dashboard-action-panel">
        <div class="dashboard-panel-header"><h3 class="dashboard-panel-title">Create Student Account</h3><a href="add-student.php" class="btn btn-primary">Add Student</a></div>
    </section>
</div>
</div></div><script src="../assets/js/main.js"></script></body></html>
