<?php 
require_once __DIR__ . '/partials/header.php'; 
?>

<div class="main-body admin-management-page">

    <?php if ($error): ?>
        <div id="pageAlert" class="dismissible-alert alert-error">
            <i class="fa-solid fa-circle-xmark"></i>
            <span><?= htmlspecialchars($error) ?></span>
            <div class="alert-progress-bar"><div class="alert-progress-fill"></div></div>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div id="pageAlert" class="dismissible-alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= htmlspecialchars($success) ?></span>
            <div class="alert-progress-bar"><div class="alert-progress-fill"></div></div>
        </div>
    <?php endif; ?>

    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 75vh; padding: 2rem 0;">
        
        <div class="stats-grid" style="margin-bottom: 2rem; width: 100%; display: flex; justify-content: center;">
            <div class="stat-card clickable-card" 
                 style="width: 100%; max-width: 400px; cursor: pointer; border-left: 5px solid #dc2626; position: relative;" 
                 onclick="openModal('adminListModal')"
                 title="Click to view all administrators">
                <div class="stat-icon red">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="stat-details">
                    <p>Authorized System Admins</p>
                    <div class="stat-value red"><?= $stats['admin_active'] ?? 0 ?> Active</div>
                </div>
                <div style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: #94a3b8;">
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </div>
        </div>

        <div class="card" style="width: 100%; max-width: 600px;">
            <div class="card-body">
                <div class="user-creation-header" style="margin-bottom: 2rem; display: flex; align-items: center; gap: 15px;">
                    <div style="background: var(--blue-50); color: var(--blue-600); width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fa-solid fa-plus-circle"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.25rem;">Register New Admin</h3>
                        <p style="margin: 0; font-size: 0.85rem; color: #64748b;">Grant high-level system access</p>
                    </div>
                </div>
                
                <form method="POST">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 700; color: #475569;">Admin ID Number <span class="required">*</span></label>
                        <input type="text" name="faculty_id" class="form-control" placeholder="e.g., ADM-001" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 700; color: #475569;">Institutional Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="admin@bpc.edu.ph" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label style="font-weight: 700; color: #475569;">First Name <span class="required">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 700; color: #475569;">Last Name <span class="required">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 700; color: #475569;">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" placeholder="Optional">
                    </div>

                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label style="font-weight: 700; color: #475569;">Role / Position</label>
                        <input type="text" value="Administrator" class="form-control" readonly style="background-color: var(--gray-100); color: var(--gray-600); cursor: not-allowed;">
                    </div>

                    <div class="password-info-box" style="margin-bottom: 2rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 15px; display: flex; align-items: flex-start; gap: 12px;">
                        <i class="fa-solid fa-circle-info" style="color: #0369a1; margin-top: 3px;"></i>
                        <div style="color: #0c4a6e; font-size: 0.9rem; line-height: 1.5;">
                            <strong>Security Note:</strong> The default password for new administrators is <strong>@adminpass123</strong>. A change of password will be required upon first login.
                        </div>
                    </div>

                    <button type="submit" name="create_admin" class="btn btn-primary btn-full-width" style="padding: 12px;">
                        <i class="fa-solid fa-user-shield" style="margin-right: 8px;"></i> Register Administrator
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="adminListModal" class="modal">
        <div class="modal-content" style="max-width: 900px; width: 95%;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-shield-halved"></i> Active System Administrators</h3>
                <button class="modal-close" onclick="closeModal('adminListModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding: 0; min-height: 300px;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th style="padding: 15px; text-align: left; color: #475569; font-size: 0.75rem; text-transform: uppercase;">Admin ID</th>
                            <th style="padding: 15px; text-align: left; color: #475569; font-size: 0.75rem; text-transform: uppercase;">Full Name</th>
                            <th style="padding: 15px; text-align: left; color: #475569; font-size: 0.75rem; text-transform: uppercase;">Institutional Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($admins)): ?>
                            <?php foreach ($admins as $admin): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                                <td style="padding: 15px;">
                                    <span class="id-badge" style="background: #fee2e2; color: #b91c1c; font-weight:800; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; border: 1px solid #fecaca;">
                                        <?= htmlspecialchars($admin['faculty_id']) ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; font-weight: 600; color: #1e293b;">
                                    <?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?>
                                </td>
                                <td style="padding: 15px; font-size: 0.85rem; color: #64748b;">
                                    <?= htmlspecialchars($admin['email']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="padding: 4rem; text-align: center; color: #94a3b8;">
                                    <i class="fa-solid fa-user-slash" style="display: block; font-size: 3rem; margin-bottom: 15px; opacity: 0.4;"></i>
                                    <h3 style="margin: 0;">No Administrators Found</h3>
                                    <p style="margin-top: 5px;">Currently there are no other active administrators registered.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer" style="background: #f8fafc; justify-content: center;">
                <button class="btn btn-secondary" onclick="closeModal('adminListModal')">Close View</button>
            </div>
        </div>
    </div>

</div>

<style>
.clickable-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.clickable-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 20px -10px rgba(220, 38, 38, 0.15);
    background: #fffcfc;
}
.data-table tbody tr:hover {
    background-color: #f8fafc;
}

.dismissible-alert {
        max-width: 600px;
        margin: 20px auto;
        padding: 15px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: opacity 0.5s ease-out;
    }
    .alert-success { background: #ecfdf5; border: 1px solid #10b981; color: #065f46; }
    .alert-error { background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; }
</style>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// Automatically hide the alert after 6 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.dismissible-alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 6000); // 6 seconds auto-dismiss
        });
    });
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>