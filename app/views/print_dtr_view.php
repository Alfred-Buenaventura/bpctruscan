<?php
$middleInitial = !empty($user['middle_name']) ? ' ' . strtoupper(substr($user['middle_name'], 0, 1)) . '.' : '';
$fullName = strtoupper($user['last_name'] . ', ' . $user['first_name'] . $middleInitial);
$facultyId = $user['faculty_id'];

$start = new DateTime($startDate);
$monthLabel = $start->format('F Y'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR - <?= htmlspecialchars($fullName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
       /* --- 1. GLOBAL RESET & PAGE BACKGROUND --- */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body.dtr-body {
    background: #525659; /* Darker gray background for high contrast */
    font-family: 'Inter', sans-serif;
    padding: 2rem;
    display: flex;
    justify-content: center;
    min-height: 100vh;
}

/* --- 2. THE DTR CONTAINER (Base Logic) --- */
.dtr-container {
    /* STRICT PHYSICAL DIMENSIONS */
    width: 3.5in;
    height: 8.5in;
    
    background: white;
    padding: 0.15in; 
    border: 1px solid #000;
    
    display: flex;
    flex-direction: column;
    overflow: hidden; 
    
    /* --- SCREEN ONLY: SCALING (THE ZOOM FIX) --- */
    /* This makes it look 1.5x bigger on screen without changing inch size */
    transform: scale(2); 
    transform-origin: top center; 
    
    /* Add visual pop for screen */
    box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    
    /* Add margin to account for the zoom overlap */
    margin-bottom: 4.5in; 
}

/* Wrapper adjustments to fit the zoomed element */
.dtr-container-wrapper {
    display: flex;
    flex-direction: column; 
    align-items: center;
    gap: 2rem;
    padding-top: 1rem;
    /* Ensure scrollbar works with the zoomed element */
    min-height: 150vh; 
}

/* --- 3. DTR CONTENT STYLING (The "Nice" Look) --- */

/* Header */
.dtr-header {
    text-align: center;
    font-family: 'Times New Roman', Times, serif;
    margin-bottom: 5px;
}
.dtr-header h3 {
    font-size: 8pt;
    font-weight: bold;
    margin: 0;
    text-align: left;
    text-transform: uppercase;
}
.dtr-header h2 {
    font-size: 11pt; 
    font-weight: bold;
    margin: 3px 0;
    text-transform: uppercase;
}

/* Info Table */
.info-table {
    width: 100%;
    margin-bottom: 5px;
    font-size: 8pt;
    border-collapse: collapse;
    font-family: 'Arial', sans-serif;
}
.info-table td { padding: 1px 0; }
.info-table .label { white-space: nowrap; padding-right: 5px; }
.info-table .value {
    border-bottom: 1px solid #000;
    width: 100%;
    font-weight: bold;
    text-align: center;
}

/* Attendance Table */
.attendance-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 6.5pt; 
    border: 1px solid #000;
    text-align: center;
    flex-grow: 0;
    font-family: 'Arial', sans-serif;
}
.attendance-table th,
.attendance-table td {
    border: 1px solid #000;
    padding: 0px; 
    height: 11px; /* Fixed row height */
    line-height: 11px;
}
.attendance-table th {
    font-weight: bold;
    background: #f3f4f6;
    padding: 2px 0;
    font-size: 7pt;
}

/* Inputs */
.attendance-table input {
    width: 100%;
    border: none;
    background: transparent;
    text-align: center;
    font-size: 7pt;
    font-family: inherit;
    outline: none;
    height: 100%;
    margin: 0;
    padding: 0;
}

/* Footer */
.dtr-footer-content {
    margin-top: 5px;
    font-size: 7pt;
    line-height: 1.2;
    font-family: 'Times New Roman', serif;
    flex-grow: 1; 
}
.signature-block { margin-top: 15px; text-align: center; }
.signature-line {
    border-bottom: 1px solid #000;
    width: 80%;
    margin: 0 auto;
    padding-top: 10px;
}
.signature-label {
    font-size: 7pt;
    margin-top: 2px;
    font-weight: bold;
    text-transform: uppercase;
}
.holiday-text {
    color: #dc2626;
    font-style: italic;
    font-size: 6pt;
}

/* Hide second copy on screen */
.dtr-copy-1 { display: none; }


/* --- 4. BUTTON STYLES --- */
.print-controls {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 100;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
}
.btn-primary { background: #059669; color: white; border: 1px solid #059669; }
.btn-primary:hover { background: #047857; }
.btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
.btn-secondary:hover { background: #e5e7eb; }

/* --- 5. PRINT LAYOUT (RESET SCALING) --- */
@media print {
    @page {
        size: A4 portrait;
        margin: 0.25in;
    }
    
    body.dtr-body {
        background: none;
        padding: 0;
        margin: 0;
        display: block;
        min-height: 0;
    }

    .dtr-container-wrapper {
        display: flex !important;
        flex-direction: row !important; 
        justify-content: center !important;
        align-items: flex-start !important;
        width: 100% !important;
        gap: 0.2in !important;
        padding: 0 !important;
        min-height: 0 !important; /* Reset height */
    }

    .dtr-container {
        /* CRITICAL: REMOVE SCALE FOR PRINT */
        transform: none !important; 
        margin: 0 !important; 
        box-shadow: none !important;

        /* Re-enforce strict size */
        width: 3.5in !important;
        height: 8.5in !important;
        border: 1px solid #000 !important;
        
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Show the second copy */
    .dtr-copy-1 { display: block !important; }
    
    /* Hide buttons */
    .print-controls { display: none !important; }
}
    </style>
</head>
<body>
    <div class="dtr-container-wrapper">
        <?php for ($i = 0; $i < 2; $i++): 
            $copyClass = ($i === 1) ? 'dtr-copy-1' : 'dtr-copy-0';
        ?>
        <div class="dtr-container <?= $copyClass ?>">
            <div class="dtr-header">
                <h3>CS Form 48</h3>
                <h2>DAILY TIME RECORD</h2>
            </div>

            <table class="info-table">
                <tr>
                    <td colspan="2" class="value"><?= htmlspecialchars($fullName) ?></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center; font-size: 6pt;">(Name)</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center; padding-top: 3px;">
                        For the month of <strong><?= htmlspecialchars($monthLabel) ?></strong>
                    </td>
                </tr>
            </table>

            <table class="attendance-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 8%;">Day</th>
                        <th colspan="2">A.M.</th>
                        <th colspan="2">P.M.</th>
                        <th colspan="2">Undertime</th>
                    </tr>
                    <tr>
                        <th>Arr</th>
                        <th>Dep</th>
                        <th>Arr</th>
                        <th>Dep</th>
                        <th>Hrs</th>
                        <th>Min</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalHours = 0;
                    $totalMinutes = 0;
                    
                    $period = new DatePeriod(new DateTime($startDate), DateInterval::createFromDateString('1 day'), (new DateTime($endDate))->modify('+1 day'));

                    foreach ($period as $dt):
                        $day = $dt->format('d');
                        $dayInt = (int)$day;
                        $rec = $dtrRecords[$dayInt] ?? [];
                        
                        // [UPDATED] Use pre-calculated display strings if available
                        $am_in = !empty($rec['am_in']) ? date('H:i', strtotime($rec['am_in'])) : '';
                        $am_out = !empty($rec['am_out']) ? date('H:i', strtotime($rec['am_out'])) : '';
                        $pm_in = !empty($rec['pm_in']) ? date('H:i', strtotime($rec['pm_in'])) : '';
                        $pm_out = !empty($rec['pm_out']) ? date('H:i', strtotime($rec['pm_out'])) : '';

                        // [UPDATED] Use the Credited Seconds passed from Controller (Clamped Logic)
                        $dailySeconds = $rec['credited_seconds'] ?? 0;

                        $day_hours = '';
                        $day_minutes = '';

                        if ($dailySeconds > 0) {
                            $h = floor($dailySeconds / 3600);
                            $m = floor(($dailySeconds % 3600) / 60);
                            $day_hours = $h; 
                            $day_minutes = $m;
                            $totalHours += $h; 
                            $totalMinutes += $m;
                        }

                        $isHoliday = !empty($rec['remarks']) && empty($am_in) && empty($pm_in);
                    ?>
                    <tr>
                        <td><?= $day ?></td>
                        <?php if ($isHoliday): ?>
                            <td colspan="4" class="holiday-text" title="<?= htmlspecialchars($rec['remarks']) ?>">
                                <?= htmlspecialchars($rec['remarks']) ?>
                            </td>
                        <?php else: ?>
                            <td><?= $am_in ?></td>
                            <td><?= $am_out ?></td>
                            <td><?= $pm_in ?></td>
                            <td><?= $pm_out ?></td>
                        <?php endif; ?>
                        <td><?= $day_hours ?></td>
                        <td><?= $day_minutes ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php
                    $totalHours += floor($totalMinutes / 60);
                    $totalMinutes = $totalMinutes % 60;
                    ?>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right; padding-right: 4px;">Total</td>
                        <td><?= $totalHours > 0 ? $totalHours : '' ?></td>
                        <td><?= $totalMinutes > 0 ? $totalMinutes : '' ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="dtr-footer-content">
                I certify on my honor that the above is a true and correct record of the hours of work performed, record of which was made daily at the time of arrival and departure from office.
            </div>

            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">(Signature of Employee)</div>
            </div>

            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">(In-charge)</div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</body>
</html>