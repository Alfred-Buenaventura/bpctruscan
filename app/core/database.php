<?php
class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    
    // Hold the single instance of the connection
    private static $instance = null;
    public $conn;

    // Private constructor prevents direct object creation
    private function __construct() {
        // Fallback to local defaults if env vars not set
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->pass = getenv('DB_PASS') ?: '';
        $this->dbname = getenv('DB_NAME') ?: 'bpc_attendance';

        $this->connect();
    }

    // Get the single instance of the Database
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    private function connect() {
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->conn = @new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if ($this->conn->connect_error) {
            error_log("Database Connection Error: " . $this->conn->connect_error);
            die("System Maintenance: Service temporarily unavailable.");
        }
        
        $this->conn->set_charset("utf8mb4");
    }

    public function query($sql, $params = [], $types = "") {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Query Prep Failed: " . $this->conn->error);
        }

        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat('s', count($params)); 
            }
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }
    
    private function __clone() {}
    public function __wakeup() {}
}
?>