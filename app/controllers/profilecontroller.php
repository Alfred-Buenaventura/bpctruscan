<?php
require_once __DIR__ . '/../core/controller.php';

class ProfileController extends Controller {

    public function index() {
        $this->requireLogin();
        $userModel = $this->model('User');
        $logModel = $this->model('ActivityLog');
        $attendanceModel = $this->model('Attendance');
        $userId = $_SESSION['user_id'];

        // Secure Photo Upload Handling
        if (isset($_GET['action']) && $_GET['action'] === 'upload_photo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            if (!isset($_FILES['croppedImage'])) { 
                echo json_encode(['success' => false, 'message' => 'No image received.']); 
                exit; 
            }

            $file = $_FILES['croppedImage'];
            
            // 1. Size Validation (10MB max)
            if ($file['size'] > 10485760) { 
                echo json_encode(['success' => false, 'message' => 'File exceeds 10MB limit.']); 
                exit; 
            }

            // 2. Content Validation (Check if it's actually an image)
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowedMimes = ['image/png', 'image/jpeg', 'image/jpg'];

            if (!in_array($mimeType, $allowedMimes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG and PNG are allowed.']);
                exit;
            }

            // 3. Verify it is a valid image structure
            if (!getimagesize($file['tmp_name'])) {
                echo json_encode(['success' => false, 'message' => 'The uploaded file is not a valid image.']);
                exit;
            }

            $uploadDir = __DIR__ . '/../../public/uploads/profile_pics/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            // 4. Force a clean extension based on our validation, not user input
            $extension = ($mimeType === 'image/png') ? '.png' : '.jpg';
            $fileName = $userId . '_' . time() . $extension;
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                $dbPath = 'public/uploads/profile_pics/' . $fileName;
                $userModel->updateProfileImage($userId, $dbPath);
                $_SESSION['profile_image'] = $dbPath; 
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload failed on server.']);
            }
            exit;
        }

        

        // Profile updating logic
        $data = ['pageTitle' => 'My Profile', 'pageSubtitle' => 'View and edit your information', 'error' => '', 'success' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        // SECURED: Verify CSRF token before updating profile
            $this->verifyCsrfToken();
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $middleName = trim($_POST['middle_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $emailNotif = isset($_POST['email_notifications']) ? 1 : 0;
            $weeklySum = isset($_POST['weekly_summary']) ? 1 : 0;

            if ($userModel->updateProfile($userId, $firstName, $lastName, $middleName, $email, $phone, $emailNotif, $weeklySum)) {
                $_SESSION['full_name'] = $firstName . ' ' . $lastName;
                $_SESSION['first_name'] = $firstName;
                
                $this->setFlash('Profile updated successfully!', 'success', 'profile.php');
            } else {
                $this->setFlash('Failed to update profile settings.', 'error', 'profile.php');
            }
        }

        $data['user'] = $userModel->findById($userId);
        $_SESSION['profile_image'] = $data['user']['profile_image'];
        $data['activities'] = $logModel->getRecentLogs(10, $userId);
        $data['stats'] = $attendanceModel->getStats($userId);
        $this->view('profile_view', $data);
    }
}