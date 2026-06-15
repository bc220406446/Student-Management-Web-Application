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

<div class="admin-panel">
    <div class="admin-panel-body">
    <div class="admin-toolbar mb-3">
        
        <form method="GET" action="" class="filter-form">
            <input type="text" name="search" class="form-control search-input" placeholder="Search name or ID..." value="<?= htmlspecialchars($search) ?>">
            <select name="status" class="form-control status-input">
                <option value="All" <?= $status_filter === 'All' ? 'selected' : '' ?>>All Status</option>
                <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" class="btn btn-outline">Filter</button>
            <?php if($search !== '' || $status_filter !== 'All'): ?>
                <a href="all-students.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>

        <a href="add-student.php" class="btn btn-primary">
            <svg class="link-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Student
        </a>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $index => $s): ?>
                        <tr>
                            <td class="text-muted"><?= $offset + $index + 1 ?></td>
                            <td class="table-name"><?= htmlspecialchars($s['Name']) ?></td>
                            <td><?= htmlspecialchars($s['Email']) ?></td>
                            <td>
                                <?php
                                $badge = $s['Status'] === 'Approved' ? 'badge-success' : ($s['Status'] === 'Pending' ? 'badge-warning' : 'badge-error');
                                echo "<span class='badge $badge'>{$s['Status']}</span>";
                                ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <button class="btn btn-outline btn-icon" title="View Full Record" onclick="openViewModal(<?= htmlspecialchars(json_encode($s)) ?>)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <a href="edit-student.php?id=<?= $s['StudentID'] ?>" class="btn btn-primary btn-icon" title="Edit">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>
                                    <button class="btn btn-primary btn-icon" title="Delete" onclick="openDeleteModal(<?= $s['StudentID'] ?>, '<?= htmlspecialchars(addslashes($s['Name'])) ?>')">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted empty-table-cell">No students found matching your criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <div class="pagination-row mt-2">
            <div class="text-muted text-sm">Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $total_rows) ?> of <?= $total_rows ?> entries</div>
            <div class="pagination-actions">
                <?php
                $q = $_GET;
                if ($page > 1) {
                    $q['page'] = $page - 1;
                    $prev_url = '?' . http_build_query($q);
                    echo "<a href='$prev_url' class='btn btn-outline btn-sm'>Previous</a>";
                }
                if ($page < $total_pages) {
                    $q['page'] = $page + 1;
                    $next_url = '?' . http_build_query($q);
                    echo "<a href='$next_url' class='btn btn-outline btn-sm'>Next</a>";
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>

<?php
include '../includes/modals/admin-delete-student.php';
include '../includes/modals/admin-view-student.php';
?>

</div> <!-- Close content-area -->
</div> <!-- Close dashboard-layout -->
<script src="../assets/js/main.js"></script>
<script>
function openDeleteModal(id, name) {
    document.getElementById('deleteStudentId').value = id;
    document.getElementById('deleteModalMessage').textContent = `Are you sure you want to permanently delete ${name}? This action cannot be undone.`;
    document.getElementById('deleteModal').classList.add('is-open');
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

function displayValue(value, fallback = '-') {
    return escapeHtml(value || fallback);
}

function academicValue(value) {
    return displayValue(value, 'Not Assigned');
}

function hasRealProfileImage(s) {
    const profilePicture = s.ProfilePicture || '';
    return s.Status === 'Approved'
        && profilePicture
        && !profilePicture.toLowerCase().includes('no profile picture')
        && !profilePicture.toLowerCase().includes('no-profile-picture');
}

function openViewModal(s) {
    const year = s.CreatedAt ? s.CreatedAt.substring(0, 4) : new Date().getFullYear();
    const stuId = s.Status === 'Approved' ? `#STU-${year}-${String(s.StudentID).padStart(5,'0')}` : 'Pending Approval';
    const statusBadge = {Approved:'badge-success',Pending:'badge-warning',Rejected:'badge-error'}[s.Status] || 'badge-warning';
    const profileHtml = hasRealProfileImage(s)
        ? `<img src="../${escapeHtml(s.ProfilePicture)}" alt="${escapeHtml(s.Name)} profile image" class="student-modal-avatar">`
        : `<div class="student-modal-avatar-empty">No Profile Image</div>`;

    document.getElementById('viewModalContent').innerHTML = `
        <div class="student-modal-section">
            <div class="student-modal-profile">
                ${profileHtml}
                <div>
                    <h3 class="student-modal-profile-name">${escapeHtml(s.Name)}</h3>
                    <span class="badge ${statusBadge}">${escapeHtml(s.Status)}</span>
                </div>
            </div>
        </div>

        <div class="student-modal-section">
            <div class="student-modal-section-title">Personal Information</div>
            <div class="detail-grid">
                <div><div class="detail-label">EMAIL</div><div>${displayValue(s.Email)}</div></div>
                <div><div class="detail-label">DATE OF BIRTH</div><div>${displayValue(s.DateOfBirth)}</div></div>
                <div><div class="detail-label">CONTACT NO</div><div>${displayValue(s.ContactNo)}</div></div>
                <div><div class="detail-label">APPLIED ON</div><div>${displayValue(s.CreatedAt ? s.CreatedAt.substring(0,10) : '')}</div></div>
                <div class="detail-grid-full"><div class="detail-label">ADDRESS</div><div>${displayValue(s.Address)}</div></div>
            </div>
        </div>

        <div class="student-modal-section">
            <div class="student-modal-section-title">Academic Record</div>
            <div class="detail-grid">
                <div><div class="detail-label">STUDENT ID</div><div class="detail-value">${escapeHtml(stuId)}</div></div>
                <div><div class="detail-label">CLASS / GRADE</div><div>${academicValue(s.ClassGrade)}</div></div>
                <div><div class="detail-label">SECTION</div><div>${academicValue(s.Section)}</div></div>
                <div><div class="detail-label">ROLL NUMBER</div><div>${academicValue(s.RollNumber)}</div></div>
                <div><div class="detail-label">ADMISSION DATE</div><div>${academicValue(s.AdmissionDate)}</div></div>
                <div><div class="detail-label">ACADEMIC YEAR</div><div>${academicValue(s.AcademicYear)}</div></div>
            </div>
        </div>
    `;
    document.getElementById('viewStudentModal').classList.add('is-open');
}
</script>
</body>
</html>
