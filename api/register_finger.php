<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Add POST and OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); // Add allowed headers
header("Content-Type: application/json");
require_once __DIR__ . '/../app/models/user.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['template'])) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

$userModel = new User();
$userId = $data['user_id'];
$template = $data['template'];

// Capture the position from the Modal Dropdown
$position = isset($data['position']) ? $data['position'] : 'Unknown';

try {
    // records the fingerprint data to its table in the database
    $userModel->addFingerprint($userId, $template, $position);
    echo json_encode(["status" => "success", "message" => "$position enrolled successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
}
?>