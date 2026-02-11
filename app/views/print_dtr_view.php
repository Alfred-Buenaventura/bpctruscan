<?php
$middleInitial = !empty($user['middle_name']) ? ' ' . strtoupper(substr($user['middle_name'], 0, 1)) . '.' : '';
$fullName = strtoupper($user['last_name'] . ', ' . $user['first_name'] . $middleInitial);
$facultyId = $user['faculty_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR - <?= htmlspecialchars($fullName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/print.css">
    <link rel="stylesheet" href="css/responsive.css?v=<?= time(); ?>">
</head>
<style>
    .attendance-table td {
        white-space: nowrap;
    }
    .holiday-text {
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        color: #d32f2f;
    }

    .signature-name-print {
        display: block;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 9pt;
        margin-bottom: 2px;
    }

    .signature-label {
        font-size: 8pt;
    }

</style>
<body class="dtr-body">
    <div class="print-controls">
        <button type="button" class="btn-dtr btn-dtr-success" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print DTR
        </button>
    </div>

    <div class="dtr-container-wrapper">
        <?php for ($copy = 0; $copy < 2; $copy++): 
            $cutoffLabel = ($copy === 0) ? $monthName . " 1-15, " . $year : $monthName . " 16-" . $lastDay . ", " . $year;
        ?>
        <div class="dtr-container">
            <div class="dtr-header">
                <h3>CS Form 48</h3>
                <h2>DAILY TIME RECORD</h2>
            </div>

            <table class="info-table">
                <tr><td class="value"><?= htmlspecialchars($fullName) ?></td></tr>
                <tr><td style="text-align: center; font-size: 6pt;">(Name)</td></tr>
                <tr><td class="faculty-id-row">FACULTY ID: <?= htmlspecialchars($facultyId) ?></td></tr>
                <tr>
                    <td style="text-align: center; padding-top: 3px;">
                        For the month of <strong><?= htmlspecialchars($cutoffLabel) ?></strong>
                    </td>
                </tr>
            </table>

            <table class="attendance-table">
                <thead>
                    <tr><th rowspan="2" style="width: 10%;">Day</th><th colspan="2">A.M.</th><th colspan="2">P.M.</th><th colspan="2">Total</th></tr>
                    <tr><th>Arr</th><th>Dep</th><th>Arr</th><th>Dep</th><th>Hrs</th><th>Min</th></tr>
                </thead>
                <tbody>
                    <?php
$totalHours = 0; $totalMinutes = 0;
for ($day = 1; $day <= 31; $day++):
    // 1. SAFELY get the record from the controller
    $rec = $dtrRecords[$day] ?? null;

    // 2. YOUR ORIGINAL SWITCHER LOGIC
    $isTargetPeriod = ($copy === 0 && $day <= 15) || ($copy === 1 && $day >= 16 && $day <= 31);

    // 3. MAP TIMESTAMPS: Only show if it's the right copy and data exists
    $am_in  = ($isTargetPeriod && $rec && !empty($rec['am_in']))  ? date('h:i', strtotime($rec['am_in']))  : '';
    $am_out = ($isTargetPeriod && $rec && !empty($rec['am_out'])) ? date('h:i', strtotime($rec['am_out'])) : '';
    $pm_in  = ($isTargetPeriod && $rec && !empty($rec['pm_in']))  ? date('h:i', strtotime($rec['pm_in']))  : '';
    $pm_out = ($isTargetPeriod && $rec && !empty($rec['pm_out'])) ? date('h:i', strtotime($rec['pm_out'])) : '';

    // 4. NEW CALCULATION: Use the schedule-clamped seconds from controller
    $dailySeconds = ($isTargetPeriod && $rec) ? ($rec['credited_seconds'] ?? 0) : 0;
    $day_h = ''; $day_m = '';
    
    if ($dailySeconds > 0) {
        $h = floor($dailySeconds / 3600);
        $m = floor(($dailySeconds % 3600) / 60);
        $day_h = $h; $day_m = $m;
        $totalHours += $h; $totalMinutes += $m;
    }
    
    // 5. HOLIDAY CHECK
    $isHoliday = ($rec && !empty($rec['remarks']) && empty($am_in) && empty($pm_in));
?>
<tr>
    <td style="text-align: center; border: 1px solid black; font-size: 8pt;"><?= $day ?></td>
    
    <?php if ($day <= $lastDay && $isHoliday): ?>
        <td colspan="4" class="holiday-text" style="text-align: center; border: 1px solid black; font-weight: bold; color: #d32f2f;">
            <?= htmlspecialchars($rec['remarks']) ?>
        </td>
    <?php else: ?>
        <td style="text-align: center; border: 1px solid black; font-size: 8pt;"><?= $am_in ?></td>
        <td style="text-align: center; border: 1px solid black; font-size: 8pt;"><?= $am_out ?></td>
        <td style="text-align: center; border: 1px solid black; font-size: 8pt;"><?= $pm_in ?></td>
        <td style="text-align: center; border: 1px solid black; font-size: 8pt;"><?= $pm_out ?></td>
    <?php endif; ?>

    <td style="text-align: center; border: 1px solid black; font-size: 8pt;"><?= $day_h ?></td>
    <td style="text-align: center; border: 1px solid black; font-size: 8pt;"><?= $day_m ?></td>
</tr>
<?php endfor; ?>
                    
                    <?php 
    // Perform the final conversion once after the day loop finishes
    $totalHours += floor($totalMinutes / 60);
    $totalMinutes %= 60;
?>
<tr style="font-weight: bold;">
    <td colspan="5" style="text-align: right; border: 1px solid black; font-size: 8pt; padding-right: 4px;">Total</td>
    <td style="text-align: center; border: 1px solid black; font-size: 8pt;">
        <?= $totalHours > 0 ? $totalHours : '0' ?>
    </td>
    <td style="text-align: center; border: 1px solid black; font-size: 8pt;">
        <?= ($totalMinutes > 0 || $totalHours > 0) ? $totalMinutes : '0' ?>
    </td>
</tr>
                </tbody>
            </table>

            <div class="dtr-footer-content">
                I certify on my honor that the above is a true and correct record of the hours of work performed, record of which was made daily at the time of arrival and departure from office.
            </div>

            <div class="signature-block">
                <span class="signature-name-print"><?= htmlspecialchars($fullName) ?></span>
                <div class="signature-label">(Signature of Employee)</div>
            </div>

            <div class="signature-block">
                <div class="signature-name-print" style="border-bottom: 1px solid black; width: 85%; margin: 0 auto; padding-top: 5px;">
                    <?= htmlspecialchars($settings['dtr_in_charge_name'] ?? '____________________') ?>
                </div>
                <div class="signature-label">Verified as to the prescribed office hours</div>
                <div class="signature-label" style="font-weight: bold; margin-top: 2px;">
                    <?= htmlspecialchars($settings['dtr_in_charge_title'] ?? 'IN-CHARGE') ?>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</body>
</html>