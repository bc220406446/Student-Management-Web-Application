<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_student_login();

// Fetch student data
$stmt = $pdo->prepare("SELECT * FROM student WHERE StudentID = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    die("Student record not found.");
}

$show_search = true;
$search_id = trim($_GET['search_id'] ?? '');
$search_record = null;
$search_error = '';

if ($search_id) {
    // Parse full ID format: #STU-2026-00001 or just numeric ID
    $parsed_id = $search_id;
    if (preg_match('/#?STU-\d{4}-(\d+)/i', $search_id, $matches)) {
        $parsed_id = (int)$matches[1]; // Extract numeric part
    }
    if ($parsed_id == $_SESSION['user_id']) {
        $search_record = $student;
    } else {
        $search_error = 'No record found for Student ID: ' . htmlspecialchars($search_id);
    }
}

// Password change logic is handled globally in includes/header_student.php
?>
<?php include '../includes/header_student.php'; ?>

<?php if ($search_error): ?>
    <div class="search-error">
        <?= htmlspecialchars($search_error) ?>
    </div>
<?php endif; ?>

<!-- Personal Information Card -->
<div class="student-profile-card">
    <div class="student-profile-banner"></div>
    <div class="student-profile-body">
        <div class="student-avatar-container">
            <?php if (!empty($student['ProfilePicture'])): ?>
                <img src="../<?= htmlspecialchars($student['ProfilePicture']) ?>" alt="Profile" class="profile-avatar">
            <?php else: ?>
                <svg width="50%" height="50%" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            <?php endif; ?>
        </div>
        
        <h2 class="student-profile-name"><?= htmlspecialchars($student['Name']) ?></h2>
        
        <!-- Personal Details Grid inside the Card -->
        <div class="personal-info-header">
            <h3 class="student-section-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--accent);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Personal Details
            </h3>
            
            <div class="info-tiles-grid">
                <!-- Email -->
                <div class="info-tile">
                    <div class="info-tile-icon-box">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div class="info-tile-content">
                        <span class="info-tile-label">Email Address</span>
                        <span class="info-tile-value"><?= htmlspecialchars($student['Email']) ?></span>
                    </div>
                </div>
                
                <!-- Contact No -->
                <div class="info-tile">
                    <div class="info-tile-icon-box">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <div class="info-tile-content">
                        <span class="info-tile-label">Contact Number</span>
                        <span class="info-tile-value"><?= htmlspecialchars($student['ContactNo'] ?? '-') ?></span>
                    </div>
                </div>
                
                <!-- Date of Birth -->
                <div class="info-tile">
                    <div class="info-tile-icon-box">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="info-tile-content">
                        <span class="info-tile-label">Date of Birth</span>
                        <span class="info-tile-value">
                            <?= $student['DateOfBirth'] ? date('F j, Y', strtotime($student['DateOfBirth'])) : '-' ?>
                        </span>
                    </div>
                </div>
                
                <!-- Address -->
                <div class="info-tile">
                    <div class="info-tile-icon-box">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div class="info-tile-content">
                        <span class="info-tile-label">Residential Address</span>
                        <span class="info-tile-value"><?= htmlspecialchars($student['Address'] ?? '-') ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="record-actions">
            <button id="viewRecordBtn" class="btn btn-outline btn-with-icon <?= $search_record ? 'is-hidden' : '' ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/><path d="M12 11v6"/><path d="M9 14h6"/></svg>
                View Academic Record
            </button>
        </div>
    </div>
</div>

<!-- Academic Record Card -->
<div class="student-section-card academic-record-card <?= $search_record ? 'is-visible' : '' ?>" id="academicRecordCard">
    <div class="student-section-header">
        <h3 class="student-section-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--accent);"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            Academic Record
        </h3>
        <button id="hideRecordBtn" class="btn btn-outline btn-xs">Hide</button>
    </div>
    
    <div class="info-tiles-grid">
        <!-- Student ID -->
        <div class="info-tile">
            <div class="info-tile-icon-box">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <div class="info-tile-content">
                <span class="info-tile-label">Student ID</span>
                <span class="info-tile-value">#STU-<?= date('Y') ?>-<?= str_pad($student['StudentID'], 5, '0', STR_PAD_LEFT) ?></span>
            </div>
        </div>

        <!-- Class/Grade -->
        <div class="info-tile">
            <div class="info-tile-icon-box">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <div class="info-tile-content">
                <span class="info-tile-label">Class / Grade</span>
                <span class="info-tile-value"><?= htmlspecialchars($student['ClassGrade'] ?? '-') ?></span>
            </div>
        </div>
        
        <!-- Section -->
        <div class="info-tile">
            <div class="info-tile-icon-box">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            </div>
            <div class="info-tile-content">
                <span class="info-tile-label">Section</span>
                <span class="info-tile-value"><?= htmlspecialchars($student['Section'] ?? 'Not Assigned') ?></span>
            </div>
        </div>
        
        <!-- Roll Number -->
        <div class="info-tile">
            <div class="info-tile-icon-box">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="5" x2="9" y2="19"/><line x1="15" y1="5" x2="15" y2="19"/><line x1="5" y1="9" x2="19" y2="9"/><line x1="5" y1="15" x2="19" y2="15"/></svg>
            </div>
            <div class="info-tile-content">
                <span class="info-tile-label">Roll Number</span>
                <span class="info-tile-value"><?= htmlspecialchars($student['RollNumber'] ?? 'Not Assigned') ?></span>
            </div>
        </div>
        
        <!-- Academic Year -->
        <div class="info-tile">
            <div class="info-tile-icon-box">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="info-tile-content">
                <span class="info-tile-label">Academic Year</span>
                <span class="info-tile-value"><?= htmlspecialchars($student['AcademicYear'] ?? '-') ?></span>
            </div>
        </div>
        
        <!-- Admission Date -->
        <div class="info-tile">
            <div class="info-tile-icon-box">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="info-tile-content">
                <span class="info-tile-label">Admission Date</span>
                <span class="info-tile-value">
                    <?= $student['AdmissionDate'] ? date('F j, Y', strtotime($student['AdmissionDate'])) : 'Pending' ?>
                </span>
            </div>
        </div>
    </div>
</div>

</div> <!-- Close content-area -->
<script src="../assets/js/main.js"></script>
<?php if ($search_record): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('academicRecordCard').scrollIntoView({ behavior: 'smooth' });
    });
</script>
<?php endif; ?>
</body>
</html>

