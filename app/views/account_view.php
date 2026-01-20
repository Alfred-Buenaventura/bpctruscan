<?php 
// FIX: Use __DIR__ to locate the partials folder correctly
require_once __DIR__ . '/partials/header.php'; 
?>

<div class="main-body account-management-page">
<style>
/* Professional Modal Overhaul */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Larger Modal for Lists (Archive) */
.modal-xl {
    max-width: 1100px;
    width: 95%;
}

.modal-header {
    border-bottom: 1px solid #f1f5f9;
    padding: 1.25rem 1.5rem;
    background-color: #f8fafc;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

.modal-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    background-color: #f8fafc;
    border-top: 1px solid #f1f5f9;
    padding: 1rem 1.5rem;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}

/* Professional Action Icon Containers */
.action-icon-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin: 0 auto 1.5rem;
}

.bg-warning-light { background-color: #fff7ed; color: #f97316; }
.bg-danger-light { background-color: #fef2f2; color: #ef4444; }
.bg-success-light { background-color: #f0fdf4; color: #22c55e; }
.bg-info-light { background-color: #f0f9ff; color: #0ea5e9; }

</style>
    <div id="toastContainer" class="toast-container"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="fa-solid fa-users"></i></div>
            <div class="stat-details">
                <p>Total Active</p>
                <div class="stat-value emerald"><?= $stats['total_active'] ?? 0 ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="fa-solid fa-briefcase"></i></div>
            <div class="stat-details">
                <p>Staff Accounts</p>
                <div class="stat-value yellow"><?= $stats['non_admin_active'] ?? 0 ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fa-solid fa-user-shield"></i></div>
            <div class="stat-details">
                <p>Admins</p>
                <div class="stat-value red"><?= $stats['admin_active'] ?? 0 ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="tabs">
            <button class="tab-btn <?= $activeTab === 'csv' ? 'active' : '' ?>" onclick="showTab(event, 'csv')">
                <i class="fa-solid fa-file-csv"></i> CSV Bulk Import
            </button>
            <button class="tab-btn <?= $activeTab === 'create' ? 'active' : '' ?>" onclick="showTab(event, 'create')">
                <i class="fa-solid fa-user-plus"></i> Account Creation
            </button>
            <button class="tab-btn <?= $activeTab === 'view' ? 'active' : '' ?>" onclick="showTab(event, 'view')">
                <i class="fa-solid fa-list"></i> View All Accounts
            </button>
        </div>

        <div id="csvTab" class="tab-content <?= $activeTab === 'csv' ? 'active' : '' ?>">
            <div class="card-body">
                <div class="csv-section-header">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <h3>Bulk User Import (CSV)</h3>
                </div>
                <p class="csv-subtitle">Import multiple user accounts from a CSV file</p>

                <div class="download-template-box" style="border: 2px solid var(--blue-200); border-radius: 8px; padding: 1.5rem; background: #f8fafc; margin-bottom: 20px;">
                    <div class="download-template-inner" style="display: flex; align-items: center; gap: 20px;">
                        <div class="step-badge" style="background: var(--blue-600); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800;">1</div>
                        <div class="download-template-content">
                            <h4 style="margin: 0;">Download CSV Template <span>to ensure correct data mapping.</span></h4>
                            <button type="button" class="btn btn-primary" style="margin-top: 10px;" onclick="confirmDownload()">
                                <i class="fa-solid fa-download"></i> Download Template
                            </button>
                        </div>
                    </div>
                </div>

                <div class="csv-requirements" style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px; margin-bottom: 25px; border-left: 5px solid #fbbf24;">
                    <h4 style="color: #92400e; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> CSV Format Requirements:
                    </h4>
                    <ul style="color: #78350f; font-size: 0.95rem; line-height: 1.6; padding-left: 20px;">
                        <li>Columns (in order): <strong>Faculty ID, Last Name, First Name, Middle Name, Username, Role, Email, Phone</strong></li>
                        <li>All users will be created with default password: <strong>@defaultpass123</strong></li>
                        <li>Users must change password on first login</li>
                        <li>Duplicate Faculty IDs will be skipped</li>
                    </ul>
                </div>

                <form method="POST" enctype="multipart/form-data" id="csvUploadForm">
                    <div style="margin-bottom: 1.5rem;">
                        <label class="csv-upload-label" style="font-weight: 700; color: #475569; display: block; margin-bottom: 8px;">Upload Finalized CSV File</label>
                        <div class="csv-dropzone" id="csvDropzone" onclick="document.getElementById('csvFileInput').click()">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p class="csv-dropzone-text"><strong>Click to choose a CSV file</strong></p>
                            <p id="csvFileStatus" class="csv-file-status">No file chosen...</p>
                        </div>
                        <input type="file" name="csvFile" id="csvFileInput" accept=".csv" style="display: none;" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full-width">
                        <i class="fa-solid fa-upload"></i> Import Users from CSV
                    </button>
                </form>
            </div>
        </div>

        <div id="createTab" class="tab-content <?= $activeTab === 'create' ? 'active' : '' ?>">
            <div class="card-body">
                <div class="user-creation-header">
                    <i class="fa-solid fa-user-plus"></i>
                    <h3>Create New User Account</h3>
                </div>
                <p class="user-creation-subtitle">Create a single user account with default password: <strong>@defaultpass123</strong></p>

                <form method="POST" style="margin-top: 1.5rem;">
                    <div class="user-creation-form-grid">
                        <div class="form-group">
                            <label>Faculty/ID Number <span class="required">*</span></label>
                            <input type="text" name="faculty_id" class="form-control" placeholder="e.g., STAFF001" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="e.g., staff@bulacan.edu.ph" required>
                        </div>
                        <div class="form-group">
                            <label>First Name <span class="required">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name <span class="required">*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" placeholder="Enter middle name (optional)">
                        </div>
                        <div class="form-group">
                            <label>Phone Number <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g., 09171234567" required>
                        </div>
                        <div class="form-group form-group-full">
                            <label>Role/Position <span class="required">*</span></label>
                            <select name="role" class="form-control" required>
                                <option value="">Select a role</option>
                                <option value="Full Time Teacher">Full Time Teacher</option>
                                <option value="Part Time Teacher">Part Time Teacher</option>
                                <option value="Registrar">Registrar</option>
                                <option value="Admission">Admission</option>
                                <option value="OPRE">OPRE</option>
                                <option value="Scholarship Office">Scholarship Office</option>
                                <option value="Doctor">Doctor</option>
                                <option value="Nurse">Nurse</option>
                                <option value="Guidance Office">Guidance Office</option>
                                <option value="Library">Library</option>
                                <option value="Finance">Finance</option>
                                <option value="Student Affair">Student Affair</option>
                                <option value="Security Personnel and Facility Operator">Security Personnel and Facility Operator</option>
                                <option value="OVPA">OVPA</option>
                                <option value="MIS">MIS</option>
                            </select>
                        </div>
                    </div>

                    <div class="password-info-box">
                        <i class="fa-solid fa-circle-info"></i>
                        <div>
                            <strong>Note:</strong> User will be assigned the default password <strong>@defaultpass123</strong> and will be prompted to change it on first login.
                        </div>
                    </div>

                    <button type="submit" name="create_user" class="btn btn-primary btn-full-width">
                        <i class="fa-solid fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>
        </div>

        <div id="viewTab" class="tab-content <?= $activeTab === 'view' ? 'active' : '' ?>">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; color: #1e293b;">All Active Accounts</h3>
                    <button class="btn btn-warning" onclick="openArchivedModal()">
                        <i class="fa-solid fa-archive"></i> View Archive (<?= count($archivedUsers) ?>)
                    </button>
                </div>

                <table class="data-table" style="width: 100%; border-collapse: separate; border-spacing: 0 8px;">
                    <thead>
                        <tr style="background-color: var(--blue-700); color: white;">
                            <th style="padding: 15px; width: 140px; border-radius: 8px 0 0 8px;">FACULTY ID</th>
                            <th style="padding: 15px; width: 25%;">NAME</th>
                            <th style="padding: 15px;">EMAIL</th>
                            <th style="padding: 15px; width: 15%;">PHONE</th>
                            <th style="padding: 15px; width: 180px;">ROLE</th>
                            <th style="padding: 15px; border-radius: 0 8px 8px 0; text-align: center;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($activeUsers) > 0): ?>
                            <?php foreach ($activeUsers as $user): ?>
                                <tr style="background: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
                                    <td style="padding: 12px;">
                                        <span class="id-badge" style="background: var(--blue-50); color: var(--blue-700); padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.75rem; border: 1px solid var(--blue-200);">
                                            <?= htmlspecialchars($user['faculty_id']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px; font-weight: 600; color: #1e293b;"><?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></td>
                                    <td style="padding: 12px; color: #64748b; font-size: 0.9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($user['email']) ?></td>
                                    <td style="padding: 12px; color: #64748b; font-size: 0.9rem;"><?= htmlspecialchars($user['phone']) ?></td>
                                    <td style="padding: 12px;"><span class="role-badge" style="display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;"><?= htmlspecialchars($user['role']) ?></span></td>
                                    <td style="padding: 12px; text-align: center; white-space: nowrap;">
                                        <button class="btn btn-sm btn-primary" title="Edit" onclick="editUser(
                                            <?= $user['id'] ?>, 
                                            '<?= htmlspecialchars($user['first_name'], ENT_QUOTES) ?>', 
                                            '<?= htmlspecialchars($user['last_name'], ENT_QUOTES) ?>', 
                                            '<?= htmlspecialchars($user['middle_name'] ?? '', ENT_QUOTES) ?>', 
                                            '<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>', 
                                            '<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES) ?>'
                                        )">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" title="Archive" onclick="confirmArchive(<?= $user['id'] ?>, '<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES) ?>')">
                                            <i class="fa-solid fa-archive"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 3rem; color: var(--gray-600);">
                                    <i class="fa-solid fa-users-slash" style="font-size: 2.5rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                                    No active accounts found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="archivedModal" class="modal">
        <div class="modal-content modal-xl"> 
            <div class="modal-header">
                <h3><i class="fa-solid fa-box-archive"></i> Archived Personnel Accounts</h3>
                <button class="modal-close" onclick="closeArchivedModal()">&times;</button>
            </div>
            <div class="modal-body" style="min-height: 400px; display: flex; flex-direction: column;">
                <div class="table-responsive">
                <?php if (count($archivedUsers) > 0): ?>
                    <table class="data-table">
                        <thead style="background: var(--gray-100);">
                            <tr>
                                <th style="padding: 15px;">FACULTY ID</th>
                                <th style="padding: 15px;">NAME</th>
                                <th style="padding: 15px;">EMAIL</th>
                                <th style="padding: 15px;">ROLE</th>
                                <th style="padding: 15px; text-align: center;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($archivedUsers as $user): ?>
                                <tr style="border-bottom: 1px solid var(--gray-200);">
                                    <td style="padding: 12px;"><span class="id-badge" style="background: var(--gray-100); padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?= htmlspecialchars($user['faculty_id']) ?></span></td>
                                    <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></td>
                                    <td style="padding: 12px;"><?= htmlspecialchars($user['email']) ?></td>
                                    <td style="padding: 12px;"><span class="role-badge"><?= htmlspecialchars($user['role']) ?></span></td>
                                    <td style="padding: 12px; text-align: center; white-space: nowrap;">
                                        <?php $name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES); ?>
                                        <button class="btn btn-sm btn-success" onclick="confirmRestore(<?= $user['id'] ?>, '<?= $name ?>')">
                                            <i class="fa-solid fa-rotate-left"></i> Restore
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $user['id'] ?>, '<?= $name ?>')">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="flex-grow: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: #94a3b8; padding: 40px;">
                        <i class="fa-solid fa-box-open" style="font-size: 5rem; margin-bottom: 20px; opacity: 0.4;"></i>
                        <h3 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #64748b;">No Archived Accounts</h3>
                        <p style="font-size: 1.1rem; margin-top: 10px;">When an account is archived, it will appear here for restoration or permanent removal.</p>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="editUserModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3><i class="fa-solid fa-user-pen"></i> Edit User Information</h3>
                <button type="button" class="modal-close" onclick="closeModal('editUserModal')">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="background: #f0f9ff; padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 12px; border: 1px solid #e0f2fe;">
                    <i class="fa-solid fa-circle-info" style="color: #0ea5e9; margin-top: 3px;"></i>
                    <div>
                        <p style="font-size: 0.95rem; color: #0369a1; margin: 0; font-weight: 600;">
                            You are editing the account details of <span id="editingUserName" style="text-decoration: underline;"></span>.
                        </p>
                        <p style="font-size: 0.85rem; color: #0c4a6e; margin: 5px 0 0 0;">
                            Please ensure all updated information is accurate before saving changes.
                        </p>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" id="editFirstName" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Last Name <span class="required">*</span></label>
                        <input type="text" name="last_name" id="editLastName" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" id="editMiddleName" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Phone</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                    
                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-body text-center" style="padding: 2.5rem 1.5rem;">
                <div id="modalIconContainer" class="action-icon-circle">
                    <i id="modalIcon" class="fa-solid"></i>
                </div>
                <h3 id="confirmTitle" style="margin-bottom: 0.75rem;">Confirm Action</h3>
                <p id="confirmMessage" style="color: #64748b; font-size: 0.95rem; line-height: 1.5;"></p>
            </div>
            <div class="modal-footer" style="justify-content: center; gap: 12px;">
                <button class="btn btn-secondary" onclick="closeConfirmModal()" style="min-width: 100px;">Cancel</button>
                <button class="btn" id="confirmActionBtn" onclick="executeConfirmedAction()" style="min-width: 100px;">Confirm</button>
            </div>
        </div>
    </div>

    <div id="duplicateUserModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header" style="background-color: var(--yellow-50);">
                <h3><i class="fa-solid fa-triangle-exclamation"></i> Duplicate Account</h3>
            </div>
            <div class="modal-body">
                <p class="fs-large" style="color: var(--gray-700);">
                    An account with this Faculty ID already exists in the system.
                </p>
                <p class="fs-small" style="color: var(--gray-600); margin-top: 1rem;">
                    Please check the "View All Accounts" tab to find the existing user. Duplicate accounts cannot be created.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="closeModal('duplicateUserModal')">OK</button>
            </div>
        </div>
    </div>

    <div id="doubleConfirmModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-body text-center" style="padding: 2.5rem 1.5rem;">
                <div class="action-icon-circle bg-danger-light">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <h3 style="color: #991b1b; margin-bottom: 0.75rem;">Permanent Deletion</h3>
                <div style="background: #fef2f2; padding: 1rem; border-radius: 8px; border: 1px solid #fee2e2; margin-bottom: 1rem;">
                    <p style="color: #991b1b; font-weight: 600; font-size: 0.85rem; margin-bottom: 0;">
                        <i class="fa-solid fa-triangle-exclamation"></i> This action cannot be undone!
                    </p>
                </div>
                <p id="doubleConfirmMessage" style="color: #64748b; font-size: 0.9rem;"></p>
            </div>
            <div class="modal-footer" style="justify-content: center; gap: 12px;">
                <button class="btn btn-secondary" onclick="closeDoubleConfirmModal()">Cancel</button>
                <button class="btn btn-danger" onclick="executeDeleteAction()">Delete Permanently</button>
            </div>
        </div>
    </div>
    
    <div id="operationStatusModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-body text-center" style="padding: 2.5rem 1.5rem;">
            <div id="statusIconContainer" class="action-icon-circle">
                <i id="statusIcon" class="fa-solid"></i>
            </div>
            <h3 id="statusModalTitle" style="margin-bottom: 0.75rem;">Success</h3>
            <p id="statusModalMessage" style="color: #64748b; font-size: 0.95rem; line-height: 1.5;"></p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-primary" style="min-width: 120px;" onclick="closeModal('operationStatusModal')">OK</button>
        </div>
    </div>
</div>

</div>

<script>
/* Global Action Variables */
let pendingAction = null;
let deleteUserId = null;
let deleteUserName = null;

/* Modal Helpers */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

/**
 * Updates confirmation modal appearance dynamically
 */
function setModalStyle(type) {
    const container = document.getElementById('modalIconContainer');
    const icon = document.getElementById('modalIcon');
    const btn = document.getElementById('confirmActionBtn');
    
    if (type === 'archive') {
        container.className = 'action-icon-circle bg-warning-light';
        icon.className = 'fa-solid fa-box-archive';
        btn.className = 'btn btn-warning';
    } else if (type === 'restore') {
        container.className = 'action-icon-circle bg-success-light';
        icon.className = 'fa-solid fa-rotate-left';
        btn.className = 'btn btn-success';
    } else if (type === 'delete') {
        container.className = 'action-icon-circle bg-danger-light';
        icon.className = 'fa-solid fa-trash';
        btn.className = 'btn btn-danger';
    } else if (type === 'download') {
        container.className = 'action-icon-circle bg-info-light';
        icon.className = 'fa-solid fa-download';
        btn.className = 'btn btn-primary';
    }
}

    document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = <?= json_encode($flashMessage) ?>;
    const flashType = <?= json_encode($flashType) ?>;

    if (flashMessage) {
        if (flashType === 'duplicate') {
            openModal('duplicateUserModal');
        } else {
            const iconContainer = document.getElementById('statusIconContainer');
            const icon = document.getElementById('statusIcon');
            const title = document.getElementById('statusModalTitle');
            const message = document.getElementById('statusModalMessage');

            // Set content
            message.textContent = flashMessage;

            if (flashType === 'success') {
                // Success Styling (Green)
                iconContainer.className = 'action-icon-circle bg-success-light';
                icon.className = 'fa-solid fa-circle-check';
                title.textContent = 'Success';
                title.style.color = '#065f46';
            } else if (flashType === 'error') {
                // Error Styling (Red)
                iconContainer.className = 'action-icon-circle bg-danger-light';
                icon.className = 'fa-solid fa-circle-xmark';
                title.textContent = 'Error';
                title.style.color = '#991b1b';
            } else {
                // Notice Styling (Blue)
                iconContainer.className = 'action-icon-circle bg-info-light';
                icon.className = 'fa-solid fa-circle-info';
                title.textContent = 'Notice';
                title.style.color = '#0c4a6e';
            }
            
            openModal('operationStatusModal');
        }
    }

    const csvFileInput = document.getElementById('csvFileInput');
    const csvDropzone = document.getElementById('csvDropzone');
    const csvFileStatus = document.getElementById('csvFileStatus');

    if (csvFileInput && csvDropzone && csvFileStatus) {
        csvFileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                csvFileStatus.textContent = this.files[0].name;
                csvFileStatus.classList.add('has-file');
            } else {
                csvFileStatus.textContent = 'No file chosen...';
                csvFileStatus.classList.remove('has-file');
            }
        });

        csvDropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        csvDropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        csvDropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                csvFileInput.files = e.dataTransfer.files;
                csvFileStatus.textContent = e.dataTransfer.files[0].name;
                csvFileStatus.classList.add('has-file');
            }
        });
    }
});

