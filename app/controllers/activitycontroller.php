<?php
require_once __DIR__ . '/../core/controller.php';

class ActivityController extends Controller {
    
    // Replace the index() method in app/controllers/activitycontroller.php

public function index() {
    $this->requireLogin(); // Changed from requireAdmin() to allow staff

    $logModel = $this->model('ActivityLog');
    $isAdmin = ($_SESSION['role'] === 'Admin');
    $userId = $isAdmin ? null : $_SESSION['user_id']; // Only filter if NOT admin

    // Pagination Logic
    $limit = 15;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    $totalLogs = $logModel->getTotalCount($userId);
    $logs = $logModel->getPaginated($limit, $offset, $userId);

    $data = [
        'pageTitle' => 'Activity Logs',
        'pageSubtitle' => $isAdmin ? 'Monitoring system-wide user actions' : 'Reviewing your account activity',
        'logs' => $logs,
        'page' => $page, // <--- Change 'currentPage' to 'page'
        'totalPages' => ceil($totalLogs / $limit),
        'isAdmin' => $isAdmin
    ];

    $this->view('activity_log_view', $data);
}
}
?>