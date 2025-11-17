<?php
require_once 'app/init.php';
require_once 'app/controllers/RegistrationController.php';

$controller = new RegistrationController();
$controller->enroll();
?>