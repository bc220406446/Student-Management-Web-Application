<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    if ($id)
        $pdo->prepare('DELETE FROM student WHERE StudentID = ?')->execute([$id]);
    set_flash_message('success', 'Student deleted.');
    header('Location: all-students.php');
    exit;
}
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? 'All';
$allowed = ['All', 'Approved', 'Not Approved', 'Pending'];
if (!in_array($status, $allowed, true))
    $status = 'All';
$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(StudentID = ? OR Name LIKE ? OR Email LIKE ? OR Department LIKE ?)';
    $params = [ctype_digit($search) ? (int) $search : 0, "%$search%", "%$search%", "%$search%"];
}
if ($status !== 'All') {
    $where[] = 'Status = ?';
    $params[] = $status;
}
$sql = 'SELECT StudentID, Name, Email, Department, Marks, Status FROM student' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY StudentID DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
$page_title = 'All Students';
include '../includes/header_admin.php';
?>
<div class="dashboard-panel">
    <div class="dashboard-panel-body">
        <div class="toolbar mb-3">
            <form method="GET" class="filter-form">
                <input name="search" class="form-control search-input" placeholder="ID, name, email, department"
                    value="<?= htmlspecialchars($search) ?>">
                <select name="status" class="form-control status-input">
                    <?php foreach ($allowed as $value): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $status === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($value) ?>
                        </option><?php endforeach; ?>
                </select>
                <button class="btn btn-outline">Filter</button>
            </form>
            <a href="add-student.php" class="btn btn-primary">Add Student</a>
        </div>
        <div class="table-wrapper all-students-table-wrapper">
            <table class="table all-students-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Marks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students):
                        foreach ($students as $s):
                            $badge = $s['Status'] === 'Approved' ? 'badge-success' : ($s['Status'] === 'Pending' ? 'badge-warning' : 'badge-error'); ?>
                            <tr>
                                <td><?= (int) $s['StudentID'] ?></td>
                                <td class="table-name"><?= htmlspecialchars($s['Name']) ?></td>
                                <td><?= htmlspecialchars($s['Email']) ?></td>
                                <td><?= htmlspecialchars($s['Department']) ?></td>
                                <td><?= number_format((float) $s['Marks']) ?></td>
                                <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($s['Status']) ?></span></td>
                                <td>
                                    <div class="action-group"><a href="edit-student.php?id=<?= (int) $s['StudentID'] ?>"
                                            class="btn btn-primary btn-sm">Edit</a><button class="btn btn-outline btn-sm"
                                            onclick="openDeleteModal(<?= (int) $s['StudentID'] ?>, <?= htmlspecialchars(json_encode($s['Name']), ENT_QUOTES, 'UTF-8') ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr><?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted empty-table-cell">No students found.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/modals/admin-delete-student.php'; ?>
</div>
</div>
<script src="../assets/js/main.js"></script>
<script>
    function openDeleteModal(id, name) {
        document.getElementById('deleteStudentId').value = id;
        document.getElementById('deleteModalMessage').textContent = `Delete ${name}? This cannot be undone.`;
        document.getElementById('deleteModal').classList.add('is-open');
    }
</script>
</body>

</html>