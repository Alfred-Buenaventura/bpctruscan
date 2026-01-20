<?php
require_once __DIR__ . '/app/init.php';
require_once __DIR__ . '/app/controllers/authcontroller.php';
$auth = new AuthController();
$auth->login();
?>