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
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dtr-body"> 
    
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
                    <td class="label">Name:</td>
                    <td class="value"><?= htmlspecialchars($fullName) ?></td>
                </tr>
                <tr>
                    <td class="label">For the month of:</td>
                    <td class="value"><?= htmlspecialchars($monthLabel) ?></td>
                </tr>
                <tr>
                    <td class="label">Official hours for arrival and departure</td>
                    <td class="value"></td> 
                </tr>
            </table>

            <table class="attendance-table">
                <colgroup>
                    <col style="width: 8%;">
                    <col style="width: 16.5%;">
                    <col style="width: 16.5%;">
                    <col style="width: 16.5%;">
                    <col style="width: 16.5%;">
                    <col style="width: 13%;">
                    <col style="width: 13%;">
                </colgroup>
                <thead>
                    <tr>
                        <th rowspan="2">Day</th>
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
                        
                        $amInTs = !empty($rec['am_in']) ? strtotime($rec['am_in']) : 0;
                        $amOutTs = !empty($rec['am_out']) ? strtotime($rec['am_out']) : 0;
                        $pmInTs = !empty($rec['pm_in']) ? strtotime($rec['pm_in']) : 0;
                        $pmOutTs = !empty($rec['pm_out']) ? strtotime($rec['pm_out']) : 0;

                        $am_in = $amInTs ? date('H:i', $amInTs) : '';
                        $am_out = $amOutTs ? date('H:i', $amOutTs) : '';
                        $pm_in = $pmInTs ? date('H:i', $pmInTs) : '';
                        $pm_out = $pmOutTs ? date('H:i', $pmOutTs) : '';

                        // Calculation Logic
                        $dailySeconds = 0;
                        if ($amInTs && $amOutTs && $amOutTs > $amInTs) $dailySeconds += ($amOutTs - $amInTs);
                        if ($pmInTs && $pmOutTs && $pmOutTs > $pmInTs) $dailySeconds += ($pmOutTs - $pmInTs);

                        $day_hours = '';
                        $day_minutes = '';

                        if ($dailySeconds > 0) {
                            $h = floor($dailySeconds / 3600);
                            $m = floor(($dailySeconds % 3600) / 60);
                            $day_hours = $h; $day_minutes = $m;
                            $totalHours += $h; $totalMinutes += $m;
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
                            <td class="time-val"><?= $am_in ?></td>
                            <td class="time-val"><?= $am_out ?></td>
                            <td class="time-val"><?= $pm_in ?></td>
                            <td class="time-val"><?= $pm_out ?></td>
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
                        <td colspan="5" style="text-align: right; font-weight: bold; padding-right: 2px;">Total</td>
                        <td style="font-weight: bold;"><?= $totalHours > 0 ? $totalHours : '' ?></td>
                        <td style="font-weight: bold;"><?= $totalMinutes > 0 ? $totalMinutes : '' ?></td>
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
                <div class="signature-line" style="margin-top: 15px;"></div>
                <div class="signature-label">(In-charge)</div>
            </div>

        </div>
        <?php endfor; ?>
    </div>

</body>
</html>