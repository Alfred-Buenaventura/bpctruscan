<?php
class Controller {
    
    public function view($viewName, $data = []) {
        extract($data);
        
        // this helps us find and pull in the right html file for the page
        $viewFile = __DIR__ . '/../views/' . strtolower($viewName) . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
             $viewFileOrig = __DIR__ . '/../views/' . $viewName . '.php';
             if (file_exists($viewFileOrig)) {
                 require_once $viewFileOrig;
             } else {
                 die("View does not exist: " . $viewName);
             }
        }
    }

    public function history() {
        $this->requireLogin(); 
        
        // we pull in the attendance model to look up specific records
        $attModel = $this->model('Attendance'); 
        
        $userId = $_SESSION['user_id'];
        
        $data = [
            'pageTitle' => 'Attendance History',
            'history'   => $attModel->getUserHistory($userId) 
        ];

        $this->view('attendance_history_view', $data);
    }

    public function model($modelName) {
        // we force lowercase here to make sure file names stay consistent on the server
        require_once __DIR__ . '/../models/' . strtolower($modelName) . '.php';
        return new $modelName();
    }

    public function requireLogin() {
        // kick the user back to the login page if they aren't authenticated
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
        
        $currentPage = basename($_SERVER['PHP_SELF']);
        // check if the user needs to update their password before they can do anything else
        if (isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] === 1) {
             if ($currentPage !== 'change_password.php' && $currentPage !== 'logout.php') {
                header('Location: change_password.php?first_login=1');
                exit;
            }
        }
    }

    public function setFlash($message, $type = 'success', $redirect = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        
        if ($redirect) {
            header("Location: " . BASE_URL . "/" . ltrim($redirect, '/'));
            exit;
        }
    }

    public function requireAdmin() {
        $this->requireLogin();
        // stop non-admin users from accessing sensitive management pages
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            die('Access Denied');
        }
    }

    public function kioskView() {
    // 1. Get key from URL
    $accessKey = $_GET['key'] ?? '';
    
    // 2. Check against the constant defined in init.php
    if (empty(KIOSK_SECRET_KEY) || $accessKey !== KIOSK_SECRET_KEY) {
        http_response_code(403);
        die("Unauthorized Access: Kiosk key required.");
    }

    // 3. If valid, load the view
    $this->view('display_view'); // This calls the view method from your file
}

    public function markAllNotificationsRead() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success'=>false, 'message' => 'Unauthorized']); 
            exit; 
        }
        
        // update the database to clear out the unread status for this specific user
        $db = Database::getInstance();
        $db->query(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", 
            [$_SESSION['user_id']], 
            "i"
        );
        
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
        exit;
    }
}
?>