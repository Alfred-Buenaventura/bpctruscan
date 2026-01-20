<?php 
require_once __DIR__ . '/partials/header.php'; 
?>

<style>
/* Clickable stats styling */
.clickable-stat {
    cursor: pointer;
    transition: transform 0.2s;
}
.clickable-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.report-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
.report-table th, .report-table td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    text-align: left;
}
.report-table th {
    background: #f8fafc;
    color: #64748b;
    font-size: 0.85rem;
    text-transform: uppercase;
}
.badge-role {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}
.badge-teaching { background: #dcfce7; color: #166534; }
.badge-non-teaching { background: #f1f5f9; color: #475569; }
</style>

<div class="main-body registration-page">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Action completed successfully.</div>
    <?php endif; ?>

    <div class="search-bar-container">
        <i class="fa-solid fa-search search-icon"></i>
        <input type="text" id="userSearchInput" class="search-input" placeholder="Search by name, faculty ID, or email...">
    </div>

    <div class="registration-stats-grid">
        <div class="reg-stat-card total-users clickable-stat" onclick="openStaffReport('all', 'Total Staff Report')">
            <div class="reg-stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="reg-stat-details">
                <p>Total Users</p>
                <span class="reg-stat-value"><?= $totalUsers ?></span>
            </div>
        </div>
        <div class="reg-stat-card registered clickable-stat" onclick="openStaffReport('registered', 'Registered Staff Report')">
            <div class="reg-stat-icon"><i class="fa-solid fa-user-check"></i></div>
            <div class="reg-stat-details">
                <p>Registered</p>
                <span class="reg-stat-value"><?= $registeredUsersCount ?></span>
            </div>
        </div>
        <div class="reg-stat-card pending clickable-stat" onclick="openStaffReport('pending', 'Pending Registration Report')">
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
                </div>
            <?php else: ?>
                <div class="user-cards-container">
                    <?php foreach ($pendingUsers as $u): ?>
                        <div class="user-card" data-search-term="<?= strtolower(htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' ' . $u['faculty_id'])) ?>">
                            <div class="user-card-header">
                                <span class="user-card-status pending">Pending</span>
                                <span class="user-card-role"><?= htmlspecialchars(strtoupper($u['role'])) ?></span>
                            </div>
                            <div class="user-card-details">
                                <p class="user-card-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></p>
                                <p class="user-card-info"><?= htmlspecialchars($u['faculty_id']) ?></p>
                            </div>
                            <a href="fingerprint_registration.php?user_id=<?= $u['id'] ?>" class="user-card-register-btn">
                                <i class="fa-solid fa-fingerprint"></i> Register Fingerprint
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
            <i class="fa-solid fa-chevron-up" id="registeredToggleIcon"></i>
        </div>
        
        <div class="card-body" id="registeredUsersContainer" style="display: block;">
            <div class="user-cards-container">
                <?php foreach ($registeredUserList as $u): ?>
                    <div class="user-card" data-search-term="<?= strtolower(htmlspecialchars($u['first_name'] . ' ' . $u['last_name'])) ?>">
                        <div class="user-card-header">
                            <span class="user-card-status registered">Registered</span>
                            <span class="user-card-role"><?= htmlspecialchars(strtoupper($u['role'])) ?></span>
                        </div>
                        <div class="user-card-details">
                            <p class="user-card-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></p>
                            <p class="user-card-info"><?= htmlspecialchars($u['faculty_id']) ?></p>
                        </div>
                        <div class="user-card-registered-status" style="display:flex; flex-direction:column; gap:10px; align-items:center; padding-top:10px;">
                            <a href="fingerprint_registration.php?user_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" style="width:100%;">
                                <i class="fa-solid fa-plus-circle"></i> Enroll Another
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div id="reportModal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 id="reportTitle">Staff Report</h3>
            <button class="modal-close" onclick="closeModal('reportModal')">&times;</button>
        </div>
        <div class="modal-body" id="reportContent">
            <p>Loading report data...</p>
        </div>
    </div>
</div>

<div id="notifyModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3><i class="fa-solid fa-bell"></i> Notify Pending Users</h3>
            <button type="button" class="modal-close" onclick="closeModal('notifyModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Send dashboard notifications to all pending users?</p>
            <div id="notify-status-message" style="margin-top: 1rem;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('notifyModal')">Cancel</button>
            <button type="button" id="confirmNotifyBtn" class="btn btn-primary" onclick="sendNotifications()">Yes, Notify All</button>
        </div>
    </div>
</div>

<script>
// Logic to fetch and build the report table
function openStaffReport(type, title) {
    document.getElementById('reportTitle').innerText = title;
    document.getElementById('reportContent').innerHTML = '<p>Generating table...</p>';
    openModal('reportModal');
    
    fetch(`api.php?action=get_staff_report&filter=${type}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = '<table class="report-table"><thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Fingerprint</th></tr></thead><tbody>';
                data.users.forEach(u => {
                    const isTeaching = u.role.toLowerCase().includes('teacher') || u.role.toLowerCase().includes('faculty');
                    const catClass = isTeaching ? 'badge-teaching' : 'badge-non-teaching';
                    const catText = isTeaching ? 'Teaching' : 'Non-Teaching';
                    const regStatus = (u.fingerprint_registered == 1) ? '<span style="color:green">YES</span>' : '<span style="color:orange">NO</span>';
                    
                    html += `<tr><td>${u.faculty_id}</td><td>${u.first_name} ${u.last_name}</td><td><span class="badge-role ${catClass}">${catText}</span></td><td>${regStatus}</td></tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('reportContent').innerHTML = html;
            }
        });
}

function toggleRegisteredUsers() {
    const container = document.getElementById('registeredUsersContainer');
    const icon = document.getElementById('registeredToggleIcon');
    const isHidden = container.style.display === 'none';
    container.style.display = isHidden ? 'block' : 'none';
    icon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
}

function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; }
function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            document.querySelectorAll('.user-card').forEach(card => {
                const searchTxt = card.getAttribute('data-search-term');
                card.style.display = searchTxt.includes(term) ? '' : 'none';
            });
        });
    }
});

function sendNotifications() {
    const btn = document.getElementById('confirmNotifyBtn');
    btn.disabled = true;
    fetch('api.php?action=notify_pending_users', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        document.getElementById('notify-status-message').innerHTML = data.message;
        setTimeout(() => { closeModal('notifyModal'); btn.disabled = false; }, 2000);
    });
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>