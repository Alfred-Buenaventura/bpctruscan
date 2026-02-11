<?php
require_once __DIR__ . '/../core/controller.php';

class HolidayController extends Controller {

    public function index() {
        $this->requireLogin();
        if (!Helper::isAdmin()) { die("Access Denied"); }
        
        $holidayModel = $this->model('Holiday');
        $error = '';
        $success = '';

        // Handle Form Submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();
            // 1. Update DTR Signatory Settings
            if (isset($_POST['update_signatory'])) {
                $holidayModel->updateSystemSetting('dtr_in_charge_name', $_POST['in_charge_name']);
                $holidayModel->updateSystemSetting('dtr_in_charge_title', $_POST['in_charge_title']);
                $success = "DTR signatory settings updated successfully.";
            }

            // 2. Add New Holiday
            if (isset($_POST['add_holiday'])) {
                $date = $_POST['holiday_date'];
                $desc = $_POST['description'];
                $type = $_POST['type'];
                if ($holidayModel->create($date, $desc, $type)) {
                    $success = "Holiday added successfully.";
                }
            }

            // 3. Delete Holiday
            if (isset($_POST['delete_holiday'])) {
                if ($holidayModel->delete($_POST['id'])) {
                    $success = "Holiday record removed from the system.";
                }
            }
        }

        $filters = [
            'search'     => $_GET['search'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date'   => $_GET['end_date'] ?? ''
        ];

        $data = [
            'pageTitle' => 'DTR Management',
            'pageSubtitle' => 'Manage institutional holidays and DTR signatory settings',
            'holidays' => $holidayModel->getAll($filters),
            'settings' => $holidayModel->getSystemSettings(),
            'filters' => $filters,
            'error' => $error,
            'success' => $success
        ];

        $this->view('holiday_view', $data);
    }
}