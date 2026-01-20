<?php
require_once __DIR__ . '/../core/database.php';

class Holiday {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance(); //
    }

    /**
     * Get all holidays with advanced filtering and chronological sorting.
     */
    public function getAll($filters = []) {
        $sql = "SELECT * FROM holidays WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filters['search'])) {
            $sql .= " AND description LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND holiday_date BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
            $types .= "ss";
        }

        // Sort by date ascending (January to December)
        $sql .= " ORDER BY holiday_date ASC";
        
        return $this->db->query($sql, $params, $types)->get_result()->fetch_all(MYSQLI_ASSOC); //
    }

    public function create($date, $desc, $type) {
        $sql = "INSERT INTO holidays (holiday_date, description, type) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$date, $desc, $type], "sss"); //
    }

    public function delete($id) {
        $sql = "DELETE FROM holidays WHERE id = ?";
        return $this->db->query($sql, [$id], "i"); //
    }

    public function getInRange($startDate, $endDate) {
        $sql = "SELECT holiday_date, description FROM holidays WHERE holiday_date BETWEEN ? AND ?";
        $res = $this->db->query($sql, [$startDate, $endDate], "ss")->get_result();
        $holidays = [];
        while($row = $res->fetch_assoc()){
            $holidays[$row['holiday_date']] = $row['description'];
        }
        return $holidays; //
    }
}
?>