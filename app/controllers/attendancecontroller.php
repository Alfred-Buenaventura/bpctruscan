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

        $rawRecords = $attModel->getRecords($filters);
        
        // --- Grouping Logic: Aggregate per user/day for the Accordion ---
        $grouped = [];
        foreach ($rawRecords as $r) {
            $key = $r['date'] . '_' . $r['user_id'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'date' => $r['date'],
                    'faculty_id' => $r['faculty_id'],
                    'name' => $r['first_name'] . ' ' . $r['last_name'],
                    'logs' => [],
                    'status' => $r['status']
                ];
            }
            
            $grouped[$key]['logs'][] = [
                'time_in' => date('h:i A', strtotime($r['time_in'])),
                'time_out' => !empty($r['time_out']) ? date('h:i A', strtotime($r['time_out'])) : '---',
                'subject' => $r['subject'] ?? 'General Duty'
            ];
        }

        $data['records'] = $grouped;
        $data['filters'] = $filters;

        $this->view('attendance_view', $data);
    }

    /**
     * Updated: Rounds seconds to nearest minute to resolve 59-minute display issue.
     */
    private function calculateClampedHours($r) {
        if (empty($r['time_in']) || empty($r['time_out'])) return 0;
        if (empty($r['sched_start']) || empty($r['sched_end'])) return 0;

        $dateStr = $r['date'];
        $actualIn = strtotime("$dateStr " . $r['time_in']);
        $actualOut = strtotime("$dateStr " . $r['time_out']);
        $schedStart = strtotime("$dateStr " . $r['sched_start']);
        $schedEnd = strtotime("$dateStr " . $r['sched_end']);

        // Only credit time within the scheduled block boundaries
        $effectiveIn = max($actualIn, $schedStart);
        $effectiveOut = min($actualOut, $schedEnd);

        $durationSeconds = $effectiveOut - $effectiveIn;
        if ($durationSeconds <= 0) return 0;

        // FIX: Round to the nearest minute to prevent "59 minutes" result from minor second offsets
        return round($durationSeconds / 60) * 60;
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
            $dtrData[$day] = ['date' => $dateStr, 'am_in' => '', 'am_out' => '', 'pm_in' => '', 'pm_out' => '', 'credited_seconds' => 0, 'remarks' => ''];
            if (isset($holidays[$dateStr])) { $dtrData[$day]['remarks'] = $holidays[$dateStr]; }
        }
        
        // Accumulate multiple shifts correctly per half-day
        foreach($records as $r) {
            $day = (int)date('d', strtotime($r['date']));
            if (!empty($r['sched_start']) && !empty($r['sched_end'])) {
                if (!empty($r['time_in'])) { $dtrData[$day]['remarks'] = ''; }
                
                $timeInTs = strtotime($r['date'] . ' ' . $r['time_in']);
                $noonTs = strtotime($r['date'] . ' 12:00:00');

                if ($timeInTs < $noonTs) {
                    // AM: Keep earliest arrival and latest departure
                    if (empty($dtrData[$day]['am_in']) || $timeInTs < strtotime($r['date'] . ' ' . $dtrData[$day]['am_in'])) {
                        $dtrData[$day]['am_in'] = $r['time_in'];
                    }
                    if (!empty($r['time_out'])) {
                        $timeOutTs = strtotime($r['date'] . ' ' . $r['time_out']);
                        if (empty($dtrData[$day]['am_out']) || $timeOutTs > strtotime($r['date'] . ' ' . $dtrData[$day]['am_out'])) {
                            $dtrData[$day]['am_out'] = $r['time_out'];
                        }
                    }
                } else {
                    // PM: Keep earliest arrival and latest departure
                    if (empty($dtrData[$day]['pm_in']) || $timeInTs < strtotime($r['date'] . ' ' . $dtrData[$day]['pm_in'])) {
                        $dtrData[$day]['pm_in'] = $r['time_in'];
                    }
                    if (!empty($r['time_out'])) {
                        $timeOutTs = strtotime($r['date'] . ' ' . $r['time_out']);
                        if (empty($dtrData[$day]['pm_out']) || $timeOutTs > strtotime($r['date'] . ' ' . $dtrData[$day]['pm_out'])) {
                            $dtrData[$day]['pm_out'] = $r['time_out'];
                        }
                    }
                }
                // Accumulate seconds for ALL shifts on this day
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