<?php
// Helper variables for the view
$middleInitial = !empty($user['middle_name']) ? ' ' . strtoupper(substr($user['middle_name'], 0, 1)) . '.' : '';
$fullName = strtoupper($user['last_name'] . ', ' . $user['first_name'] . $middleInitial);
$facultyId = $user['faculty_id'];

// Date formatting
$start = new DateTime($startDate);
$end = new DateTime($endDate);
$monthName = $start->format('F');
$year = $start->format('Y');
$daysInMonth = (int)$start->format('t');

if ($start->format('Y-m') != $end->format('Y-m')) {
    $monthName = $start->format('F Y') . ' - ' . $end->format('F Y');
} else {
    $monthName = $monthName . " " . $year;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR - <?= htmlspecialchars($fullName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/display.css">
    <style>
        /* Ensure DTR specific styles are applied if they were inline or specific to this page */
        .dtr-day-disabled { background: #eee; height: 100%; width: 100%; color: #999; display: flex; align-items: center; justify-content: center;}
        .time-val { font-family: 'Courier New', Courier, monospace; font-weight: 600; }
    </style>
</head>
<body class="dtr-body"> 
    
    <?php if (!$isPreview): ?>
    <div class="print-controls">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fa-solid fa-print"></i>
            Print DTR
        </button>
        <button class="btn btn-secondary back-link" onclick="history.back()">
            <i class="fa-solid fa-arrow-left"></i>
            Back
        </button>
    </div>
    <?php endif; ?>

    <div class="dtr-container-wrapper">

        <?php for ($i = 0; $i < 2; $i++): // Loop to print two copies on one page ?>
        <div class="dtr-container">
            <div class="dtr-header">
                <h3>CS Form 48</h3>
                <h2>DAILY TIME RECORD</h2>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">Name</td>
                    <td class="value" style="text-align: center; font-weight: bold; font-size: 1rem;"><?= htmlspecialchars($fullName) ?></td>
                </tr>
                <tr>
                    <td class="label">For the month of</td>
                    <td class="value"><?= htmlspecialchars($monthName) ?></td>
                </tr>
                <tr>
                    <td class="label">Faculty ID</td>
                    <td class="value"><?= htmlspecialchars($facultyId) ?></td>
                </tr>
                <tr>
                    <td class="label">Office Hours (regular days)</td>
                    <td class="value">8:00 AM - 5:00 PM (1hr break)</td>
                </tr>
            </table>

            <table class="attendance-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="day-col">Day</th>
                        <th colspan="2">A.M.</th>
                        <th colspan="2">P.M.</th>
                        <th colspan="2">Hours</th>
                    </tr>
                    <tr>
                        <th class="col-small">Arrival</th>
                        <th class="col-small">Departure</th>
                        <th class="col-small">Arrival</th>
                        <th class="col-small">Departure</th>
                        <th class="col-large">Hours</th>
                        <th class="col-large">Min.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalHours = 0;
                    $totalMinutes = 0;

                    for ($day = 1; $day <= 31; $day++):
                        $am_in = '';
                        $am_out = '';
                        $pm_in = '';
                        $pm_out = '';
                        $day_hours = '';
                        $day_minutes = '';
                        
                        // Check if day is valid for this month
                        if ($day > $daysInMonth) {
                            $am_in = '<div class="dtr-day-disabled">-</div>';
                            $am_out = '<div class="dtr-day-disabled">-</div>';
                            $pm_in = '<div class="dtr-day-disabled">-</div>';
                            $pm_out = '<div class="dtr-day-disabled">-</div>';
                        } else {
                            // Check if we have a record for this day in the passed data
                            if (isset($dtrRecords[$day])) {
                                $rec = $dtrRecords[$day];
                                
                                // Parse Time In
                                if (!empty($rec['time_in'])) {
                                    $time_in_ts = strtotime($rec['time_in']);
                                    // Simple logic: if before 12PM, it's AM Arrival. Else PM Arrival.
                                    if ($time_in_ts < strtotime('12:00:00')) {
                                        $am_in = date('g:i', $time_in_ts);
                                    } else {
                                        $pm_in = date('g:i', $time_in_ts);
                                    }
                                }

                                // Parse Time Out
                                if (!empty($rec['time_out'])) {
                                    $time_out_ts = strtotime($rec['time_out']);
                                    // Simple logic: if after 1PM, it's PM Departure. Else AM Departure.
                                    if ($time_out_ts > strtotime('13:00:00')) {
                                        $pm_out = date('g:i', $time_out_ts);
                                    } else {
                                        $am_out = date('g:i', $time_out_ts);
                                    }
                                }

                                // Calculate Hours (using the pre-calculated working_hours column if available, or raw math)
                                if (!empty($rec['working_hours'])) {
                                    $wh = floatval($rec['working_hours']);
                                    $day_hours = floor($wh);
                                    $day_minutes = round(($wh - $day_hours) * 60);
                                    
                                    $totalHours += $day_hours;
                                    $totalMinutes += $day_minutes;
                                }
                            }
                        }
                    ?>
                    <tr>
                        <td><?= $day ?></td>
                        <td class="time-val"><?= $am_in ?></td>
                        <td class="time-val"><?= $am_out ?></td>
                        <td class="time-val"><?= $pm_in ?></td>
                        <td class="time-val"><?= $pm_out ?></td>
                        <td><?= $day_hours ?></td>
                        <td><?= $day_minutes ?></td>
                    </tr>
                    <?php endfor; ?>

                    <?php
                    // Final total calculation
                    $totalHours += floor($totalMinutes / 60);
                    $totalMinutes = $totalMinutes % 60;
                    ?>
                    <tr class="total-row">
                        <td colspan="5">Total</td>
                        <td><?= $totalHours > 0 ? $totalHours : '' ?></td>
                        <td><?= $totalMinutes > 0 ? $totalMinutes : '' ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="dtr-footer-content">
                I certify on my honor that the above is true and correct record of the hours of work performed, record of which was made daily at the time of arrival and departure from the office.
            </div>

            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">(Signature)</div>
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