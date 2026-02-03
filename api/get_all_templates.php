<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
require_once __DIR__ . '/../app/init.php';
ob_end_clean();

$db = Database::getInstance()->conn;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit;
}

$sql = "SELECT id, fingerprint_data FROM user_fingerprints";
$result = $db->query($sql);

$templates = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $templates[] = [
    'id' => (int)$row['id'],
    'fingerprint_template' => $row['fingerprint_data'] // Must match the C# 'fingerprint_template' key
];
    }
}

echo json_encode([
    'success' => true,
    'data' => $templates,
    'count' => count($templates)
]);
?>