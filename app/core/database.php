<?php
class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    public $conn;

    public function __construct() {
        // Detect Environment
        if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
            // Localhost (XAMPP)
            $this->host = 'localhost';
            $this->user = 'root';
            $this->pass = '';
            $this->dbname = 'bpc_attendance';
        } else {
            // IONOS Live
            $this->host = 'db5019021856.hosting-data.io';
            $this->user = 'dbu226629';
            $this->pass = 'truscanpass.';
            $this->dbname = 'dbs14972169';
        }

        $this->connect();
    }

    private function connect() {
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->conn = @new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if ($this->conn->connect_error) {
            if ($_SERVER['SERVER_NAME'] === 'localhost') {
                die("Local DB Error: " . $this->conn->connect_error);
            } else {
                die("System Maintenance: Database Connection Error.");
            }
        }
    }

    public function query($sql, $params = [], $types = "") {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Query Prep Failed: " . $this->conn->error . " | SQL: " . $sql);
        }

        if (!empty($params)) {
            // Compatibility Fix: Use bind_param instead of execute($params)
            // This works on both older PHP (XAMPP) and new PHP (IONOS)
            if (!empty($types)) {
                // We use argument unpacking (...) to pass the array values
                $stmt->bind_param($types, ...$params);
            }
            $success = $stmt->execute();
        } else {
            $success = $stmt->execute();
        }

        if (!$success) {
            die("Query Execution Failed: " . $stmt->error);
        }

        return $stmt;
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
}
?>