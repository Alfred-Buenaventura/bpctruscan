<?php
session_start();
require_once 'app/init.php';
require_once 'app/controllers/accountcontroller.php';

$controller = new AccountController();
$controller->index();
?>