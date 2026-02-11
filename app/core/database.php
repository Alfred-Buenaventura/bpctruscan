<?php
class Database {
    private static $instance = null;
    public $conn;

    // These properties will now be populated dynamically from your .env file
    private $host;
    private $user;
    private $pass;
    private $dbname;

    private function __construct() {
        // Assign values from environment variables loaded by Helper::loadEnv in init.php
        $this->host   = $_ENV['DB_HOST'] ?? 'localhost';
        $this->user   = $_ENV['DB_USER'] ?? 'root';
        $this->pass   = $_ENV['DB_PASS'] ?? '';
        $this->dbname = $_ENV['DB_NAME'] ?? 'bpc_attendance';

        // Start up the connection to the MySQL server
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8");
    }

    public static function getInstance() {
        // Singleton pattern to prevent opening multiple connections simultaneously
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function query($sql, $params = [], $types = "") {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Query Prepare Failed: " . $this->conn->error);
        }

        if (!empty($params)) {
            // Default to string 's' for each parameter if types are not specified
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