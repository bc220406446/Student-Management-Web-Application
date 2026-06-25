<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_student_login();

$stmt = $pdo->prepare('SELECT StudentID, Name, Email, Department, Marks, Status FROM student WHERE StudentID = ?');
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
if (!$student) { session_destroy(); header('Location: login.php'); exit; }

$page_title = 'Student Dashboard';
include '../includes/header_student.php';
?>
<div class="review-banner">Welcome, <?= htmlspecialchars($student['Name']) ?>. Choose what you would like to do.</div>

<div class="dashboard-action-list">
    <section class="dashboard-panel dashboard-action-panel"><div class="dashboard-panel-header"><h3 class="dashboard-panel-title">My Student Record</h3><a href="view-record.php" class="btn btn-primary">View Record</a></div></section>
    <section class="dashboard-panel dashboard-action-panel"><div class="dashboard-panel-header"><h3 class="dashboard-panel-title">Search Record</h3><a href="search-record.php" class="btn btn-primary">Search by ID</a></div></section>
    <section class="dashboard-panel dashboard-action-panel"><div class="dashboard-panel-header"><h3 class="dashboard-panel-title">Personal Information</h3><a href="edit-profile.php" class="btn btn-primary">Update Info</a></div></section>
</div>
</div></main></div><script src="../assets/js/main.js"></script></body></html>
