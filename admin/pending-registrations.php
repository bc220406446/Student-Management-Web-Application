<?php
// Load required files and restrict this page to administrators.
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin_login();

// Process an approval or rejection submitted by a confirmation modal.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    if ($student_id && in_array($action, ['approve', 'reject'], true)) {
        // Convert the requested action into the status stored in the database.
        $status = $action === 'approve' ? 'Approved' : 'Not Approved';
        $update = $pdo->prepare("UPDATE student SET Status = ? WHERE StudentID = ? AND Status = 'Pending'");
        $update->execute([$status, $student_id]);
        set_flash_message('success', $action === 'approve' ? 'Registration approved.' : 'Registration marked as not approved.');
    }
    header('Location: pending-registrations.php');
    exit;
}

// Show only registrations that still require an administrator decision.
$pending_students = $pdo->query("SELECT StudentID, Name, Email, Department, Marks, Status FROM student WHERE Status = 'Pending' ORDER BY StudentID ASC")->fetchAll();
$page_title = 'Pending Registrations';
include '../includes/header_admin.php';
?>
<?php if ($pending_students): ?>
    <div class="review-banner"><?= count($pending_students) ?> registration(s) awaiting a decision.</div><?php endif; ?>
<div class="dashboard-panel">
    <div class="dashboard-panel-body">
        <div class="table-wrapper all-students-table-wrapper">
            <table class="table all-students-table pending-registrations-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Marks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody><?php if ($pending_students):
                    foreach ($pending_students as $s): ?>
                            <tr>
                                <td><?= (int) $s['StudentID'] ?></td>
                                <td class="table-name"><?= htmlspecialchars($s['Name']) ?></td>
                                <td><?= htmlspecialchars($s['Email']) ?></td>
                                <td><?= htmlspecialchars($s['Department']) ?></td>
                                <td><?= number_format((float) $s['Marks']) ?></td>
                                <td>
                                    <div class="action-group"><button class="btn btn-primary btn-sm"
                                            onclick="openApproveModal(<?= (int) $s['StudentID'] ?>, <?= htmlspecialchars(json_encode($s['Name']), ENT_QUOTES, 'UTF-8') ?>)">Approve</button><button
                                            class="btn btn-outline btn-sm"
                                            onclick="openRejectModal(<?= (int) $s['StudentID'] ?>, <?= htmlspecialchars(json_encode($s['Name']), ENT_QUOTES, 'UTF-8') ?>)">Not
                                            Approve</button></div>
                                </td>
                            </tr><?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted empty-table-cell">No pending registrations.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/modals/admin-approve-registration.php';
include '../includes/modals/admin-reject-registration.php'; ?>
</div>
</div>
<script src="../assets/js/main.js"></script>
<script>
    // Fill and open the correct confirmation modal for the selected student.
    function openApproveModal(id, name) { document.getElementById('approveStudentId').value = id; document.getElementById('approveModalMessage').textContent = `Approve ${name}'s registration?`; document.getElementById('approveModal').classList.add('is-open'); }
    function openRejectModal(id, name) { document.getElementById('rejectStudentId').value = id; document.getElementById('rejectModalMessage').textContent = `Mark ${name}'s registration as not approved?`; document.getElementById('rejectModal').classList.add('is-open'); }
</script>
</body>

</html>
