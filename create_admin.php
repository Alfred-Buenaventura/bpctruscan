<?php
session_start();
require_once 'app/init.php';
require_once 'app/controllers/createadmincontroller.php';
$controller = new AccountAdminController();
$controller->create();
?>