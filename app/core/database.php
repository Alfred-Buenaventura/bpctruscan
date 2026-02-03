<?php
class Database {
    private static $instance = null;
    public $conn;

    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'bpc_attendance'; 

    private function __construct() {
        // start up the connection to the mysql server
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }

    public static function getInstance() {
        // we use a singleton here so we don't open a thousand connections at once
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
            // if we didn't specify types, we just assume everything is a string by default
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