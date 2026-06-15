<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin_login();

// Handle Actions (same as dashboard, but redirects back here)
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
    header("Location: pending-registrations.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM student WHERE Status = 'Pending' ORDER BY CreatedAt ASC");
$pending_students = $stmt->fetchAll();

$page_title = "Pending Registrations";
include '../includes/header_admin.php';
?>

<?php if (count($pending_students) > 0): ?>
    <div class="review-banner">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <?= count($pending_students) ?> registration(s) are awaiting your review.
    </div>
    
    <div class="admin-panel">
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
                    <?php foreach ($pending_students as $student): ?>
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
                                    <button class="btn btn-outline btn-icon" title="View Full Record" onclick="openViewModal(<?= htmlspecialchars(json_encode($student)) ?>)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button class="btn btn-primary btn-sm" onclick="openApproveModal(<?= $student['StudentID'] ?>, '<?= htmlspecialchars(addslashes($student['Name'])) ?>', '<?= htmlspecialchars(addslashes($student['ClassGrade'])) ?>', '<?= htmlspecialchars(addslashes($student['Section'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($student['RollNumber'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($student['AdmissionDate'] ?? '')) ?>')">Approve</button>
                                    <button class="btn btn-outline btn-sm" onclick="openRejectModal(<?= $student['StudentID'] ?>, '<?= htmlspecialchars(addslashes($student['Name'])) ?>')">Reject</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <h3>All caught up.</h3>
        <p>No pending registrations at the moment.</p>
    </div>
<?php endif; ?>

<?php
include '../includes/modals/admin-view-student.php';
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
    
    // Pre-fill if values already assigned
    const sectionSelect = document.getElementById('approveSection');
    const rollInput = document.getElementById('approveRollNumber');
    const dateInput = document.getElementById('approveAdmissionDate');
    
    if (existingSection) {
        sectionSelect.value = existingSection;
    }
    if (existingRoll) {
        rollInput.value = existingRoll;
    }
    if (existingDate && existingDate !== '0000-00-00') {
        dateInput.value = existingDate;
    }
    
    document.getElementById('approveModal').classList.add('is-open');
}

function openRejectModal(id, name) {
    document.getElementById('rejectStudentId').value = id;
    document.getElementById('rejectModalMessage').textContent = `Are you sure you want to reject ${name}? They will not be able to log in.`;
    document.getElementById('rejectModal').classList.add('is-open');
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
