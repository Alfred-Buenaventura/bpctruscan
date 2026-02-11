<?php
require_once __DIR__ . '/../core/controller.php';

class AccountController extends Controller {

    public function index() {
        $this->requireAdmin();

        $userModel = $this->model('User');
        $logModel = $this->model('ActivityLog');
        $notifModel = $this->model('Notification');
        
        $data = [
            'pageTitle' => 'Account Management',
            'pageSubtitle' => 'Manage user accounts individually or import in bulk via CSV',
            'activeTab' => $_GET['tab'] ?? 'view',
            'flashMessage' => $_SESSION['flash_message'] ?? null,
            'flashType' => $_SESSION['flash_type'] ?? null
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. Bulk CSV Import
            if (isset($_FILES['csvFile'])) {
                $this->handleCsvImport($_FILES['csvFile'], $userModel, $logModel, $notifModel);
                exit();
            }

            // 2. Individual Account Creation
            if (isset($_POST['create_user'])) {
                $this->handleCreateUser($_POST, $userModel, $logModel, $notifModel);
                exit();
            }

            // 3. User Editing
            if (isset($_POST['edit_user'])) {
                $userModel->update($_POST['user_id'], clean($_POST['first_name']), clean($_POST['last_name']), clean($_POST['middle_name']), clean($_POST['email']), clean($_POST['phone']));
                $logModel->log($_SESSION['user_id'], 'User Updated', "Updated user ID: " . $_POST['user_id']);
                $this->setFlash('User information updated successfully!', 'success', 'create_account.php?tab=view');
                exit();
            }

            // 4. User Archiving
            if (isset($_POST['archive_user'])) {
                $userModel->updateStatus($_POST['user_id'], 'archived');
                $logModel->log($_SESSION['user_id'], 'User Archived', "Archived user ID: " . $_POST['user_id']);
                $this->setFlash('User archived successfully!', 'success', 'create_account.php?tab=view');
                exit();
            }

            // 5. User Restoring
            if (isset($_POST['restore_user'])) {
                $userModel->updateStatus($_POST['user_id'], 'active');
                $logModel->log($_SESSION['user_id'], 'User Restored', "Restored user ID: " . $_POST['user_id']);
                // Fixed redirect string
                $this->setFlash('User restored successfully!', 'success', 'create_account.php?tab=view');
                exit();
            }

            // 6. User Deleting (Permanent)
            if (isset($_POST['delete_user'])) {
                $userModel->delete($_POST['user_id']);
                $logModel->log($_SESSION['user_id'], 'User Deleted', "Permanently deleted user ID: " . $_POST['user_id']);
                $this->setFlash('User permanently deleted!', 'success', 'create_account.php?tab=view');
                exit();
            }
        }

        $data['stats'] = $userModel->getStats();
        $data['activeUsers'] = $userModel->getAllActive();
        $data['archivedUsers'] = $userModel->getAllArchived();

        $this->view('account_view', $data);
    }

    private function handleCreateUser($post, $userModel, $logModel, $notifModel) {
        try {
            $facultyId = clean($post['faculty_id']);
            
            if ($post['role'] === 'Admin') {
                $this->setFlash('Admin accounts must be created from Admin Management.', 'error', 'create_account.php?tab=create');
                return;
            }

            if ($userModel->exists($facultyId)) {
                $this->setFlash("Account with Faculty ID ($facultyId) already exists.", 'error', 'create_account.php?tab=create');
                return;
            }

            $userData = [
                'faculty_id' => $facultyId,
                'username' => strtolower($facultyId),
                'password' => password_hash('@defaultpass123', PASSWORD_DEFAULT),
                'first_name' => clean($post['first_name']),
                'last_name' => clean($post['last_name']),
                'middle_name' => clean($post['middle_name']),
                'email' => clean($post['email']),
                'phone' => clean($post['phone']),
                'role' => clean($post['role'])
            ];

            $newId = $userModel->create($userData);
            if ($newId) {
                $logModel->log($_SESSION['user_id'], 'User Created', "Created user: $facultyId");
                $notifModel->create($newId, "Welcome! Your account has been created.", 'success');
                sendEmail($userData['email'], "BPC Account Created", "Welcome! Your temporary password is @defaultpass123");
                
                $this->setFlash("Account for {$userData['first_name']} created!", 'success', 'create_account.php?tab=view');
            }
        } catch (Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'error', 'create_account.php?tab=create');
        }
    }

    private function handleCsvImport($file, $userModel, $logModel, $notifModel) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('Error uploading file.', 'error', 'create_account.php?tab=csv');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        fgetcsv($handle); // skip header
        
        $imported = 0; $skipped = 0;
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 7) continue;
            $facultyId = clean($data[0]);

            if ($userModel->exists($facultyId)) {
                $skipped++;
                continue;
            }

            $userData = [
                'faculty_id' => $facultyId,
                'last_name' => clean($data[1]),
                'first_name' => clean($data[2]),
                'middle_name' => clean($data[3]),
                'username' => $facultyId,
                'role' => clean($data[4]),
                'email' => clean($data[5]),
                'phone' => $data[6] ?? '',
                'password' => password_hash('@defaultpass123', PASSWORD_DEFAULT)
            ];

            if ($userModel->create($userData)) $imported++;
        }
        fclose($handle);

        $logModel->log($_SESSION['user_id'], 'CSV Import', "Imported $imported users.");
        $this->setFlash("Imported $imported users. Skipped $skipped.", 'success', 'create_account.php?tab=view');
    }

    public function downloadTemplate() {
        $this->requireAdmin();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bpc_template.csv"');
        echo "Faculty ID,Last Name,First Name,Middle Name,Username,Role,Email,Phone\n";
        echo "FAC001,Dela Cruz,Juan,P.,jdelacruz,Teacher,juan@bpc.edu.ph,09123456789";
        exit;
    }
}