<?php
require_once __DIR__ . '/../core/database.php';

class Notification {
    
    // FIX: Use Singleton directly in static method
    public static function create($userId, $message, $type = 'info') {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)";
        return $db->query($sql, [$userId, $message, $type], "iss");
    }
}
?>