window.showTab = function(event, tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    const el = document.getElementById(tab + 'Tab');
    if (el) el.classList.add('active');
    if (event && event.target) {
        const btn = event.target.closest('.tab-btn');
        if (btn) btn.classList.add('active');
    }
};

function editUser(id, firstName, lastName, middleName, email, phone) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editFirstName').value = firstName || '';
    document.getElementById('editLastName').value = lastName || '';
    document.getElementById('editMiddleName').value = middleName || '';
    document.getElementById('editEmail').value = email || '';
    document.getElementById('editPhone').value = phone || '';
    
    // Dynamic naming for the edit header
    document.getElementById('editingUserName').textContent = firstName + " " + lastName;
    
    openModal('editUserModal');
}

function openArchivedModal() {
    openModal('archivedModal');
}

function closeArchivedModal() {
    closeModal('archivedModal');
}

function confirmDownload() {
    setModalStyle('download');
    document.getElementById('confirmTitle').innerText = 'Download Template';
    document.getElementById('confirmMessage').textContent = 'Download the CSV template file? This template shows the correct format for bulk user import.';

    pendingAction = function() {
        window.location.href = 'download_template.php';
    };
    openModal('confirmModal');
}

function confirmArchive(userId, userName) {
    setModalStyle('archive');
    document.getElementById('confirmTitle').innerText = 'Confirm Archive';
    document.getElementById('confirmMessage').textContent = `Are you sure you want to archive ${userName}? They will no longer have access to the system.`;

    pendingAction = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="user_id" value="${userId}"><input type="hidden" name="archive_user" value="1">`;
        document.body.appendChild(form);
        form.submit();
    };
    openModal('confirmModal');
}

function confirmRestore(userId, userName) {
    setModalStyle('restore');
    document.getElementById('confirmTitle').innerText = 'Confirm Restore';
    document.getElementById('confirmMessage').textContent = `Are you sure you want to restore ${userName}? They will regain access to the system.`;

    pendingAction = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="user_id" value="${userId}"><input type="hidden" name="restore_user" value="1">`;
        document.body.appendChild(form);
        form.submit();
    };
    openModal('confirmModal');
}

