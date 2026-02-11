<?php require_once __DIR__ . '/partials/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
.report-stat-card:active { transform: translateY(-2px); }

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

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Content Slide Down */
@keyframes slideDown {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

#exportModal {
    animation: fadeIn 0.3s ease-out;
}

#exportModalContent {
    animation: slideDown 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Hover effect for buttons */
.modal-btn:hover {
    filter: brightness(90%);
    transform: translateY(-1px);
}

input[type="date"] {
    font-family: 'Inter', 'Segoe UI', sans-serif; /* Modern, stylish font */
    font-size: 0.95rem;
    color: #1e293b;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    outline: none;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    appearance: none; /* Removes some default browser styling */
}

/* Add a glow and border change on hover/focus */
input[type="date"]:hover {
    border-color: #cbd5e1;
}

input[type="date"]:focus {
    border-color: #cc980a; /* Matches your export button color */
    box-shadow: 0 0 0 3px rgba(204, 152, 10, 0.15);
    background-color: #ffffff;
}

/* Stylish "Calendar Icon" color (WebKit browsers like Chrome/Edge) */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(48%) sepia(79%) saturate(2476%) hue-rotate(357deg) brightness(94%) contrast(92%);
    /* This tint matches a golden/amber tone to match your Export button */
}

/* Styling the select focus and hover to match date inputs */
    .stylish-select:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }

    .stylish-select:focus {
        border-color: #cc980a; /* Matches your Export button theme */
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(204, 152, 10, 0.1);
    }

    /* Polish for the options list */
    .stylish-select option {
        font-weight: 500;
        padding: 10px;
    }

</style>

<div class="main-body">
    <div class="info-card-header">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Institutional Attendance Summary</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Review performance metrics and faculty history for the selected period.</p>
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
            <h3 style="margin: 0; font-weight: 700; display: flex; align-items: center; gap: 10px; color: white;">
                <i class="fa-solid fa-filter"></i> Filter & Export History
            </h3>
        </div>
        <div class="card-body" style="padding: 1.5rem;">
            <form method="GET" action="attendance_history.php">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; align-items: flex-end;">
                    
                    <div class="form-group">
    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
        Date Range
    </label>
    <div style="display: flex; gap: 12px; align-items: center;">
        <div style="position: relative; flex: 1;">
            <input type="date" name="start_date" class="stylish-date" 
                   value="<?= htmlspecialchars($filters['start_date']) ?>" 
                   style="width: 100%; padding: 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: 'Inter', sans-serif; font-weight: 500; color: #1e293b; background: #f8fafc; transition: all 0.2s ease; outline: none;">
        </div>
        
        <span style="color: #cbd5e1; font-weight: bold;">&rarr;</span>
        
        <div style="position: relative; flex: 1;">
            <input type="date" name="end_date" class="stylish-date" 
                   value="<?= htmlspecialchars($filters['end_date']) ?>" 
                   style="width: 100%; padding: 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: 'Inter', sans-serif; font-weight: 500; color: #1e293b; background: #f8fafc; transition: all 0.2s ease; outline: none;">
        </div>
    </div>
</div>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
<div class="form-group" style="flex: 1;">
    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
        Select Personnel
    </label>
    <div style="position: relative;">
        <select name="user_id" class="stylish-select" 
                style="width: 100%; padding: 10px 40px 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: 'Inter', sans-serif; font-weight: 500; color: #1e293b; background: #f8fafc; transition: all 0.2s ease; outline: none; appearance: none; cursor: pointer;">
            <option value="all" <?= ($filters['user_id'] === 'all' || empty($filters['user_id'])) ? 'selected' : '' ?>>-- Show All Personnel --</option>
            <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>" <?= ($filters['user_id'] == $u['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['faculty_id'] . ' - ' . $u['first_name'] . ' ' . $u['last_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <div style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #94a3b8;">
            <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i>
        </div>
    </div>
</div>
<?php endif; ?>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn" style="flex: 1; background: #059669; color: white; padding: 11px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer;">
                            <i class="fa-solid fa-check"></i> Apply
                        </button>
                        <button type="button" id="confirmExportBtn" class="btn" 
        style="flex: 1; background: #f59e0b; color: white; padding: 11px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer;">
    <i class="fa-solid fa-file-csv"></i> Export Excel
</button>
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
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="directory-table">
                <thead>
                    <tr>
                        <th>FACULTY ID</th>
                        <th>NAME</th>
                        <th>DATE</th>
                        <th>TIME IN</th>
                        <th>TIME OUT</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">No records found.</td></tr>
                    <?php else: foreach ($records as $row): ?>
                        <tr>
                            <td><strong style="color: #059669;"><?= htmlspecialchars($row['faculty_id']) ?></strong></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['date'])) ?></td>
                            <td style="color: #059669; font-weight: 600;"><?= $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '--:--' ?></td>
                            <td style="color: #2563eb; font-weight: 600;"><?= ($row['time_out'] && $row['time_out'] != '00:00:00') ? date('h:i A', strtotime($row['time_out'])) : '--:--' ?></td>
                            <td>
                                <span class="badge" style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
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

<div id="exportModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter: blur(2px);">
    <div id="exportModalContent" style="background:white; width:380px; margin:12% auto; padding:30px; border-radius:16px; text-align:center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="background: #fef3c7; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <i class="fa-solid fa-file-excel" style="color: #d97706; font-size: 1.5rem;"></i>
        </div>
        <h3 style="margin: 0 0 10px; color: #1e293b; font-size: 1.25rem;">Generate Report?</h3>
        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5;">
            An Excel file will be created based on your currently selected filters.
        </p>
        <div style="margin-top:25px; display:flex; gap:12px;">
            <button id="cancelExport" class="modal-btn" style="flex:1; padding:12px; border-radius:10px; border:1px solid #e2e8f0; background:white; color:#64748b; cursor:pointer; font-weight:600; transition: all 0.2s;">
                Cancel
            </button>
            <button id="proceedExport" class="modal-btn" style="flex:1; padding:12px; border-radius:10px; background:#cc980a; color:white; border:none; cursor:pointer; font-weight:600; transition: all 0.2s;">
                Download
            </button>
        </div>
    </div>
</div>

<script>
   document.getElementById('confirmExportBtn').addEventListener('click', function() {
    document.getElementById('exportModal').style.display = 'block';
});

document.getElementById('cancelExport').addEventListener('click', function() {
    document.getElementById('exportModal').style.display = 'none';
});

document.getElementById('proceedExport').addEventListener('click', function() {
    // Get current filters from your form to ensure the export matches what the user sees
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    const userId = "<?= $filters['user_id'] ?>"; // Using the user_id from your controller filters

    // Construct the export URL
    const exportUrl = `attendance_history.php?action=export_csv&start_date=${startDate}&end_date=${endDate}&user_id=${userId}`;
    
    // Hide modal and trigger download
    document.getElementById('exportModal').style.display = 'none';
    window.location.href = exportUrl;
});

// Close modal if user clicks outside of it
window.onclick = function(event) {
    if (event.target == document.getElementById('exportModal')) {
        document.getElementById('exportModal').style.display = "none";
    }
} 
</script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>