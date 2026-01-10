<?php 
require_once __DIR__ . '/partials/header.php'; 

// Grouping Logic (Using $records, NOT $logs)
$groupedRecords = [];
if (!empty($records)) {
    foreach ($records as $r) {
        $key = $r['user_id'] . '_' . $r['date'];
        if (!isset($groupedRecords[$key])) {
            $groupedRecords[$key] = [
                'user' => $r,
                'am_in' => null, 'am_out' => null, 'am_status' => null,
                'pm_in' => null, 'pm_out' => null, 'pm_status' => null,
                'status_list' => []
            ];
        }
        
        // [UPDATED] AM/PM FIX
        // We MUST combine the record date with the time to perform a correct timestamp comparison.
        // Otherwise, it compares "Today's Date + TimeIn" vs "Record Date + Noon", which fails for past records.
        $timeInTimestamp = strtotime($r['date'] . ' ' . $r['time_in']);
        $noonCutoff = strtotime($r['date'] . ' 12:00:00');

        if ($timeInTimestamp < $noonCutoff) {
            $groupedRecords[$key]['am_in'] = $r['time_in'];
            $groupedRecords[$key]['am_out'] = $r['time_out'];
            $groupedRecords[$key]['am_status'] = $r['status'];
            if ($r['status']) $groupedRecords[$key]['status_list'][] = $r['status'];
        } else {
            $groupedRecords[$key]['pm_in'] = $r['time_in'];
            $groupedRecords[$key]['pm_out'] = $r['time_out'];
            $groupedRecords[$key]['pm_status'] = $r['status'];
            if ($r['status']) $groupedRecords[$key]['status_list'][] = $r['status'];
        }
    }
}
?>

<div class="main-body attendance-reports-page"> 

    <div class="report-stats-grid">
        <div class="report-stat-card">
            <div class="stat-icon-bg bg-emerald-100 text-emerald-600"><i class="fa-solid fa-arrow-right-to-bracket"></i></div>
            <div class="stat-content"><span class="stat-label">Entries</span><span class="stat-value"><?= $stats['entries'] ?? 0 ?></span></div>
        </div>
         <div class="report-stat-card">
            <div class="stat-icon-bg bg-red-100 text-red-600"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <div class="stat-content"><span class="stat-label">Exits</span><span class="stat-value"><?= $stats['exits'] ?? 0 ?></span></div>
        </div>
         <div class="report-stat-card">
            <div class="stat-icon-bg bg-blue-100 text-blue-600"><i class="fa-solid fa-user-check"></i></div>
            <div class="stat-content"><span class="stat-label">Present</span><span class="stat-value"><?= $stats['present_total'] ?? 0 ?></span></div>
        </div>
         <div class="report-stat-card">
            <div class="stat-icon-bg bg-gray-100 text-gray-600"><i class="fa-solid fa-list-alt"></i></div>
            <div class="stat-content"><span class="stat-label">Records</span><span class="stat-value"><?= $totalRecords ?? 0 ?></span></div>
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
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search keywords..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
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
                    <button type="submit" class="btn btn-primary btn-sm apply-filter-btn"><i class="fa-solid fa-check"></i> Apply</button>
                    
                    <button type="button" class="btn btn-warning btn-sm" onclick="handlePrintDTR()">
                        <i class="fa-solid fa-print"></i> View DTR
                    </button>
                    
                    </div>
            </form>
        </div>
    </div>
    
    <div class="card attendance-table-card">
         <div class="card-body" style="padding: 0; overflow-x: auto;"> 
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="margin: 1rem;"><?= htmlspecialchars($error) ?></div>
            <?php elseif (empty($groupedRecords)): ?>
                <p style="text-align: center; color: var(--gray-500); padding: 40px;">No records found.</p>
            <?php else: ?>
                <table class="attendance-table-new" style="min-width: 1000px;">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Date</th>
                            <th style="text-align: center;">AM In</th>
                            <th style="text-align: center;">AM Out</th>
                            <th style="text-align: center;">PM In</th>
                            <th style="text-align: center;">PM Out</th>
                            <th>Day Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groupedRecords as $row): 
                            $u = $row['user'];
                            $statusList = array_unique($row['status_list']);
                            $statusStr = implode(', ', $statusList);
                        ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <span class="user-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></span>
                                    <span class="user-id"><?= htmlspecialchars($u['faculty_id']) ?></span>
                                </div>
                            </td>
                            <td><span class="date-cell"><?= date('m/d/Y', strtotime($u['date'])) ?></span></td>
                            <td style="text-align: center;"><?= $row['am_in'] ? date('h:i A', strtotime($row['am_in'])) : '-' ?></td>
                            <td style="text-align: center;"><?= $row['am_out'] ? date('h:i A', strtotime($row['am_out'])) : '-' ?></td>
                            <td style="text-align: center;"><?= $row['pm_in'] ? date('h:i A', strtotime($row['pm_in'])) : '-' ?></td>
                            <td style="text-align: center;"><?= $row['pm_out'] ? date('h:i A', strtotime($row['pm_out'])) : '-' ?></td>
                            <td style="vertical-align: middle;">
                                <?php if (strpos($statusStr, 'Late') !== false): ?>
                                    <span class="status-label status-late">Has Late</span>
                                <?php elseif (!empty($statusStr)): ?>
                                    <span class="status-label status-present">Complete</span>
                                <?php else: ?>
                                    <span class="status-label">-</span>
                                <?php endif; ?>
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
                    <button type="button" class="btn btn-sm btn-light" onclick="printDtrFrame()" style="background: white; color: var(--emerald-600); border:none; font-weight:600;">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
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
                <p style="font-size: 0.95rem; color: #6b7280;">
                    You must specify an employee to generate the Daily Time Record.
                </p>
            </div>
            <div class="modal-footer" style="justify-content: center; background: #f9fafb;">
                <button class="btn btn-secondary" onclick="closeModal('noUserModal')">Okay, I'll select one</button>
            </div>
        </div>
    </div>

</div>

<script>
// Logic to handle DTR View
function handlePrintDTR() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    let userId = '';

    <?php if ($isAdmin): ?>
        const userIdSelect = document.getElementById('userId');
        if (userIdSelect) {
            userId = userIdSelect.value;
        }
        
        // If Admin and NO user selected, show Modal
        if (!userId) {
            openModal('noUserModal');
            return;
        }
    <?php else: ?>
        // Regular user always sees their own DTR
        userId = '<?= $_SESSION['user_id'] ?? '' ?>'; 
    <?php endif; ?>

    // If we have a user ID, open the preview
    if (userId) {
        const url = `print_dtr.php?user_id=${userId}&start_date=${startDate}&end_date=${endDate}&preview=1`;
        const f = document.getElementById('dtrFrame');
        const m = document.getElementById('dtrPreviewModal');
        
        if(f && m) { 
            f.src = url; 
            m.style.display = 'flex'; 
            document.body.style.overflow = 'hidden'; 
        }
    }
}

function printDtrFrame() {
    const iframe = document.getElementById('dtrFrame');
    if (iframe && iframe.contentWindow) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }
}

function closeDtrModal() {
    const modal = document.getElementById('dtrPreviewModal');
    if (modal) modal.style.display = 'none'; 
    document.body.style.overflow = 'auto'; 
    
    const frame = document.getElementById('dtrFrame');
    if (frame) frame.src = 'about:blank'; 
}

// Modal Helpers
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'flex';
}
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>