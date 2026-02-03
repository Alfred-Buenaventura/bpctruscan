<?php
require_once __DIR__ . '/../core/controller.php';

class HolidayController extends Controller {

    public function index() {
        $this->requireLogin(); //
        if (!Helper::isAdmin()) { die("Access Denied"); }
        $holidayModel = $this->model('Holiday');
        $filters = [
            'search'     => $_GET['search'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date'   => $_GET['end_date'] ?? ''
        ];

        $data = [
            'pageTitle' => 'Holiday Management',
            'pageSubtitle' => 'Manage school holidays and special dates',
            'holidays' => $holidayModel->getAll($filters),
            'filters' => $filters,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_holiday'])) {
                $date = $_POST['holiday_date'];
                $desc = $_POST['description'];
                $type = $_POST['type'];
                
                if ($holidayModel->create($date, $desc, $type)) {
                    $data['success'] = "Holiday added successfully.";
                    $data['holidays'] = $holidayModel->getAll($filters);
                }
            }

            if (isset($_POST['delete_holiday'])) {
                if ($holidayModel->delete($_POST['id'])) {
                    $data['success'] = "Holiday record removed from the system.";
                    $data['holidays'] = $holidayModel->getAll($filters);
                }
            }
        }

        $this->view('holiday_view', $data);
    }
}
?>