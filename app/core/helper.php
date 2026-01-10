<?php
class Helper {
    
    
    public static function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }

        if (!isset($_ENV)) {
            $_ENV = [];
        }

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
            
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }

            // Populate Environment Variables
            if (function_exists('putenv')) {
                putenv(sprintf('%s=%s', $name, $value));
            }
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    public static function clean($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function jsonResponse($success, $message, $data = null) {
        header('Content-Type: application/json');
        $response = ['success' => $success, 'message' => $message];
        if ($data) $response['data'] = $data;
        echo json_encode($response);
        exit;
    }
}
?>