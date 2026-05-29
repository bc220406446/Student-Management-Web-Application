<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin_login();

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $student_id = $_POST['student_id'] ?? 0;
    $delete = $pdo->prepare("DELETE FROM student WHERE StudentID = ?");
    $delete->execute([$student_id]);
    set_flash_message('success', 'Student record deleted permanently.');
    header("Location: all-students.php");
    exit;
}

// Search and Filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'All';

$where_clauses = [];
$params = [];

if ($search !== '') {
    // Parse full ID format: #STU-2026-00001 → extract numeric ID
    $search_id_numeric = null;
    if (preg_match('/#?STU-\d{4}-(\d+)/i', $search, $matches)) {
        $search_id_numeric = (int)$matches[1];
    }
    if ($search_id_numeric !== null) {
        $where_clauses[] = "StudentID = ?";
        $params[] = $search_id_numeric;
    } else {
        $where_clauses[] = "(Name LIKE ? OR StudentID LIKE ? OR Email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
}

if ($status_filter !== 'All') {
    $where_clauses[] = "Status = ?";
    $params[] = $status_filter;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) FROM student $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT * FROM student $where_sql ORDER BY StudentID DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$page_title = "All Students";
include '../includes/header_admin.php';
?>

<div class="card" style="padding: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        
        <form method="GET" action="" style="display: flex; gap: 1rem; align-items: center;">
            <input type="text" name="search" class="form-control" placeholder="Search name or ID..." value="<?= htmlspecialchars($search) ?>" style="width: 250px;">
            <select name="status" class="form-control" style="width: 150px;">
                <option value="All" <?= $status_filter === 'All' ? 'selected' : '' ?>>All Status</option>
                <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if($search !== '' || $status_filter !== 'All'): ?>
                <a href="all-students.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>

        <a href="add-student.php" class="btn btn-accent">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:0.5rem;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Student
        </a>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $index => $s): ?>
                        <tr>
                            <td class="text-muted"><?= $offset + $index + 1 ?></td>
                            <td><?= $s['Status'] === 'Approved' ? '#STU-' . date('Y', strtotime($s['CreatedAt'])) . '-' . str_pad($s['StudentID'], 5, '0', STR_PAD_LEFT) : '-' ?></td>
                            <td style="font-weight:500;"><?= htmlspecialchars($s['Name']) ?></td>
                            <td><?= htmlspecialchars($s['Email']) ?></td>
                            <td><?= htmlspecialchars($s['ClassGrade']) ?></td>
                            <td><?= htmlspecialchars($s['Section'] ?? '-') ?></td>
                            <td>
                                <?php
                                $badge = $s['Status'] === 'Approved' ? 'badge-success' : ($s['Status'] === 'Pending' ? 'badge-warning' : 'badge-error');
                                echo "<span class='badge $badge'>{$s['Status']}</span>";
                                ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:0.5rem;">
                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;" title="View Full Record" onclick="openViewModal(<?= htmlspecialchars(json_encode($s)) ?>)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <a href="edit-student.php?id=<?= $s['StudentID'] ?>\" class="btn btn-accent" style="padding: 0.25rem 0.5rem;" title="Edit">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>
                                    <button class="btn btn-danger" style="padding: 0.25rem 0.5rem;" title="Delete" onclick="openDeleteModal(<?= $s['StudentID'] ?>, '<?= htmlspecialchars(addslashes($s['Name'])) ?>')">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 2rem;">No students found matching your criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <div class="text-muted text-sm">Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $total_rows) ?> of <?= $total_rows ?> entries</div>
            <div style="display: flex; gap: 0.5rem;">
                <?php
                $q = $_GET;
                if ($page > 1) {
                    $q['page'] = $page - 1;
                    $prev_url = '?' . http_build_query($q);
                    echo "<a href='$prev_url' class='btn btn-outline' style='padding: 0.25rem 0.75rem;'>Previous</a>";
                }
                if ($page < $total_pages) {
                    $q['page'] = $page + 1;
                    $next_url = '?' . http_build_query($q);
                    echo "<a href='$next_url' class='btn btn-outline' style='padding: 0.25rem 0.75rem;'>Next</a>";
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-card">
        <h3 class="mb-3 text-danger">Delete Student</h3>
        <p class="mb-3" id="deleteModalMessage">Are you sure you want to permanently delete this student? This action cannot be undone.</p>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="student_id" id="deleteStudentId">
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-danger">Delete Permanently</button>
            </div>
        </form>
    </div>
</div>

<!-- View Student Modal -->
<div class="modal-overlay" id="viewStudentModal">
    <div class="modal-card" style="max-width:600px; width:90%;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h3 style="margin:0;" id="viewModalTitle">Student Record</h3>
            <button class="btn btn-outline" style="padding:0.25rem 0.75rem;" data-modal-close>✕</button>
        </div>
        <div id="viewModalContent"></div>
    </div>
</div>

</div> <!-- Close content-area -->
</div> <!-- Close dashboard-layout -->
<script src="../assets/js/main.js"></script>
<script>
function openDeleteModal(id, name) {
    document.getElementById('deleteStudentId').value = id;
    document.getElementById('deleteModalMessage').textContent = `Are you sure you want to permanently delete ${name}? This action cannot be undone.`;
    document.getElementById('deleteModal').style.display = 'flex';
}

function openViewModal(s) {
    const year = s.CreatedAt ? s.CreatedAt.substring(0, 4) : new Date().getFullYear();
    const stuId = s.Status === 'Approved' ? `#STU-${year}-${String(s.StudentID).padStart(5,'0')}` : 'Pending Approval';
    const statusBadge = {Approved:'badge-success',Pending:'badge-warning',Rejected:'badge-error'}[s.Status] || 'badge-warning';
    document.getElementById('viewModalTitle').textContent = s.Name;
    document.getElementById('viewModalContent').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">STUDENT ID</div><div style="font-weight:500;">${stuId}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">STATUS</div><span class="badge ${statusBadge}">${s.Status}</span></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">EMAIL</div><div>${s.Email}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">DATE OF BIRTH</div><div>${s.DateOfBirth || '-'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">CONTACT NO</div><div>${s.ContactNo || '-'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">CLASS / GRADE</div><div>${s.ClassGrade || '-'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">SECTION</div><div>${s.Section || 'Not Assigned'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">ROLL NUMBER</div><div>${s.RollNumber || 'Not Assigned'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">ADMISSION DATE</div><div>${s.AdmissionDate || 'Pending'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">ACADEMIC YEAR</div><div>${s.AcademicYear || '-'}</div></div>
            <div style="grid-column:span 2;"><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">ADDRESS</div><div>${s.Address || '-'}</div></div>
        </div>
        <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border-color);font-size:0.75rem;color:var(--text-muted);">Registered: ${s.CreatedAt ? s.CreatedAt.substring(0,10) : '-'}</div>
    `;
    document.getElementById('viewStudentModal').style.display = 'flex';
}
</script>
</body>
</html>
