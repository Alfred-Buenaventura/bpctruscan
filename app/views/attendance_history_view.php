<?php require_once __DIR__ . '/partials/header.php'; 

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

<div class="main-body">
    
    <div style="margin-bottom: 1rem;">
        <a href="attendance_reports.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to Reports
        </a>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" class="filter-inputs" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                
                <?php if ($isAdmin): ?>
                <div class="form-group">
                    <label>Select User</label>
                    <select name="user_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- All Users --</option>
                        <?php foreach ($allUsers as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $filters['user_id'] == $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>From</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filters['start_date']) ?>" onchange="this.form.submit()">
                </div>
                <div class="form-group">
                    <label>To</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($filters['end_date']) ?>" onchange="this.form.submit()">
                </div>

                <input type="hidden" name="status_type" value="<?= htmlspecialchars($filters['status_type']) ?>">

                <div class="form-group">
                     <button type="submit" class="btn btn-primary" style="width: 100%;">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="stats-grid history-stats-grid">
        <a href="?user_id=<?= $filters['user_id'] ?>&start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&status_type=Present" 
           class="stat-card history-card <?= $filters['status_type'] == 'Present' ? 'active-filter' : '' ?>">
            <div class="stat-icon emerald"><i class="fa-solid fa-user-check"></i></div>
            <div class="stat-details"><p>Presents</p><div class="stat-value emerald"><?= $stats['present'] ?? 0 ?></div></div>
        </a>
        <a href="?user_id=<?= $filters['user_id'] ?>&start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&status_type=Late" 
           class="stat-card history-card <?= $filters['status_type'] == 'Late' ? 'active-filter' : '' ?>">
            <div class="stat-icon yellow"><i class="fa-solid fa-user-clock"></i></div>
            <div class="stat-details"><p>Lates</p><div class="stat-value" style="color: #d97706;"><?= $stats['late'] ?? 0 ?></div></div>
        </a>
        <a href="?user_id=<?= $filters['user_id'] ?>&start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&status_type=Absent" 
           class="stat-card history-card <?= $filters['status_type'] == 'Absent' ? 'active-filter' : '' ?>">
            <div class="stat-icon red"><i class="fa-solid fa-user-xmark"></i></div>
            <div class="stat-details"><p>Absences</p><div class="stat-value red"><?= $stats['absent'] ?? 0 ?></div></div>
        </a>
        <a href="?user_id=<?= $filters['user_id'] ?>&start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>" 
           class="stat-card history-card <?= empty($filters['status_type']) ? 'active-filter' : '' ?>">
            <div class="stat-icon" style="background: var(--blue-100); color: var(--blue-600);"><i class="fa-solid fa-list"></i></div>
            <div class="stat-details"><p>Total Records</p><div class="stat-value" style="color: var(--blue-700);"><?= $stats['total'] ?? 0 ?></div></div>
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>
                <?php if($filters['status_type']): ?>
                    Filtered by: <?= htmlspecialchars($filters['status_type']) ?>
                <?php else: ?>
                    All Records
                <?php endif; ?>
            </h3>
        </div>
        <div class="card-body">
            <?php if(empty($groupedRecords)): ?>
                <p style="text-align: center; padding: 2rem; color: var(--gray-500);">No records found for this selection.</p>
            <?php else: ?>
                <table class="attendance-table-new">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th style="text-align: center;">AM In</th>
                            <th style="text-align: center;">AM Out</th>
                            <th style="text-align: center;">PM In</th>
                            <th style="text-align: center;">PM Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($groupedRecords as $row): 
                            $u = $row['user'];
                            $statusStr = implode(', ', array_unique($row['status_list']));
                        ?>
                            <tr>
                                <td>
                                    <span class="user-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></span>
                                    <br><span class="user-id" style="font-size:0.8rem; color:gray;"><?= htmlspecialchars($u['faculty_id']) ?></span>
                                </td>
                                <td><?= date('M d, Y', strtotime($u['date'])) ?></td>
                                
                                <td style="text-align: center; vertical-align: middle;">
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

                                <td style="text-align: center; vertical-align: middle;">
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

                                <td>
                                    <?php 
                                        if(strpos($statusStr, 'Late') !== false) echo '<span class="status-label status-late">Has Late</span>';
                                        elseif(!empty($statusStr)) echo '<span class="status-label status-present">Complete</span>';
                                        elseif($u['status'] == 'Absent') echo '<span class="status-label" style="background:#fee2e2; color:#991b1b;">Absent</span>';
                                        else echo '-';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php require_once __DIR__ . '/partials/footer.php'; ?>