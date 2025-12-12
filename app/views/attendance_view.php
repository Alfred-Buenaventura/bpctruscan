<?php 
require_once __DIR__ . '/partials/header.php'; 

// --- HELPER: Group Records by Day ---
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
        
        $timeIn = strtotime($r['time_in']);
        // 12:00 PM Cutoff
        if ($timeIn < strtotime($r['date'] . ' 12:00:00')) {
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
    
    <?php if ($isAdmin): ?>
    <div class="report-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div></div> 
        <a href="attendance_history.php" class="btn btn-secondary">
            <i class="fa-solid fa-clock-rotate-left"></i> View Full History
        </a>
    </div>
    <?php endif; ?>

    <div class="report-stats-grid">
        <div class="report-stat-card">
            <div class="stat-icon-bg bg-emerald-100 text-emerald-600"><i class="fa-solid fa-arrow-right-to-bracket"></i></div>
            <div class="stat-content"><span class="stat-label">Entries</span><span class="stat-value"><?= $stats['entries'] ?></span></div>
        </div>
         <div class="report-stat-card">
            <div class="stat-icon-bg bg-red-100 text-red-600"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <div class="stat-content"><span class="stat-label">Exits</span><span class="stat-value"><?= $stats['exits'] ?></span></div>
        </div>
         <div class="report-stat-card">
            <div class="stat-icon-bg bg-blue-100 text-blue-600"><i class="fa-solid fa-user-check"></i></div>
            <div class="stat-content"><span class="stat-label">Present</span><span class="stat-value"><?= $stats['present_total'] ?></span></div>
        </div>
         <div class="report-stat-card">
            <div class="stat-icon-bg bg-gray-100 text-gray-600"><i class="fa-solid fa-list-alt"></i></div>
            <div class="stat-content"><span class="stat-label">Records</span><span class="stat-value"><?= $totalRecords ?></span></div>
        </div>
    </div>

    <div class="filter-export-section card">
        <div class="card-header">
            <h3><i class="fa-solid fa-filter"></i> Filter & Export</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="attendance_reports.php" class="filter-controls-new">
                <div class="filter-inputs" <?= !$isAdmin ? 'style="grid-template-columns: 1fr;"' : '' ?>>
                    <?php if ($isAdmin): ?>
                    <div class="form-group filter-item">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?= htmlspecialchars($filters['search']) ?>">
                    </div>
                    <?php endif; ?>

                    <div class="form-group filter-item">
                        <label>Date Range</label>
                         <div style="display: flex; gap: 0.5rem;">
                             <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filters['start_date']) ?>">
                             <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($filters['end_date']) ?>">
                         </div>
                    </div>
                    
                    <?php if ($isAdmin): ?>
                    <div class="form-group filter-item">
                        <label>Select User</label>
                        <select name="user_id" class="form-control">
                            <option value="">All Users</option>
                            <?php foreach ($allUsers as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="filter-actions-new">
                    <button type="submit" class="btn btn-primary btn-sm apply-filter-btn"><i class="fa-solid fa-check"></i> Apply</button>
                    <a href="export_attendance.php" class="btn btn-danger btn-sm export-csv-btn"><i class="fa-solid fa-file-csv"></i> CSV</a>
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
                        <tr class="clickable-row" onclick="openDtrModal('print_dtr.php?user_id=<?= $u['user_id'] ?>&preview=1', '<?= htmlspecialchars($u['first_name']) ?>')">
                            <td>
                                <div class="user-cell">
                                    <span class="user-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></span>
                                    <span class="user-id"><?= htmlspecialchars($u['faculty_id']) ?></span>
                                </div>
                            </td>
                            <td><span class="date-cell"><?= date('m/d/Y', strtotime($u['date'])) ?></span></td>
                            
                            <td style="text-align: center;">
                                <?php if ($row['am_in']): ?>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <span class="time-val"><?= date('h:i A', strtotime($row['am_in'])) ?></span>
                                        <?php if ($row['am_status'] == 'Late'): ?>
                                            <span class="status-label status-late">Late</span>
                                        <?php elseif ($row['am_status'] == 'Present'): ?>
                                            <span class="status-label status-present">Present</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?> - <?php endif; ?>
                            </td>

                            <td style="text-align: center;">
                                <?php if ($row['am_out']): ?>
                                    <span class="time-val"><?= date('h:i A', strtotime($row['am_out'])) ?></span>
                                <?php else: ?> - <?php endif; ?>
                            </td>

                            <td style="text-align: center;">
                                <?php if ($row['pm_in']): ?>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <span class="time-val"><?= date('h:i A', strtotime($row['pm_in'])) ?></span>
                                        <?php if ($row['pm_status'] == 'Late'): ?>
                                            <span class="status-label status-late">Late</span>
                                        <?php elseif ($row['pm_status'] == 'Present'): ?>
                                            <span class="status-label status-present">Present</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?> - <?php endif; ?>
                            </td>

                            <td style="text-align: center;">
                                <?php if ($row['pm_out']): ?>
                                    <span class="time-val"><?= date('h:i A', strtotime($row['pm_out'])) ?></span>
                                <?php else: ?> - <?php endif; ?>
                            </td>

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
        <div class="modal-content" style="width: 95%; max-width: 1200px; height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header" style="justify-content: space-between; display: flex; width: 100%; align-items: center; flex-shrink: 0;">
                <h3 style="margin:0; color: white !important;">DTR Preview</h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="printDtrFromModal()">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                    <button type="button" class="modal-close" onclick="closeDtrModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color: white !important;">&times;</button>
                </div>
            </div>
            <div class="modal-body" style="padding: 0 !important; flex-grow: 1; overflow-y: auto; background: #e5e7eb;">
                <iframe id="dtrFrame" src="about:blank" frameborder="0" style="width:100%; height: 1840px; display: block;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
function openDtrModal(url, name) {
    const f = document.getElementById('dtrFrame');
    const m = document.getElementById('dtrPreviewModal');
    if(f && m) { 
        f.src = url; 
        m.style.display = 'flex'; 
        document.body.style.overflow = 'hidden'; // Stop main page scroll
    }
}
function closeDtrModal() {
    const m = document.getElementById('dtrPreviewModal');
    if(m) { 
        m.style.display = 'none'; 
        document.body.style.overflow = 'auto'; 
        document.getElementById('dtrFrame').src = 'about:blank'; 
    }
}
function printDtrFromModal() {
    const iframe = document.getElementById('dtrFrame');
    if (iframe && iframe.contentWindow) { 
        iframe.contentWindow.focus(); 
        iframe.contentWindow.print(); 
    }
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>