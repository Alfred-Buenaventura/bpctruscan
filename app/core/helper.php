<?php
class Helper {
    
    public static function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }

        if (!isset($_ENV)) {
            $_ENV = [];
        }

        // read the .env file line by line to pull in configuration settings
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line) || strpos($line, '#') === 0 || strpos($line, ';') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // strip out any quotes that might be surrounding the values in the file
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }

            if (function_exists('putenv')) {
                putenv(sprintf('%s=%s', $name, $value));
            }
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    public static function csrfInput() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $token = $_SESSION['csrf_token'] ?? '';
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    public static function clean($data) {
        // strip whitespace and convert special characters to stop xss attacks
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function jsonResponse($success, $message, $data = null) {
        // format the data into a standard json structure for ajax calls
        header('Content-Type: application/json');
        $response = ['success' => $success, 'message' => $message];
        if ($data) $response['data'] = $data;
        echo json_encode($response);
        exit;
    }
}
?>