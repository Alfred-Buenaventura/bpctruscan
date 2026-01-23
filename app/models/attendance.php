<?php
require_once __DIR__ . '/../core/database.php';

class Attendance {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get basic today's record for a specific user
     */
    public function getTodayRecord($userId) {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM attendance_records WHERE user_id = ? AND DATE(date) = ? LIMIT 1";
        $stmt = $this->db->query($sql, [$userId, $today], "is");
        return $stmt->get_result()->fetch_assoc();
    }

    public function getUserHistory($userId, $filters = []) {
    // UPDATED: JOINing with the correct table 'class_schedules'
    $sql = "SELECT r.*, u.first_name, u.last_name, u.faculty_id, 
                   cs.subject as duty_subject, cs.room as duty_room, cs.type as duty_type
            FROM attendance_records r 
            JOIN users u ON r.user_id = u.id 
            LEFT JOIN class_schedules cs ON r.schedule_id = cs.id 
            WHERE 1=1"; 
    
    $params = [];
    $types = "";

    if (!empty($userId)) {
        $sql .= " AND r.user_id = ?";
        $params[] = $userId;
        $types .= "i";
    }

    if (!empty($filters['status_type'])) {
        $sql .= " AND r.status = ?";
        $params[] = $filters['status_type'];
        $types .= "s";
    }
    
    $sql .= " AND r.date BETWEEN ? AND ? ORDER BY r.date DESC, r.time_in ASC";
    $params[] = $filters['start_date'];
    $params[] = $filters['end_date'];
    $types .= "ss";

    // Use the core MySQLi query method
    $stmt = $this->db->query($sql, $params, $types);
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
    /**
     * Get records based on filters (for the main table and DTR)
     */
    public function getRecords($filters) {
        $sql = "SELECT ar.*, 
                       u.faculty_id, u.first_name, u.last_name, u.role,
                       s.start_time as sched_start, s.end_time as sched_end,
                       s.subject
                FROM attendance_records ar
                JOIN users u ON ar.user_id = u.id
                LEFT JOIN class_schedules s ON ar.schedule_id = s.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND ar.date BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
            $types .= "ss";
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND ar.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.faculty_id LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search; $params[] = $search; $params[] = $search;
            $types .= "sss";
        }

        $sql .= " ORDER BY ar.date DESC, ar.time_in ASC";
        
        $res = $this->db->query($sql, $params, $types)->get_result();
        
        $records = [];
        while ($row = $res->fetch_assoc()) {
            $key = $row['date'] . '_' . $row['user_id'];
            
            if (!isset($records[$key])) {
                $records[$key] = [
                    'user_id'    => $row['user_id'],
                    'name'       => $row['first_name'] . ' ' . $row['last_name'],
                    'faculty_id' => $row['faculty_id'],
                    'date'       => $row['date'],
                    'status'     => $row['status'],
                    'logs'       => []
                ];
            }
            
            if (!empty($row['time_in'])) {
                $records[$key]['logs'][] = [
                    'subject'     => $row['subject'] ?? 'General Duty',
                    'time_in'     => $row['time_in'],
                    'time_out'    => $row['time_out'],
                    'sched_start' => $row['sched_start'], // Added for clamping logic
                    'sched_end'   => $row['sched_end']    // Added for clamping logic
                ];
            }
        }
        
        return array_values($records);
    }

    /**
     * Get Count Stats for Dashboard Cards
     */
    public function getStats($userId = null) {
        $today = date('Y-m-d');
        $stats = ['entries' => 0, 'exits' => 0, 'present_total' => 0, 'late' => 0];

        $hasIn  = " (time_in IS NOT NULL AND time_in != '' AND time_in != '00:00:00') ";
        $hasOut = " (time_out IS NOT NULL AND time_out != '' AND time_out != '00:00:00') ";
        $noOut  = " (time_out IS NULL OR time_out = '' OR time_out = '00:00:00') ";

        if ($userId) {
            $sqlEntries = "SELECT COUNT(*) as c FROM attendance_records WHERE user_id = ? AND DATE(date) = ? AND $hasIn";
            $sqlExits   = "SELECT COUNT(*) as c FROM attendance_records WHERE user_id = ? AND DATE(date) = ? AND $hasOut";
            $sqlPresent = "SELECT COUNT(*) as c FROM attendance_records WHERE user_id = ? AND DATE(date) = ? AND $hasIn AND $noOut";
            $sqlLate    = "SELECT COUNT(*) as c FROM attendance_records WHERE user_id = ? AND DATE(date) = ? AND status LIKE '%Late%'";

            $stats['entries'] = $this->db->query($sqlEntries, [$userId, $today], "is")->get_result()->fetch_assoc()['c'] ?? 0;
            $stats['exits']   = $this->db->query($sqlExits, [$userId, $today], "is")->get_result()->fetch_assoc()['c'] ?? 0;
            $stats['present_total'] = $this->db->query($sqlPresent, [$userId, $today], "is")->get_result()->fetch_assoc()['c'] ?? 0;
            $stats['late']    = $this->db->query($sqlLate, [$userId, $today], "is")->get_result()->fetch_assoc()['c'] ?? 0;
        } else {
            $sqlEntries = "SELECT COUNT(DISTINCT user_id) as c FROM attendance_records WHERE DATE(date) = ? AND $hasIn";
            $sqlExits   = "SELECT COUNT(DISTINCT user_id) as c FROM attendance_records WHERE DATE(date) = ? AND $hasOut";
            $sqlPresent = "SELECT COUNT(DISTINCT user_id) as c FROM attendance_records WHERE DATE(date) = ? AND $hasIn AND $noOut";
            $sqlLate    = "SELECT COUNT(DISTINCT user_id) as c FROM attendance_records WHERE DATE(date) = ? AND status LIKE '%Late%'";

            $stats['entries'] = $this->db->query($sqlEntries, [$today], "s")->get_result()->fetch_assoc()['c'] ?? 0;
            $stats['exits']   = $this->db->query($sqlExits, [$today], "s")->get_result()->fetch_assoc()['c'] ?? 0;
            $stats['present_total'] = $this->db->query($sqlPresent, [$today], "s")->get_result()->fetch_assoc()['c'] ?? 0;
            $stats['late']    = $this->db->query($sqlLate, [$today], "s")->get_result()->fetch_assoc()['c'] ?? 0;
        }

        return $stats;
    }

