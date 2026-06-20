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

<div class="dashboard-action-list">
    <section class="admin-panel dashboard-action-panel">
        <div class="admin-panel-header"><h3 class="admin-panel-title">Registration Decisions</h3><a href="pending-registrations.php" class="btn btn-primary">Review Pending</a></div>
    </section>
    <section class="admin-panel dashboard-action-panel">
        <div class="admin-panel-header"><h3 class="admin-panel-title">Student Records</h3><a href="all-students.php" class="btn btn-primary">Manage Students</a></div>
    </section>
    <section class="admin-panel dashboard-action-panel">
        <div class="admin-panel-header"><h3 class="admin-panel-title">Create Student</h3><a href="add-student.php" class="btn btn-primary">Add Student</a></div>
    </section>
</div>
</div></div><script src="../assets/js/main.js"></script></body></html>
