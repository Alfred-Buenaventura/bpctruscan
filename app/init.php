<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,  // Prevents JavaScript from accessing the session cookie (stops XSS theft)
        'cookie_secure'   => true,  // ONLY sends the cookie over HTTPS (set to false ONLY for local testing without SSL)
        'cookie_samesite' => 'Lax', // Prevents the cookie from being sent on cross-site requests (mitigates CSRF)
        'use_only_cookies' => true, // Prevents session ID passing via URL
    ]);
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Global helper function for the CSRF input
if (!function_exists('csrf_field')) {
    function csrf_field() { Helper::csrfInput(); }
}

if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // Every 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
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

$apiSecret = $_ENV['API_SECRET_KEY'] ?? '';
define('API_SECRET_KEY', $apiSecret);

define('SESSION_TIMEOUT', 1800);

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

if (session_status() === PHP_SESSION_NONE) session_start();

// 2. Database connection must be ready
require_once 'core/database.php';
$db = Database::getInstance();

// 3. AUTO-LOGIN LOGIC (Add this right here)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    
    // Search for a user who has this specific token
    $res = $db->query("SELECT * FROM users WHERE remember_token = ? AND status = 'active'", [$token], "s");
    $user = $res->get_result()->fetch_assoc();

    if ($user) {
        // Re-establish the session exactly like the Login Controller does
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['profile_image'] = $user['profile_image'];
        $_SESSION['force_password_change'] = (int)$user['force_password_change'];
    } else {
        // Token in cookie doesn't match database? Delete the "bad" cookie.
        setcookie('remember_me', '', time() - 3600, '/');
    }
}

// FIX: Use Singleton Instance
function db() {
    return Database::getInstance();
}
?>