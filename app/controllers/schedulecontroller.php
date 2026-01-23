<?php
require_once __DIR__ . '/../core/controller.php';
require_once __DIR__ . '/../models/schedule.php';
require_once __DIR__ . '/../models/user.php';

class ScheduleController extends Controller {

    public function index() {
        $this->requireLogin();
        
        $scheduleModel = new Schedule();
        $userModel = new User();
        $notifModel = $this->model('Notification');
        $logModel = $this->model('ActivityLog');

        // --- AJAX: CHECK FOR CONFLICTS ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_conflict'])) {
            header('Content-Type: application/json');
            
            $checks = [];
            if (isset($_POST['schedules']) && is_array($_POST['schedules'])) {
                $checks = $_POST['schedules'];
            } else {
                $checks[] = [
                    'day' => $_POST['day'],
                    'start' => $_POST['start'],
                    'end' => $_POST['end'],
                    'room' => $_POST['room'],
                    'id' => $_POST['id'] ?? null
                ];
            }

            foreach ($checks as $check) {
                if (empty($check['room']) || empty($check['start']) || empty($check['end'])) continue;

                $conflict = $scheduleModel->checkOverlap(
                    $check['day'], 
                    $check['start'], 
                    $check['end'], 
                    $check['room'],
                    $check['id'] ?? null
                );

                if ($conflict) {
                    echo json_encode([
                        'has_conflict' => true,
                        'conflict_details' => $conflict,
                        'input_details' => $check
                    ]);
                    exit;
                }
            }

            echo json_encode(['has_conflict' => false]);
            exit;
        }

        $data = [
            'pageTitle' => 'Schedule Management', 
            'pageSubtitle' => 'Manage and monitor class schedules',
            'isAdmin' => Helper::isAdmin(),
            'error' => '',
            'success' => '',
            'activeTab' => $_GET['tab'] ?? 'manage',
            'searchQuery' => $_GET['search'] ?? '', 
            'filters' => ['user_id' => $_GET['user_id'] ?? null],
            'users' => [],
            'approvedSchedules' => [],
            'groupedApprovedSchedules' => [],
            'groupedPendingSchedules' => [], 
            'pendingSchedules' => [],
            'pendingCount' => 0,
            'stats' => [],
            'selectedUserId' => $_GET['user_id'] ?? null,
            'selectedUserInfo' => null,
            'userStats' => null,
            'allUsers' => [],
            'rooms' => $scheduleModel->getRooms()
        ];

