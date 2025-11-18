<?php
require_once __DIR__ . '/../core/controller.php';
require_once __DIR__ . '/../models/schedule.php';
require_once __DIR__ . '/../models/user.php';

class ScheduleController extends Controller {

    public function index() {
        $this->requireLogin();
        
        $scheduleModel = new Schedule();
        $userModel = new User();
        $data = [
            'isAdmin' => Helper::isAdmin(),
            'error' => '',
            'success' => '',
            'activeTab' => $_GET['tab'] ?? 'manage',
            'filters' => ['user_id' => $_GET['user_id'] ?? null],
            'users' => [],
            'approvedSchedules' => [],
            'groupedApprovedSchedules' => [],
            'pendingSchedules' => [],
            'stats' => [],
            'selectedUserId' => $_GET['user_id'] ?? null,
            'selectedUserInfo' => null,
            'userStats' => null
        ];

        $adminId = $_SESSION['user_id'];
        $logModel = $this->model('ActivityLog');

        // --- POST ACTIONS ---
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // Handle Add Schedule
                if (isset($_POST['add_schedule'])) {
                    $userId = $_POST['user_id'] ?? $_SESSION['user_id'];
                    $schedules = [];
                    for ($i = 0; $i < count($_POST['day_of_week']); $i++) {
                        $schedules[] = [
                            'day' => $_POST['day_of_week'][$i],
                            'subject' => $_POST['subject'][$i],
                            'start' => $_POST['start_time'][$i],
                            'end' => $_POST['end_time'][$i],
                            'room' => $_POST['room'][$i],
                        ];
                    }
                    if ($scheduleModel->create($userId, $schedules, $data['isAdmin'])) {
                        $data['success'] = "Schedule(s) submitted for approval.";
                        if (!$data['isAdmin']) {
                            $logModel->log($userId, 'Schedule Submitted', count($schedules) . ' schedule(s) added');
                        }
                    } else {
                        $data['error'] = "Failed to add schedules.";
                    }
                }
                
                // Handle Edit Schedule
                elseif (isset($_POST['edit_schedule'])) {
                    $this->requireAdmin();
                    $scheduleId = $_POST['schedule_id_edit'];
                    $userId = $_POST['user_id_edit'];
                    $day = $_POST['day_of_week_edit'];
                    $subject = $_POST['subject_edit'];
                    $start = $_POST['start_time_edit'];
                    $end = $_POST['end_time_edit'];
                    $room = $_POST['room_edit'];

                    if ($scheduleModel->update($scheduleId, $day, $subject, $start, $end, $room)) {
                        $data['success'] = "Schedule updated successfully.";
                        $logModel->log($adminId, 'Schedule Edited', "Edited schedule ID $scheduleId for user $userId");
                    } else {
                        $data['error'] = "Failed to update schedule.";
                    }
                }

                // Handle Delete Schedule
                elseif (isset($_POST['delete_schedule'])) {
                    $scheduleId = $_POST['schedule_id_delete'];
                    $userId = $_POST['user_id_delete'];
                    if ($scheduleModel->delete($scheduleId, $userId, $data['isAdmin'])) {
                        $data['success'] = "Schedule deleted.";
                        $logModel->log($adminId, 'Schedule Deleted', "Deleted schedule ID $scheduleId for user $userId");
                    } else {
                        $data['error'] = "Failed to delete schedule.";
                    }
                }
                
                // Handle Single Schedule Approval
                elseif (isset($_POST['approve_schedule'])) {
                    $this->requireAdmin();
                    $scheduleId = $_POST['schedule_id'];
                    $userId = $_POST['user_id'];
                    if ($scheduleModel->updateStatus($scheduleId, 'approved')) {
                        $data['success'] = "Schedule approved successfully.";
                        $data['activeTab'] = 'pending'; // Stay on pending tab
                        $logModel->log($adminId, 'Schedule Approved', "Approved schedule ID $scheduleId for user $userId");
                    } else {
                        $data['error'] = "Failed to approve schedule.";
                        $data['activeTab'] = 'pending';
                    }
                }
                
                // Handle Single Schedule Decline
                elseif (isset($_POST['decline_schedule'])) {
                    $this->requireAdmin();
                    $scheduleId = $_POST['schedule_id'];
                    $userId = $_POST['user_id'];
                    if ($scheduleModel->delete($scheduleId, $userId, true)) {
                        $data['success'] = "Schedule declined and removed.";
                        $data['activeTab'] = 'pending'; // Stay on pending tab
                        $logModel->log($adminId, 'Schedule Declined', "Declined schedule ID $scheduleId for user $userId");
                    } else {
                        $data['error'] = "Failed to decline schedule.";
                        $data['activeTab'] = 'pending';
                    }
                }
                
                // Handle Bulk Approve All for a User
                elseif (isset($_POST['approve_all_user'])) {
                    $this->requireAdmin();
                    $scheduleIds = $_POST['schedule_ids'] ?? [];
                    $userId = $_POST['user_id'];
                    $approvedCount = 0;
                    
                    foreach ($scheduleIds as $scheduleId) {
                        if ($scheduleModel->updateStatus($scheduleId, 'approved')) {
                            $approvedCount++;
                        }
                    }
                    
                    if ($approvedCount > 0) {
                        $data['success'] = "Successfully approved $approvedCount schedule(s).";
                        $data['activeTab'] = 'pending'; // Stay on pending tab
                        $logModel->log($adminId, 'Bulk Schedule Approval', "Approved $approvedCount schedules for user ID $userId");
                    } else {
                        $data['error'] = "Failed to approve schedules.";
                        $data['activeTab'] = 'pending';
                    }
                }
                
                // Handle Bulk Decline All for a User
                elseif (isset($_POST['decline_all_user'])) {
                    $this->requireAdmin();
                    $scheduleIds = $_POST['schedule_ids'] ?? [];
                    $userId = $_POST['user_id'];
                    $declinedCount = 0;
                    
                    foreach ($scheduleIds as $scheduleId) {
                        if ($scheduleModel->delete($scheduleId, $userId, true)) {
                            $declinedCount++;
                        }
                    }
                    
                    if ($declinedCount > 0) {
                        $data['success'] = "Successfully declined $declinedCount schedule(s).";
                        $data['activeTab'] = 'pending'; // Stay on pending tab
                        $logModel->log($adminId, 'Bulk Schedule Decline', "Declined $declinedCount schedules for user ID $userId");
                    } else {
                        $data['error'] = "Failed to decline schedules.";
                        $data['activeTab'] = 'pending';
                    }
                }
            }
        } catch (Exception $e) {
            $data['error'] = 'System error: ' . $e->getMessage();
            error_log("Schedule Controller Error: " . $e->getMessage());
        }

        // --- GET DATA FOR VIEW ---
        if ($data['isAdmin']) {
            $data['users'] = $userModel->getAllStaff();
            $data['pendingSchedules'] = $scheduleModel->getAllByStatus('pending');
            $data['stats'] = $scheduleModel->getAdminStats();
            
            if (!empty($data['selectedUserId'])) {
                $data['approvedSchedules'] = $scheduleModel->getByUser($data['selectedUserId'], 'approved');
                $data['selectedUserInfo'] = $userModel->findById($data['selectedUserId']);
                $data['userStats'] = $scheduleModel->getUserStats($data['selectedUserId']);
            } else {
                $data['groupedApprovedSchedules'] = $scheduleModel->getAllApprovedGroupedByUser();
            }
        } else {
            $userId = $_SESSION['user_id'];
            $data['approvedSchedules'] = $scheduleModel->getByUser($userId, 'approved');
            $data['pendingSchedules'] = $scheduleModel->getByUser($userId, 'pending');
            $data['userStats'] = $scheduleModel->getUserStats($userId);
            $data['selectedUserInfo'] = $userModel->findById($userId);
        }

        $this->view('schedule_view', $data);
    }
}
?>