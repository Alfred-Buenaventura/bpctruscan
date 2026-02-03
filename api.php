<?php
//entry point for AJAX and API requests of the system
require_once 'app/init.php';

// loads all the necessary controllers of the system
require_once 'app/controllers/apicontroller.php';
require_once 'app/controllers/attendancecontroller.php';
require_once 'app/controllers/registrationcontroller.php';
require_once 'app/controllers/displaycontroller.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    // loads the attendance display module (no login required for this function)
    case 'record_attendance':
        (new ApiController())->recordAttendance();
        break;
        
    case 'get_templates':
        (new ApiController())->getFingerprintTemplates();
        break;
    
    case 'mark_notification_read':
        (new ApiController())->markNotificationRead();
        break;
    
    case 'mark_all_notifications_read':
        (new ApiController())->markAllNotificationsRead();
        break;
        
    case 'notify_pending_users':
        (new RegistrationController())->notify();
        break;

    case 'get_staff_report':
        (new RegistrationController())->getStaffReport();
        break;

    case 'get_attendance_summary':
        (new AttendanceController())->getAttendanceSummary();
        break;

    case 'submit_attendance_feedback':
        (new AttendanceController())->submitFeedback();
        break;

    default:
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Invalid API Action']);
        break;
}