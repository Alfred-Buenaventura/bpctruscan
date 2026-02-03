<?php
// 1. Secure Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Set Timezone
date_default_timezone_set('Asia/Manila');

// 3. Load Core Classes
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/controller.php';
require_once __DIR__ . '/core/helper.php';
require_once __DIR__ . '/core/mailer.php';

// 4. Load .env
Helper::loadEnv(__DIR__ . '/../.env');

// Define the Admin Backup Key from the .env file
$adminBypass = $_ENV['ADMIN_BYPASS_KEY'] ?? '';
define('ADMIN_BYPASS_KEY', $adminBypass);

$envKey = getenv('KIOSK_SECRET_KEY') ?: ($_ENV['KIOSK_SECRET_KEY'] ?? null);

if (isset($_ENV['KIOSK_SECRET_KEY'])) {
    define('KIOSK_SECRET_KEY', $_ENV['KIOSK_SECRET_KEY']);
} else {
    define('KIOSK_SECRET_KEY', ''); // Keep it empty if not found
}

if (!defined('API_ACCESS')) {
    define('API_ACCESS', false);
}

// Define the Base URL for the system to resolve paths correctly
define('BASE_URL', 'https://bpctruscan.com'); // Update this to match your actual project folder

// Load Models
require_once __DIR__ . '/models/user.php';
require_once __DIR__ . '/models/activitylog.php';
require_once __DIR__ . '/models/notification.php';
require_once __DIR__ . '/models/holiday.php';

// Global Helper Functions
if (!function_exists('clean')) {
    function clean($data) { return Helper::clean($data); }
}
if (!function_exists('isAdmin')) {
    function isAdmin() { return Helper::isAdmin(); }
}
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() { return Helper::isLoggedIn(); }
}
if (!function_exists('jsonResponse')) {
    function jsonResponse($s, $m, $d=null) { Helper::jsonResponse($s, $m, $d); }
}
if (!function_exists('sendEmail')) {
    function sendEmail($to, $sub, $msg) { return Mailer::send($to, $sub, $msg); }
}

// FIX: Use Singleton Instance
function db() {
    $database = Database::getInstance(); 
    return $database->conn;
}
?>