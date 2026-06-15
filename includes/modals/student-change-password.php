<!-- Change Password Modal -->
<div class="modal-overlay" id="passwordModal">
    <div class="modal-card">
        <h3 class="mb-3">Change Password</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
                <label class="form-label">Current Password</label>
                <div class="password-wrapper">
                    <input type="password" name="current_password" class="form-control" required>
                    <span class="password-toggle">Show</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">New Password (min 8 chars)</label>
                <div class="password-wrapper">
                    <input type="password" name="new_password" class="form-control" required>
                    <span class="password-toggle">Show</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" class="form-control" required>
                    <span class="password-toggle">Show</span>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
        </form>
    </div>
</div>
