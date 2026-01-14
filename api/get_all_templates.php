<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../app/init.php';
ob_end_clean();

$db = Database::getInstance()->conn;

// MUST use the new table and column
$sql = "SELECT id, fingerprint_data FROM user_fingerprints";
$result = $db->query($sql);

$templates = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $templates[] = [
            'id' => (int)$row['id'],
            'fingerprint_template' => $row['fingerprint_data']
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => $templates,
    'count' => count($templates)
]);
?>