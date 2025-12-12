<?php
require_once 'app/init.php';
require_once 'app/controllers/attendancecontroller.php';

// Route request to the Controller logic
$controller = new AttendanceController();
$controller->downloadDtrPdf();
?>