<?php
session_start(); // Start session to access user data
require_once __DIR__ . '/../app/init.php'; // loads the constants, .env file for the authorization and helper class

header('Content-Type: application/json');
$userId = $_GET['user_id'] ?? 0;
$startDate = $_GET['start_date'] ?? date('Y-m-01');

if (empty($userId)) {
    jsonResponse(false, 'No user ID provided.');
    exit;
}

try {
    $start = new DateTime($startDate);
    $month = $start->format('m');
    $year = $start->format('Y');
    
    $daysInMonth = (int)$start->format('t');
    $endDate = $start->format('Y-m-t');

    $db = db();

    //fetches existing records for the month
    $stmt = $db->prepare(
        "SELECT id, date, time_in, time_out, status, remarks 
         FROM attendance_records 
         WHERE user_id = ? AND date BETWEEN ? AND ?"
    );
    $stmt->bind_param("iss", $userId, $startDate, $endDate);
    $stmt->execute();
    $dbRecords = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // processes the records into a day keyed array for easy user lookup and acess
    $attendanceData = [];
    foreach ($dbRecords as $rec) {
        $dayOfMonth = (int)(new DateTime($rec['date']))->format('j');
        $attendanceData[$dayOfMonth] = $rec;
    }

    // 31 day arraw for the full month
    $fullMonthData = [];
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
        
        if (isset($attendanceData[$day])) {
            // fetches a record that exists for the day
            $rec = $attendanceData[$day];
            $fullMonthData[] = [
                'day' => $day,
                'date' => $rec['date'],
                'record_id' => $rec['id'],
                'time_in' => $rec['time_in'] ? date('H:i:s', strtotime($rec['time_in'])) : null,
                'time_out' => $rec['time_out'] ? date('H:i:s', strtotime($rec['time_out'])) : null,
                'status' => $rec['status'],
                'remarks' => $rec['remarks'],
                'exists' => true
            ];
        } else {
            // for the no records of the day
            $fullMonthData[] = [
                'day' => $day,
                'date' => $date,
                'record_id' => null,
                'time_in' => null,
                'time_out' => null,
                'status' => null,
                'remarks' => null,
                'exists' => false
            ];
        }
    }
    
    // fills in the rest of 31 days as disabled
    for ($day = $daysInMonth + 1; $day <= 31; $day++) {
         $fullMonthData[] = [
            'day' => $day,
            'date' => null,
            'record_id' => null,
            'time_in' => null,
            'time_out' => null,
            'status' => null,
            'remarks' => null,
            'exists' => false,
            'disabled' => true
        ];
    }

    jsonResponse(true, 'Data fetched', $fullMonthData);

} catch (Exception $e) {
    jsonResponse(false, 'An error occurred: ' . $e->getMessage());
}
?>