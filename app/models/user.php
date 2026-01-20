<?php
require_once __DIR__ . '/../core/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get detailed list for reports
     * @param string $filter 'all', 'registered', 'pending'
     */
    public function getDetailedReport($filter = 'all') {
        $sql = "SELECT first_name, last_name, faculty_id, role, email, status, fingerprint_registered 
                FROM users WHERE status = 'active'";
        
        if ($filter === 'registered') {
            $sql .= " AND fingerprint_registered = 1";
        } elseif ($filter === 'pending') {
            $sql .= " AND fingerprint_registered = 0";
        }

        $sql .= " ORDER BY role ASC, last_name ASC";
        
        $result = $this->db->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // =========================================================
    // 1. AUTHENTICATION & LOGIN
    // =========================================================
    public function findUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE (username = ? OR faculty_id = ?) AND status = 'active'";
        $stmt = $this->db->query($sql, [$username, $username], "ss");
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ? AND status = 'active'";
        $stmt = $this->db->query($sql, [$id], "i");
        return $stmt->get_result()->fetch_assoc();
    }

    // =========================================================
    // 2. FINGERPRINT MANAGEMENT
    // =========================================================
    
    public function getRegisteredFingers($userId) {
        $sql = "SELECT id, finger_name, created_at FROM user_fingerprints WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, [$userId], "i");
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addFingerprint($userId, $fingerprintData, $fingerName) {
        $check = $this->db->query("SELECT id FROM user_fingerprints WHERE user_id = ? AND finger_name = ?", [$userId, $fingerName], "is");
        
        if ($check->get_result()->num_rows > 0) {
            $sql = "UPDATE user_fingerprints SET fingerprint_data = ?, created_at = NOW() WHERE user_id = ? AND finger_name = ?";
            $this->db->query($sql, [$fingerprintData, $userId, $fingerName], "sis");
        } else {
            $sql = "INSERT INTO user_fingerprints (user_id, finger_name, fingerprint_data) VALUES (?, ?, ?)";
            $this->db->query($sql, [$userId, $fingerName, $fingerprintData], "iss");
        }

        $this->db->query("UPDATE users SET fingerprint_registered=1, fingerprint_registered_at=NOW() WHERE id=?", [$userId], "i");
        return true;
    }

    // =========================================================
    // 3. PROFILE & ACCOUNT UPDATES
    // =========================================================

    /**
     * UPDATED: Handles 8 parameters for sssssiii
     */
    public function updateProfile($id, $firstName, $lastName, $middleName, $email, $phone, $emailNotif, $weeklySum) {
        $sql = "UPDATE users SET 
                first_name=?, 
                last_name=?, 
                middle_name=?, 
                email=?, 
                phone=?, 
                email_notifications_enabled=?, 
                weekly_summary_enabled=? 
                WHERE id=?";
        
        return $this->db->query($sql, [
            $firstName, $lastName, $middleName, $email, $phone, 
            $emailNotif, $weeklySum, $id
        ], "sssssiii");
    }
    
    public function updateProfileImage($id, $imagePath) {
        $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
        return $this->db->query($sql, [$imagePath, $id], "si");
    }

    public function update($id, $firstName, $lastName, $middleName, $email, $phone) {
        $sql = "UPDATE users SET first_name=?, last_name=?, middle_name=?, email=?, phone=? WHERE id=?";
        $this->db->query($sql, [$firstName, $lastName, $middleName, $email, $phone, $id], "sssssi");
        return true;
    }

    public function setStatus($id, $status) {
        $sql = "UPDATE users SET status=? WHERE id=?";
        $this->db->query($sql, [$status, $id], "si");
        return true;
    }

    // =========================================================
    // 4. CREATION & DELETION
    // =========================================================
    public function exists($facultyId) {
        $sql = "SELECT id FROM users WHERE faculty_id = ?";
        $res = $this->db->query($sql, [$facultyId], "s");
        return $res->get_result()->num_rows > 0;
    }

    public function create($data) {
        $sql = "INSERT INTO users (faculty_id, username, password, first_name, last_name, middle_name, email, phone, role, force_password_change) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $this->db->query($sql, [
            $data['faculty_id'],
            $data['username'],
            $data['password'],
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'],
            $data['email'],
            $data['phone'],
            $data['role']
        ], "sssssssss");
        return $this->db->conn->insert_id;
    }

    public function delete($id) {
        $this->db->query("DELETE FROM class_schedules WHERE user_id=?", [$id], "i");
        $this->db->query("DELETE FROM attendance_records WHERE user_id=?", [$id], "i");
        $this->db->query("DELETE FROM notifications WHERE user_id=?", [$id], "i");
        $this->db->query("DELETE FROM activity_logs WHERE user_id=?", [$id], "i");
        $this->db->query("DELETE FROM user_fingerprints WHERE user_id=?", [$id], "i");
        
        $sql = "DELETE FROM users WHERE id=?";
        $this->db->query($sql, [$id], "i");
        return true;
    }

    // =========================================================
    // 5. DATA RETRIEVAL (Lists & Stats)
    // =========================================================
    public function getAllActive() {
        $result = $this->db->conn->query("SELECT * FROM users WHERE status='active' ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllArchived() {
        $result = $this->db->conn->query("SELECT * FROM users WHERE status='archived' ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllStaff() {
        $result = $this->db->conn->query("SELECT id, faculty_id, first_name, last_name FROM users WHERE status='active' AND role != 'Admin' ORDER BY first_name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPendingUsers() {
        $result = $this->db->conn->query("SELECT * FROM users WHERE status='active' AND fingerprint_registered=0 ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getRegisteredUsers() {
        $result = $this->db->conn->query("SELECT * FROM users WHERE status='active' AND fingerprint_registered=1 ORDER BY first_name ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getStats() {
        $sql = "SELECT
            COUNT(*) as total_active,
            SUM(CASE WHEN role != 'Admin' THEN 1 ELSE 0 END) as non_admin_active,
            SUM(CASE WHEN role = 'Admin' THEN 1 ELSE 0 END) as admin_active
        FROM users WHERE status = 'active'";
        return $this->db->conn->query($sql)->fetch_assoc();
    }

    public function countActive() {
        $res = $this->db->conn->query("SELECT COUNT(*) as c FROM users WHERE status='active'");
        return $res->fetch_assoc()['c'] ?? 0;
    }

    public function countPendingFingerprint() {
        $res = $this->db->conn->query("SELECT COUNT(*) as c FROM users WHERE status='active' AND fingerprint_registered=0");
        return $res->fetch_assoc()['c'] ?? 0;
    }

    public function getFingerprintStatus($userId) {
        $stmt = $this->db->query("SELECT fingerprint_registered FROM users WHERE id = ?", [$userId], "i");
        $row = $stmt->get_result()->fetch_assoc();
        return $row['fingerprint_registered'] ?? 0;
    }
}
?>