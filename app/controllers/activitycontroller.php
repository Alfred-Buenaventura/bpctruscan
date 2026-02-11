<?php
require_once __DIR__ . '/../core/controller.php';

class ActivityController extends Controller {
//this function is mostly for the activity logs in the dashboard of users and admin
public function index() {
    $this->requireLogin();

    $logModel = $this->model('activitylog');
    $isAdmin = ($_SESSION['role'] === 'Admin');
    $userId = $isAdmin ? null : $_SESSION['user_id'];
    $limit = 15;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    $totalLogs = $logModel->getTotalCount($userId);
    $logs = $logModel->getPaginated($limit, $offset, $userId);

    $data = [
        'pageTitle' => 'Activity Logs',
        'pageSubtitle' => $isAdmin ? 'Monitoring system-wide user actions' : 'Reviewing your account activity',
        'logs' => $logs,
        'page' => $page,
        'totalPages' => ceil($totalLogs / $limit),
        'isAdmin' => $isAdmin
    ];

    $this->view('activity_log_view', $data);
}
}
?>