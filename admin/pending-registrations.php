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
    <div style="background: var(--warning); color: white; padding: 1rem 1.5rem; border-radius: var(--radius-md); margin-bottom: 2rem; font-weight: 500; display: flex; align-items: center; gap: 0.75rem;">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <?= count($pending_students) ?> registration(s) are awaiting your review.
    </div>
    
    <div class="card" style="padding: 0;">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Date of Birth</th>
                        <th>Class Applied</th>
                        <th>Academic Year</th>
                        <th>Date Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_students as $student): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:0.75rem;">
                                    <div class="avatar-chip" style="width:32px; height:32px; font-size:0.875rem;">
                                        <?= substr(htmlspecialchars($student['Name']), 0, 1) ?>
                                    </div>
                                    <span style="font-weight:500;"><?= htmlspecialchars($student['Name']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($student['Email']) ?></td>
                            <td><?= htmlspecialchars($student['DateOfBirth']) ?></td>
                            <td><?= htmlspecialchars($student['ClassGrade']) ?></td>
                            <td><?= htmlspecialchars($student['AcademicYear']) ?></td>
                            <td class="text-muted"><?= date('M j, Y', strtotime($student['CreatedAt'])) ?></td>
                            <td>
                                <div style="display:flex; gap:0.5rem;">
                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;" title="View Full Record" onclick="openViewModal(<?= htmlspecialchars(json_encode($student)) ?>)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="openApproveModal(<?= $student['StudentID'] ?>, '<?= htmlspecialchars(addslashes($student['Name'])) ?>', '<?= htmlspecialchars(addslashes($student['ClassGrade'])) ?>', '<?= htmlspecialchars(addslashes($student['Section'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($student['RollNumber'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($student['AdmissionDate'] ?? '')) ?>')">Approve</button>
                                    <button class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="openRejectModal(<?= $student['StudentID'] ?>, '<?= htmlspecialchars(addslashes($student['Name'])) ?>')">Reject</button>
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
        <div style="margin-bottom: 1.5rem; color: var(--success);">
            <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <h3>All caught up.</h3>
        <p>No pending registrations at the moment.</p>
    </div>
<?php endif; ?>

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

<!-- Approve Modal -->
<div class="modal-overlay" id="approveModal">
    <div class="modal-card">
        <h3 class="mb-3" id="approveModalTitle">Approve Registration</h3>
        <p class="text-muted mb-3" id="approveModalSub">Student details...</p>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="student_id" id="approveStudentId">
            
            <div class="row">
                <div class="col-6 form-group">
                    <label class="form-label">Assign Section</label>
                    <select name="section" id="approveSection" class="form-control" required>
                        <option value="">Select...</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <div class="col-6 form-group">
                    <label class="form-label">Assign Roll Number</label>
                    <input type="text" name="roll_number" id="approveRollNumber" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Admission Date</label>
                <input type="date" name="admission_date" id="approveAdmissionDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-success">Confirm Approval</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal">
    <div class="modal-card">
        <h3 class="mb-3">Reject Registration</h3>
        <p class="mb-3" id="rejectModalMessage">Are you sure you want to reject this registration?</p>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="student_id" id="rejectStudentId">
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

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
    
    document.getElementById('approveModal').style.display = 'flex';
}

function openRejectModal(id, name) {
    document.getElementById('rejectStudentId').value = id;
    document.getElementById('rejectModalMessage').textContent = `Are you sure you want to reject ${name}? They will not be able to log in.`;
    document.getElementById('rejectModal').style.display = 'flex';
}

function openViewModal(s) {
    document.getElementById('viewModalTitle').textContent = s.Name;
    document.getElementById('viewModalContent').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">EMAIL</div><div>${s.Email}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">DATE OF BIRTH</div><div>${s.DateOfBirth || '-'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">CONTACT NO</div><div>${s.ContactNo || '-'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">CLASS / GRADE</div><div>${s.ClassGrade || '-'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">ACADEMIC YEAR</div><div>${s.AcademicYear || '-'}</div></div>
            <div><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">REGISTERED</div><div>${s.CreatedAt ? s.CreatedAt.substring(0,10) : '-'}</div></div>
            <div style="grid-column:span 2;"><div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem;">ADDRESS</div><div>${s.Address || '-'}</div></div>
        </div>
    `;
    document.getElementById('viewStudentModal').style.display = 'flex';
}
</script>
</body>
</html>
