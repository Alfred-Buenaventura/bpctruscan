<?php
//
session_start();

// 1. CRITICAL: Set Timezone to Philippines (Fixes IONOS server time issue)
date_default_timezone_set('Asia/Manila');

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
    $db = Database::getInstance(); 
    
    $today = date('Y-m-d');
    $now = date('H:i:s');
    $currentDay = date('l'); // e.g., "Monday"
    
    // ---------------------------------------------------------
    // STEP 1: FETCH USER
    // ---------------------------------------------------------
    $userStmt = $db->query("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId], "i");
    $user = $userStmt->get_result()->fetch_assoc();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => "User not found."]);
        exit;
    }

    // ---------------------------------------------------------
    // STEP 2: THE "CLEANUP" LOOP (Auto-Mark Absents)
    // ---------------------------------------------------------
    // Get ALL classes for this student for TODAY
    $schedSql = "SELECT * FROM class_schedules 
                 WHERE user_id = ? AND day_of_week = ? 
                 ORDER BY start_time ASC";
    $schedStmt = $db->query($schedSql, [$userId, $currentDay], "is");
    $schedules = $schedStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($schedules as $class) {
        $schedId = $class['id'];
        $endTime = $class['end_time'];

        // Check if this class has already ended (Past Class)
        if ($now > $endTime) {
            // Check if there is an attendance record for this specific class
            $checkAtt = $db->query(
                "SELECT id FROM attendance_records 
                 WHERE user_id = ? AND schedule_id = ? AND date = ?", 
                [$userId, $schedId, $today], 
                "iis"
            );

            if ($checkAtt->get_result()->num_rows == 0) {
                // NO RECORD FOUND -> Mark as ABSENT
                $db->query(
                    "INSERT INTO attendance_records (user_id, schedule_id, date, status) 
                     VALUES (?, ?, ?, 'Absent')",
                    [$userId, $schedId, $today], 
                    "iis"
                );
            }
        }
    }

    // ---------------------------------------------------------
    // STEP 3: PROCESS THE CURRENT SCAN
    // ---------------------------------------------------------
    
    $status = "";
    $isWarning = false;
    $scheduleId = null; // We will try to find which class this is

    // Find which class happens RIGHT NOW (Start Time <= Now <= End Time)
    $currentClass = null;
    foreach ($schedules as $class) {
        // Add a small buffer? (e.g. allow login 30 mins before start)
        // For now, we stick to strict schedule times or just assume generic logic if no match
        // Let's assume strict + grace period handling is done inside logic
        if ($now >= $class['start_time'] && $now <= $class['end_time']) {
            $currentClass = $class;
            break;
        }
    }

    // Check for an OPEN session (Time In but NO Time Out)
    // We filter by status != 'Absent' to ignore the auto-absents we just made
    $openStmt = $db->query(
        "SELECT id, time_in, schedule_id FROM attendance_records 
         WHERE user_id = ? AND date = ? AND time_out IS NULL AND status != 'Absent' 
         ORDER BY id DESC LIMIT 1", 
        [$userId, $today], 
        "is"
    );
    $openRecord = $openStmt->get_result()->fetch_assoc();

    if ($openRecord) {
        // === SCENARIO A: TIME OUT ===
        // User is closing an existing session
        
        $timeInTs = strtotime($openRecord['time_in']);
        $currentTs = strtotime("$today $now");

        // Anti-Double Scan (60 seconds cooldown)
        if (($currentTs - $timeInTs) < 60) {
            $status = "Already Timed In";
            $isWarning = true;
        } else {
            // Calculate Hours
            $duration = $currentTs - $timeInTs;
            $hours = $duration / 3600.0;

            $db->query(
                "UPDATE attendance_records SET time_out = ?, working_hours = ? WHERE id = ?", 
                [$now, $hours, $openRecord['id']], 
                "sdi"
            );
            $status = "Time Out";
        }

    } else {
        // === SCENARIO B: TIME IN ===
        
        if ($currentClass) {
            // We found a specific class schedule for this time
            $scheduleId = $currentClass['id'];

            // Check if already attended THIS specific class
            $checkClass = $db->query(
                "SELECT id FROM attendance_records 
                 WHERE user_id = ? AND schedule_id = ? AND date = ?", 
                [$userId, $scheduleId, $today], 
                "iis"
            );

            if ($checkClass->get_result()->num_rows > 0) {
                $status = "Already Completed This Class";
                $isWarning = true;
            } else {
                // Calculate LATE Status
                $graceLimit = 15; // default 15 mins
                
                // Fetch Grace Period from settings if available
                $graceStmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'late_threshold_minutes'");
                if ($row = $graceStmt->get_result()->fetch_assoc()) {
                    $graceLimit = (int)$row['setting_value'];
                }

                $schedStartTs = strtotime("$today " . $currentClass['start_time']);
                $currentTs = strtotime("$today $now");
                $lateThreshold = $schedStartTs + ($graceLimit * 60);

                $timeInStatus = ($currentTs > $lateThreshold) ? "Late" : "Present";

                // INSERT RECORD
                $db->query(
                    "INSERT INTO attendance_records (user_id, schedule_id, date, time_in, status) 
                     VALUES (?, ?, ?, ?, ?)", 
                    [$userId, $scheduleId, $today, $now, $timeInStatus], 
                    "iisss"
                );
                $status = "Time In - " . $timeInStatus;
            }

        } else {
            // === FALLBACK: No Class Scheduled Right Now ===
            // Option 1: Reject scan
            // Option 2: Allow "Generic" Time In (useful for employees/staff without fixed classes)
            
            // For students, we might want to say "No Class Scheduled"
            // But to be safe, let's allow a generic Time In but mark it 'Extra' or similar
            // Or just 'Present' with NULL schedule_id
            
             $db->query(
                "INSERT INTO attendance_records (user_id, date, time_in, status) 
                 VALUES (?, ?, ?, 'Present')", 
                [$userId, $today, $now], 
                "iss"
            );
            $status = "Time In (No Schedule)";
        }
    }

    echo json_encode([
        'success' => true, 
        'message' => "Attendance processed", 
        'data' => [
            "type"   => "attendance",
            "name"   => $user['first_name'] . ' ' . $user['last_name'],
            "status" => $status,
            "time"   => date('h:i A'),
            "date"   => date('l, F j, Y'),
            "is_warning" => $isWarning
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "System error: " . $e->getMessage()]);
}
?>