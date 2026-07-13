<!-- Confirmation dialog used before approving a pending registration. -->
<div class="modal-overlay" id="approveModal">
    <div class="modal-card">
        <h3 class="mb-3">Approve Registration</h3>
        <p class="text-muted mb-3" id="approveModalMessage">Approve this student registration?</p>
        <!-- JavaScript fills the hidden student ID before opening this modal. -->
        <form method="POST"><input type="hidden" name="action" value="approve"><input type="hidden" name="student_id" id="approveStudentId">
            <div class="modal-actions"><button type="button" class="btn btn-outline" data-modal-close>Cancel</button><button type="submit" class="btn btn-primary">Approve</button></div>
        </form>
    </div>
</div>
