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
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Faculty ID', 'Name', 'Role', 'Time In', 'Time Out', 'Status']);
        foreach ($records as $row) {
            fputcsv($output, [$row['date'], $row['faculty_id'], $row['first_name'].' '.$row['last_name'], $row['role'], $row['time_in'], $row['time_out'], $row['status']]);
        }
        fclose($output);
        exit;
    }

    private function calculateClampedHours($r) {
        if (empty($r['time_in']) || empty($r['time_out'])) return 0;

        $dateStr = $r['date'];
        $actualIn = strtotime("$dateStr " . $r['time_in']);
        $actualOut = strtotime("$dateStr " . $r['time_out']);

        if (!empty($r['sched_start']) && !empty($r['sched_end'])) {
            $schedStart = strtotime("$dateStr " . $r['sched_start']);
            $schedEnd = strtotime("$dateStr " . $r['sched_end']);

            $effectiveIn = max($actualIn, $schedStart);
            $effectiveOut = min($actualOut, $schedEnd);

            return max(0, $effectiveOut - $effectiveIn); 
        }

        return 0; 
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
        $period = new DatePeriod(new DateTime($fullMonthStart), new DateInterval('P1D'), (new DateTime($fullMonthEnd))->modify('+1 day'));

        foreach ($period as $dt) {
            $dateStr = $dt->format('Y-m-d');
            $day = (int)$dt->format('d');
            $dtrData[$day] = [
                'date' => $dateStr, 
                'am_in' => '', 'am_out' => '',
                'pm_in' => '', 'pm_out' => '', 
                'credited_seconds' => 0, 
                'remarks' => ''
            ];
            if (isset($holidays[$dateStr])) { $dtrData[$day]['remarks'] = $holidays[$dateStr]; }
        }
        
        foreach($records as $r) {
            $day = (int)date('d', strtotime($r['date']));
            if (!empty($r['sched_start']) && !empty($r['sched_end'])) {
                if (!empty($r['time_in'])) { $dtrData[$day]['remarks'] = ''; }
                
                $timeInTs = strtotime($r['date'] . ' ' . $r['time_in']);
                $noonTs = strtotime($r['date'] . ' 12:00:00');

                if ($timeInTs < $noonTs) {
                    $dtrData[$day]['am_in'] = $r['time_in'];
                    $dtrData[$day]['am_out'] = $r['time_out'];
                } else {
                    $dtrData[$day]['pm_in'] = $r['time_in'];
                    $dtrData[$day]['pm_out'] = $r['time_out'];
                }
                $dtrData[$day]['credited_seconds'] += $this->calculateClampedHours($r);
            }
        }

        $this->view('print_dtr_view', [
            'user' => $userModel->findById($userId),
            'monthName' => $monthName,
            'year' => $year,
            'lastDay' => $lastDay,
            'dtrRecords' => $dtrData
        ]);
    }
}