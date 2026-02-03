<?php
require_once __DIR__ . '/../core/controller.php';

class ProfileController extends Controller {

    public function index() {
        $this->requireLogin();
        $userModel = $this->model('User');
        $logModel = $this->model('ActivityLog');
        $attendanceModel = $this->model('Attendance');
        $userId = $_SESSION['user_id'];

        // ajax chopper css for handling the photo image editing/dragging
        if (isset($_GET['action']) && $_GET['action'] === 'upload_photo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            if (!isset($_FILES['croppedImage'])) { 
                echo json_encode(['success'=>false, 'message'=>'No image received.']); 
                exit; 
            }

            $file = $_FILES['croppedImage'];
            // image/profile upload sets limit, 10mb max
            if ($file['size'] > 10485760) { 
                echo json_encode(['success'=>false, 'message'=>'File exceeds 10MB limit.']); 
                exit; 
            }

            $uploadDir = __DIR__ . '/../../public/uploads/profile_pics/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileName = $userId . '_' . time() . '.png';
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                $dbPath = 'public/uploads/profile_pics/' . $fileName;
                $userModel->updateProfileImage($userId, $dbPath);
                $_SESSION['profile_image'] = $dbPath; 
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload failed.']);
            }
            exit;
        }

        // profile updating
        $data = ['pageTitle' => 'My Profile', 'pageSubtitle' => 'View and edit your information', 'error' => '', 'success' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
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
?>