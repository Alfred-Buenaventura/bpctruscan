<?php
require_once __DIR__ . '/../core/controller.php';
require_once __DIR__ . '/../libraries/SimpleXLSXGen.php';

use Shuchkin\SimpleXLSXGen;

class AttendanceController extends Controller {

    public function index() {
        $this->requireLogin(); 
        $attModel = $this->model('Attendance');
        $userModel = $this->model('User');
        
        $data = [
            'isAdmin' => ($_SESSION['role'] === 'Admin'),
            'error' => ''
        ];

        if ($data['isAdmin']) {
            $data['pageTitle'] = 'Attendance Reports';
            $data['pageSubtitle'] = 'Manage and monitor personnel logs';
        } else {
            $data['pageTitle'] = 'My Attendance';
            $data['pageSubtitle'] = 'View your personal time records';
        }

        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date'   => $_GET['end_date']   ?? date('Y-m-d'),
            'search'     => $_GET['search']     ?? '',
            'user_id'    => $_GET['user_id']    ?? ''
        ];

        if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
            $this->exportHistoryExcel();
        }

        if ($data['isAdmin']) {
            $data['allUsers'] = $userModel->getAllStaff();
            $data['stats'] = $attModel->getStats(); 
        } else {
            $filters['user_id'] = $_SESSION['user_id'];
            $data['stats'] = $attModel->getStats($_SESSION['user_id']);
        }

        $data['records'] = $attModel->getRecords($filters);
        $data['filters'] = $filters;

        $this->view('attendance_view', $data);
    }

    public function history() {
    $this->requireLogin();
    $attModel = $this->model('Attendance');
    $userModel = $this->model('User'); 

    $isAdmin = ($_SESSION['role'] === 'Admin');

    // 1. STANDARD NAME: Change 'user_filter' to 'user_id' to match your view line 69
    $filters = [
        'start_date'  => $_GET['start_date'] ?? date('Y-m-01'),
        'end_date'    => $_GET['end_date']   ?? date('Y-m-d'),
        'status_type' => $_GET['status_type'] ?? '',
        'user_id'     => ($isAdmin) ? ($_GET['user_id'] ?? 'all') : $_SESSION['user_id']
    ];

    // 2. EXPORT GATEKEEPER: This catches the button click before any HTML starts
    if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
        if (ob_get_length()) ob_end_clean(); // Clears hidden spaces that cause "refreshing"
        $this->exportHistoryExcel();
        exit(); 
    }

    // Fetch records using your existing logic
    $allRecords = $attModel->getUserHistory($filters['user_id'], $filters);

    // Summary calculation (Preserved original functionality)
    $summary = ['present' => 0, 'late' => 0, 'absent' => 0, 'office' => 0];
    foreach ($allRecords as $rec) {
        if (stripos($rec['status'], 'Late') !== false) $summary['late']++;
        elseif ($rec['status'] === 'Present') $summary['present']++;
        elseif ($rec['status'] === 'Absent') $summary['absent']++;
        if (isset($rec['duty_type']) && (stripos($rec['duty_type'], 'Office') !== false)) $summary['office']++;
    }

    $data = [
        'pageTitle'    => 'Attendance History',
        'pageSubtitle' => 'View a comprehensive record logs of all user attendance records.',
        'records'      => $allRecords,
        'summary'      => $summary,
        'filters'      => $filters, // This passes 'user_id' to the view
        'isAdmin'      => $isAdmin,
        'allUsers'     => $isAdmin ? $userModel->getAllActive() : [] 
    ];

    $this->view('attendance_history_view', $data);
}

    private function calculateClampedHours($log, $dateStr) {
        if (empty($log['time_in']) || empty($log['time_out'])) return 0;
        if (empty($log['sched_start']) || empty($log['sched_end'])) return 0;

        $actualIn = strtotime("$dateStr " . $log['time_in']);
        $actualOut = strtotime("$dateStr " . $log['time_out']);
        $schedStart = strtotime("$dateStr " . $log['sched_start']);
        $schedEnd = strtotime("$dateStr " . $log['sched_end']);

        $effectiveIn = max($actualIn, $schedStart);
        $effectiveOut = min($actualOut, $schedEnd);

        $durationSeconds = $effectiveOut - $effectiveIn;
        if ($durationSeconds <= 0) return 0;

        return round($durationSeconds / 60) * 60;
    }

    public function getAttendanceSummary() {
        $this->requireLogin();
        $attModel = $this->model('Attendance');
        $type = $_GET['type'] ?? 'entries';
        
        $results = $attModel->getDetailedStatsByType($type);
        
        foreach ($results as &$u) {
            if ($type === 'exits') {
                $u['display_time'] = "Out: " . date('h:i A', strtotime($u['time_out']));
            } else {
                $u['display_time'] = "In: " . date('h:i A', strtotime($u['time_in']));
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'users' => $results]);
        exit;
    }

    

    public function submitFeedback() {
        $this->requireLogin();
        $userModel = $this->model('User');
        $db = Database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->verifyCsrfToken();
    }
        
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        $date = clean($_POST['record_date']);
        $message = clean($_POST['message']);
        
        $sql = "INSERT INTO attendance_feedbacks (user_id, target_date, message, status) VALUES (?, ?, ?, 'Pending')";
        $result = $db->query($sql, [$userId, $date, $message], "iss");

        if ($result) {
            $admins = $userModel->getAdmins();
            $subject = "ATTENDANCE DISCREPANCY REPORT: " . $userName;
            
            $emailBody = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 20px auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;'>
                <div style='background-color: #ef4444; color: white; padding: 25px; text-align: center;'>
                    <h2 style='margin: 0;'>New Attendance Feedback</h2>
                </div>
                <div style='padding: 30px; color: #1e293b;'>
                    <p>A faculty member has reported a discrepancy in their logs:</p>
                    <table style='width: 100%; margin-top: 20px;'>
                        <tr><td style='padding: 8px 0; font-weight: bold;'>Faculty:</td><td>$userName</td></tr>
                        <tr><td style='padding: 8px 0; font-weight: bold;'>Target Date:</td><td>" . date('M d, Y', strtotime($date)) . "</td></tr>
                    </table>
                    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid #f1f5f9;'>
                        <p style='margin: 0; font-style: italic;'>\"$message\"</p>
                    </div>
                </div>
            </div>";

            foreach ($admins as $admin) {
                Mailer::send($admin['email'], $subject, $emailBody);
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => (bool)$result]);
        exit;
    }

