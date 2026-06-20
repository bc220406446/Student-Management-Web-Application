<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal">
    <div class="modal-card">
        <h3 class="mb-3">Do Not Approve Registration</h3>
        <p class="mb-3" id="rejectModalMessage">Are you sure you do not want to approve this registration?</p>

        <form method="POST" action="">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="student_id" id="rejectStudentId">

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Not Approve</button>
            </div>
        </form>
    </div>
</div>