    public function countActiveToday() {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(DISTINCT user_id) as c FROM attendance_records WHERE DATE(date) = ? AND time_in IS NOT NULL AND time_in != ''";
        $res = $this->db->query($sql, [$today], "s")->get_result();
        return $res->fetch_assoc()['c'] ?? 0;
    }

    /**
     * Fetch specific list of users for the Detail Modals
     */
    public function getDetailedStatsByType($type) {
        $today = date('Y-m-d');
        $hasIn  = " (ar.time_in IS NOT NULL AND ar.time_in != '' AND ar.time_in != '00:00:00') ";
        $hasOut = " (ar.time_out IS NOT NULL AND ar.time_out != '' AND ar.time_out != '00:00:00') ";
        $noOut  = " (ar.time_out IS NULL OR ar.time_out = '' OR ar.time_out = '00:00:00') ";

        $sql = "SELECT u.faculty_id, u.first_name, u.last_name, u.role, ar.time_in, ar.time_out 
                FROM attendance_records ar
                JOIN users u ON ar.user_id = u.id
                WHERE DATE(ar.date) = ?";
        
        switch ($type) {
            case 'entries': $sql .= " AND $hasIn"; break;
            case 'exits': $sql .= " AND $hasOut"; break;
            case 'present': $sql .= " AND $hasIn AND $noOut"; break;
            case 'late': $sql .= " AND ar.status LIKE '%Late%'"; break;
        }
        
        $sql .= " GROUP BY u.id";
        return $this->db->query($sql, [$today], "s")->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getHolidaysInRange($startDate, $endDate) {
        $holidays = [];
        $sql = "SELECT holiday_date, description FROM holidays WHERE holiday_date BETWEEN ? AND ?";
        $res = $this->db->query($sql, [$startDate, $endDate], "ss")->get_result();
        while($row = $res->fetch_assoc()){
            $holidays[$row['holiday_date']] = $row['description'];
        }
        return $holidays;
    }

    public function getDailySummary($userId) {
    $today = date('Y-m-d');
    $dayOfWeek = date('l'); // e.g., 'Monday', 'Tuesday'
    
    // 1. Get the earliest Time In and latest Time Out
    $sqlSummary = "SELECT 
                MIN(NULLIF(time_in, '00:00:00')) as first_in, 
                MAX(NULLIF(time_out, '00:00:00')) as last_out
            FROM attendance_records 
            WHERE user_id = ? AND DATE(date) = ?";
    
    $stmtSummary = $this->db->query($sqlSummary, [$userId, $today], "is");
    $summary = $stmtSummary->get_result()->fetch_assoc();

    // 2. Determine initial status based on attendance logs
    $sqlStatus = "SELECT status FROM attendance_records 
                  WHERE user_id = ? AND DATE(date) = ? 
                  ORDER BY time_in ASC LIMIT 1";
                  
    $stmtStatus = $this->db->query($sqlStatus, [$userId, $today], "is");
    $statusRow = $stmtStatus->get_result()->fetch_assoc();
    $currentStatus = $statusRow['status'] ?? null;

    // 3. LOGIC REFINEMENT: If no logs exist, check the schedule
    if (empty($summary['first_in'])) {
        // Check if the user has any approved schedules for today
        $sqlSched = "SELECT COUNT(*) as sched_count FROM class_schedules 
                     WHERE user_id = ? AND day_of_week = ? AND status = 'approved'";
        $resSched = $this->db->query($sqlSched, [$userId, $dayOfWeek], "is")->get_result()->fetch_assoc();
        
        if ($resSched['sched_count'] > 0) {
            $currentStatus = 'Absent'; // Scheduled but no log
        } else {
            $currentStatus = 'Not Present'; // No schedule and no log
        }
    }
    
    return [
        'time_in'  => $summary['first_in'] ?? null,
        'time_out' => $summary['last_out'] ?? null,
        'status'   => $currentStatus
    ];
}
}