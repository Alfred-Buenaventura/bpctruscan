<?php 
require_once __DIR__ . '/partials/header.php'; 
?>

<style>
.clickable-stat {
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.clickable-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.report-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 15px;
}
.report-table th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 15px;
    border-bottom: 2px solid #e2e8f0;
    text-align: left;
}
.report-table td {
    padding: 15px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
    color: #1e293b;
}
.report-table tr:hover { background: #f8fafc; }

.status-pill {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
    display: inline-block;
}
.status-pill.registered { 
    background: #dcfce7; color: #166534; border: 1px solid #10b981; 
}

.status-pill.pending { 
    background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; 
}

.btn-emerald-vibrant {
    background-color: #10b981 !important;
    color: white !important;
    font-weight: 700 !important;
    border: none !important;
    transition: all 0.3s ease !important;
}

.btn-emerald-vibrant:hover {
    background-color: #059669 !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
}

.user-cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); /* Increased from 280px */
    gap: 20px;
    width: 100%;
}

.user-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px; /* Ensures space between status and role */
    padding-bottom: 10px;
}

.user-card-status {
    white-space: nowrap; /* Prevents "NOT REGISTERED" from breaking */
    font-size: 0.65rem;
    font-weight: 800;
}

#reportModal .modal-content {
    max-width: 1000px;
    width: 95%;
}
</style>

