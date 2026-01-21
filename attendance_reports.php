<?php
session_start();
require_once 'app/init.php';
require_once 'app/controllers/attendancecontroller.php';

$controller = new AttendanceController();
// Check if a specific action is requested via the URL
$action = $_GET['action'] ?? 'index';

if ($action === 'print_dtr') {
    $controller->printDtr(); // Calls the DTR preview logic
} else {
    $controller = new AttendanceController();
    $controller->index(); // Defaults to the dashboard
}
?>