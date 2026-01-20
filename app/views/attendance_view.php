<?php 
require_once __DIR__ . '/partials/header.php'; 
?>

<div class="main-body attendance-reports-page"> 

    <div class="report-stats-grid">
        <div class="report-stat-card">
            <div class="stat-icon-bg bg-emerald-100 text-emerald-600"><i class="fa-solid fa-arrow-right-to-bracket"></i></div>
            <div class="stat-content">
                <span class="stat-label">Entries Today</span>
                <span class="stat-value"><?= $stats['entries'] ?? 0 ?></span>
            </div>
        </div>
         <div class="report-stat-card">
            <div class="stat-icon-bg bg-red-100 text-red-600"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <div class="stat-content">
                <span class="stat-label">Exits Today</span>
                <span class="stat-value"><?= $stats['exits'] ?? 0 ?></span>
            </div>
        </div>
         <div class="report-stat-card">
            <div class="stat-icon-bg bg-blue-100 text-blue-600"><i class="fa-solid fa-user-check"></i></div>
            <div class="stat-content">
                <span class="stat-label">Present</span>
                <span class="stat-value"><?= $stats['present_total'] ?? 0 ?></span>
            </div>
        </div>
        <div class="report-stat-card">
            <div class="stat-icon-bg bg-orange-100 text-orange-600"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-content">
                <span class="stat-label">Late Logs</span>
                <span class="stat-value"><?= $stats['late'] ?? 0 ?></span>
            </div>
        </div>
    </div>

    <div class="filter-export-section card">
        <div class="card-header">
            <h3><i class="fa-solid fa-filter"></i> Filter & Reports</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="attendance_reports.php" id="reportFilterForm" class="filter-controls-new">
                <div class="filter-inputs" <?= !$isAdmin ? 'style="grid-template-columns: 1fr;"' : '' ?>>
                    <?php if ($isAdmin): ?>
                    <div class="form-group filter-item">
                        <label>Select User</label>
                        <div class="select-wrapper">
                            <select name="user_id" id="userId" class="form-control stylish-select">
                                <option value="">-- Select User for DTR --</option>
                                <?php if (!empty($allUsers) && is_array($allUsers)): ?>
                                    <?php foreach ($allUsers as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= (isset($filters['user_id']) && $filters['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <i class="fa-solid fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                    <div class="form-group filter-item">
                        <label>Search Faculty</label>
                        <input type="text" name="search" class="form-control" placeholder="ID or Name..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                    <?php endif; ?>

                    <div class="form-group filter-item">
                        <label>Date Range</label>
                         <div style="display: flex; gap: 0.5rem;">
                             <input type="date" name="start_date" id="startDate" class="form-control" value="<?= htmlspecialchars($filters['start_date'] ?? date('Y-m-01')) ?>">
                             <input type="date" name="end_date" id="endDate" class="form-control" value="<?= htmlspecialchars($filters['end_date'] ?? date('Y-m-d')) ?>">
                         </div>
                    </div>
                </div>
                <div class="filter-actions-new" style="align-items: center;">
                    <button type="submit" class="btn btn-primary btn-sm apply-filter-btn"><i class="fa-solid fa-check"></i> Apply Filters</button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="handlePrintDTR()">
                        <i class="fa-solid fa-print"></i> Generate DTR
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card attendance-table-card">
         <div class="card-body" style="padding: 0; overflow-x: auto;"> 
            <?php if (empty($records)): ?>
                <p style="text-align: center; color: var(--gray-500); padding: 40px;">No attendance records found.</p>
            <?php else: ?>
                <table class="attendance-table-new accordion-table" style="min-width: 1000px;">
                    <thead>
                        <tr>
                            <th style="width: 50px;"></th>
                            <th>Faculty / Staff</th>
                            <th>Date</th>
                            <th>Duty Sessions</th>
                            <th>Final Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $idx = 0; foreach ($records as $row): $idx++; ?>
                        <tr class="accordion-toggle" onclick="toggleAccordion('details-<?= $idx ?>', this)">
                            <td style="text-align: center;"><i class="fa-solid fa-chevron-right toggle-icon"></i></td>
                            <td>
                                <div class="user-cell">
                                    <span class="user-name"><?= htmlspecialchars($row['name']) ?></span>
                                    <span class="user-id">ID: <?= htmlspecialchars($row['faculty_id']) ?></span>
                                </div>
                            </td>
                            <td><span class="date-cell"><?= date('M d, Y', strtotime($row['date'])) ?></span></td>
                            <td><span class="session-pill"><?= count($row['logs']) ?> Class Block(s)</span></td>
                            <td style="vertical-align: middle;">
                                <span class="status-label status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr id="details-<?= $idx ?>" class="accordion-content" style="display: none; background-color: #f8fafc;">
                            <td colspan="5" style="padding: 0;">
                                <div class="duty-breakdown-wrapper" style="padding: 20px 20px 20px 60px;">
                                    <h4 style="font-size: 0.9rem; color: #475569; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                                        <i class="fa-solid fa-fingerprint"></i> Duty Scan Breakdown
                                    </h4>
                                    <div class="scan-logs-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 15px;">
                                        <?php foreach ($row['logs'] as $log): ?>
                                            <div class="scan-log-card" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                                    <div class="time-block">
                                                        <span style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">In</span>
                                                        <span style="display: block; font-size: 1.05rem; font-weight: 600; color: #0f172a;"><?= $log['time_in'] ?></span>
                                                    </div>
                                                    <div style="color: #cbd5e1;"><i class="fa-solid fa-arrow-right-long"></i></div>
                                                    <div class="time-block" style="text-align: right;">
                                                        <span style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Out</span>
                                                        <span style="display: block; font-size: 1.05rem; font-weight: 600; color: #0f172a;"><?= $log['time_out'] ?></span>
                                                    </div>
                                                </div>
                                                <div style="border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                                                    <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;">Class/Duty:</span>
                                                    <span style="font-size: 0.75rem; color: #1e293b;"><?= htmlspecialchars($log['subject']) ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div id="dtrPreviewModal" class="modal modal-dtr-preview">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="color: white !important;">DTR Preview</h3>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="modal-close" onclick="closeDtrModal()" style="color: white !important;">&times;</button>
                </div>
            </div>
            <div class="modal-body" style="padding: 0 !important; flex-grow: 1; overflow-y: auto; background: #e5e7eb;">
                <iframe id="dtrFrame" src="about:blank" frameborder="0" style="width:100%; height: 1840px; display: block;"></iframe>
            </div>
        </div>
    </div>

    <div id="noUserModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <div class="modal-header warning" style="justify-content: center; background-color: #d97706 !important;">
                <h3><i class="fa-solid fa-triangle-exclamation"></i> No User Selected</h3>
            </div>
            <div class="modal-body">
                <p style="font-size: 1.1rem; color: #374151; margin-bottom: 1rem;">
                    Please select a user from the dropdown list to view their DTR.
                </p>
            </div>
            <div class="modal-footer" style="justify-content: center; background: #f9fafb;">
                <button class="btn btn-secondary" onclick="closeModal('noUserModal')">Okay</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Accordion Table Styles */
.accordion-toggle { cursor: pointer; transition: all 0.2s ease; }
.accordion-toggle:hover { background-color: #f1f5f9; }
.accordion-toggle.active { background-color: #f8fafc; border-bottom: none; }
.toggle-icon { transition: transform 0.3s; color: #94a3b8; }
.accordion-toggle.active .toggle-icon { transform: rotate(90deg); }

.session-pill {
    background-color: #eef2ff;
    color: #4338ca;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid #c7d2fe;
}
</style>

<script>
function toggleAccordion(contentId, toggleEl) {
    const content = document.getElementById(contentId);
    const isVisible = content.style.display !== 'none';
    content.style.display = isVisible ? 'none' : 'table-row';
    toggleEl.classList.toggle('active');
}

function handlePrintDTR() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    let userId = '';

    <?php if ($isAdmin): ?>
        const userIdSelect = document.getElementById('userId');
        if (userIdSelect) userId = userIdSelect.value;
        if (!userId) { openModal('noUserModal'); return; }
    <?php else: ?>
        userId = '<?= $_SESSION['user_id'] ?? '' ?>'; 
    <?php endif; ?>

    if (userId) {
        const url = `print_dtr.php?user_id=${userId}&start_date=${startDate}&end_date=${endDate}&preview=1`;
        const f = document.getElementById('dtrFrame');
        const m = document.getElementById('dtrPreviewModal');
        if(f && m) { f.src = url; m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
    }
}

function closeDtrModal() {
    const modal = document.getElementById('dtrPreviewModal');
    if (modal) modal.style.display = 'none'; 
    document.body.style.overflow = 'auto'; 
    const frame = document.getElementById('dtrFrame');
    if (frame) frame.src = 'about:blank'; 
}

function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; }
function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>