<div class="main-body fingerprint-registration">
    <div class="info-card-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 20px;">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-fingerprint"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Biometric Enrollment Center</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Register unique biometric fingerprint templates and link hardware IDs to faculty accounts.</p>
        </div>
    </div>

    <div class="info-guide-wrapper" style="margin-bottom: 2.5rem; padding: 0 5px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="background: #eef2ff; color: #4338ca; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Enrollment Guide:</span>
                    Select a <span style="color: #6366f1; font-weight: 600;">Faculty Profile</span> to begin. Ensure the scanner is clear before initializing the <span style="color: #6366f1; font-weight: 600;">Sensor Scan</span> sequence.
                </p>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Action completed successfully.</div>
    <?php endif; ?>

    <div class="registration-stats-grid">
        <div class="reg-stat-card total-users clickable-stat" onclick="openStaffReport('all', 'Master Staff Directory')">
            <div class="reg-stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="reg-stat-details">
                <p>Total Staff</p>
                <span class="reg-stat-value"><?= $totalUsers ?></span>
            </div>
        </div>
        <div class="reg-stat-card registered clickable-stat" onclick="openStaffReport('registered', 'Registered Biometric Users')">
            <div class="reg-stat-icon"><i class="fa-solid fa-user-check"></i></div>
            <div class="reg-stat-details">
                <p>Registered</p>
                <span class="reg-stat-value"><?= $registeredUsersCount ?></span>
            </div>
        </div>
        <div class="reg-stat-card pending clickable-stat" onclick="openStaffReport('pending', 'Pending Biometric Enrollment')">
            <div class="reg-stat-icon"><i class="fa-solid fa-user-clock"></i></div>
            <div class="reg-stat-details">
                <p>Pending</p>
                <span class="reg-stat-value"><?= $pendingCount ?></span>
            </div>
        </div>
    </div>

    <div class="search-bar-container">
        <i class="fa-solid fa-search search-icon"></i>
        <input type="text" id="userSearchInput" class="search-input" placeholder="Search by name, faculty ID, or email...">
    </div>

    <div class="card pending-registrations-section">
        <div class="card-header card-header-flex" style="justify-content: space-between; align-items: center;">
            <h3><i class="fa-solid fa-clock"></i> Pending Registrations (<?= $pendingCount ?>)</h3>
            <button class="btn btn-warning btn-sm" onclick="openModal('notifyModal')" <?= empty($pendingUsers) ? 'disabled' : '' ?>>
                <i class="fa-solid fa-bell"></i> Notify All Pending
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($pendingUsers)): ?>
                <div class="empty-state" style="padding: 3rem 0;">
                     <i class="fa-solid fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.1rem; font-weight: 600; color: #475569;">Biometric Enrollment Complete</p>
                </div>
            <?php else: ?>
                <div class="user-cards-container">
                    <?php foreach ($pendingUsers as $u): ?>
                        <div class="user-card" data-search-term="<?= strtolower(htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' ' . $u['faculty_id'])) ?>">
                            <div class="user-card-header">
                                <span class="user-card-status pending">NOT REGISTERED</span>
                                <span class="user-card-role" style="font-size:0.65rem; background:#fef3c7; color:#92400e; padding:2px 6px; border-radius:4px;"><?= htmlspecialchars(strtoupper($u['role'])) ?></span>
                            </div>
                            <div class="user-card-details">
                                <p class="user-card-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></p>
                                <p class="user-card-info">ID: <?= htmlspecialchars($u['faculty_id']) ?></p>
                            </div>
                            <a href="fingerprint_registration.php?user_id=<?= $u['id'] ?>" class="user-card-register-btn">
                                <i class="fa-solid fa-fingerprint"></i> Start Enrollment
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card registered-users-section" style="margin-top: 2rem;">
        <div class="card-header card-header-flex" style="justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleRegisteredUsers()">
            <h3><i class="fa-solid fa-user-check"></i> Registered Personnel (<?= count($registeredUserList) ?>)</h3>
            <i class="fa-solid fa-chevron-up" id="registeredToggleIcon"></i>
        </div>
        
        <div class="card-body" id="registeredUsersContainer" style="display: block;">
            <div class="user-cards-container">
                <?php foreach ($registeredUserList as $u): ?>
                    <div class="user-card" style="border-top: 4px solid #10b981;" data-search-term="<?= strtolower(htmlspecialchars($u['first_name'] . ' ' . $u['last_name'])) ?>">
                        <div class="user-card-header">
                            <span class="user-card-status registered" style="background:#10b981; color:white;">REGISTERED</span>
                            <span class="user-card-role" style="font-size:0.65rem; background:#f1f5f9; color:#475569; padding:2px 6px; border-radius:4px;"><?= htmlspecialchars(strtoupper($u['role'] ?? 'N/A')) ?></span>
                        </div>
                        <div class="user-card-details">
                            <p class="user-card-name" style="font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></p>
                            <p class="user-card-info" style="color:#64748b; font-weight:600;">ID: <?= htmlspecialchars($u['faculty_id']) ?></p>
                            <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 10px;">
                                <i class="fa-solid fa-calendar-check"></i> Enrolled: <?= isset($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : '---' ?>
                            </p>
                        </div>
                        <div class="user-card-registered-status" style="padding-top:10px;">
                            <a href="fingerprint_registration.php?user_id=<?= $u['id'] ?>" class="btn btn-sm btn-emerald-vibrant" style="width:100%; border-radius: 8px; padding: 10px;">
                                <i class="fa-solid fa-plus-circle"></i> Enroll Another Fingerprint
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div id="reportModal" class="modal">
    <div class="modal-content">
        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <h3 id="reportTitle" style="color: #1e293b;">Institutional Staff Directory</h3>
            <button class="modal-close" onclick="closeModal('reportModal')">&times;</button>
        </div>
        <div class="modal-body" id="reportContent" style="padding: 0; max-height: 70vh; overflow-y: auto;">
            </div>
        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
            <button class="btn btn-secondary" onclick="closeModal('reportModal')">Close View</button>
        </div>
    </div>
</div>

<div id="notifyModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3><i class="fa-solid fa-bell"></i> Send Enrollment Reminder</h3>
            <button type="button" class="modal-close" onclick="closeModal('notifyModal')">&times;</button>
        </div>
        <div class="modal-body" style="text-align: center; padding: 2rem;">
            <div style="background: #fff7ed; color: #ea580c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #ffedd5;">
                <i class="fa-solid fa-envelope-circle-check" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
                <p style="font-weight: 600;">Send email reminders to all users with pending biometric registration?</p>
            </div>
            <div id="notify-status-message"></div>
        </div>
        <div class="modal-footer" style="justify-content: center; gap: 15px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('notifyModal')">Cancel</button>
            <button type="button" id="confirmNotifyBtn" class="btn btn-primary" onclick="sendNotifications()">
                <i class="fa-solid fa-paper-plane"></i> Yes, Send Reminders
            </button>
        </div>
    </div>
</div>
<script>

function openStaffReport(type, title) {
    document.getElementById('reportTitle').innerText = title;
    document.getElementById('reportContent').innerHTML = '<div style="padding: 50px; text-align: center;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Synchronizing directory data...</p></div>';
    openModal('reportModal');
    
    fetch(`api.php?action=get_staff_report&filter=${type}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = `
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Faculty ID</th>
                            <th>Full Name</th>
                            <th>Current Role</th>
                            <th style="text-align: center;">Biometric Status</th>
                        </tr>
                    </thead>
                    <tbody>`;
                
                data.users.forEach(u => {
                    const regStatus = (u.fingerprint_registered == 1);
                    const statusClass = regStatus ? 'registered' : 'pending';
                    const statusText = regStatus ? 'Registered' : 'Not Registered yet';
                    
                    html += `
                    <tr>
                        <td style="font-weight: 800; color: #475569;">${u.faculty_id}</td>
                        <td style="font-weight: 600;">${u.first_name} ${u.last_name}</td>
                        <td>${u.role}</td>
                        <td style="text-align: center;">
                            <span class="status-pill ${statusClass}">${statusText}</span>
                        </td>
                    </tr>`;
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
    document.getElementById('notify-status-message').innerHTML = '<p style="color:#2563eb;"><i class="fa-solid fa-spinner fa-spin"></i> Processing emails...</p>';
    
    fetch('api.php?action=notify_pending_users', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        document.getElementById('notify-status-message').innerHTML = `<p style="color:#059669; font-weight:700;">${data.message}</p>`;
        setTimeout(() => { 
            closeModal('notifyModal'); 
            btn.disabled = false; 
            document.getElementById('notify-status-message').innerHTML = '';
        }, 2000);
    });
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>