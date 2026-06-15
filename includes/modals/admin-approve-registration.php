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
                <button type="submit" class="btn btn-primary">Confirm Approval</button>
            </div>
        </form>
    </div>
</div>
