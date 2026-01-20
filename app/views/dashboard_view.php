<?php 
require_once __DIR__ . '/partials/header.php'; 
?>

<?php if ($isAdmin): ?>
    <div class="main-body admin-dashboard">
        
        <div style="margin-bottom: 1.5rem; display: flex; justify-content: flex-end;">
            <a href="display.php" target="_blank" class="btn btn-primary" style="background-color: var(--blue-600); border-color: var(--blue-600);">
                <i class="fa-solid fa-desktop"></i> Launch Attendance Display
            </a>
        </div>

        <div class="stats-grid">
            <a href="create_account.php" class="stat-card clickable-card" title="Manage User Accounts">
                <div class="stat-icon emerald">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-details">
                    <p>Total Users</p>
                    <div class="stat-value emerald"><?= $totalUsers ?></div>
                </div>
            </a>

            <a href="attendance_reports.php" class="stat-card clickable-card" title="View Attendance Records">
                <div class="stat-icon yellow">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div class="stat-details">
                    <p>Active Today</p>
                    <div class="stat-value"><?= $activeToday ?></div>
                </div>
            </a>

            <a href="complete_registration.php" class="stat-card clickable-card" title="Pending Biometric Registrations">
                <div class="stat-icon red">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div class="stat-details">
                    <p>Pending Registration</p>
                    <div class="stat-value red"><?= $pendingRegistrations ?></div>
                </div>
            </a>
        </div>

        <div class="card" id="recent-activity-card">
            <div class="card-header">
                <h3>Recent Activity</h3>
            </div>
            <div class="card-body">
                <?php if (empty($activityLogs)): ?>
                    <p style="text-align: center; color: var(--gray-500); padding: 2rem;">No recent activity found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Details</th>
                                <th>User</th>
                                <th>Time & Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activityLogs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['action']) ?></td>
                                    <td><?= htmlspecialchars($log['description']) ?></td>
                                    <td><?= htmlspecialchars(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? 'System')) ?></td>
                                    <td><?= date('M d, Y g:i A', strtotime($log['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                 <div style="text-align: right; margin-top: 1rem;">
                     <a href="activity_log.php" class="btn btn-sm btn-secondary">View All Activity &rarr;</a>
                 </div>
            </div>
        </div>

        <div class="card info-card-minimal" style="margin-top: 2rem; border-left: 5px solid #e3a406; background-color: #fffbeb;">
            <div class="card-body" style="padding: 1.5rem;">
                <div style="display: flex; align-items: flex-start; gap: 1rem;">
                    <div style="color: #fbbf24; font-size: 1.5rem; margin-top: 0.2rem;">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 style="color: #92400e; margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 700;">System Overview</h3>
                        <p style="color: #78350f; line-height: 1.6; font-size: 0.95rem; margin: 0;">
                            <strong>BPC TruScan</strong> is a biometric attendance system for Bulacan Polytechnic College. 
                            It automates the collection of attendance data and the generation of CS Form 48 (DTR) using secure fingerprint recognition, 
                            ensuring accurate and verifiable timekeeping for all faculty and staff.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="main-body user-dashboard-body">
        
        <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
            <a href="attendance_history.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-clock-rotate-left"></i> View My Attendance History
            </a>
        </div>

        <div class="ud-grid">
            <div class="ud-card">
                <h3 class="ud-card-title emerald-header">
                    <i class="fa-solid fa-clipboard-check"></i> Registration Status
                </h3>
                <div class="ud-card-content">
                    <div class="ud-card-row">
                        <span class="ud-card-label">Account Created</span>
                        <span class="ud-badge completed">Completed</span>
                    </div>
                    <div class="ud-card-row">
                        <span class="ud-card-label">Fingerprint Registered</span>
                        <?php if ($fingerprint_registered): ?>
                            <span class="ud-badge completed">Completed</span>
                        <?php else: ?>
                            <span class="ud-badge pending">Pending</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="ud-card">
                <h3 class="ud-card-title emerald-header">
                    <i class="fa-solid fa-calendar-check"></i> Today's Attendance
                </h3>
                <div class="ud-card-content">
                    <div class="ud-card-row">
                        <span class="ud-card-label">Status</span>
                        <?php
                            $status = $attendance['status'] ?? 'Not Present';
                            $statusClass = 'not-present';
                            if ($status === 'Present' || $status === 'On-time') $statusClass = 'completed';
                            if ($status === 'Late') $statusClass = 'pending';
                        ?>
                        <span class="ud-badge <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
                    </div>
                    <div class="ud-card-row">
                        <span class="ud-card-label">Time In</span>
                        <span class="ud-card-value">
                            <?= isset($attendance['time_in']) ? date('g:i A', strtotime($attendance['time_in'])) : '------' ?>
                        </span>
                    </div>
                    <div class="ud-card-row">
                        <span class="ud-card-label">Time Out</span>
                        <span class="ud-card-value">
                            <?= isset($attendance['time_out']) ? date('g:i A', strtotime($attendance['time_out'])) : '------' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="ud-card ud-activity-card">
            <h3 class="ud-card-title emerald-header">
                <i class="fa-solid fa-history"></i> My Recent Activity
            </h3>
            <div class="ud-card-content">
                <?php if (empty($activityLogs)): ?>
                    <div class="ud-activity-empty">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>No activity recorded.</span>
                    </div>
                <?php else: ?>
                    <div class="ud-activity-list">
                        <?php foreach ($activityLogs as $log): ?>
                            <div class="ud-activity-item">
                                <div class="ud-activity-details">
                                    <strong class="ud-activity-action"><?= htmlspecialchars($log['action']) ?></strong>
                                    <span class="ud-activity-description"><?= htmlspecialchars($log['description']) ?></span>
                                </div>
                                <span class="ud-activity-time">
                                    <?= date('M d, Y g:i A', strtotime($log['created_at'])) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
             </div>
        </div>

        <div class="page-hint-card">
            <div class="page-hint-icon">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <div class="page-hint-content">
                <h4>Note!</h4>
                <p>
                    This is your main dashboard. You can quickly see your registration status and check if your attendance for today has been recorded. Use the "View My Attendance History" button to see your complete attendance records.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div id="scannerOnlineModal" class="modal">
    <div class="modal-content modal-small" style="text-align: center; border-top: 5px solid #10b981;">
        <div class="modal-body" style="padding: 2.5rem 1.5rem;">
            <i class="fa-solid fa-circle-check" style="color: #15ac79; font-size: 3.5rem; margin-bottom: 1rem;"></i>
            <h3>Scanner Operational</h3>
            <p style="color: #64748b; margin-top: 0.5rem;">The fingerprint scanner is working properly. <strong>Please keep the scanner surface clean</strong> to ensure accurate biometric reading.</p>
        </div>
        <div class="modal-footer" style="justify-content: center;"><button class="btn btn-primary" onclick="closeModal('scannerOnlineModal')">Got it!</button></div>
    </div>
</div>

<div id="scannerOfflineModal" class="modal">
    <div class="modal-content modal-small" style="text-align: center; border-top: 5px solid #ef4444;">
        <div class="modal-body" style="padding: 2.5rem 1.5rem;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444; font-size: 3.5rem; margin-bottom: 1rem;"></i>
            <h3>Scanner Offline</h3>
            <p style="color: #64748b; margin-top: 0.5rem;">The system cannot detect the fingerprint scanner. Please <strong>check the USB connection</strong> and ensure the device is connected properly.</p>
        </div>
        <div class="modal-footer" style="justify-content: center;"><button class="btn btn-secondary" onclick="closeModal('scannerOfflineModal')">Close</button></div>
    </div>
</div>

<style>
.clickable-card {
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
    display: flex;
}
.clickable-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}


</style>

<script>
function checkScannerStatus(isOnline) {
    if (isOnline) {
        openModal('scannerOnlineModal');
    } else {
        openModal('scannerOfflineModal');
    }
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>