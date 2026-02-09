</main> 
    </div> 

    <div id="logoutConfirmModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3><i class="fa-solid fa-arrow-right-from-bracket"></i> Confirm Logout</h3>
                <button type="button" class="modal-close" onclick="closeModal('logoutConfirmModal')">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-size: 1rem; color: var(--gray-700);">Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('logoutConfirmModal')">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="window.location.href='logout.php'">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
                </button>
            </div>
        </div>
    </div>

    <div id="operationStatusModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-body text-center" style="padding: 2.5rem 1.5rem;">
            <div id="statusIconContainer" class="action-icon-circle">
                <i id="statusIcon" class="fa-solid"></i>
            </div>
            <h3 id="statusModalTitle" style="margin-bottom: 0.75rem;"></h3>
            <p id="statusModalMessage" style="color: #64748b; font-size: 0.95rem; line-height: 1.5;"></p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-primary" style="min-width: 120px;" onclick="closeModal('operationStatusModal')">OK</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = <?= json_encode($_SESSION['flash_message'] ?? null) ?>;
    const flashType = <?= json_encode($_SESSION['flash_type'] ?? null) ?>;

    if (flashMessage) {
        const iconContainer = document.getElementById('statusIconContainer');
        const icon = document.getElementById('statusIcon');
        const title = document.getElementById('statusModalTitle');
        const message = document.getElementById('statusModalMessage');

        message.textContent = flashMessage;

        if (flashType === 'success') {
            iconContainer.className = 'action-icon-circle bg-success-light';
            icon.className = 'fa-solid fa-circle-check';
            title.textContent = 'Success';
            title.style.color = '#065f46';
        } else if (flashType === 'error') {
            iconContainer.className = 'action-icon-circle bg-danger-light';
            icon.className = 'fa-solid fa-circle-xmark';
            title.textContent = 'Error';
            title.style.color = '#991b1b';
        }
        
        openModal('operationStatusModal');
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    }
});
</script>

    <script src="js/main.js"></script>
</body>
</html>