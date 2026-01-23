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

    public function printDtr() {
        $this->requireLogin();
        $userId = $_GET['user_id'] ?? $_SESSION['user_id'];
        if (!isAdmin() && $userId != $_SESSION['user_id']) { die('Access Denied'); }

        $userModel = $this->model('User');
        $attModel = $this->model('Attendance');
        
        $baseDate = $_GET['start_date'] ?? date('Y-m-01');
        $fullMonthStart = date('Y-m-01', strtotime($baseDate));
        $fullMonthEnd   = date('Y-m-t', strtotime($baseDate));
        
        $monthName = date('F', strtotime($fullMonthStart));
        $year = date('Y', strtotime($fullMonthStart));
        $lastDay = (int)date('t', strtotime($fullMonthStart));

        $filters = ['start_date' => $fullMonthStart, 'end_date' => $fullMonthEnd, 'user_id' => $userId];
        $records = $attModel->getRecords($filters);
        $holidays = $attModel->getHolidaysInRange($fullMonthStart, $fullMonthEnd);
        
        $dtrData = [];
        for ($dayNum = 1; $dayNum <= $lastDay; $dayNum++) {
            $dateStr = sprintf("%s-%02d-%02d", $year, date('m', strtotime($fullMonthStart)), $dayNum);
            $dtrData[$dayNum] = [
                'date' => $dateStr, 
                'am_in' => '', 'am_out' => '', 
                'pm_in' => '', 'pm_out' => '', 
                'credited_seconds' => 0, 
                'remarks' => $holidays[$dateStr] ?? ''
            ];
        }
        
        foreach($records as $r) {
            $day = (int)date('d', strtotime($r['date']));
            foreach($r['logs'] as $log) {
                $timeInTs = strtotime($r['date'] . ' ' . $log['time_in']);
                $noonTs = strtotime($r['date'] . ' 12:00:00');

                if ($timeInTs < $noonTs) {
                    if (empty($dtrData[$day]['am_in']) || $timeInTs < strtotime($r['date'] . ' ' . $dtrData[$day]['am_in'])) {
                        $dtrData[$day]['am_in'] = $log['time_in'];
                    }
                    if (!empty($log['time_out'])) {
                        $timeOutTs = strtotime($r['date'] . ' ' . $log['time_out']);
                        if (empty($dtrData[$day]['am_out']) || $timeOutTs > strtotime($r['date'] . ' ' . $dtrData[$day]['am_out'])) {
                            $dtrData[$day]['am_out'] = $log['time_out'];
                        }
                    }
                } else {
                    if (empty($dtrData[$day]['pm_in']) || $timeInTs < strtotime($r['date'] . ' ' . $dtrData[$day]['pm_in'])) {
                        $dtrData[$day]['pm_in'] = $log['time_in'];
                    }
                    if (!empty($log['time_out'])) {
                        $timeOutTs = strtotime($r['date'] . ' ' . $log['time_out']);
                        if (empty($dtrData[$day]['pm_out']) || $timeOutTs > strtotime($r['date'] . ' ' . $dtrData[$day]['pm_out'])) {
                            $dtrData[$day]['pm_out'] = $log['time_out'];
                        }
                    }
                }
                $dtrData[$day]['credited_seconds'] += $this->calculateClampedHours($log, $r['date']);
            }
        }

        $user = $userModel->findById($userId);
        extract([
            'user' => $user,
            'monthName' => $monthName,
            'year' => $year,
            'lastDay' => $lastDay,
            'dtrRecords' => $dtrData
        ]);
        
        require_once __DIR__ . '/../views/print_dtr_view.php';
        exit();
    }
}