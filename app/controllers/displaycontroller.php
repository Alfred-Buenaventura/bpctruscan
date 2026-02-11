<?php
require_once __DIR__ . '/../core/controller.php';

class DisplayController extends Controller {

    /**
     * The main entry point for the Kiosk/Display page.
     * This now enforces the KIOSK_SECRET_KEY check.
     */
    public function index() {
        // 1. Get the key from the URL (e.g., display.php?key=YOUR_KEY)
        $accessKey = $_GET['key'] ?? '';
        
        // 2. Verify the key against the constant defined in init.php
        // We use hash_equals to prevent timing attacks
        if (empty(KIOSK_SECRET_KEY) || !hash_equals(KIOSK_SECRET_KEY, $accessKey)) {
            http_response_code(403);
            die("Unauthorized Access: A valid Kiosk key is required to view this page.");
        }

        // 3. If valid, load the display view
        $this->view('display_view');
    }
    
    public function markRead() {
        header('Content-Type: application/json');
        
        // Ensure only logged-in users can mark notifications as read
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success' => false, 'message' => 'Unauthorized']); 
            exit; 
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $notifId = $data['notification_id'] ?? null;

        if ($notifId) {
            $db = Database::getInstance();
            $db->query(
                "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", 
                [$notifId, $_SESSION['user_id']], 
                "ii"
            );
        }
        
        echo json_encode(['success' => true]);
        exit;
    }
}
?>