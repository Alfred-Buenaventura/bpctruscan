<?php
// 1. Set CORS headers at the absolute top
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// 2. Define API_ACCESS to bypass potential CSRF/Token checks in init.php
define('API_ACCESS', true);

session_start();
date_default_timezone_set('Asia/Manila');

// 3. Handle pre-flight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 4. Load the framework
require_once __DIR__ . '/../app/init.php';
header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput);

if (!$data || !isset($data->user_id)) {
    echo json_encode(['success' => false, 'message' => "Invalid input."]);
    exit;
}

$db = Database::getInstance(); 
$scannedId = (int)$data->user_id; 

$isSynced = isset($data->timestamp) && !empty($data->timestamp);
$effectiveTime = $isSynced ? strtotime($data->timestamp) : time();

$today = date('Y-m-d', $effectiveTime);
$now = date('H:i:s', $effectiveTime);
$dayOfWeek = date('l', $effectiveTime);

// Map template ID to User ID
$check = $db->query("SELECT user_id FROM user_fingerprints WHERE id = ?", [$scannedId], "i");
$row = $check->get_result()->fetch_assoc();
$userId = $row ? $row['user_id'] : $scannedId;

$userStmt = $db->query("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId], "i");
$user = $userStmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => "User not found."]);
    exit;
}

// Find applicable schedule
$schedQuery = "SELECT id FROM class_schedules 
               WHERE user_id = ? AND day_of_week = ? AND status = 'approved' 
               AND (ABS(TIMESTAMPDIFF(MINUTE, start_time, ?)) <= 60 
                    OR (start_time <= ? AND end_time >= ?))
               LIMIT 1";
$schedStmt = $db->query($schedQuery, [$userId, $dayOfWeek, $now, $now, $now], "issss");
$currentSched = $schedStmt->get_result()->fetch_assoc();
$scheduleId = $currentSched ? $currentSched['id'] : null;

$status = "";
$isWarning = false;

// 1. Fetch the absolute latest record for this user today (open or closed)
$lastRecordStmt = $db->query("SELECT * FROM attendance_records WHERE user_id = ? AND date = ? ORDER BY id DESC LIMIT 1", [$userId, $today], "is");
$lastRecord = $lastRecordStmt->get_result()->fetch_assoc();

if ($lastRecord) {
    // 2. Case: User is currently "Timed In" (waiting to Time Out)
    if ($lastRecord['time_out'] === null) {
        $timeInTs = strtotime($lastRecord['time_in']);
        
        if (($effectiveTime - $timeInTs) < 60) {
            $status = "Already Timed In!";
            $isWarning = true;
        } else {
            $db->query("UPDATE attendance_records SET time_out = ? WHERE id = ?", [$now, $lastRecord['id']], "si");
            $status = "Time Out";
        }
    } 
    // 3. Case: User is currently "Timed Out" (waiting for next Time In)
    else {
        $timeOutTs = strtotime($lastRecord['time_out']);
        
        if (($effectiveTime - $timeOutTs) < 60) {
            $status = "Already Timed Out!";
            $isWarning = true;
        } else {
            $db->query("INSERT INTO attendance_records (user_id, date, time_in, schedule_id, status) VALUES (?, ?, ?, ?, 'Present')", 
                       [$userId, $today, $now, $scheduleId], "issi");
            $status = "Time In";
        }
    }
} else {
    // 4. No records exist for today yet
    $db->query("INSERT INTO attendance_records (user_id, date, time_in, schedule_id, status) VALUES (?, ?, ?, ?, 'Present')", 
               [$userId, $today, $now, $scheduleId], "issi");
    $status = "Time In";
}

// email notification logic
if (!$isWarning && !empty($user['email']) && $user['email_notifications_enabled']) {
    $formattedTime = date('h:i A', $effectiveTime);
    $subject = "Attendance Notification: $status Recorded";
    
    $emailBody = "
    <html>
    <body style='font-family: Arial, sans-serif; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
            <div style='background: #059669; color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>BPC TruScan Attendance</h2>
            </div>
            <div style='padding: 30px;'>
                <p>Hello <strong>{$user['first_name']}</strong>,</p>
                <p>Your attendance has been recorded for today, <strong>" . date('M d, Y') . "</strong>.</p>
                <div style='background: #f3f4f6; padding: 15px; border-radius: 6px; text-align: center; margin: 20px 0;'>
                    <span style='font-size: 1.2rem; color: #1f2937;'>Status: <strong>$status</strong></span><br>
                    <span style='font-size: 1.5rem; color: #059669;'>Time: <strong>$formattedTime</strong></span>
                </div>
            </div>
            <div style='background: #f9fafb; padding: 15px; text-align: center; font-size: 0.75rem; color: #9ca3af;'>
                &copy; " . date('Y') . " Bulacan Polytechnic College
            </div>
        </div>
    </body>
    </html>";

    sendEmail($user['email'], $subject, $emailBody);
}

echo json_encode([
    'success' => true, 
    'message' => "Attendance processed", 
    'data' => [
        "name" => $user['first_name'] . ' ' . $user['last_name'],
        "status" => $status,
        "time" => date('h:i A'),
        "is_warning" => $isWarning
    ]
]);
?>