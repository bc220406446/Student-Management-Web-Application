<?php
// Load required files and restrict the page to logged-in students.
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_student_login();

$search_id = trim($_GET['student_id'] ?? '');
$student = null;
$search_error = '';

// Process a search only after a student ID has been entered.
if ($search_id !== '') {
    if (!ctype_digit($search_id)) {
        $search_error = 'Enter a valid numeric Student ID.';
    } elseif ((int) $search_id !== (int) $_SESSION['user_id']) {
        // Compare with the session ID to prevent access to another record.
        $search_error = 'You can only search for your own student record.';
    } else {
        // A prepared statement safely loads the current student's record.
        $stmt = $pdo->prepare('SELECT StudentID, Name, Email, Department, Marks, Status FROM student WHERE StudentID = ?');
        $stmt->execute([(int) $search_id]);
        $student = $stmt->fetch();
        if (!$student) $search_error = 'No record was found for that Student ID.';
    }
}

// Display the search form and its result in the student layout.
$page_title = 'Search My Record';
include '../includes/header_student.php';
?>
<div class="dashboard-panel">
    <div class="dashboard-panel-body">
        <div class="toolbar mb-3">
            <form method="GET" class="filter-form">
                <input type="number" min="1" name="student_id" class="form-control search-input" placeholder="Enter Student ID" value="<?= htmlspecialchars($search_id) ?>" required>
                <button class="btn btn-outline" type="submit">Filter</button>
            </form>
        </div>

        <?php if ($search_error): ?><p class="form-error mb-2"><?= htmlspecialchars($search_error) ?></p><?php endif; ?>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr><th>Student ID</th><th>Name</th><th>Email</th><th>Department</th><th>Marks</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if ($student):
                        // Choose a badge color that matches the account status.
                        $badge = $student['Status'] === 'Approved' ? 'badge-success' : ($student['Status'] === 'Pending' ? 'badge-warning' : 'badge-error');
                    ?>
                        <tr>
                            <td><?= (int) $student['StudentID'] ?></td>
                            <td class="table-name"><?= htmlspecialchars($student['Name']) ?></td>
                            <td><?= htmlspecialchars($student['Email']) ?></td>
                            <td><?= htmlspecialchars($student['Department']) ?></td>
                            <td><?= number_format((float) $student['Marks'], 2) ?></td>
                            <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($student['Status']) ?></span></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted empty-table-cell"><?= $search_id === '' ? 'Enter your Student ID to display your record.' : 'No record to display.' ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div></main></div><script src="../assets/js/main.js"></script></body></html>