public function exportHistoryExcel() {
    // 1. Security Check
    $this->requireLogin();
    $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin');
    
    $attModel = $this->model('Attendance');
    $userModel = $this->model('User');

    // 2. Filter Logic
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $targetUserId = ($isAdmin) ? ($_GET['user_filter'] ?? 'all') : $_SESSION['user_id'];

    // If 'all', fetch all staff; otherwise, just fetch the specific user
    if ($targetUserId === 'all' && $isAdmin) {
        $staffList = $userModel->getAllActive(); // or getAllStaff()
    } else {
        $singleUser = $userModel->findUserById($targetUserId);
        $staffList = $singleUser ? [$singleUser] : [];
    }

    // 3. Date Context
    $monthStart = date('Y-m-01', strtotime($startDate));
    $monthEnd = date('Y-m-t', strtotime($startDate));
    $monthLabel = date('F Y', strtotime($monthStart));
    $lastDay = (int)date('t', strtotime($monthStart));

    // 4. Fetch and Process Data
    // Ensure getRecords filters by user if targetUserId isn't 'all'
    $rawRecords = $attModel->getRecords([
        'start_date' => $monthStart, 
        'end_date' => $monthEnd,
        'user_id' => ($targetUserId === 'all') ? null : $targetUserId
    ]);
    
    $processed = [];
    foreach ($rawRecords as $rec) {
        $uid = $rec['user_id'];
        $day = (int)date('d', strtotime($rec['date']));
        // Use your existing log extraction logic
        $ins = array_filter(array_column($rec['logs'], 'time_in'));
        $outs = array_filter(array_column($rec['logs'], 'time_out'));
        if (!empty($ins)) {
            $processed[$uid][$day] = date('g:i A', strtotime(min($ins))) . " - " . (!empty($outs) ? date('g:i A', strtotime(max($outs))) : 'No Out');
        }
    }

    // 5. Build Excel Data (Preserving your Styles)
    $titleStyle  = '<style bgcolor="#148038" color="#FFFFFF" align="center" valign="center"><b>';
    $columnStyle = '<style bgcolor="#D3D3D3" align="center" valign="center"><b>';
    $centerStyle = '<style align="center" valign="center">';

    $data = [];
    // ROW 1-3: TITLE
    $row1 = [$titleStyle . "ATTENDANCE HISTORY (" . strtoupper($monthLabel) . ")</b></style>"];
    for ($i = 1; $i < $lastDay + 2; $i++) { $row1[] = ""; }
    $data[] = $row1;
    $data[] = array_fill(0, $lastDay + 2, "");
    $data[] = array_fill(0, $lastDay + 2, "");

    // ROW 4: HEADERS
    $row4 = [$columnStyle . "ID</b></style>", $columnStyle . "NAME</b></style>"];
    for ($d = 1; $d <= $lastDay; $d++) {
        $currentDate = date('M d', strtotime("$monthStart + " . ($d - 1) . " days"));
        $row4[] = $columnStyle . strtoupper($currentDate) . "</b></style>";
    }
    $data[] = $row4;

    // ROW 5: SUB-HEADERS
    $row5 = ["", ""];
    for ($i = 1; $i <= $lastDay; $i++) { $row5[] = $columnStyle . "TIME IN - TIME OUT</b></style>"; }
    $data[] = $row5;

    // ROW 6+: PERSONNEL DATA
    foreach ($staffList as $staff) {
        $rowData = [
            $centerStyle . $staff['faculty_id'] . "</style>",
            $centerStyle . strtoupper(($staff['last_name'] ?? '') . ', ' . ($staff['first_name'] ?? '')) . "</style>"
        ];
        for ($d = 1; $d <= $lastDay; $d++) {
            $rowData[] = $centerStyle . ($processed[$staff['id']][$d] ?? '---') . "</style>";
        }
        $data[] = $rowData;
    }

    // 6. Generate and Download
    $xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
    $xlsx->downloadAs("BPC_Attendance_Report_" . date('Ym') . ".xlsx");
    
    // Auto-calculate last column for merging (e.g., AH)
    $totalCols = $lastDay + 2;
    $idx = $totalCols - 1;
    $lastCol = "";
    while ($idx >= 0) {
        $lastCol = chr(($idx % 26) + 65) . $lastCol;
        $idx = floor($idx / 26) - 1;
    }

    $xlsx->mergeCells('A1:' . $lastCol . '3'); 
    $xlsx->mergeCells('A4:A5');
    $xlsx->mergeCells('B4:B5');

    $xlsx->setColWidth(1, 12); 
    $xlsx->setColWidth(2, 35); 
    for ($c = 3; $c <= $totalCols; $c++) { $xlsx->setColWidth($c, 22); }

    // Custom filename for personal vs admin
    $fileLabel = ($targetUserId === 'all') ? "Institutional" : ($_SESSION['last_name'] ?? "Personal");
    $xlsx->downloadAs("BPC_Attendance_{$fileLabel}_" . date('Ym') . ".xlsx");
    exit();
}

    public function printDtr() {
    $this->requireLogin();
    $userId = $_GET['user_id'] ?? $_SESSION['user_id'];
    
    // Security check: Only admins can view others' DTRs
    if (!Helper::isAdmin() && $userId != $_SESSION['user_id']) { 
        die('Access Denied'); 
    }

    $userModel = $this->model('User');
    $attModel = $this->model('Attendance');
    $holidayModel = $this->model('Holiday');
    $scheduleModel = $this->model('Schedule');
    
    $baseDate = $_GET['start_date'] ?? date('Y-m-01');
    $fullMonthStart = date('Y-m-01', strtotime($baseDate));
    $fullMonthEnd   = date('Y-m-t', strtotime($baseDate));
    
    $monthName = date('F', strtotime($fullMonthStart));
    $year = date('Y', strtotime($fullMonthStart));
    $lastDay = (int)date('t', strtotime($fullMonthStart));

    // 1. Fetch all Raw Data
    $user = $userModel->findById($userId);
    $settings = $holidayModel->getSystemSettings();
    $holidays = $attModel->getHolidaysInRange($fullMonthStart, $fullMonthEnd);
    $logs = $attModel->getUserHistory($userId, ['start_date' => $fullMonthStart, 'end_date' => $fullMonthEnd]);
    $approvedSchedules = $scheduleModel->getByUser($userId, 'approved');

    // 2. Process Daily Data
    $dtrRecords = [];
    for ($day = 1; $day <= $lastDay; $day++) {
        $currentDate = sprintf("%s-%02d-%02d", $year, date('m', strtotime($fullMonthStart)), $day);
        $dayOfWeek = date('l', strtotime($currentDate));
        
        // Find all logs for this specific date
        $dayLogs = array_filter($logs, function($l) use ($currentDate) {
            return date('Y-m-d', strtotime($l['date'])) === $currentDate;
        });

        // Initialize DTR Row
        $dtrRecords[$day] = [
            'am_in' => '', 'am_out' => '', 'pm_in' => '', 'pm_out' => '',
            'credited_seconds' => 0,
            'remarks' => $holidays[$currentDate] ?? ''
        ];

        if (!empty($dayLogs)) {
            // --- PLOTTING LOGIC: Visual Display (Real-Time Extremities) ---
            
            // Separate logs into AM and PM sessions based on 12:00 PM threshold
            $amLogs = array_filter($dayLogs, function($l) {
                return !empty($l['time_in']) && strtotime($l['time_in']) < strtotime('12:00:00');
            });

            $pmLogs = array_filter($dayLogs, function($l) {
                return !empty($l['time_in']) && strtotime($l['time_in']) >= strtotime('12:00:00');
            });

            // AM Plotting: First log in the morning to last log in the morning
            if (!empty($amLogs)) {
                $amIns = array_filter(array_column($amLogs, 'time_in'));
                $amOuts = array_filter(array_column($amLogs, 'time_out'));
                
                if (!empty($amIns)) $dtrRecords[$day]['am_in'] = min($amIns);
                if (!empty($amOuts)) $dtrRecords[$day]['am_out'] = max($amOuts);
            }

            // PM Plotting: First log in the afternoon to last log in the afternoon
            if (!empty($pmLogs)) {
                $pmIns = array_filter(array_column($pmLogs, 'time_in'));
                $pmOuts = array_filter(array_column($pmLogs, 'time_out'));
                
                if (!empty($pmIns)) $dtrRecords[$day]['pm_in'] = min($pmIns);
                if (!empty($pmOuts)) $dtrRecords[$day]['pm_out'] = max($pmOuts);
            }

            // --- CALCULATION LOGIC: The "Presence Mask" (Schedule-Based) ---
            
            // Find approved schedule blocks for this day of the week
            $todaySchedules = array_filter($approvedSchedules, function($s) use ($dayOfWeek) {
                return $s['day_of_week'] === $dayOfWeek;
            });

            foreach ($todaySchedules as $sched) {
                $schedStart = strtotime($sched['start_time']);
                $schedEnd = strtotime($sched['end_time']);

                // Calculate overlap with every physical log window for the day
                foreach ($dayLogs as $log) {
                    if (empty($log['time_in']) || empty($log['time_out'])) continue;

                    $presenceIn = strtotime($log['time_in']);
                    $presenceOut = strtotime($log['time_out']);

                    // Find intersection: max of starts and min of ends
                    $overlapStart = max($schedStart, $presenceIn);
                    $overlapEnd = min($schedEnd, $presenceOut);

                    if ($overlapEnd > $overlapStart) {
                        $dtrRecords[$day]['credited_seconds'] += ($overlapEnd - $overlapStart);
                    }
                }
            }
        }
    }

    $data = [
        'user' => $user,
        'monthName' => $monthName,
        'year' => $year,
        'lastDay' => $lastDay,
        'dtrRecords' => $dtrRecords,
        'settings' => $settings
    ];

    extract($data);
    require_once __DIR__ . '/../views/print_dtr_view.php';
    exit();
}
}