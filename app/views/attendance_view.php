<?php 
require_once __DIR__ . '/partials/header.php'; 
?>
<link rel="stylesheet" href="css/print.css">

<?php
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $subject = $_POST['subject'] ?? 'Attendance Record Discrepancy';
    $messageBody = $_POST['message'] ?? '';
    $adminEmail = getenv('SMTP_USER');
    $userFullName = $_SESSION['full_name'];
    $userEmail = $_SESSION['email'] ?? 'No Email Provided';
    $fullContent = "<h2>Attendance Discrepancy Report</h2><p>From: {$userFullName}</p><p>{$messageBody}</p>";

    if (Mailer::send($adminEmail, $subject, $fullContent)) {
        $success = "Your discrepancy report has been sent to the IT Department.";
    } else {
        $error = "Failed to send report. Please visit the IT office directly.";
    }
}
?>

<style>
.report-stat-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.report-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    border-color: rgba(0,0,0,0.05);
    background: #fff;
}

.report-stat-card:active { 
    transform: translateY(-2px); 
}

.attendance-table-new tbody tr.accordion-toggle:nth-child(4n+1) { 
    background-color: #ffffff; 
}

.attendance-table-new tbody tr.accordion-toggle:nth-child(4n+3) { 
    background-color: #f8fafc; 
}

.attendance-table-new tbody tr.accordion-toggle:hover { 
    background-color: #f1f5f9 !important; 
}

.session-pill {
    background: #eff6ff;
    color: #2563eb;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.85rem;
    border: 1px solid #bfdbfe;
    display: inline-block;
    transition: all 0.2s ease;
}

.session-pill:hover {
    background: #dbeafe;
    border-color: #3b82f6;
}

.directory-table { 
    width: 100%; border-collapse: collapse; 
}

.directory-table th { 
    background: #f1f5f9; padding: 12px; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase; 
}

.directory-table td { 
    padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; 
}

.btn-feedback {
    background: #4b5563;
    color: white;
    border-radius: 50px;
    padding: 8px 20px;
    font-weight: 600;
    transition: 0.3s;
}
.btn-feedback:hover { 
    background: #374151; 
}

#feedbackModal .modal-content {
    max-width: 550px;
    border-radius: 16px;
    border-top: 6px solid #059669;
}
.feedback-header-box {
    text-align: center;
    padding: 1.5rem 0;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 2rem;
}
.feedback-header-box i {
    font-size: 2.5rem;
    color: #059669;
    margin-bottom: 10px;
}
.feedback-textarea {
    min-height: 140px;
    border-radius: 10px;
    padding: 15px;
    border: 2px solid #e2e8f0;
    transition: border-color 0.2s;
}
.feedback-textarea:focus { 
    border-color: #059669; outline: none; 
}

#dtrPreviewModal .modal-content {
    max-width: 1400px;
    width: 98%;
    height: 96vh;
    margin: 2vh auto;
    display: flex;
    flex-direction: column;
    border-radius: 12px;
    border: none;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

#dtrPreviewModal .modal-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 1rem 1.5rem;
}

#dtrPreviewModal .modal-body {
    flex: 1;
    padding: 0 !important;
    display: flex;
    overflow: hidden;
}

#dtrFrame {
    width: 100%;
    height: 100%;
    border: none;
    background: #fff;
}

