<?php
require_once __DIR__ . '/../core/controller.php';

class AttendanceController extends Controller {

    public function index() {
        $this->requireLogin(); 
        
        $attModel = $this->model('Attendance');
        $userModel = $this->model('User');
        
        $data = [
            'isAdmin' => ($_SESSION['role'] === 'Admin'),
            'error' => ''
        ];

        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date'   => $_GET['end_date']   ?? date('Y-m-d'),
            'search'     => $_GET['search']     ?? '',
            'user_id'    => $_GET['user_id']    ?? ''
        ];

        if ($data['isAdmin']) {
            $data['pageTitle'] = 'Attendance Reports';
            $data['pageSubtitle'] = 'View and manage all user attendance records';
            $data['allUsers'] = $userModel->getAllStaff();
            $data['stats'] = $attModel->getStats(); 
        } else {
            $data['pageTitle'] = 'My Attendance';
            $data['pageSubtitle'] = 'View your personal attendance history';
            $filters['user_id'] = $_SESSION['user_id'];
            $data['stats'] = $attModel->getStats($_SESSION['user_id']);
        }

        $data['records'] = $attModel->getRecords($filters);
        $data['totalRecords'] = count($data['records']);
        $data['filters'] = $filters;

        $this->view('attendance_view', $data);
    }

    public function history() {
        $this->requireLogin();
        $attModel = $this->model('Attendance');
        $userModel = $this->model('User');

        $isAdmin = ($_SESSION['role'] === 'Admin');
        
        $filters = [
            'user_id'     => $isAdmin ? ($_GET['user_id'] ?? '') : $_SESSION['user_id'],
            'start_date'  => $_GET['start_date'] ?? date('Y-01-01'),
            'end_date'    => $_GET['end_date']   ?? date('Y-m-d'),
            'status_type' => $_GET['status_type'] ?? '' 
        ];

        $data = [
            'pageTitle' => 'Attendance History',
            'pageSubtitle' => 'Detailed breakdown of attendance records',
            'isAdmin' => $isAdmin,
            'allUsers' => $isAdmin ? $userModel->getAllStaff() : [],
            'filters' => $filters,
            'stats' => $attModel->getHistoryStats($filters),
            'records' => $attModel->getRecords($filters)
        ];

        $this->view('attendance_history_view', $data);
    }

    public function export() {
        $this->requireLogin();
        $attModel = $this->model('Attendance');
        
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
            'user_id' => isAdmin() ? ($_GET['user_id'] ?? '') : $_SESSION['user_id'],
            'search' => $_GET['search'] ?? ''
        ];

        $records = $attModel->getRecords($filters);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Faculty ID', 'Name', 'Role', 'Time In', 'Time Out', 'Status']);
        
        foreach ($records as $row) {
            fputcsv($output, [
                $row['date'], 
                $row['faculty_id'], 
                $row['first_name'] . ' ' . $row['last_name'],
                $row['role'], 
                $row['time_in'], 
                $row['time_out'], 
                $row['status']
            ]);
        }
        fclose($output);
        exit;
    }

    // --- HELPER FUNCTION: Calculate Clamped Hours ---
    private function calculateClampedHours($r) {
        if (empty($r['time_in']) || empty($r['time_out'])) return 0;

        // 1. Get Timestamps (Combine Date + Time to fix AM/PM/Past Date issues)
        $dateStr = $r['date'];
        $actualIn = strtotime("$dateStr " . $r['time_in']);
        $actualOut = strtotime("$dateStr " . $r['time_out']);

        // 2. Check if Schedule Exists
        if (!empty($r['sched_start']) && !empty($r['sched_end'])) {
            $schedStart = strtotime("$dateStr " . $r['sched_start']);
            $schedEnd = strtotime("$dateStr " . $r['sched_end']);

            // 3. CLAMPING LOGIC
            // Effective In = Max(Actual In, Sched Start)
            $effectiveIn = max($actualIn, $schedStart);
            
            // Effective Out = Min(Actual Out, Sched End)
            $effectiveOut = min($actualOut, $schedEnd);

            $duration = $effectiveOut - $effectiveIn;
        } else {
            // No schedule? Just use raw duration
            $duration = $actualOut - $actualIn;
        }

        return max(0, $duration); // Return in seconds, ensure no negative
    }

    // --- UPDATED PRINT DTR (Full Month Structure) ---
    public function printDtr() {
        $this->requireLogin();
        $userId = $_GET['user_id'] ?? $_SESSION['user_id'];
        
        if (!isAdmin() && $userId != $_SESSION['user_id']) {
            die('Access Denied');
        }

        $userModel = $this->model('User');
        $attModel = $this->model('Attendance');
        
        // 1. Get Filtered Dates
        $filterStart = $_GET['start_date'] ?? date('Y-m-01');
        $filterEnd   = $_GET['end_date'] ?? date('Y-m-t');
        
        // 2. Calculate Full Month Dates
        $startObj = new DateTime($filterStart);
        $fullMonthStart = $startObj->format('Y-m-01');
        $fullMonthEnd   = $startObj->format('Y-m-t');

        // 3. Fetch Records using FILTERED dates
        $filters = [
            'start_date' => $filterStart,
            'end_date' => $filterEnd,
            'user_id' => $userId
        ];
        $records = $attModel->getRecords($filters);
        
        // 4. Fetch Holidays
        $holidays = $attModel->getHolidaysInRange($fullMonthStart, $fullMonthEnd);
        
        // --- GROUPING LOGIC ---
        $dtrData = [];
        $fullStartObj = new DateTime($fullMonthStart);
        $fullEndObj = new DateTime($fullMonthEnd);
        $period = new DatePeriod($fullStartObj, DateInterval::createFromDateString('1 day'), $fullEndObj->modify('+1 day'));

        foreach ($period as $dt) {
            $dateStr = $dt->format('Y-m-d');
            $day = (int)$dt->format('d');
            
            $dtrData[$day] = [
                'date' => $dateStr,
                'am_in' => '', 'am_out' => '',
                'pm_in' => '', 'pm_out' => '',
                'credited_seconds' => 0, // NEW FIELD
                'remarks' => ''
            ];

            if (isset($holidays[$dateStr])) {
                $dtrData[$day]['remarks'] = $holidays[$dateStr];
            }
        }
        
        // Fill actual attendance
        foreach($records as $r) {
            $day = (int)date('d', strtotime($r['date']));
            
            if (!empty($r['time_in'])) { $dtrData[$day]['remarks'] = ''; }
            
            // [UPDATED] AM/PM Fix: Use specific date for comparison
            $timeInTs = strtotime($r['date'] . ' ' . $r['time_in']);
            $noonTs = strtotime($r['date'] . ' 12:00:00');

            if ($timeInTs < $noonTs) {
                $dtrData[$day]['am_in'] = $r['time_in'];
                $dtrData[$day]['am_out'] = $r['time_out'];
            } else {
                $dtrData[$day]['pm_in'] = $r['time_in'];
                $dtrData[$day]['pm_out'] = $r['time_out'];
            }
            
            // [UPDATED] Calculate Credited Hours (Clamped)
            $dtrData[$day]['credited_seconds'] += $this->calculateClampedHours($r);
        }

        $data = [
            'user' => $userModel->findById($userId),
            'startDate' => $fullMonthStart,
            'endDate' => $fullMonthEnd,
            'dtrRecords' => $dtrData,
            'isPreview' => isset($_GET['preview'])
        ];

        $this->view('print_dtr_view', $data);
    }

    // --- UPDATED PDF DOWNLOAD (Full Month Structure) ---
    public function downloadDtrPdf() {
        $this->requireLogin();
        require_once __DIR__ . '/../../vendor/tcpdf/tcpdf.php'; 

        $userId = $_GET['user_id'] ?? $_SESSION['user_id'];
        if (!isAdmin() && $userId != $_SESSION['user_id']) { die('Access Denied'); }

        $attModel = $this->model('Attendance');
        $userModel = $this->model('User');
        $user = $userModel->findById($userId);

        $filterStart = $_GET['start_date'] ?? date('Y-m-01');
        $filterEnd   = $_GET['end_date'] ?? date('Y-m-t');

        $startObj = new DateTime($filterStart);
        $fullMonthStart = $startObj->format('Y-m-01');
        $fullMonthEnd   = $startObj->format('Y-m-t');

        $filters = [ 'start_date' => $filterStart, 'end_date' => $filterEnd, 'user_id' => $userId ];
        $records = $attModel->getRecords($filters);
        
        $holidays = $attModel->getHolidaysInRange($fullMonthStart, $fullMonthEnd);

        $dtrData = [];
        $fullStartObj = new DateTime($fullMonthStart);
        $fullEndObj = new DateTime($fullMonthEnd);
        $period = new DatePeriod($fullStartObj, DateInterval::createFromDateString('1 day'), $fullEndObj->modify('+1 day'));

        foreach ($period as $dt) {
            $dateStr = $dt->format('Y-m-d');
            $day = (int)$dt->format('d');
            $dtrData[$day] = ['am_in'=>'', 'am_out'=>'', 'pm_in'=>'', 'pm_out'=>'', 'remarks'=>'', 'credited_seconds' => 0];
            if (isset($holidays[$dateStr])) {
                $dtrData[$day]['remarks'] = $holidays[$dateStr];
            }
        }

        foreach($records as $r) {
            $day = (int)date('d', strtotime($r['date']));
            if (!empty($r['time_in'])) { $dtrData[$day]['remarks'] = ''; }
            
            // [UPDATED] AM/PM Fix
            $timeInTs = strtotime($r['date'] . ' ' . $r['time_in']);
            $noonTs = strtotime($r['date'] . ' 12:00:00');

            if ($timeInTs < $noonTs) {
                $dtrData[$day]['am_in'] = $r['time_in'];
                $dtrData[$day]['am_out'] = $r['time_out'];
            } else {
                $dtrData[$day]['pm_in'] = $r['time_in'];
                $dtrData[$day]['pm_out'] = $r['time_out'];
            }

             // [UPDATED] Calculate Credited Hours (Clamped)
             $dtrData[$day]['credited_seconds'] += $this->calculateClampedHours($r);
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('BPC Attendance System');
        $pdf->SetTitle('DTR - ' . $user['last_name']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        $monthLabel = date('F Y', strtotime($fullMonthStart));
        $fullName = strtoupper($user['last_name'] . ', ' . $user['first_name']);

        $html = '<h1 style="text-align:center; font-size: 14pt;">Civil Service Form No. 48</h1>';
        $html .= '<h2 style="text-align:center; font-size: 16pt; font-weight: bold;">DAILY TIME RECORD</h2>';
        $html .= '<table cellpadding="5" cellspacing="0" style="width: 100%; margin-bottom: 10px;">';
        $html .= '<tr><td style="text-align:center; border-bottom: 1px solid black;"><strong>' . $fullName . '</strong></td></tr>';
        $html .= '<tr><td style="text-align:center; font-size: 10pt;">(Name)</td></tr>';
        $html .= '<tr><td style="text-align:center;">For the month of <strong>' . $monthLabel . '</strong></td></tr>';
        $html .= '</table>';
        
        $html .= '<table border="1" cellpadding="4" cellspacing="0" style="text-align:center; font-size: 9pt;">';
        $html .= '<tr style="background-color:#f0f0f0; font-weight:bold;">
                    <th width="10%" rowspan="2">Day</th>
                    <th width="45%" colspan="2">A.M.</th>
                    <th width="45%" colspan="2">P.M.</th>
                    <th width="15%" rowspan="2">Total (Hrs)</th>
                  </tr>
                  <tr style="background-color:#f0f0f0; font-weight:bold;">
                    <th>Arrival</th><th>Departure</th><th>Arrival</th><th>Departure</th>
                  </tr>';
        
        foreach ($dtrData as $day => $data) {
            $am_in = $data['am_in'] ? date('h:i', strtotime($data['am_in'])) : '';
            $am_out = $data['am_out'] ? date('h:i', strtotime($data['am_out'])) : '';
            $pm_in = $data['pm_in'] ? date('h:i', strtotime($data['pm_in'])) : '';
            $pm_out = $data['pm_out'] ? date('h:i', strtotime($data['pm_out'])) : '';
            
            // Calculate display hours
            $totalHrs = '';
            if ($data['credited_seconds'] > 0) {
                $totalHrs = number_format($data['credited_seconds'] / 3600, 2);
            }

            $html .= '<tr>';
            $html .= '<td>' . $day . '</td>';
            if (!empty($data['remarks']) && empty($am_in) && empty($pm_in)) {
                 $html .= '<td colspan="4" style="color:red; font-style:italic;">' . $data['remarks'] . '</td>';
            } else {
                 $html .= '<td>' . $am_in . '</td><td>' . $am_out . '</td>';
                 $html .= '<td>' . $pm_in . '</td><td>' . $pm_out . '</td>';
            }
            $html .= '<td>' . $totalHrs . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '<p style="font-size: 8pt; margin-top: 10px;">I certify on my honor that the above is a true and correct record...</p>';
        $html .= '<br><br><table style="width: 100%;"><tr><td style="text-align:center; border-bottom: 1px solid black; width: 60%; margin: 0 auto;"></td></tr><tr><td style="text-align:center;">(Signature)</td></tr></table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('DTR_' . $user['faculty_id'] . '.pdf', 'D');
        exit;
    }

    // API Methods (Admin Editing) - No Change needed but kept for completeness
    public function getMonthlyDtr() {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $userId = $_GET['user_id'] ?? 0;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');

        if (empty($userId)) { echo json_encode(['success' => false, 'message' => 'No user ID.']); exit; }

        try {
            $db = Database::getInstance();
            $start = new DateTime($startDate);
            $month = $start->format('m');
            $year = $start->format('Y');
            $daysInMonth = (int)$start->format('t');
            $endDate = $start->format('Y-m-t');

            $stmt = $db->query(
                "SELECT id, date, time_in, time_out, status, remarks 
                 FROM attendance_records 
                 WHERE user_id = ? AND date BETWEEN ? AND ?",
                 [$userId, $startDate, $endDate], "iss"
            );
            $dbRecords = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $attendanceData = [];
            foreach ($dbRecords as $rec) {
                $dayOfMonth = (int)(new DateTime($rec['date']))->format('j');
                $attendanceData[$dayOfMonth] = $rec;
            }

            $fullMonthData = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                
                if (isset($attendanceData[$day])) {
                    $rec = $attendanceData[$day];
                    $fullMonthData[] = [
                        'day' => $day, 'date' => $rec['date'], 'record_id' => $rec['id'],
                        'time_in' => $rec['time_in'] ? date('H:i:s', strtotime($rec['time_in'])) : null,
                        'time_out' => $rec['time_out'] ? date('H:i:s', strtotime($rec['time_out'])) : null,
                        'status' => $rec['status'], 'remarks' => $rec['remarks'], 'exists' => true
                    ];
                } else {
                    $fullMonthData[] = [
                        'day' => $day, 'date' => $date, 'record_id' => null,
                        'time_in' => null, 'time_out' => null, 'status' => null, 'remarks' => null, 'exists' => false
                    ];
                }
            }
            echo json_encode(['success' => true, 'data' => $fullMonthData]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function saveMonthlyDtr() {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? 0;
        $records = $data['records'] ?? [];

        if (empty($userId) || empty($records)) { echo json_encode(['success' => false, 'message' => 'Invalid data.']); exit; }

        $db = Database::getInstance();
        $conn = $db->conn;
        $conn->begin_transaction();

        try {
            $stmt_update = $conn->prepare("UPDATE attendance_records SET time_in=?, time_out=?, status=?, working_hours=?, remarks=? WHERE id=?");
            $stmt_insert = $conn->prepare("INSERT INTO attendance_records (user_id, date, time_in, time_out, status, working_hours, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($records as $rec) {
                $recordId = $rec['record_id'];
                $date = $rec['date'];
                $timeIn = !empty($rec['time_in']) ? $rec['time_in'] : null;
                $timeOut = !empty($rec['time_out']) ? $rec['time_out'] : null;
                $status = !empty($rec['status']) ? $rec['status'] : null;
                $remarks = !empty($rec['remarks']) ? $rec['remarks'] : null;

                if (empty($recordId) && empty($timeIn) && empty($timeOut) && empty($status) && empty($remarks)) {
                    continue;
                }
                
                $workingHours = 0; 
                if ($timeIn && $timeOut) {
                    $span = (new DateTime($timeOut))->getTimestamp() - (new DateTime($timeIn))->getTimestamp();
                    $workingHours = $span / 3600.0;
                    if ($workingHours > 5) $workingHours -= 1;
                }

                if (!empty($recordId)) {
                    $stmt_update->bind_param("sssdsi", $timeIn, $timeOut, $status, $workingHours, $remarks, $recordId);
                    $stmt_update->execute();
                } else {
                    $stmt_insert->bind_param("issssds", $userId, $date, $timeIn, $timeOut, $status, $workingHours, $remarks);
                    $stmt_insert->execute();
                }
            }

            $conn->commit();
            $logModel = $this->model('ActivityLog');
            $logModel->log($_SESSION['user_id'], 'DTR Edited', "Admin edited DTR for user ID $userId");

            echo json_encode(['success' => true, 'message' => 'DTR updated successfully!']);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>