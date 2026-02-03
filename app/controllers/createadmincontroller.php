<?php
require_once __DIR__ . '/../core/controller.php';
require_once __DIR__ . '/../core/mailer.php';

class AccountAdminController extends Controller {

    public function create() {
        $this->requireAdmin();
        $userModel = $this->model('User');
        $logModel = $this->model('ActivityLog');
        
        $data = [
            'pageTitle' => 'Admin Management', 
            'pageSubtitle' => 'Configure high-level system administrative access', 
            'error' => '', 
            'success' => ''
        ];

        $allActive = $userModel->getAllActive();
        $data['admins'] = array_filter($allActive, function($u) {
            return $u['role'] === 'Admin';
        });
        
        $data['stats'] = $userModel->getStats();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $defaultPass = "@adminpass123"; 
        $adminData = [
            'faculty_id' => clean($_POST['faculty_id']),
            'username' => strtolower(clean($_POST['faculty_id'])),
            'password' => password_hash($defaultPass, PASSWORD_DEFAULT),
            'first_name' => clean($_POST['first_name']),
            'last_name' => clean($_POST['last_name']),
            'email' => clean($_POST['email']),
            'role' => 'Admin'
        ];

        if ($userModel->exists($adminData['faculty_id'])) {
            $data['error'] = "Admin ID already exists.";
        } else if ($userModel->create($adminData)) {
            // Send Admin Creation Email
            $subject = "Administrative Access Granted - BPC TruScan";
            $emailBody = "
                <div style='font-family: Arial, sans-serif; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #10b981;'>Welcome, Administrator!</h2>
                    <p>Your admin account has been initialized for the BPC Attendance System.</p>
                    <p><strong>Username:</strong> {$adminData['username']}<br>
                    <strong>Temporary Password:</strong> @adminpass123</p>
                    <p style='color: #666; font-size: 12px;'>Please change your password upon your first login.</p>
                </div>";
            
            Mailer::send($adminData['email'], $subject, $emailBody); //

            $logModel->log($_SESSION['user_id'], 'Admin Created', "Added system administrator: {$adminData['faculty_id']}");
            header("Location: create_admin.php?success=1");
            exit();
        }
    }
    $this->view('admin_create_view', $data);
}
}