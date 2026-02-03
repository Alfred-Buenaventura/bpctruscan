<?php
require_once __DIR__ . '/../core/controller.php';

class RegistrationController extends Controller {

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

    // fingerprint registration process
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
            'pageSubtitle' => "Fingerprint Registration Process", 
            'targetUser' => $targetUser,
            'registeredFingers' => $registeredFingers
        ];
        
        $this->view('registration_view', $data);
    }

     // AJAX endpoint to fetch data for the staff report modal
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

    //notification api for pending fingerprint registration
    public function notify() {
        $this->requireAdmin();
        $userModel = $this->model('User');
        $notifModel = $this->model('Notification');
        $logModel = $this->model('ActivityLog');

        header('Content-Type: application/json');

        $pendingUsers = $userModel->getPendingUsers();
        $count = 0;
        
        foreach ($pendingUsers as $user) {
            $notifModel->create($user['id'], "Action Required: Please complete your fingerprint registration.", 'warning');
            
            $subject = "Action Required: Fingerprint Enrollment";
            $currentYear = date("Y");
            $emailBody = "
            <!DOCTYPE html>
            <html>
            <body style='font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;'>
                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td align='center' style='padding: 20px 0;'>
                            <table width='600' border='0' cellspacing='0' cellpadding='0' style='background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                                <tr>
                                    <td style='background-color: #10b981; padding: 40px 20px; text-align: center;'>
                                        <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>Biometric Registration</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding: 40px 30px;'>
                                        <h2 style='color: #1e293b; margin-top: 0;'>Hello, " . htmlspecialchars($user['first_name']) . "!</h2>
                                        <p style='color: #475569; line-height: 1.6; font-size: 16px;'>
                                            Your account has been created, but your <strong>fingerprint enrollment</strong> is still pending. To finalize your biometric attendance access, please visit the Admin's Office as soon as possible.
                                        </p>
                                        <div style='background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 15px; margin: 25px 0;'>
                                            <p style='color: #166534; margin: 0; font-weight: bold;'>Location: Admin's Office</p>
                                            <p style='color: #166534; margin: 5px 0 0 0;'>Please bring your Faculty ID for verification.</p>
                                        </div>
                                        <p style='color: #64748b; font-size: 14px;'>If you have already visited the office today, please disregard this message as the system may still be updating.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;'>
                                        <p style='color: #94a3b8; font-size: 12px; margin: 0;'>&copy; {$currentYear} Bulacan Polytechnic College - TruScan System</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>";

            if (sendEmail($user['email'], $subject, $emailBody)) {
                $count++;
            }
        }

        $logModel->log($_SESSION['user_id'], 'Sent Reminders', "Sent $count biometric enrollment reminders.");
        echo json_encode(['success' => true, 'message' => "Successfully sent $count email reminders."]);
        exit;
    }
}