<?php
// 1. Load the core system
require_once 'app/init.php';

// 2. Immediate Security Check
$accessKey = $_GET['key'] ?? '';
$expectedKey = defined('KIOSK_SECRET_KEY') ? KIOSK_SECRET_KEY : '';

// If the key is empty OR doesn't match, STOP IMMEDIATELY
if (empty($expectedKey) || $accessKey !== $expectedKey) {
    http_response_code(403);
    die("<h1>403 Forbidden</h1>Unauthorized Access: Kiosk key is required.");
}

// 3. If secure, load the Controller and the View
$controller = new Controller();
$controller->view('display_view');
?>