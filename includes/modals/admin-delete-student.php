<!-- Confirmation dialog used before permanently deleting a student. -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-card">
        <h3 class="mb-3 text-danger">Delete Student</h3>
        <p class="mb-3" id="deleteModalMessage">Are you sure you want to permanently delete this student? This action cannot be undone.</p>

        <!-- Hidden fields tell the receiving page which student to delete. -->
        <form method="POST" action="">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="student_id" id="deleteStudentId">

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Delete Permanently</button>
            </div>
        </form>
    </div>
</div>
