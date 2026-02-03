<?php
require_once __DIR__ . '/../core/controller.php';

class DashboardController extends Controller {

    public function index() {
        $this->requireLogin();

        $data = [];
        $userModel = $this->model('User');
        $logModel = $this->model('ActivityLog');
        $attModel = $this->model('Attendance');
        $data['pageTitle'] = 'Dashboard';

        if ($_SESSION['role'] === 'Admin') {
            $data['pageSubtitle'] = 'Welcome back, System Administrator!';
            $data['totalUsers'] = $userModel->countActive();
            $data['pendingRegistrations'] = $userModel->countPendingFingerprint();
            $data['activeToday'] = $attModel->countActiveToday();
            $data['activityLogs'] = $logModel->getRecentLogs(5);
            $data['isAdmin'] = true;
        } else {
    $firstName = $_SESSION['first_name'] ?? 'User';
    $data['pageSubtitle'] = "Welcome back, " . htmlspecialchars($firstName) . "!";
    $data['fingerprint_registered'] = $userModel->getFingerprintStatus($_SESSION['user_id']);
    $data['attendance'] = $attModel->getDailySummary($_SESSION['user_id']); 
    $data['activityLogs'] = $logModel->getRecentLogs(5, $_SESSION['user_id']);
    $data['stats'] = $attModel->getStats($_SESSION['user_id']); 
    $data['isAdmin'] = false;
}

        $this->view('dashboard_view', $data);
    }
}
?>