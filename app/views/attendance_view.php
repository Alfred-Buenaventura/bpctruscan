<?php 
require_once __DIR__ . '/partials/header.php'; 
?>

<style>
/* 1. Status Card Interactivity */
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
.report-stat-card:active { transform: translateY(-2px); }

/* 2. Zebra Striping (Odd/Even) */
.attendance-table-new tbody tr.accordion-toggle:nth-child(4n+1) { background-color: #ffffff; }
.attendance-table-new tbody tr.accordion-toggle:nth-child(4n+3) { background-color: #f8fafc; }
.attendance-table-new tbody tr.accordion-toggle:hover { background-color: #f1f5f9 !important; }

/* Design Elements */
.session-pill {
    background: #f1f5f9;
    color: #475569;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
    border: 1px solid #e2e8f0;
    display: inline-block;
}

.directory-table { width: 100%; border-collapse: collapse; }
.directory-table th { background: #f1f5f9; padding: 12px; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase; }
.directory-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }

/* Feedback Button */
.btn-feedback {
    background: #4b5563;
    color: white;
    border-radius: 50px;
    padding: 8px 20px;
    font-weight: 600;
    transition: 0.3s;
}
.btn-feedback:hover { background: #374151; }

/* PROFESSIONAL FEEDBACK MODAL STYLES */
#feedbackModal .modal-content {
    max-width: 550px;
    border-radius: 16px;
    border-top: 6px solid var(--blue-600);
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
    color: var(--blue-600);
    margin-bottom: 10px;
}
.feedback-textarea {
    min-height: 140px;
    border-radius: 10px;
    padding: 15px;
    border: 2px solid #e2e8f0;
    transition: border-color 0.2s;
}
.feedback-textarea:focus { border-color: var(--blue-400); outline: none; }

/* DTR PREVIEW MODAL STYLES */
#dtrPreviewModal .modal-content {
    max-width: 1000px;
    width: 95%;
    height: 90vh;
    display: flex;
    flex-direction: column;
}
#dtrFrame {
    flex-grow: 1;
    width: 100%;
    border: none;
    border-radius: 8px;
    background: #f1f5f9;
}
</style>

<div class="main-body attendance-reports-page"> 

    <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem; gap: 10px;">
        <button class="btn btn-feedback" onclick="openModal('feedbackModal')">
            <i class="fa-solid fa-comment-dots"></i> Report Discrepancy
        </button>
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
                                <?php if (!empty($allUsers)): ?>
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
                        <i class="fa-solid fa-file-invoice"></i> Preview & Generate DTR
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
                <p style="font-size: 0.8rem; color: #64748b;">*Use the print icon within the preview to finalize the document.</p>
                <button class="btn btn-secondary" onclick="closeModal('dtrPreviewModal')">Close Preview</button>
            </div>
        </div>
    </div>
    
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Attendance Correction Request</h3>
                <button class="modal-close" onclick="closeModal('feedbackModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding: 2.5rem;">
                <div class="feedback-header-box">
                    <i class="fa-solid fa-clipboard-question"></i>
                    <p style="color: #64748b; font-size: 0.95rem; margin: 0; padding: 0 20px;">
                        Please provide accurate details regarding the log discrepancy for administrative review.
                    </p>
                </div>
                <form id="feedbackForm">
                    <div class="feedback-form-group">
                        <label>Date of Concerned Record</label>
                        <input type="date" name="record_date" class="form-control" required value="<?= date('Y-m-d') ?>" style="padding: 12px; border-radius: 8px;">
                    </div>
                    <div class="feedback-form-group">
                        <label>Discrepancy Details</label>
                        <textarea name="message" class="form-control feedback-textarea" placeholder="e.g., I timed in at 8:00 AM but the record shows 8:30 AM..." required></textarea>
                    </div>
                    <button type="button" class="btn btn-primary btn-full-width" style="padding: 15px; font-size: 1rem; border-radius: 10px;" onclick="submitFeedback()">
                        <i class="fa-solid fa-paper-plane"></i> Send Discrepancy Report
                    </button>
                </form>
            </div>
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

<script>
/**
 * RESTORED: Handle DTR Preview inside Modal
 */
function handlePrintDTR() {
    const userIdInput = document.getElementById('userId');
    const userId = userIdInput ? userIdInput.value : '<?= $_SESSION['user_id'] ?>';
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (!userId) {
        alert("Please select a user to generate a DTR.");
        return;
    }

    // Ensure this path correctly hits the "printDtr" method in your controller
    const url = `attendance_reports.php?action=print_dtr&user_id=${userId}&start_date=${startDate}&end_date=${endDate}`;
    
    document.getElementById('dtrFrame').src = url;
    openModal('dtrPreviewModal');
}

/**
 * Status Card Summaries
 */
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
                    html += `<tr>
                        <td style="font-weight:700;">${u.faculty_id}</td>
                        <td style="font-weight:600;">${u.first_name} ${u.last_name}</td>
                        <td>${u.role}</td>
                        <td>${u.display_time || '<span style="color:#10b981; font-weight:700;">On-Site</span>'}</td>
                    </tr>`;
                });
                if(data.users.length === 0) html += '<tr><td colspan="4" style="text-align:center; padding:40px; color:#94a3b8;">No records found.</td></tr>';
                html += '</tbody></table>';
                document.getElementById('detailModalBody').innerHTML = html;
            }
        });
}

function submitFeedback() {
    const form = document.getElementById('feedbackForm');
    const formData = new FormData(form);
    
    fetch('api.php?action=submit_attendance_feedback', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeModal('feedbackModal');
            openModal('feedbackSuccessModal');
            form.reset();
        } else {
            alert('Error submitting feedback. Please try again.');
        }
    });
}

function toggleAccordion(contentId, toggleEl) {
    const content = document.getElementById(contentId);
    const isVisible = content.style.display !== 'none';
    content.style.display = isVisible ? 'none' : 'table-row';
    toggleEl.classList.toggle('active');
}

window.closeDtrModal = function() {
    closeModal('dtrPreviewModal');
    // Clear iframe src to stop the document from loading in background
    document.getElementById('dtrFrame').src = 'about:blank';
};
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>