#dtrPreviewModal .modal-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stylish-input:hover { border-color: #cbd5e1 !important; background: #fff !important; }
    .stylish-input:focus { border-color: #6366f1 !important; background: #fff !important; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important; }
    
    .action-btn:hover { transform: translateY(-1px); filter: brightness(105%); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .action-btn:active { transform: translateY(0); }
    
    .date-field::-webkit-calendar-picker-indicator { cursor: pointer; filter: opacity(0.6) sepia(100%) saturate(200%) hue-rotate(190deg); }
</style>

<div class="main-body attendance-reports-page"> 
    <div class="info-card-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 20px;">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid <?= $isAdmin ? 'fa-chart-line' : 'fa-user-clock' ?>"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">
                <?= $isAdmin ? 'Personnel Attendance Reports' : 'My Personal Attendance' ?>
            </h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">
                <?= $isAdmin ? 'Manage personnel logs and generate administrative time records.' : 'Monitor your daily logs and track your attendance performance.' ?>
            </p>
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
                    The Attendance reports page summarizes entries and exits for today. Use the 
                    <span style="color: #6366f1; font-weight: 600;">Filter & Reports</span> section below to 
                    search specific dates or generate your <span style="color: #6366f1; font-weight: 600;">Personal DTR Record</span> 
                    previews.
                </p>
            </div>
        </div>
    </div>

    <div class="report-stats-grid">
        <div class="report-stat-card" onclick="openAttendanceDetail('entries', 'Today\'s Total Entries')">
            <div class="stat-icon-bg bg-emerald-100 text-emerald-600"><i class="fa-solid fa-arrow-right-to-bracket"></i></div>
            <div class="stat-content">
                <span class="stat-label">Entries Today</span>
                <span class="stat-value"><?= $stats['entries'] ?? 0 ?></span>
            </div>
        </div>
        <div class="report-stat-card" onclick="openAttendanceDetail('exits', 'Today\'s Total Exits')">
            <div class="stat-icon-bg bg-red-100 text-red-600"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <div class="stat-content">
                <span class="stat-label">Exits Today</span>
                <span class="stat-value"><?= $stats['exits'] ?? 0 ?></span>
            </div>
        </div>
        <div class="report-stat-card" onclick="openAttendanceDetail('present', 'Personnel Currently On-Site')">
            <div class="stat-icon-bg bg-blue-100 text-blue-600"><i class="fa-solid fa-user-check"></i></div>
            <div class="stat-content">
                <span class="stat-label">Present</span>
                <span class="stat-value"><?= $stats['present_total'] ?? 0 ?></span>
            </div>
        </div>
        <div class="report-stat-card" onclick="openAttendanceDetail('late', 'Today\'s Late Arrivals')">
            <div class="stat-icon-bg bg-orange-100 text-orange-600"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-content">
                <span class="stat-label">Late Logs</span>
                <span class="stat-value"><?= $stats['late'] ?? 0 ?></span>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem; gap: 10px;">
        <a href="attendance_history.php" class="btn" style="background: #6366f1; color: white; border-radius: 50px; padding: 8px 20px; font-weight: 600; text-decoration: none; transition: 0.3s;">
        <i class="fa-solid fa-clock-rotate-left"></i> Attendance History
        </a>
        <button class="btn btn-feedback" onclick="openModal('feedbackModal')">
            <i class="fa-solid fa-comment-dots"></i> Report Discrepancy
        </button>
    </div>

    <div class="filter-export-section card" style="border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 2rem;">
    <div class="card-header" style="background: #059669; border-bottom: none; padding: 1.25rem;">
        <h3 style="margin: 0; font-size: 1.1rem; color: #ffffff; font-weight: 700; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-filter" style="color: rgba(255,255,255,0.8);"></i> Filter & Reports
        </h3>
    </div>
    <div class="card-body" style="padding: 1.5rem; background: white;">
        <form method="GET" action="attendance_reports.php" id="reportFilterForm" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1.5rem;">
            
            <?php if ($isAdmin): ?>
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">Select User</label>
                <div style="position: relative;">
                    <select name="user_id" id="userId" class="stylish-input" style="width: 100%; padding: 10px 40px 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: 'Inter', sans-serif; font-weight: 500; color: #1e293b; background: #f8fafc; appearance: none; cursor: pointer; outline: none; transition: all 0.2s;">
                        <option value="">-- Select User --</option>
                        <?php if (!empty($allUsers)): ?>
                            <?php foreach ($allUsers as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= (isset($filters['user_id']) && $filters['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <i class="fa-solid fa-chevron-down" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #94a3b8; font-size: 0.8rem;"></i>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group" style="flex: 1.5; min-width: 280px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">Date Range</label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="date" name="start_date" id="startDate" class="stylish-input date-field" value="<?= htmlspecialchars($filters['start_date'] ?? date('Y-m-01')) ?>" style="flex: 1; padding: 10px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: 'Inter', sans-serif; outline: none; background: #f8fafc;">
                    <span style="color: #cbd5e1; font-weight: bold;">&rarr;</span>
                    <input type="date" name="end_date" id="endDate" class="stylish-input date-field" value="<?= htmlspecialchars($filters['end_date'] ?? date('Y-m-d')) ?>" style="flex: 1; padding: 10px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: 'Inter', sans-serif; outline: none; background: #f8fafc;">
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="action-btn" style="padding: 11px 18px; background: #05865b; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; white-space: nowrap;">
                    <i class="fa-solid fa-check"></i> Apply Filter
                </button>
                <button type="button" class="action-btn" onclick="handlePrintDTR()" style="padding: 11px 18px; background: #f59e0b; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; white-space: nowrap;">
                    <i class="fa-solid fa-file-invoice"></i> Generate DTR
                </button>
            </div>
        </form>
    </div>
</div>
    
    <div class="card attendance-table-card">
         <div class="card-body" style="padding: 0; overflow-x: auto;"> 
            <?php if (empty($records)): ?>
                <p style="text-align: center; color: #059669; padding: 40px;">No attendance records found.</p>
            <?php else: ?>
                <table class="attendance-table-new accordion-table" id="attendanceMainTable" style="min-width: 1000px;">
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
                <span class="user-name" style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($row['name']) ?></span>
                <span class="user-id" style="display:block; font-size:0.75rem; color:#64748b;">ID: <?= htmlspecialchars($row['faculty_id']) ?></span>
            </div>
        </td>
        <td><span class="date-cell"><?= date('M d, Y', strtotime($row['date'])) ?></span></td>
        <td><span class="session-pill"><?= count($row['logs']) ?> Scan Record(s)</span></td>
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
                    <i class="fa-solid fa-fingerprint"></i> Duty Scan Breakdown (AM/PM Summary)
                </h4>

                <div class="scan-logs-grid" style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <?php 
                    $am_in = null; $am_out = null;
                    $pm_in = null; $pm_out = null;

                    foreach ($row['logs'] as $log) {
                        $tin = strtotime($log['time_in']);
                        $tout = !empty($log['time_out']) ? strtotime($log['time_out']) : null;
                        $noon = strtotime('12:00:00');
                        $grace = strtotime('12:30:00');

                        // Morning Bucket
                        if ($tin < $noon) {
                            if ($am_in === null || $tin < $am_in) $am_in = $tin;
                            if ($tout && $tout <= $grace) {
                                if ($am_out === null || $tout > $am_out) $am_out = $tout;
                            }
                        } else {
                            // Afternoon Bucket
                            if ($pm_in === null || $tin < $pm_in) $pm_in = $tin;
                        }

                        // PM Out is the absolute latest out recorded
                        if ($tout && ($pm_out === null || $tout > $pm_out)) {
                            $pm_out = $tout;
                        }
                    }
                    ?>

                    <?php if ($am_in): ?>
                    <div class="scan-log-card" style="background: white; border: 1px solid #e2e8f0; border-left: 4px solid #059669; border-radius: 8px; padding: 12px; min-width: 220px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <span style="font-size: 0.7rem; font-weight: 800; color: #059669; text-transform: uppercase;">
                            <i class="fa-solid fa-sun"></i> Morning Session
                        </span>
                        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                            <div><small style="color: #94a3b8;">IN</small><br><strong><?= date('g:i A', $am_in) ?></strong></div>
                            <div><small style="color: #94a3b8;">OUT</small><br><strong><?= $am_out ? date('g:i A', $am_out) : '---' ?></strong></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($pm_in): ?>
                    <div class="scan-log-card" style="background: white; border: 1px solid #e2e8f0; border-left: 4px solid #2563eb; border-radius: 8px; padding: 12px; min-width: 220px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <span style="font-size: 0.7rem; font-weight: 800; color: #2563eb; text-transform: uppercase;">
                            <i class="fa-solid fa-moon"></i> Afternoon Session
                        </span>
                        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                            <div><small style="color: #94a3b8;">IN</small><br><strong><?= date('g:i A', $pm_in) ?></strong></div>
                            <div><small style="color: #94a3b8;">OUT</small><br><strong><?= $pm_out ? date('g:i A', $pm_out) : '---' ?></strong></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!$am_in && !$pm_in): ?>
                        <span style="color: #94a3b8; font-style: italic; font-size: 0.85rem;">No session logs recorded.</span>
                    <?php endif; ?>
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
<div id="selectionWarningModal" class="modal">
        <div class="modal-content" style="max-width: 420px; text-align: center; border-top: 5px solid #059669;">
            <div class="modal-body" style="padding: 2.5rem;">
                <i class="fa-solid fa-user-clock" style="font-size: 3.5rem; color: #059669; margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.5rem; font-weight: 800;">Action Required</h3>
                <p style="color: #64748b; margin-bottom: 1.5rem;">To generate a Daily Time Record preview, please select a specific faculty member from the dropdown filter first.</p>
                <button class="btn btn-primary btn-full-width" onclick="closeModal('selectionWarningModal')" style="padding: 12px; border-radius: 8px; font-weight: 600;">Close & Select</button>
            </div>
        </div>
    </div>

    <div id="attendanceDetailModal" class="modal">
        <div class="modal-content" style="max-width: 900px; width: 95%;">
            <div class="modal-header">
                <h3 id="detailModalTitle">Attendance Details</h3>
                <button class="modal-close" onclick="closeModal('attendanceDetailModal')">&times;</button>
            </div>
            <div class="modal-body" id="detailModalBody" style="min-height: 200px; padding: 0;"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('attendanceDetailModal')">Close</button>
            </div>
        </div>
    </div>

    <div id="dtrPreviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h3><i class="fa-solid fa-file-invoice"></i> CS Form 48 - DTR Preview</h3>
                <button class="modal-close" onclick="closeModal('dtrPreviewModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding: 1rem; flex-grow: 1;">
                <iframe id="dtrFrame" src="about:blank"></iframe>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between;">
                <p style="font-size: 0.8rem; color: #64748b;">*This is a preview of your DTR Record. Print DTR by pressing the Print button at the top.</p>
            </div>
        </div>
    </div>
    
    <div id="feedbackModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; width: 95%; max-width: 500px; border-radius: 20px; border-top: 8px solid #f59e0b; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
        
        <div style="padding: 25px 30px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-weight: 800; color: #1e293b !important;">Report Discrepancy</h3>
            <button onclick="closeFeedbackModal()" style="background: none; border: none; font-size: 1.8rem; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
        </div>
        
        <form method="POST" action="attendance_reports.php">
            <div style="padding: 30px;">
                <input type="hidden" name="subject" value="Attendance Record Discrepancy">
                
                <div style="background: #fffbeb; border: 1px solid #fef3c7; padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                    <p style="margin: 0; font-size: 0.85rem; color: #92400e; line-height: 1.6;">
                        <i class="fa-solid fa-circle-info" style="margin-right: 5px;"></i>
                        Please specify the <strong>Date</strong> and <strong>Time</strong> of the error. This message will be sent directly to the IT Administration for verification.
                    </p>
                </div>

                <div class="form-group">
                    <label style="font-weight: 700; color: #475569; display: block; margin-bottom: 8px;">Discrepancy Details</label>
                    <textarea name="message" class="form-control" rows="5" 
                        placeholder="Example: On Jan 22, my Time-Out was 5:00 PM but it shows as 4:30 PM..." 
                        required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-family: inherit; resize: none;"></textarea>
                </div>
            </div>
            
            <div style="padding: 20px 30px; background: #f8fafc; display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeFeedbackModal()" class="btn btn-secondary">Cancel</button>
        <button type="submit" name="submit_contact" class="btn btn-primary" style="background: #f59e0b; border: none;">
            <i class="fa-solid fa-paper-plane"></i> Submit Report
        </button>
            </div>
        </form>
    </div>
</div>

    <div id="feedbackSuccessModal" class="modal">
        <div class="modal-content modal-small" style="text-align: center;">
            <div class="modal-body" style="padding: 3rem 1rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: #10b981; margin-bottom: 1.5rem;"></i>
                <h3>Feedback Sent</h3>
                <p>The administrator has been notified. They will review your attendance logs shortly.</p>
                <button class="btn btn-primary" style="margin-top: 1.5rem;" onclick="closeModal('feedbackSuccessModal')">Got it</button>
            </div>
        </div>
    </div>

</div>

<div id="statusPopupModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 999999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; width: 90%; max-width: 400px; border-radius: 20px; border-top: 8px solid <?= $success ? '#10b981' : '#ef4444' ?>; text-align: center; padding: 40px 30px;">
        <div style="width: 70px; height: 70px; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 35px; <?= $success ? 'background: #ecfdf5; color: #10b981;' : 'background: #fef2f2; color: #ef4444;' ?>">
            <i class="fa-solid <?= $success ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
        </div>
        <h3 style="font-weight: 800; color: #1e293b;"><?= $success ? 'Report Submitted' : 'Submission Failed' ?></h3>
        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5; margin-top: 10px;">
            <?= htmlspecialchars(($success ?? $error) ?? '') ?>
        </p>
        <button onclick="document.getElementById('statusPopupModal').style.display='none'; document.body.style.overflow='auto';" style="margin-top: 25px; background: #1e293b; color: white; border: none; padding: 10px 40px; border-radius: 50px; font-weight: 700; cursor: pointer;">
            Got it!
        </button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if ($success || $error): ?>
        const statusModal = document.getElementById('statusPopupModal');
        if (statusModal) {
            statusModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    <?php endif; ?>
});
</script>

<script>
function filterTableRealTime() {
    const input = document.getElementById("liveSearchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#attendanceMainTable tbody tr.accordion-toggle");

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const isMatch = text.includes(input);
        row.style.display = isMatch ? "" : "none";
        
        const detailId = row.getAttribute('onclick').match(/'([^']+)'/)[1];
        const detailRow = document.getElementById(detailId);
        if (detailRow) {
             detailRow.style.display = "none";
             row.classList.remove('active');
        }
    });
}

