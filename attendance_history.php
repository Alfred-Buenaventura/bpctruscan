<?php
session_start();
require_once 'app/init.php';
require_once 'app/controllers/attendancecontroller.php';

$controller = new AttendanceController();

// IMPORTANT: This block catches the export request
if (isset($_GET['action']) && $_GET['action'] === 'exportExcel') {
    $controller->exportHistoryExcel();
} else {
    // Default to the standard history page
    $controller->history();
}
?>