        $adminId = $_SESSION['user_id'];

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // --- APPROVAL / DECLINE ACTIONS ---
                if (isset($_POST['approve_schedule']) || isset($_POST['decline_schedule']) || isset($_POST['bulk_action_type'])) {
                    $this->requireAdmin();
                    $itemsToProcess = [];
                    $action = ''; 

                    if (isset($_POST['bulk_action_type']) && !empty($_POST['bulk_action_type'])) {
                        $action = $_POST['bulk_action_type'];
                        $itemsToProcess = $_POST['selected_schedules'] ?? [];
                    } else {
                        $action = isset($_POST['approve_schedule']) ? 'approve' : 'decline';
                        if(isset($_POST['schedule_id'])) {
                            $itemsToProcess[] = $_POST['schedule_id'];
                        }
                    }

                    if (empty($itemsToProcess)) {
                        $data['error'] = "No schedules selected.";
                    } else {
                        $processedCount = 0;
                        $failedCount = 0;
                        $usersToNotify = [];

                        foreach ($itemsToProcess as $schedId) {
                            $schedInfo = $scheduleModel->findById($schedId);
                            if ($schedInfo) {
                                $success = false;

                                if ($action === 'approve') {
                                    // 1. SAFETY CHECK: Check overlap before approving
                                    $conflict = $scheduleModel->checkOverlap(
                                        $schedInfo['day_of_week'],
                                        $schedInfo['start_time'],
                                        $schedInfo['end_time'],
                                        $schedInfo['room'],
                                        $schedInfo['id']
                                    );

                                    if ($conflict) {
                                        $failedCount++;
                                        continue; 
                                    }

                                    // 2. APPROVE
                                    $success = $scheduleModel->updateStatus($schedId, 'approved');

                                    // 3. AUTO-DECLINE / CLEANUP LOGIC
                                    if ($success) {
                                        // Find pending conflicts
                                        $pendingConflicts = $scheduleModel->getPendingConflicts(
                                            $schedInfo['day_of_week'],
                                            $schedInfo['start_time'],
                                            $schedInfo['end_time'],
                                            $schedInfo['room'],
                                            $schedInfo['id']
                                        );

                                        if (!empty($pendingConflicts)) {
                                            foreach ($pendingConflicts as $pc) {
                                                // A. Send Dashboard Notification
                                                $timeRange = date('g:i A', strtotime($pc['start_time'])) . ' - ' . date('g:i A', strtotime($pc['end_time']));
                                                $msg = "Update: Your schedule request for {$pc['day_of_week']} ($timeRange) at {$pc['room']} is no longer available and has been removed.";
                                                Notification::create($pc['user_id'], $msg, 'error');
                                                
                                                // B. Send Professional Email
                                                $loserUser = $userModel->findById($pc['user_id']);
                                                if ($loserUser && !empty($loserUser['email'])) {
                                                    $this->sendAutoDeclineEmail($loserUser, $pc);
                                                }

                                                // C. Delete the conflicting schedule
                                                $scheduleModel->delete($pc['id'], $pc['user_id'], true);
                                            }
                                        }
                                    }

                                } else {
                                    // Manual Decline
                                    $success = $scheduleModel->delete($schedId, $schedInfo['user_id'], true);
                                }

                                if ($success) {
                                    $processedCount++;
                                    if (!isset($usersToNotify[$schedInfo['user_id']])) {
                                        $usersToNotify[$schedInfo['user_id']] = ['days' => [], 'schedules' => []];
                                        $actionVerb = ($action === 'approve') ? 'Approved' : 'Declined';
        $logModel->log($schedInfo['user_id'], "Schedule $actionVerb", "Your request for {$schedInfo['day_of_week']} has been $action" . "d.");      
                                    }
                                    $usersToNotify[$schedInfo['user_id']]['days'][] = $schedInfo['day_of_week'];
                                    $usersToNotify[$schedInfo['user_id']]['schedules'][] = [
                                        'day' => $schedInfo['day_of_week'],
                                        'subject' => $schedInfo['subject'],
                                        'start_time' => $schedInfo['start_time'],
                                        'end_time' => $schedInfo['end_time'],
                                        'room' => $schedInfo['room']
                                    ];
                                }
                            }
                        }

                        // Notifications for the Primary Action (Approve/Decline)
                        foreach ($usersToNotify as $uId => $userData) {
                            $user = $userModel->findById($uId);
                            if (!$user) continue;

                            $uniqueDays = array_unique($userData['days']);
                            $daysString = implode(', ', $uniqueDays);

                            $notifMsg = ($action === 'approve') 
                                ? "Your schedule for $daysString has been approved." 
                                : "Your schedule for $daysString has been declined.";
                            $notifType = ($action === 'approve') ? 'success' : 'error';
                            
                            Notification::create($uId, $notifMsg, $notifType);

                            if (!empty($user['email'])) {
                                if ($action === 'approve') {
                                    $this->sendApprovalEmail($user, $userData['schedules']);
                                } else {
                                    $this->sendDeclineEmail($user, $daysString);
                                }
                            }
                        }

                        if ($processedCount > 0) {
                            $data['success'] = ucfirst($action) . "d $processedCount schedule(s) successfully.";
                            $logModel->log($adminId, "Schedule " . ucfirst($action), "Processed $processedCount items");
                        }
                        
                        if ($failedCount > 0) {
                            $data['error'] = "Warning: $failedCount schedule(s) could not be approved because they now conflict with existing schedules.";
                        }
                        
                        $data['activeTab'] = 'pending';
                    }
                }
                
