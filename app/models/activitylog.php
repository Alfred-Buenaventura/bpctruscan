<?php
require_once __DIR__ . '/../core/database.php';

class ActivityLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function log($userId, $action, $details = '') {
        // saves a new event to the audit trail including the user's ip address
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; 
        $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$userId, $action, $details, $ip], "isss");
    }

    public function getRecentLogs($limit = 5, $userId = null) {
        // pulls a specific number of the most recent actions for the dashboard feed
        $sql = "SELECT al.*, u.first_name, u.last_name 
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.id";
        
        if ($userId) {
            $sql .= " WHERE al.user_id = ? ORDER BY al.created_at DESC LIMIT ?";
            $stmt = $this->db->query($sql, [$userId, $limit], "ii");
        } else {
            $sql .= " ORDER BY al.created_at DESC LIMIT ?";
            $stmt = $this->db->query($sql, [$limit], "i");
        }
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalCount($userId = null) {
        // calculates total entries to determine pagination needs
        if ($userId) {
            $res = $this->db->query("SELECT COUNT(*) as total FROM activity_logs WHERE user_id = ?", [$userId], "i");
        } else {
            $res = $this->db->query("SELECT COUNT(*) as total FROM activity_logs");
        }
        return $res->get_result()->fetch_assoc()['total'] ?? 0;
    }

    public function getPaginated($limit, $offset, $userId = null) {
        // fetches log records in chunks to support the system's numbered page list
        $sql = "SELECT al.*, u.first_name, u.last_name 
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.id";
        
        if ($userId) {
            $sql .= " WHERE al.user_id = ? ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
            return $this->db->query($sql, [$userId, $limit, $offset], "iii")->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
            return $this->db->query($sql, [$limit, $offset], "ii")->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
}