<?php
require_once __DIR__ . '/../core/controller.php';

class ApiController extends Controller {

    public function __construct() {
    }

    private function verifyApiKey() {
        // We look for the key in the 'X-API-KEY' header
        $providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty(API_SECRET_KEY) || !hash_equals(API_SECRET_KEY, $providedKey)) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid API Key.']);
            exit;
        }
    }

    public function recordAttendance() {
        $this->verifyApiKey(); // SECURED
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'));

        if (!$data || !isset($data->user_id)) {
            echo json_encode(['success' => false, 'message' => "Invalid user ID."]);
            exit;
        }

        $userId = (int)$data->user_id;
        $db = Database::getInstance();

        // fetches the user 
        $userQuery = $db->query("SELECT * FROM users WHERE id = ?", [$userId], "i");
        $user = $userQuery->get_result()->fetch_assoc();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => "User not found."]);
            exit;
        }

        $today = date('Y-m-d');
        $now = date('H:i:s');
        $status = "";

        // checks the records for an exisiting attendance record today
        $stmt = $db->query("SELECT id, time_in FROM attendance_records WHERE user_id = ? AND date = ?", [$userId, $today], "is");
        $record = $stmt->get_result()->fetch_assoc();

        if ($record) {
            $db->query("UPDATE attendance_records SET time_out = ? WHERE id = ?", [$now, $record['id']], "si");
            $status = "Time Out";
   } else {

    $timeInStatus = "On-time";
    $dayOfWeek = date('l'); 

    $graceStmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'late_threshold_minutes'");
    $graceRow = $graceStmt->get_result()->fetch_assoc();
    $graceMinutes = $graceRow ? (int)$graceRow['setting_value'] : 15;
   
    $scheduleStmt = $db->query(
        "SELECT MIN(start_time) AS first_class_start 
         FROM class_schedules 
         WHERE user_id = ? AND day_of_week = ? AND status = 'approved'",
        [$userId, $dayOfWeek], "is"
    );
    $schedule = $scheduleStmt->get_result()->fetch_assoc();

    if ($schedule && $schedule['first_class_start']) {
        $firstClassStart = strtotime($schedule['first_class_start']);
        $currentTime = strtotime($now);
        $gracePeriodSeconds = $graceMinutes * 60; 

        if ($currentTime > ($firstClassStart + $gracePeriodSeconds)) {
            $timeInStatus = "Late";
        }
    }

    $db->query("INSERT INTO attendance_records (user_id, date, time_in, status) VALUES (?, ?, ?, ?)", 
        [$userId, $today, $now, $timeInStatus], "isss");
    
    $status = ($timeInStatus === "Late") ? "Time In (Late)" : "Time In";
}

        echo json_encode([
            'success' => true,
            'message' => "Attendance recorded",
            'data' => [
                "type"   => "attendance",
                "name"   => $user['first_name'] . ' ' . $user['last_name'],
                "status" => $status,
                "time"   => date('h:i A', strtotime($now)),
                "date"   => date('l, F j, Y'),
                "full_timestamp" => date('c')
            ]
        ]);
        exit;
    }

    // this function is the one that fetches the fingerprint templates from the database to be used for the bridge app
    public function getFingerprintTemplates() {
        $this->verifyApiKey(); // SECURED
        header('Content-Type: application/json');
        $db = Database::getInstance();
        
        $sql = "SELECT id, fingerprint_data FROM users WHERE fingerprint_data IS NOT NULL AND status = 'active'";
        $result = $db->query($sql);

        if ($result) {
            $templates = [];
            $res = $result->get_result();
            while ($row = $res->fetch_assoc()) {
                $templates[] = [
                    'id' => $row['id'], 
                    'fingerprint_template' => $row['fingerprint_data'] 
                ];
            }
            echo json_encode(['success' => true, 'message' => "Templates fetched", 'data' => $templates]);
        } else {
            echo json_encode(['success' => false, 'message' => "Database error"]);
        }
        exit;
    }
    //handles the notification reading for a single notif
    public function markNotificationRead() {
        $this->requireLogin();
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success'=>false, 'message' => 'Unauthorized']); 
            exit; 
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $notifId = $data['notification_id'] ?? null;
        
        if (!$notifId) {
            echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
            exit;
        }
        
        $db = Database::getInstance();
        $db->query("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$notifId, $_SESSION['user_id']], "ii");
        
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        exit;
    }
    
    // for all notif
    public function markAllNotificationsRead() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success'=>false, 'message' => 'Unauthorized']); 
            exit; 
        }
        
        $db = Database::getInstance();
        $db->query(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", 
            [$_SESSION['user_id']], 
            "i"
        );
        
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
        exit;
    }
}