function handlePrintDTR() {
    const userIdInput = document.getElementById('userId');
    const userId = userIdInput ? userIdInput.value : '<?= $_SESSION['user_id'] ?>';
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (!userId) {
        openModal('selectionWarningModal');
        return;
    }

    const url = `attendance_reports.php?action=print_dtr&user_id=${userId}&start_date=${startDate}&end_date=${endDate}`;
    document.getElementById('dtrFrame').src = url;
    openModal('dtrPreviewModal');
}

function openAttendanceDetail(type, title) {
    document.getElementById('detailModalTitle').innerText = title;
    document.getElementById('detailModalBody').innerHTML = '<div style="padding: 50px; text-align: center;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Fetching records...</p></div>';
    openModal('attendanceDetailModal');

    fetch(`api.php?action=get_attendance_summary&type=${type}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = '<table class="directory-table"><thead><tr><th>Faculty ID</th><th>Name</th><th>Position</th><th>Status/Time</th></tr></thead><tbody>';
                data.users.forEach(u => {
                    let logTime = u.time_in || u.time_out || '<span style="color:#10b981; font-weight:700;">On-Site</span>';
                    html += `<tr>
                        <td style="font-weight:700;">${u.faculty_id}</td>
                        <td style="font-weight:600;">${u.first_name} ${u.last_name}</td>
                        <td>${u.role}</td>
                        <td>${logTime}</td>
                    </tr>`;
                });
                if(data.users.length === 0) html += '<tr><td colspan="4" style="text-align:center; padding:40px; color:#94a3b8;">No records found.</td></tr>';
                html += '</tbody></table>';
                document.getElementById('detailModalBody').innerHTML = html;
            }
        });
}

function openFeedbackModal() {
    const modal = document.getElementById('feedbackModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeFeedbackModal() {
    const modal = document.getElementById('feedbackModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('feedbackModal');
    if (event.target == modal) {
        closeFeedbackModal();
    }
}

async function submitFeedback() {
    const btn = document.getElementById('submitBtn');
    const form = btn.closest('form');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

    try {
        const formData = new FormData(form);
        formData.append('submit_contact', '1');

        const response = await fetch('attendance_reports.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await response.json();

        closeFeedbackModal();
        form.reset();

        const statusModal = document.getElementById('statusPopupModal');
        
        if (statusModal) {
            statusModal.style.display = 'flex';
            
            const pTag = statusModal.querySelector('p');
            const h3Tag = statusModal.querySelector('h3');
            
            if (pTag) pTag.innerText = data.message;
            if (h3Tag) h3Tag.innerText = data.success ? 'Report Submitted' : 'Submission Failed';
            
            document.body.style.overflow = 'hidden';
        } else {

            alert(data.message);
        }

    } catch (err) {
        console.error("Popup Logic Error:", err);
        alert("Report sent successfully! (Note: Success modal was not found in this file).");
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function toggleAccordion(contentId, toggleEl) {
    const content = document.getElementById(contentId);
    const isVisible = content.style.display !== 'none';
    content.style.display = isVisible ? 'none' : 'table-row';
    toggleEl.classList.toggle('active');
}

window.closeDtrModal = function() {
    closeModal('dtrPreviewModal');
    document.getElementById('dtrFrame').src = 'about:blank';
};

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        const id = event.target.id;
        closeModal(id);
        if(id === 'dtrPreviewModal') document.getElementById('dtrFrame').src = 'about:blank';
    }
};
</script>

<?php require_once __DIR__ . '/partials/footer.php'?>