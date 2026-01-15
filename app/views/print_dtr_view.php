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
</head>
<body class="dtr-body">
    <div class="print-controls">
        <button type="button" class="btn-dtr" onclick="window.parent.closeDtrModal();">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
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
                        $rec = $dtrRecords[$day] ?? null;
                        $isTargetPeriod = ($copy === 0 && $day <= 15) || ($copy === 1 && $day >= 16 && $day <= $lastDay);

                        // CHANGED: Formatted to 12-hour format (h:i)
                        $am_in = ($isTargetPeriod && $rec && !empty($rec['am_in'])) ? date('h:i', strtotime($rec['am_in'])) : '';
                        $am_out = ($isTargetPeriod && $rec && !empty($rec['am_out'])) ? date('h:i', strtotime($rec['am_out'])) : '';
                        $pm_in = ($isTargetPeriod && $rec && !empty($rec['pm_in'])) ? date('h:i', strtotime($rec['pm_in'])) : '';
                        $pm_out = ($isTargetPeriod && $rec && !empty($rec['pm_out'])) ? date('h:i', strtotime($rec['pm_out'])) : '';

                        $dailySeconds = ($isTargetPeriod && $rec) ? ($rec['credited_seconds'] ?? 0) : 0;
                        $day_h = ''; $day_m = '';
                        if ($dailySeconds > 0) {
                            $h = floor($dailySeconds / 3600);
                            $m = floor(($dailySeconds % 3600) / 60);
                            $day_h = $h; $day_m = $m;
                            $totalHours += $h; $totalMinutes += $m;
                        }
                        $isHoliday = ($isTargetPeriod && $rec && !empty($rec['remarks']) && empty($am_in) && empty($pm_in));
                    ?>
                    <tr>
                        <td><?= $day ?></td>
                        <?php if ($isHoliday): ?>
                            <td colspan="4" class="holiday-text"><?= htmlspecialchars($rec['remarks']) ?></td>
                        <?php else: ?>
                            <td><?= $am_in ?></td><td><?= $am_out ?></td>
                            <td><?= $pm_in ?></td><td><?= $pm_out ?></td>
                        <?php endif; ?>
                        <td><?= $day_h ?></td><td><?= $day_m ?></td>
                    </tr>
                    <?php endfor; ?>
                    <?php 
                        $totalHours += floor($totalMinutes / 60);
                        $totalMinutes %= 60;
                    ?>
                    <tr style="font-weight: bold;">
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
                <span class="signature-name-print"><?= htmlspecialchars($fullName) ?></span>
                <div class="signature-label">(Signature of Employee)</div>
            </div>

            <div class="signature-block">
                <div style="border-bottom: 1px solid black; width: 80%; margin: 0 auto; padding-top: 5px;"></div>
                <div class="signature-label">Verified as to the prescribed office hours</div>
                <div class="signature-label" style="font-weight: bold; margin-top: 2px;">IN-CHARGE</div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</body>
</html>