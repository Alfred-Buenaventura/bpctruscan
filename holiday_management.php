<?php
require_once __DIR__ . '/app/init.php';
require_once __DIR__ . '/app/controllers/holidaycontroller.php';
$controller = new HolidayController();
$controller->index();
?>