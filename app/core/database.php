<?php
class Database {
    private static $instance = null;
    public $conn;

    // UPDATE YOUR CREDENTIALS HERE
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'bpc_attendance'; 

    private function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // --- THIS IS THE MISSING METHOD CAUSING YOUR ERROR ---
    public function query($sql, $params = [], $types = "") {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Query Prepare Failed: " . $this->conn->error);
        }

        if (!empty($params)) {
            // Check if types string is provided, otherwise generate it (s = string, i = int)
            if (empty($types)) {
                $types = str_repeat('s', count($params)); 
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
             throw new Exception("Query Execute Failed: " . $stmt->error);
        }

        return $stmt;
    }
}
?>