function confirmDelete(userId, userName) {
    setModalStyle('delete');
    document.getElementById('confirmTitle').innerText = 'Confirm Delete';
    document.getElementById('confirmMessage').textContent = `Are you sure you want to permanently delete ${userName}? This action cannot be undone.`;

    deleteUserId = userId;
    deleteUserName = userName;

    pendingAction = function() {
        closeConfirmModal();
        setTimeout(() => {
            const doubleMsg = document.getElementById('doubleConfirmMessage');
            if (doubleMsg) doubleMsg.textContent = `Type confirmation: Are you absolutely sure you want to delete ${userName}?`;
            openModal('doubleConfirmModal');
        }, 300);
    };
    openModal('confirmModal');
}

function closeConfirmModal() {
    closeModal('confirmModal');
    pendingAction = null;
}

function closeDoubleConfirmModal() {
    closeModal('doubleConfirmModal');
    deleteUserId = null;
    deleteUserName = null;
}

function executeConfirmedAction() {
    if (pendingAction) pendingAction();
    closeConfirmModal();
}

function executeDeleteAction() {
    if (deleteUserId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="user_id" value="${deleteUserId}"><input type="hidden" name="delete_user" value="1">`;
        document.body.appendChild(form);
        form.submit();
    }
    closeDoubleConfirmModal();
}

window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            closeModal(modal.id);
        }
    });
};
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>