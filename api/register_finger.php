<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__ . '/../app/models/User.php';

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
    // Add to the 'user_fingerprints' table
    $userModel->addFingerprint($userId, $template, $position);
    echo json_encode(["status" => "success", "message" => "$position enrolled successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
}
?>