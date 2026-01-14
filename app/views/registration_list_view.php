<?php 
require_once __DIR__ . '/partials/header.php'; 
?>

<div class="main-body registration-page">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Action completed successfully.</div>
    <?php endif; ?>

    <div class="search-bar-container">
        <i class="fa-solid fa-search search-icon"></i>
        <input type="text" id="userSearchInput" class="search-input" placeholder="Search by name, faculty ID, or email...">
    </div>

    <div class="registration-stats-grid">
        <div class="reg-stat-card total-users">
            <div class="reg-stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="reg-stat-details">
                <p>Total Users</p>
                <span class="reg-stat-value"><?= $totalUsers ?></span>
            </div>
        </div>
        <div class="reg-stat-card registered">
            <div class="reg-stat-icon"><i class="fa-solid fa-user-check"></i></div>
            <div class="reg-stat-details">
                <p>Registered</p>
                <span class="reg-stat-value"><?= $registeredUsersCount ?></span>
            </div>
        </div>
        <div class="reg-stat-card pending">
            <div class="reg-stat-icon"><i class="fa-solid fa-user-clock"></i></div>
            <div class="reg-stat-details">
                <p>Pending</p>
                <span class="reg-stat-value"><?= $pendingCount ?></span>
            </div>
        </div>
    </div>

    <div class="card pending-registrations-section">
        <div class="card-header card-header-flex" style="justify-content: space-between; align-items: center;">
            <h3><i class="fa-solid fa-clock"></i> Pending Registrations (<?= $pendingCount ?>)</h3>
            <button class="btn btn-warning btn-sm" onclick="openModal('notifyModal')" <?= empty($pendingUsers) ? 'disabled' : '' ?>>
                <i class="fa-solid fa-bell"></i> Notify All
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($pendingUsers)): ?>
                <div class="empty-state">
                     <i class="fa-solid fa-check-circle" style="font-size: 3rem; color: var(--emerald-500); margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.2rem; font-weight: 600; color: var(--gray-700);">No Pending Registrations</p>
                    <p style="color: var(--gray-600);">All active users have completed fingerprint registration.</p>
                </div>
            <?php else: ?>
                <div class="user-cards-container">
                    <?php foreach ($pendingUsers as $u): ?>
                        <div class="user-card" data-search-term="<?= strtolower(htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' ' . $u['faculty_id'] . ' ' . $u['email'])) ?>">
                            <div class="user-card-header">
                                <span class="user-card-status pending">Pending</span>
                                <span class="user-card-role"><?= htmlspecialchars(str_replace(' ', '_', strtoupper($u['role']))) ?></span>
                            </div>
                            <div class="user-card-details">
                                <p class="user-card-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></p>
                                <p class="user-card-info"><?= htmlspecialchars($u['faculty_id']) ?></p>
                                <p class="user-card-info"><?= htmlspecialchars($u['email']) ?></p>
                            </div>
                            <a href="fingerprint_registration.php?user_id=<?= $u['id'] ?>" class="user-card-register-btn">
                                <i class="fa-solid fa-fingerprint"></i>
                                Register Fingerprint
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card registered-users-section" style="margin-top: 2rem;">
        <div class="card-header card-header-flex" style="justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleRegisteredUsers()">
            <h3><i class="fa-solid fa-user-check"></i> Registered Users (<?= count($registeredUserList) ?>)</h3>
            <i class="fa-solid fa-chevron-down" id="registeredToggleIcon"></i>
        </div>
        
        <div class="card-body" id="registeredUsersContainer" style="display: none;">
            <?php if (empty($registeredUserList)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-user-slash" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                    <p style="color: var(--gray-600);">No users have completed fingerprint registration yet.</p>
                </div>
            <?php else: ?>
                <div class="user-cards-container">
                    <?php foreach ($registeredUserList as $u): ?>
                        <div class="user-card" data-search-term="<?= strtolower(htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' ' . $u['faculty_id'] . ' ' . $u['email'])) ?>">
                            <div class="user-card-header">
                                <span class="user-card-status registered">Registered</span>
                                <span class="user-card-role"><?= htmlspecialchars(str_replace(' ', '_', strtoupper($u['role']))) ?></span>
                            </div>
                            <div class="user-card-details">
                                <p class="user-card-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></p>
                                <p class="user-card-info"><?= htmlspecialchars($u['faculty_id']) ?></p>
                                <p class="user-card-info"><?= htmlspecialchars($u['email']) ?></p>
                            </div>
                            
                            <div class="user-card-registered-status" style="display:flex; flex-direction:column; gap:10px; align-items:center; padding-top:10px;">
                                <div style="display:flex; align-items:center; gap:5px; color:var(--emerald-600); font-weight:bold;">
                                    <i class="fa-solid fa-check-circle"></i>
                                    <span>Registered</span>
                                </div>
                                <a href="fingerprint_registration.php?user_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" style="width:100%; font-size:0.85rem;">
                                    <i class="fa-solid fa-plus-circle"></i> Enroll Another
                                </a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="notifyModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3><i class="fa-solid fa-bell"></i> Notify Pending Users</h3>
            <button type="button" class="modal-close" onclick="closeModal('notifyModal')">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p class="fs-large">
                Are you sure you want to send a dashboard notification to all
                <strong><?= $pendingCount ?></strong> pending user(s)?
            </p>
            <div id="notify-status-message" style="margin-top: 1rem;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('notifyModal')">Cancel</button>
            <button type="button" id="confirmNotifyBtn" class="btn btn-primary" onclick="sendNotifications()">
                <i class="fa-solid fa-paper-plane"></i> Yes, Notify All
            </button>
        </div>
    </div>
</div>

<script>
function toggleRegisteredUsers() {
    const container = document.getElementById('registeredUsersContainer');
    const icon = document.getElementById('registeredToggleIcon');
    if (container.style.display === 'none') {
        container.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        container.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}
function openModal(modalId) { const m = document.getElementById(modalId); if(m) m.style.display='flex'; }
function closeModal(modalId) { const m = document.getElementById(modalId); if(m) m.style.display='none'; }
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearchInput');
    const userCards = document.querySelectorAll('.user-card');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            userCards.forEach(card => {
                const cardSearchTerm = card.getAttribute('data-search-term');
                card.style.display = (cardSearchTerm && cardSearchTerm.includes(searchTerm)) ? '' : 'none';
            });
        });
    }
});
function sendNotifications() {
    const notifyBtn = document.getElementById('confirmNotifyBtn');
    const statusMessage = document.getElementById('notify-status-message');
    notifyBtn.disabled = true;
    notifyBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
    
    fetch('api.php?action=notify_pending_users', { method: 'POST', headers: { 'Content-Type': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            statusMessage.innerHTML = '<i class="fa-solid fa-check-circle"></i> ' + data.message;
            statusMessage.className = 'alert alert-success';
            notifyBtn.innerHTML = 'Done';
            setTimeout(() => { closeModal('notifyModal'); notifyBtn.disabled=false; notifyBtn.innerHTML='Yes, Notify All'; }, 2500);
        } else { throw new Error(data.message); }
    })
    .catch(e => { statusMessage.innerHTML='Error: '+e.message; statusMessage.className='alert alert-error'; notifyBtn.disabled=false; });
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>