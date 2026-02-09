<?php
require_once __DIR__ . '/../core/database.php';

class Notification {
    
    public static function create($userId, $message, $type = 'info') {
        // static helper to quickly insert alerts into a user's dashboard notifications
        $db = Database::getInstance();
        
        $sql = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)";
        return $db->query($sql, [$userId, $message, $type], "iss");
    }
}