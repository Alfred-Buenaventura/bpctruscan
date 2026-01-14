<?php
session_start();
date_default_timezone_set('Asia/Manila');
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

// CORRECTED: Check 'user_fingerprints' table
$check = $db->query("SELECT user_id FROM user_fingerprints WHERE id = ?", [$scannedId], "i");
$row = $check->get_result()->fetch_assoc();

if ($row) {
    $userId = $row['user_id']; // It was a fingerprint scan
} else {
    $userId = $scannedId; // Fallback for manual entry
}

// Fetch User
$userStmt = $db->query("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId], "i");
$user = $userStmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => "User not found."]);
    exit;
}

// Record Attendance Logic
$today = date('Y-m-d');
$now = date('H:i:s');
$status = "";
$isWarning = false;

// Check for open session (Time In but no Time Out)
$openStmt = $db->query("SELECT id, time_in FROM attendance_records WHERE user_id = ? AND date = ? AND time_out IS NULL ORDER BY id DESC LIMIT 1", [$userId, $today], "is");
$openRecord = $openStmt->get_result()->fetch_assoc();

if ($openRecord) {
    $timeInTs = strtotime($openRecord['time_in']);
    $currentTs = strtotime("$today $now");
    
    // Prevent double punch (1 minute interval)
    if (($currentTs - $timeInTs) < 60) {
        $status = "Already Timed In";
        $isWarning = true;
    } else {
        $db->query("UPDATE attendance_records SET time_out = ? WHERE id = ?", [$now, $openRecord['id']], "si");
        $status = "Time Out";
    }
} else {
    $db->query("INSERT INTO attendance_records (user_id, date, time_in, status) VALUES (?, ?, ?, 'Present')", [$userId, $today, $now], "iss");
    $status = "Time In";
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