<?php
require_once __DIR__ . '/app/init.php';
require_once 'app/controllers/dashboardcontroller.php';
$dashboard = new DashboardController();
$dashboard->index();
?>