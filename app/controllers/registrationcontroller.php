<?php
require_once __DIR__ . '/../core/controller.php';

class RegistrationController extends Controller {

    // 1. The List View
    public function index() {
        $this->requireAdmin();
        $userModel = $this->model('User');
        
        $data = [
            'pageTitle' => "Complete Registration",
            'pageSubtitle' => "Manage user fingerprint registration status.",
            'totalUsers' => $userModel->countActive(),
            'registeredUsersCount' => $userModel->countActive() - $userModel->countPendingFingerprint(),
            'pendingCount' => $userModel->countPendingFingerprint(),
            'pendingUsers' => $userModel->getPendingUsers(),
            'registeredUserList' => $userModel->getRegisteredUsers()
        ];

        $this->view('registration_list_view', $data);
    }

    // 2. The Enrollment Logic
    public function enroll() {
        $this->requireAdmin();
        $userModel = $this->model('User');
        $logModel = $this->model('ActivityLog');

        $targetUserId = $_GET['user_id'] ?? 0;
        $targetUser = $userModel->findById($targetUserId);
        
        if (!$targetUser) {
            header("Location: complete_registration.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fingerprint_data'])) {
            $fingerName = $_POST['finger_name'] ?? 'Unknown Finger';
            $fingerData = $_POST['fingerprint_data'];

            if ($userModel->addFingerprint($targetUserId, $fingerData, $fingerName)) {
                $logModel->log($_SESSION['user_id'], "Fingerprint Registration", "Registered $fingerName for {$targetUser['faculty_id']}");
                header("Location: fingerprint_registration.php?user_id=$targetUserId&success=1&finger=" . urlencode($fingerName));
                exit;
            }
        }

        $registeredFingers = $userModel->getRegisteredFingers($targetUserId);

        $data = [
            'pageTitle' => "Fingerprint Registration",
            // FIX: Explicitly set subtitle to prevent "Welcome back!" default
            'pageSubtitle' => "Fingerprint Registration Process", 
            'targetUser' => $targetUser,
            'registeredFingers' => $registeredFingers
        ];
        
        $this->view('registration_view', $data);
    }

    /**
     * AJAX endpoint to fetch data for the staff report modal
     */
    public function getStaffReport() {
        $this->requireAdmin();
        $userModel = $this->model('User');
        $filter = $_GET['filter'] ?? 'all';
        
        $users = [];
        if ($filter === 'registered') {
            $users = $userModel->getRegisteredUsers();
        } elseif ($filter === 'pending') {
            $users = $userModel->getPendingUsers();
        } else {
            $users = array_merge($userModel->getRegisteredUsers(), $userModel->getPendingUsers());
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    // 3. The Notification API
    public function notify() {
        $this->requireAdmin();
        $userModel = $this->model('User');
        $notifModel = $this->model('Notification');
        $logModel = $this->model('ActivityLog');

        header('Content-Type: application/json');

        $pendingUsers = $userModel->getPendingUsers();
        $count = 0;
        
        foreach ($pendingUsers as $user) {
            $notifModel->create($user['id'], "Please visit the IT office to complete your fingerprint registration.", 'warning');
            
            $subject = "Action Required: Fingerprint Registration";
            $currentYear = date("Y");
            $emailBody = "
            <!DOCTYPE html>
            <html>
            <body style='font-family: sans-serif;'>
                <div style='max-width: 600px; margin: 20px auto; border: 1px solid #ddd; padding: 20px;'>
                    <h1 style='color: #059669;'>Registration Pending</h1>
                    <p>Hello, " . htmlspecialchars($user['first_name']) . "!</p>
                    <p>To finalize your access, you are required to register your fingerprint.</p>
                    <p><strong>Please visit the Registrar's Office to scan your fingerprint.</strong></p>
                    <hr>
                    <footer style='font-size: 12px; color: #666;'>&copy; {$currentYear} Bulacan Polytechnic College.</footer>
                </div>
            </body>
            </html>";

            if (sendEmail($user['email'], $subject, $emailBody)) {
                $count++;
            }
        }

        $logModel->log($_SESSION['user_id'], 'Sent Notifications', "Sent $count email reminders.");
        echo json_encode(['success' => true, 'message' => "Successfully sent $count notifications."]);
        exit;
    }
}