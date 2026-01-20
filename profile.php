<?php
require_once 'app/init.php';
require_once 'app/controllers/profilecontroller.php';
$dashboard = new ProfileController();
$dashboard->index();
?>