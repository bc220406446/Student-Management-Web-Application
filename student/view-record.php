<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_student_login();

$stmt = $pdo->prepare('SELECT StudentID, Name, Email, Department, Marks, Status FROM student WHERE StudentID = ?');
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
if (!$student) { session_destroy(); header('Location: login.php'); exit; }

$badge = $student['Status'] === 'Approved' ? 'badge-success' : ($student['Status'] === 'Pending' ? 'badge-warning' : 'badge-error');
$page_title = 'My Record';
include '../includes/header_student.php';
?>
<div class="dashboard-panel">
    <div class="dashboard-panel-body">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr><th>Student ID</th><th>Name</th><th>Email</th><th>Department</th><th>Marks</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= (int) $student['StudentID'] ?></td>
                        <td class="table-name"><?= htmlspecialchars($student['Name']) ?></td>
                        <td><?= htmlspecialchars($student['Email']) ?></td>
                        <td><?= htmlspecialchars($student['Department']) ?></td>
                        <td><?= number_format((float) $student['Marks'], 2) ?></td>
                        <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($student['Status']) ?></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div></main></div><script src="../assets/js/main.js"></script></body></html>
