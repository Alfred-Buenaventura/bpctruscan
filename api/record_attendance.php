<?php
session_start();
require_once __DIR__ . '/../app/init.php';

header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput);

if (!$data || !isset($data->user_id)) {
    echo json_encode(['success' => false, 'message' => "Invalid user ID."]);
    exit;
}

$userId = (int)$data->user_id;

try {
    $db = Database::getInstance(); // Use Singleton
    
    $today = date('Y-m-d');
    $now = date('H:i:s');
    $currentTs = strtotime("$today $now");
    $noonTs = strtotime("$today 12:00:00");
    
    $status = "";
    $isWarning = false;

    // 1. Fetch User
    $userStmt = $db->query("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId], "i");
    $user = $userStmt->get_result()->fetch_assoc();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => "User not found."]);
        exit;
    }

    // 2. Check for ANY open session (Time In but NO Time Out) for today
    // This allows a user to clock in AM and clock out PM without creating a new record.
    $openStmt = $db->query(
        "SELECT id, time_in FROM attendance_records WHERE user_id = ? AND date = ? AND time_out IS NULL LIMIT 1", 
        [$userId, $today], 
        "is"
    );
    $openRecord = $openStmt->get_result()->fetch_assoc();

    if ($openRecord) {
        // === TIME OUT LOGIC ===
        // User has an open record. Close it.
        $timeInTs = strtotime($openRecord['time_in']);

        // Check Cooldown (60 Seconds) - Prevents accidental double scans
        if (($currentTs - $timeInTs) < 60) {
            $status = "Already Timed In";
            $isWarning = true;
        } else {
            // Valid Time Out
            $durationSeconds = $currentTs - $timeInTs;
            $workingHours = $durationSeconds / 3600.0;
            
            // Deduct lunch break (1 hour) if duration > 5 hours
            if ($workingHours > 5) { 
                $workingHours -= 1; 
            }

            $db->query(
                "UPDATE attendance_records SET time_out = ?, working_hours = ? WHERE id = ?", 
                [$now, $workingHours, $openRecord['id']], 
                "sdi"
            );
            
            $status = "Time Out";
        }

    } else {
        // === TIME IN LOGIC ===
        // No open records. Attempt to create a NEW Time In.
        
        $isAM = ($currentTs < $noonTs);
        
        // Check if the user has ALREADY completed a session for this period (AM or PM)
        // This prevents a user from clocking in twice in the morning or twice in the afternoon.
        $checkCompletedSql = "SELECT id FROM attendance_records 
                              WHERE user_id = ? AND date = ? AND time_out IS NOT NULL AND " . 
                              ($isAM ? "time_in < '12:00:00'" : "time_in >= '12:00:00'");
        
        $checkCompleted = $db->query($checkCompletedSql, [$userId, $today], "is");
        
        if ($checkCompleted->get_result()->num_rows > 0) {
             $status = "Already Completed " . ($isAM ? "AM" : "PM") . " Session";
             $isWarning = true;
        } else {
            // Proceed to Time In
            $timeInStatus = "On-time"; // Default
            $dayOfWeek = date('l'); 

            // A. Fetch Configurable Grace Period (default 15 mins)
            $graceStmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'late_threshold_minutes'");
            $graceRow = $graceStmt->get_result()->fetch_assoc();
            $graceMinutes = $graceRow ? (int)$graceRow['setting_value'] : 15;
            $graceSeconds = $graceMinutes * 60;

            // B. Check User's Schedule for Today
            // We get the EARLIEST start time for the current day
            $scheduleStmt = $db->query(
                "SELECT MIN(start_time) AS first_class_start 
                 FROM class_schedules 
                 WHERE user_id = ? AND day_of_week = ? AND status = 'approved'",
                [$userId, $dayOfWeek], "is"
            );
            $schedule = $scheduleStmt->get_result()->fetch_assoc();

            // C. Determine Status
            if ($schedule && $schedule['first_class_start']) {
                $scheduleStartTs = strtotime($today . ' ' . $schedule['first_class_start']);
                $lateThreshold = $scheduleStartTs + $graceSeconds;

                if ($currentTs > $lateThreshold) {
                    $timeInStatus = "Late";
                }
            }

            // D. Insert Record
            $db->query(
                "INSERT INTO attendance_records (user_id, date, time_in, status) VALUES (?, ?, ?, ?)", 
                [$userId, $today, $now, $timeInStatus], 
                "isss"
            );
            
            $status = "Time In - " . $timeInStatus;
        }
    }

    echo json_encode([
        'success' => true, 
        'message' => "Attendance processed", 
        'data' => [
            "type"   => "attendance",
            "name"   => $user['first_name'] . ' ' . $user['last_name'],
            "status" => $status,
            "time"   => date('h:i A', $currentTs),
            "date"   => date('l, F j, Y'),
            "is_warning" => $isWarning
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "System error: " . $e->getMessage()]);
}
?>