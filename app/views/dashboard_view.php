<?php 
require_once __DIR__ . '/partials/header.php'; 
?>
<style>
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

.ud-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    border: 1px solid transparent;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.ud-badge.completed {
    background-color: #ecfdf5;
    color: #059669; 
    border-color: #a7f3d0; 
}

.ud-badge.pending {
    background-color: #fffbeb;
    color: #d97706;           
    border-color: #fde68a;   
}

.ud-badge.not-present {
    background-color: #f8fafc; 
    color: #64748b;            
    border-color: #e2e8f0;   
}

.ud-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}

.ud-card-row:last-child {
    border-bottom: none;
}

.ud-badge.absent {
    background-color: #fef2f2;
    color: #dc2626;            
    border-color: #fecaca;  
}
</style>

<?php if ($isAdmin): ?>

    <div class="main-body admin-dashboard">
    <div class="info-card-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 20px;">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-gauge-high"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">System Administrator Dashboard</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Real-time overview of faculty attendance, system health, and pending requests.</p>
        </div>
    </div>

    <div class="info-guide-wrapper" style="margin-bottom: 2.5rem; padding: 0 5px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="background: #f1f5f9; color: #64748b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Admin Tip:</span>
                    Quickly view today's active sessions and total headcount. For detailed corrections, navigate to the 
                    <span style="color: #6366f1; font-weight: 600;">Attendance Reports</span> section.
                </p>
            </div>
        </div>
    </div>

        <div style="margin-bottom: 1.5rem; display: flex; justify-content: flex-end;">
            <a href="display.php?key=BPC_TRUSCAN_KIOSK_2026" target="_blank" class="btn btn-primary" style="background-color: var(--blue-600); border-color: var(--blue-600);">
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

    <div class="info-card-header">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-house-user"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Dashboard</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Welcome back! Here is a snapshot of your current attendance and registration status.</p>
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
                    The cards below summarize your activity for today. For a comprehensive breakdown of all past records, 
                    please visit your <span style="color: #6366f1; font-weight: 600;">Attendance History</span> page.
                </p>
            </div>
        </div>
    </div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon emerald"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-details">
            <p>Today's Entries</p>
            <div class="stat-value emerald"><?= $stats['entries'] ?? 0 ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-details">
            <p>Late Arrivals</p>
            <div class="stat-value yellow"><?= $stats['late'] ?? 0 ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-door-open"></i></div>
        <div class="stat-details">
            <p>Current Status</p>
            <div class="stat-value blue" style="font-size: 1rem;">
                <?= ($stats['present_total'] > 0) ? 'Currently In' : 'Timed Out' ?>
            </div>
        </div>
    </div>
</div>


        <div class="ud-grid">
    <div class="ud-card" style="border-top: 6px solid var(--emerald-500);">
    <h3 class="ud-card-title emerald-header">
        <i class="fa-solid fa-calendar-day"></i> Today's Attendance
    </h3>
    <div class="ud-card-content">
        <div class="ud-card-row">
    <span class="ud-card-label" style="font-weight: 600; color: #475569;">Attendance Status</span>
    <?php
        $status = $attendance['status'] ?? 'Not Present';
        $badgeClass = 'not-present';
        $icon = 'fa-circle-xmark';

        if ($status === 'Present' || $status === 'On-time') {
            $badgeClass = 'completed';
            $icon = 'fa-circle-check';
        } elseif ($status === 'Late') {
            $badgeClass = 'pending';
            $icon = 'fa-clock';
        } elseif ($status === 'Absent') {
            $badgeClass = 'absent';
            $icon = 'fa-user-xmark';
        }
    ?>
    <span class="ud-badge <?= $badgeClass ?>">
        <i class="fa-solid <?= $icon ?>"></i> <?= htmlspecialchars($status) ?>
    </span>
</div>
        <div class="ud-card-row">
            <span class="ud-card-label" style="color: #64748b;">First In</span> <span class="ud-card-value" style="font-family: monospace; font-weight: 700; color: #1e293b;">
                <?= isset($attendance['time_in']) ? date('g:i A', strtotime($attendance['time_in'])) : '--:-- --' ?>
            </span>
        </div>
        <div class="ud-card-row">
            <span class="ud-card-label" style="color: #64748b;">Last Out</span> <span class="ud-card-value" style="font-family: monospace; font-weight: 700; color: #1e293b;">
                <?= isset($attendance['time_out']) ? date('g:i A', strtotime($attendance['time_out'])) : '--:-- --' ?>
            </span>
        </div>
    </div>
</div>

    <div class="ud-card" style="border-top: 6px solid var(--emerald-500);">
        <h3 class="ud-card-title emerald-header">
            <i class="fa-solid fa-id-card-clip"></i> Registration Status
        </h3>
        <div class="ud-card-content">
            <div class="ud-card-row">
                <span class="ud-card-label" style="font-weight: 600; color: #475569;">System Account</span>
                <span class="ud-badge completed"><i class="fa-solid fa-user-check"></i> Active</span>
            </div>
            <div class="ud-card-row">
                <span class="ud-card-label" style="font-weight: 600; color: #475569;">Biometrics</span>
                <?php if ($fingerprint_registered): ?>
                    <span class="ud-badge completed"><i class="fa-solid fa-fingerprint"></i> Registered</span>
                <?php else: ?>
                    <span class="ud-badge pending"><i class="fa-solid fa-triangle-exclamation"></i> Registration Required </span>
                <?php endif; ?>
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
<div style="text-align: right; margin-top: 1rem; border-top: 1px solid var(--gray-100); padding-top: 1rem;">
            <a href="activity_log.php" class="btn btn-sm btn-secondary">
                View All My Activity &rarr;
            </a>
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