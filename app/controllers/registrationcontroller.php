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
            if ($userModel->updateFingerprint($targetUserId, $_POST['fingerprint_data'])) {
                $logModel->log($_SESSION['user_id'], "Fingerprint Registration", "Completed for {$targetUser['faculty_id']}");
                header("Location: complete_registration.php?success=1");
                exit;
            }
        }

        $data = [
            'pageTitle' => "Fingerprint Registration",
            'targetUser' => $targetUser
        ];
        $this->view('registration_view', $data);
    }

    // 3. The Notification API (Updated with HTML Email)
    public function notify() {
        $this->requireAdmin();
        $userModel = $this->model('User');
        $notifModel = $this->model('Notification');
        $logModel = $this->model('ActivityLog');

        header('Content-Type: application/json');

        $pendingUsers = $userModel->getPendingUsers();
        $count = 0;
        $errors = 0;
        
        $message = "Action Required: Complete Your Fingerprint Registration";

        foreach ($pendingUsers as $user) {
            // Create Dashboard Notification
            $notifModel->create($user['id'], "Please visit the IT office to complete your fingerprint registration.", 'warning');

            $subject = "Action Required: Fingerprint Registration";
            
            // --- PROFESSIONAL HTML EMAIL TEMPLATE START ---
            $currentYear = date("Y");
            $emailBody = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                    .email-container { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
                    
                    /* Emerald Header */
                    .header { background-color: #059669; /* Emerald 600 */ padding: 30px 20px; text-align: center; }
                    .header h1 { margin: 0; color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: 0.5px; }
                    
                    /* Content Area */
                    .content { padding: 30px 25px; color: #374151; line-height: 1.6; }
                    .greeting { font-size: 18px; font-weight: 600; color: #059669; margin-top: 0; }
                    
                    /* Status Badge in Email */
                    .status-badge { 
                        display: inline-block; 
                        background-color: #fef3c7; /* Yellow 100 */
                        color: #b45309; /* Yellow 700 */
                        padding: 6px 12px; 
                        border-radius: 12px; 
                        font-size: 14px; 
                        font-weight: bold; 
                        margin: 10px 0;
                    }

                    /* Call to Action Button */
                    .btn-container { text-align: center; margin: 30px 0; }
                    .btn { 
                        background-color: #059669; 
                        color: #ffffff !important; 
                        text-decoration: none; 
                        padding: 12px 25px; 
                        border-radius: 6px; 
                        font-weight: 600; 
                        display: inline-block; 
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    .btn:hover { background-color: #047857; }

                    /* Footer */
                    .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='header'>
                        <h1>Registration Pending</h1>
                    </div>
                    <div class='content'>
                        <p class='greeting'>Hello, " . htmlspecialchars($user['first_name']) . "!</p>
                        
                        <p>We noticed that your account setup is incomplete. To finalize your access and enable attendance tracking, you are required to register your fingerprint.</p>
                        
                        <div style='text-align: center;'>
                            <span class='status-badge'>Status: Fingerprint Required</span>
                        </div>

                        <p><strong>Please visit the Registrar's Office or the IT Department at your earliest convenience to scan your fingerprint.</strong></p>
                        
                        <div class='btn-container'>
                            <a href='#' class='btn'>View Account Status</a>
                        </div>
                        
                        <p style='font-size: 0.9em; color: #9ca3af;'>If you have already done this, please disregard this automated message.</p>
                    </div>
                    <div class='footer'>
                        &copy; {$currentYear} Bulacan Polytechnic College. All rights reserved.
                    </div>
                </div>
            </body>
            </html>
            ";
            // --- PROFESSIONAL HTML EMAIL TEMPLATE END ---

            // Send Email using existing helper function
            // Assuming sendEmail returns true/false
            if (sendEmail($user['email'], $subject, $emailBody)) {
                $count++;
            } else {
                $errors++;
            }
        }

        if ($count > 0) {
            $logModel->log($_SESSION['user_id'], 'Sent Notifications', "Sent $count email reminders.");
            echo json_encode(['success' => true, 'message' => "Successfully sent $count email notifications." . ($errors > 0 ? " ($errors failed)" : "")]);
        } else {
            // Even if 0 emails sent (maybe all failed), we return a valid JSON response
            echo json_encode(['success' => false, 'message' => $errors > 0 ? "Failed to send emails." : "No pending users found."]);
        }
        exit;
    }
}
?>