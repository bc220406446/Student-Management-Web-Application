<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin_login();

// Handle Actions from Recent Registrations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $student_id = $_POST['student_id'] ?? 0;
    
    if ($_POST['action'] === 'approve') {
        $section = $_POST['section'] ?? '';
        $roll_number = $_POST['roll_number'] ?? '';
        $admission_date = $_POST['admission_date'] ?? date('Y-m-d');
        
        $update = $pdo->prepare("UPDATE student SET Status = 'Approved', Section = ?, RollNumber = ?, AdmissionDate = ?, ApprovedBy = ? WHERE StudentID = ?");
        $update->execute([$section, $roll_number, $admission_date, $_SESSION['admin_id'], $student_id]);
        set_flash_message('success', 'Student approved successfully.');
        
    } elseif ($_POST['action'] === 'reject') {
        $update = $pdo->prepare("UPDATE student SET Status = 'Rejected', ApprovedBy = ? WHERE StudentID = ?");
        $update->execute([$_SESSION['admin_id'], $student_id]);
        set_flash_message('success', 'Student registration rejected.');
    }
    header("Location: dashboard.php");
    exit;
}

// Fetch stats
$total_students = $pdo->query("SELECT COUNT(*) FROM student")->fetchColumn();
$pending_students = $pdo->query("SELECT COUNT(*) FROM student WHERE Status = 'Pending'")->fetchColumn();
$approved_students = $pdo->query("SELECT COUNT(*) FROM student WHERE Status = 'Approved'")->fetchColumn();
$rejected_students = $pdo->query("SELECT COUNT(*) FROM student WHERE Status = 'Rejected'")->fetchColumn();

// Fetch pending registrations
$pending_reg_stmt = $pdo->query("SELECT * FROM student WHERE Status = 'Pending' ORDER BY CreatedAt DESC LIMIT 5");
$pending_reg_students = $pending_reg_stmt->fetchAll();

$page_title = "Dashboard Overview";
include '../includes/header_admin.php';
?>

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div>
            <div class="stat-value"><?= number_format($total_students) ?></div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div>
            <div class="stat-value"><?= number_format($pending_students) ?></div>
            <div class="stat-label">Pending Approvals</div>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div>
            <div class="stat-value"><?= number_format($approved_students) ?></div>
            <div class="stat-label">Approved Students</div>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        </div>
        <div>
            <div class="stat-value"><?= number_format($rejected_students) ?></div>
            <div class="stat-label">Rejected Students</div>
        </div>
    </div>
</div>

<div class="admin-panel">
    <div class="admin-panel-header">
        <h3 class="admin-panel-title">Pending Registrations</h3>
        <a href="pending-registrations.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Class Applied</th>
                    <th>Applied Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pending_reg_students) > 0): ?>
                    <?php foreach ($pending_reg_students as $student): ?>
                        <tr>
                            <td>
                                <div class="inline-identity">
                                    <span class="table-name"><?= htmlspecialchars($student['Name']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($student['Email']) ?></td>
                            <td><?= htmlspecialchars($student['ClassGrade']) ?></td>
                            <td class="text-muted"><?= date('M j, Y', strtotime($student['CreatedAt'])) ?></td>
                            <td>
                                <div class="action-group">
                                    <button class="btn btn-primary btn-sm" onclick="openApproveModal(<?= $student['StudentID'] ?>, '<?= htmlspecialchars(addslashes($student['Name'])) ?>', '<?= htmlspecialchars(addslashes($student['ClassGrade'])) ?>', '<?= htmlspecialchars(addslashes($student['Section'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($student['RollNumber'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($student['AdmissionDate'] ?? '')) ?>')">Approve</button>
                                    <button class="btn btn-outline btn-sm" onclick="openRejectModal(<?= $student['StudentID'] ?>, '<?= htmlspecialchars(addslashes($student['Name'])) ?>')">Reject</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted empty-table-cell">No pending registrations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include '../includes/modals/admin-approve-registration.php';
include '../includes/modals/admin-reject-registration.php';
?>

</div> <!-- Close content-area -->
</div> <!-- Close dashboard-layout -->
<script src="../assets/js/main.js"></script>
<script>
function openApproveModal(id, name, grade, existingSection, existingRoll, existingDate) {
    document.getElementById('approveStudentId').value = id;
    document.getElementById('approveModalTitle').textContent = `Approve Registration — ${name}`;
    document.getElementById('approveModalSub').textContent = `Applied for: ${grade}`;
    const sectionSelect = document.getElementById('approveSection');
    const rollInput = document.getElementById('approveRollNumber');
    const dateInput = document.getElementById('approveAdmissionDate');
    if (existingSection) sectionSelect.value = existingSection;
    if (existingRoll) rollInput.value = existingRoll;
    if (existingDate && existingDate !== '0000-00-00') dateInput.value = existingDate;
    document.getElementById('approveModal').classList.add('is-open');
}

function openRejectModal(id, name) {
    document.getElementById('rejectStudentId').value = id;
    document.getElementById('rejectModalMessage').textContent = `Are you sure you want to reject ${name}? They will not be able to log in.`;
    document.getElementById('rejectModal').classList.add('is-open');
}
</script>
</body>
</html>

