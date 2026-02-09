<?php require_once __DIR__ . '/partials/header.php'; ?>
<style>
.report-stat-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid transparent;
    background: #fff;
}

.report-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.08);
    border-color: rgba(99, 102, 241, 0.2);
}

.report-stat-card:active { 
    transform: translateY(-2px); 
}

.info-card-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>

<div class="main-body">
    <div class="info-card-header">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Personal Attendance Summary</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Review your performance metrics and duty history for the selected period.</p>
        </div>
    </div>

    <div class="info-guide-wrapper" style="margin-bottom: 2.5rem; padding: 0 5px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="background: #f1f5f9; color: #64748b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Notice:</span> 
                    Track your performance metrics including presence, lates, and office duties. 
                    Adjust the <span style="color: #6366f1; font-weight: 600;">Date Range</span> or 
                    <span style="color: #6366f1; font-weight: 600;">Category</span> below to update the statistics and detailed logs.
                </p>
            </div>
        </div>
    </div>
    <div class="report-stats-grid" style="margin-bottom: 2rem;">
        <div class="report-stat-card">
            <div class="stat-icon-bg bg-emerald-100 text-emerald-600"><i class="fa-solid fa-check-double"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Presents</span>
                <span class="stat-value"><?= $summary['present'] ?></span>
            </div>
        </div>
        <div class="report-stat-card">
            <div class="stat-icon-bg bg-orange-100 text-orange-600"><i class="fa-solid fa-business-time"></i></div>
            <div class="stat-content">
                <span class="stat-label">Late Sessions</span>
                <span class="stat-value"><?= $summary['late'] ?></span>
            </div>
        </div>
        <div class="report-stat-card">
            <div class="stat-icon-bg bg-red-100 text-red-600"><i class="fa-solid fa-user-xmark"></i></div>
            <div class="stat-content">
                <span class="stat-label">Absences</span>
                <span class="stat-value"><?= $summary['absent'] ?></span>
            </div>
        </div>
        <div class="report-stat-card">
            <div class="stat-icon-bg bg-indigo-100 text-indigo-600"><i class="fa-solid fa-building-user"></i></div>
            <div class="stat-content">
                <span class="stat-label">Office Duties</span>
                <span class="stat-value"><?= $summary['office'] ?></span>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: #059669; border-bottom: 1px solid #e2e8f0; padding: 1.25rem;">
        <h3 style="margin: 0; font-weight: 700; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-filter" style="color: #6366f1;"></i> Filter & Export History
        </h3>
    </div>
    <div class="card-body" style="padding: 1.5rem;">
        <form method="GET" action="attendance_history.php">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; align-items: flex-end;">
                
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Date Range</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="date" name="start_date" id="startDate" class="form-control" value="<?= htmlspecialchars($filters['start_date']) ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        <input type="date" name="end_date" id="endDate" class="form-control" value="<?= htmlspecialchars($filters['end_date']) ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>
                </div>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                    <div class="form-group">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Quick Search</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text" id="historyLiveSearch" onkeyup="filterHistoryTable()" placeholder="Name, ID, or Status..." 
                        style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>
                </div>
                <?php endif; ?>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn" style="flex: 1; background: #059669; color: white; padding: 11px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer;">
                        <i class="fa-solid fa-check"></i> Apply
                    </button>
                    <a href="attendance_reports.php?action=export_csv&<?= http_build_query($_GET) ?>" class="btn" 
                       style="flex: 1; background: #cc980a; color: white; padding: 11px; border-radius: 8px; font-weight: 700; text-decoration: none; text-align: center; display: inline-block;">
                        <i class="fa-solid fa-file-csv"></i> Export
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

    <div class="card" style="border-radius: 12px; border: 1px solid #ffffff; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: #059669; color: #ffffff; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-weight: 700; color: #ffffff; letter-spacing: 0.5px;">
            <i class="fa-solid fa-table-list" style="margin-right: 10px; color: #ffffff;"></i> Attendance Records
        </h3>
        <span style="font-size: 0.8rem; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-weight: 600;">
            Monthly Summary
        </span>
    </div>
        <div class="card-body" style="padding: 0;">
            <table class="directory-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject/Duty</th>
                        <th>Type</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">No records found for the selected criteria.</td></tr>
                    <?php else: foreach ($records as $row): ?>
                        <tr>
                            <td style="font-weight: 600; color: #1e293b;"><?= date('M d, Y', strtotime($row['date'])) ?></td>
                            <td><?= htmlspecialchars($row['duty_subject'] ?? 'General') ?></td>
                            <td><span class="session-pill" style="font-size: 0.75rem;"><?= $row['duty_type'] ?? 'Class' ?></span></td>
                            <td style="color: #059669; font-weight: 600;"><?= $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '--:--' ?></td>
                            <td style="color: #2563eb; font-weight: 600;"><?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '--:--' ?></td>
                            <td>
                                <span class="status-label status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>