                // ... (Add Schedule, Delete Schedule, Edit Schedule blocks remain the same) ...
                // --- ADD SCHEDULE ---
                elseif (isset($_POST['add_schedule'])) {
                    $userId = $_POST['user_id'] ?? $_SESSION['user_id'];
                    $schedules = [];
                    if(isset($_POST['day_of_week']) && is_array($_POST['day_of_week'])) {
                        for ($i = 0; $i < count($_POST['day_of_week']); $i++) {
                            $schedules[] = [ 
                                'day' => $_POST['day_of_week'][$i], 
                                'subject' => $_POST['subject'][$i], 
                                'start' => $_POST['start_time'][$i], 
                                'end' => $_POST['end_time'][$i], 
                                'room' => $_POST['room'][$i] 
                            ];
                        }
                        if ($scheduleModel->create($userId, $schedules, $data['isAdmin'])) {
                            $data['success'] = "Schedule(s) added successfully.";
                            $sessionCount = count($schedules);
        $logModel->log($userId, 'Schedule Submitted', "Submitted $sessionCount session(s) for approval.");
        
        if (!$data['isAdmin']) { $this->notifyAdminsOfPendingSchedule($userId, $schedules); 
                        } else { 
                            $data['error'] = "Failed to add schedule(s)."; 
                        }
                    }
                }
                // --- DELETE SCHEDULE ---
                elseif (isset($_POST['delete_schedule'])) {
                     if ($scheduleModel->delete($_POST['schedule_id_delete'], $_POST['user_id_delete'], $data['isAdmin'])) {
                        $data['success'] = "Schedule deleted successfully.";
                        $logModel->log($_SESSION['user_id'], 'Schedule Removed', "Deleted a schedule entry from the system.");
    }
                    } else { $data['error'] = "Failed to delete schedule."; }
                }
                // --- EDIT SCHEDULE ---
                elseif (isset($_POST['edit_schedule'])) {
                    $this->requireAdmin();
                    $conflict = $scheduleModel->checkOverlap($_POST['day_of_week_edit'], $_POST['start_time_edit'], $_POST['end_time_edit'], $_POST['room_edit'], $_POST['schedule_id_edit']);
                    if (!$conflict) {
                        if ($scheduleModel->update($_POST['schedule_id_edit'], $_POST['day_of_week_edit'], $_POST['subject_edit'], $_POST['start_time_edit'], $_POST['end_time_edit'], $_POST['room_edit'])) {
                            $data['success'] = "Schedule updated successfully.";
                        } else { $data['error'] = "Failed to update schedule."; }
                    } else {
                        $data['error'] = "Cannot update: Conflict detected with " . $conflict['first_name'] . " " . $conflict['last_name'];
                    }
                }
            }
        } catch (Exception $e) {
            $data['error'] = 'System error: ' . $e->getMessage();
        }

        if ($data['isAdmin']) {
            $data['allUsers'] = $userModel->getAllActive(); 
            $data['stats'] = $scheduleModel->getAdminStats();
            
            $allPending = $scheduleModel->getAllByStatus('pending');
            $data['pendingCount'] = count($allPending);

            $data['groupedApprovedSchedules'] = $scheduleModel->getGroupedSchedulesByStatus('approved', $data['searchQuery']);
            $data['groupedPendingSchedules'] = $scheduleModel->getGroupedSchedulesByStatus('pending', $data['searchQuery']);
            
        } else {
            $userId = $_SESSION['user_id'];
            $data['approvedSchedules'] = $scheduleModel->getByUser($userId, 'approved');
            $pendingRaw = $scheduleModel->getByUser($userId, 'pending');
            $data['pendingCount'] = count($pendingRaw);
            $data['pendingSchedules'] = $pendingRaw; 
            
            $data['userStats'] = $scheduleModel->getUserStats($userId);
            $data['selectedUserInfo'] = $userModel->findById($userId);
        }

        $this->view('schedule_view', $data);
    }

    private function sendAutoDeclineEmail($user, $scheduleDetails) {
        $firstName = htmlspecialchars($user['first_name']);
        $emailSubject = "Schedule Request Update - BPC Attendance System";
        
        $day = htmlspecialchars($scheduleDetails['day_of_week']);
        $time = date('g:i A', strtotime($scheduleDetails['start_time'])) . ' - ' . date('g:i A', strtotime($scheduleDetails['end_time']));
        $room = htmlspecialchars($scheduleDetails['room']);

        $emailBody = "<!DOCTYPE html>";
        $emailBody .= "<html><head><style>";
        $emailBody .= "body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }";
        $emailBody .= ".container { max-width: 600px; margin: 0 auto; padding: 20px; }";
        $emailBody .= ".header { background: #dc2626; color: white; padding: 25px; text-align: center; border-radius: 8px 8px 0 0; }";
        $emailBody .= ".content { background: #ffffff; padding: 40px 30px; border: 1px solid #e5e7eb; border-top: none; }";
        $emailBody .= ".warning-box { background: #fef2f2; border-left: 4px solid #dc2626; padding: 20px; margin: 25px 0; border-radius: 4px; color: #7f1d1d; }";
        $emailBody .= ".details-list { background: #f9fafb; padding: 15px; border-radius: 6px; margin-top: 10px; border: 1px solid #e5e7eb; }";
        $emailBody .= ".footer { background: #f3f4f6; padding: 25px; text-align: center; font-size: 13px; color: #6b7280; border-radius: 0 0 8px 8px; border: 1px solid #e5e7eb; border-top: none; }";
        $emailBody .= "</style></head><body>";
        
        $emailBody .= "<div class='container'>";
        $emailBody .= "<div class='header'><h2 style='margin:0; font-weight:600;'>Schedule Update</h2></div>";
        
        $emailBody .= "<div class='content'>";
        $emailBody .= "<p style='margin-top:0;'>Dear <strong>{$firstName}</strong>,</p>";
        $emailBody .= "<p>We are writing to inform you regarding your recent schedule request.</p>";
        
        $emailBody .= "<div class='warning-box'>";
        $emailBody .= "<p style='margin-top:0; font-weight:bold;'>Notice of Schedule Unavailability</p>";
        $emailBody .= "<p style='margin-bottom:0;'>The schedule slot you requested for <strong>{$day}</strong> at <strong>{$time}</strong> in <strong>{$room}</strong> is no longer available as it has been allocated to another faculty member.</p>";
        $emailBody .= "</div>";
        
        $emailBody .= "<p>Consequently, your pending request for this specific slot has been automatically removed from the system to maintain schedule accuracy.</p>";
        $emailBody .= "<p>We apologize for any inconvenience this may cause. Please review the updated schedule availability and submit a new request for an alternative time or room.</p>";
        $emailBody .= "</div>";
        
        $emailBody .= "<div class='footer'>";
        $emailBody .= "<p style='margin:0 0 10px 0;'><strong>Bulacan Polytechnic College</strong><br>Attendance Monitoring System</p>";
        $emailBody .= "<p style='margin:0;'>This is an automated notification. Please do not reply directly to this email.</p>";
        $emailBody .= "</div></div></body></html>";
        
        return sendEmail($user['email'], $emailSubject, $emailBody);
    }

    // ... [Existing sendApprovalEmail, sendDeclineEmail, and notifyAdminsOfPendingSchedule methods remain unchanged] ...
    private function sendApprovalEmail($user, $schedules) {
        $firstName = htmlspecialchars($user['first_name']);
        $emailSubject = "Schedule Approved - BPC Attendance System";
        $emailBody = "<!DOCTYPE html><html><head><style>body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; } .container { max-width: 600px; margin: 0 auto; padding: 20px; } .header { background: #059669; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; } .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; } .schedule-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; } .schedule-table th { background: #ecfdf5; color: #065f46; padding: 12px; text-align: left; border-bottom: 2px solid #059669; } .schedule-table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; } .footer { background: #f3f4f6; padding: 20px; text-align: center; font-size: 14px; color: #6b7280; border-radius: 0 0 8px 8px; } .success-badge { display: inline-block; background: #10b981; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; margin: 10px 0; }</style></head><body><div class='container'><div class='header'><h1 style='margin: 0;'>✓ Schedule Approved</h1></div><div class='content'><p>Dear <strong>{$firstName}</strong>,</p><p>Great news! Your class schedule has been <span class='success-badge'>APPROVED</span></p><p>Below are the details of your approved schedule:</p><table class='schedule-table'><thead><tr><th>Day</th><th>Subject</th><th>Time</th><th>Room</th></tr></thead><tbody>";
        
        $dayOrder = ['Monday'=>1, 'Tuesday'=>2, 'Wednesday'=>3, 'Thursday'=>4, 'Friday'=>5, 'Saturday'=>6];
        usort($schedules, function($a, $b) use ($dayOrder) {
            return ($dayOrder[$a['day']] ?? 7) - ($dayOrder[$b['day']] ?? 7);
        });
        
        foreach ($schedules as $schedule) {
            $day = htmlspecialchars($schedule['day']);
            $subject = htmlspecialchars($schedule['subject']);
            $startTime = date('g:i A', strtotime($schedule['start_time']));
            $endTime = date('g:i A', strtotime($schedule['end_time']));
            $room = htmlspecialchars($schedule['room']);
            $emailBody .= "<tr><td><strong>{$day}</strong></td><td>{$subject}</td><td>{$startTime} - {$endTime}</td><td>{$room}</td></tr>";
        }
        
        $emailBody .= "</tbody></table><p style='margin-top: 20px;'>Your schedule is now active.</p></div><div class='footer'><p><strong>Bulacan Polytechnic College</strong></p></div></div></body></html>";
        return sendEmail($user['email'], $emailSubject, $emailBody);
    }

    private function sendDeclineEmail($user, $daysString) {
        $firstName = htmlspecialchars($user['first_name']);
        $emailSubject = "Schedule Declined - BPC Attendance System";
        $emailBody = "<!DOCTYPE html><html><head><style>body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; } .container { max-width: 600px; margin: 0 auto; padding: 20px; } .header { background: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; } .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; } .footer { background: #f3f4f6; padding: 20px; text-align: center; font-size: 14px; color: #6b7280; border-radius: 0 0 8px 8px; } .warning-box { background: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; }</style></head><body><div class='container'><div class='header'><h1 style='margin: 0;'>⚠ Schedule Declined</h1></div><div class='content'><p>Dear <strong>{$firstName}</strong>,</p><div class='warning-box'><p style='margin: 0;'><strong>Your schedule submission for {$daysString} has been declined.</strong></p></div><p>Please contact the administration office for more details.</p></div><div class='footer'><p><strong>Bulacan Polytechnic College</strong></p></div></div></body></html>";
        return sendEmail($user['email'], $emailSubject, $emailBody);
    }

    private function notifyAdminsOfPendingSchedule($userId, $schedules) {
        $userModel = $this->model('User');
        $db = Database::getInstance();
        $submitter = $userModel->findById($userId);
        if (!$submitter) return;
        $submitterName = $submitter['first_name'] . ' ' . $submitter['last_name'];
        $scheduleCount = count($schedules);
        $days = array_unique(array_column($schedules, 'day'));
        $daysString = implode(', ', $days);
        $message = "{$submitterName} has submitted {$scheduleCount} schedule(s) for {$daysString} pending your approval.";
        $stmt = $db->query("SELECT id FROM users WHERE role = 'Admin' AND status = 'active'", [], "");
        $admins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($admins as $admin) { Notification::create($admin['id'], $message, 'warning'); }
    }
}