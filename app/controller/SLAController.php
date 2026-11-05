<?php

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../model/SLAModel.php';
session_start();

class SLAController
{
    private $slaModel;

    public function __construct($conn)
    {
        $this->slaModel = new SLAModel($conn);
    }


    // ==========================================
    // GET SLA SETTINGS
    // ==========================================
    public function getSLASettings()
    {
        header('Content-Type: application/json');

        $settings = $this->slaModel->getSLASettings();

        echo json_encode([
            'success' => true,
            'settings' => $settings
        ]);

        exit();
    }


    // ==========================================
    // UPDATE SLA SETTINGS
    // ==========================================
    public function updateSLASettings()
{
    header('Content-Type: application/json');

    

    $settings = $_POST['settings'] ?? [];

    if (!is_array($settings) || empty($settings)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid SLA settings.'
        ]);
        exit();
    }

    $allowedPriorities = ['Low', 'Medium', 'High', 'Emergency'];

    foreach ($settings as $priority => $slaHours) {

        // CHANGED: added true (strict check)
        if (!in_array($priority, $allowedPriorities, true)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid SLA priority.'
            ]);
            exit();
        }

        $slaHours = (int) $slaHours;

        // CHANGED: added an upper limit
        if ($slaHours <= 0 || $slaHours > 720) {
            echo json_encode([
                'success' => false,
                'message' => 'SLA hours must be between 1 and 720.'
            ]);
            exit();
        }
    }

    $updatedBy = $_SESSION['user_id'] ?? null;

    $success = $this->slaModel->updateSLASettings($settings, $updatedBy);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'SLA settings updated successfully.'
            : 'Failed to update SLA settings.'
    ]);
    exit();
}

    // ==========================================
    // GET WORKING HOURS
    // ==========================================
    public function getWorkingHours()
    {
        header('Content-Type: application/json');

        $workingHours = $this->slaModel->getWorkingHours();

        echo json_encode([
            'success' => true,
            'working_hours' => $workingHours
        ]);

        exit();
    }


    // ==========================================
    // UPDATE WORKING HOURS
    // ==========================================
    public function updateWorkingHours()
    {
        header('Content-Type: application/json');

        $workingHours = $_POST['working_hours'] ?? [];

        if (!is_array($workingHours) || empty($workingHours)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid working hours.'
            ]);

            exit();
        }


        // ==========================================
        // ALLOWED DAYS
        // ==========================================

        $allowedDays = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ];


        foreach ($workingHours as $day => $data) {

            if (!in_array($day, $allowedDays)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid day of week.'
                ]);

                exit();
            }


            $isWorkingDay = isset($data['is_working_day'])
                ? (int) $data['is_working_day']
                : 0;


            if (!in_array($isWorkingDay, [0, 1])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid working day value.'
                ]);

                exit();
            }


            // ==========================================
            // IF WORKING DAY
            // START AND END TIME REQUIRED
            // ==========================================

            if ($isWorkingDay === 1) {

                $startTime = trim($data['start_time'] ?? '');
                $endTime = trim($data['end_time'] ?? '');


                if ($startTime === '' || $endTime === '') {

                    echo json_encode([
                        'success' => false,
                        'message' => $day .
                            ' requires a start time and end time.'
                    ]);

                    exit();
                }


                if ($startTime >= $endTime) {

                    echo json_encode([
                        'success' => false,
                        'message' => $day .
                            ' end time must be later than start time.'
                    ]);

                    exit();
                }
            }
        }


        // ==========================================
        // UPDATE WORKING HOURS
        // ==========================================

        $success = $this->slaModel->updateWorkingHours(
            $workingHours
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Working hours updated successfully.'
                : 'Failed to update working hours.'
        ]);

        exit();
    }


    // ==========================================
    // GET PUBLIC HOLIDAYS
    // ==========================================
    public function getPublicHolidays()
    {
        header('Content-Type: application/json');

        $holidays = $this->slaModel->getPublicHolidays();

        echo json_encode([
            'success' => true,
            'holidays' => $holidays
        ]);

        exit();
    }


    // ==========================================
    // CREATE PUBLIC HOLIDAY
    // ==========================================
    public function createPublicHoliday()
    {
        header('Content-Type: application/json');

        $holidayDate = trim($_POST['holiday_date'] ?? '');
        $holidayName = trim($_POST['holiday_name'] ?? '');


        // ==========================================
        // VALIDATION
        // ==========================================

        if ($holidayDate === '' || $holidayName === '') {

            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields.'
            ]);

            exit();
        }


        // ==========================================
        // CHECK DATE FORMAT
        // ==========================================

        $dateObject = DateTime::createFromFormat(
            'Y-m-d',
            $holidayDate
        );

        if (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $holidayDate
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid holiday date.'
            ]);

            exit();
        }


        // ==========================================
        // CHECK DUPLICATE DATE
        // ==========================================

        if (
            $this->slaModel->holidayExists($holidayDate)
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'A public holiday already exists on this date.'
            ]);

            exit();
        }


        // ==========================================
        // CREATE HOLIDAY
        // ==========================================

        $success = $this->slaModel->createPublicHoliday(
            $holidayDate,
            $holidayName
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Public holiday added successfully.'
                : 'Failed to add public holiday.'
        ]);

        exit();
    }


    // ==========================================
    // UPDATE PUBLIC HOLIDAY
    // ==========================================
    public function updatePublicHoliday()
    {
        header('Content-Type: application/json');

        $holidayId = isset($_POST['holiday_id'])
            ? (int) $_POST['holiday_id']
            : 0;

        $holidayDate = trim($_POST['holiday_date'] ?? '');
        $holidayName = trim($_POST['holiday_name'] ?? '');


        // ==========================================
        // VALIDATION
        // ==========================================

        if ($holidayId <= 0) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid holiday ID.'
            ]);

            exit();
        }


        if ($holidayDate === '' || $holidayName === '') {

            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields.'
            ]);

            exit();
        }


        // ==========================================
        // CHECK DATE FORMAT
        // ==========================================

        $dateObject = DateTime::createFromFormat(
            'Y-m-d',
            $holidayDate
        );

        if (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $holidayDate
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid holiday date.'
            ]);

            exit();
        }


        // ==========================================
        // CHECK HOLIDAY EXISTS
        // ==========================================

        $holiday = $this->slaModel->getPublicHolidayById(
            $holidayId
        );

        if (!$holiday) {

            echo json_encode([
                'success' => false,
                'message' => 'Public holiday not found.'
            ]);

            exit();
        }


        // ==========================================
        // CHECK DUPLICATE DATE
        // ==========================================

        if (
            $this->slaModel->holidayExists(
                $holidayDate,
                $holidayId
            )
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'A public holiday already exists on this date.'
            ]);

            exit();
        }


        // ==========================================
        // UPDATE HOLIDAY
        // ==========================================

        $success = $this->slaModel->updatePublicHoliday(
            $holidayId,
            $holidayDate,
            $holidayName
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Public holiday updated successfully.'
                : 'Failed to update public holiday.'
        ]);

        exit();
    }


    // ==========================================
    // UPDATE PUBLIC HOLIDAY STATUS
    // ==========================================
    public function updateHolidayStatus()
    {
        header('Content-Type: application/json');

        $holidayId = isset($_POST['holiday_id'])
            ? (int) $_POST['holiday_id']
            : 0;

        $status = trim($_POST['status'] ?? '');


        // ==========================================
        // VALIDATION
        // ==========================================

        if ($holidayId <= 0) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid holiday ID.'
            ]);

            exit();
        }


        if (!in_array($status, ['Active', 'Inactive'])) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid holiday status.'
            ]);

            exit();
        }


        // ==========================================
        // UPDATE STATUS
        // ==========================================

        $success = $this->slaModel->updatePublicHolidayStatus(
            $holidayId,
            $status
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Public holiday status updated successfully.'
                : 'Failed to update public holiday status.'
        ]);

        exit();
    }


    // ==========================================
    // DELETE PUBLIC HOLIDAY
    // ==========================================
    public function deletePublicHoliday()
    {
        header('Content-Type: application/json');

        $holidayId = isset($_POST['holiday_id'])
            ? (int) $_POST['holiday_id']
            : 0;


        // ==========================================
        // VALIDATION
        // ==========================================

        if ($holidayId <= 0) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid holiday ID.'
            ]);

            exit();
        }


        // ==========================================
        // DELETE HOLIDAY
        // ==========================================

        $success = $this->slaModel->deletePublicHoliday(
            $holidayId
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Public holiday deleted successfully.'
                : 'Failed to delete public holiday.'
        ]);

        exit();
    }
}


// ==================================================
// REQUEST HANDLER
// ==================================================

$controller = new SLAController($conn);

$action = $_GET['action'] ?? $_POST['action'] ?? '';


switch ($action) {

    // ==========================================
    // SLA SETTINGS
    // ==========================================

    case 'get_sla_settings':
        $controller->getSLASettings();
        break;

    case 'update_sla_settings':
        $controller->updateSLASettings();
        break;


    // ==========================================
    // WORKING HOURS
    // ==========================================

    case 'get_working_hours':
        $controller->getWorkingHours();
        break;

    case 'update_working_hours':
        $controller->updateWorkingHours();
        break;


    // ==========================================
    // PUBLIC HOLIDAYS
    // ==========================================

    case 'get_public_holidays':
        $controller->getPublicHolidays();
        break;

    case 'create_public_holiday':
        $controller->createPublicHoliday();
        break;

    case 'update_public_holiday':
        $controller->updatePublicHoliday();
        break;

    case 'update_holiday_status':
        $controller->updateHolidayStatus();
        break;

    case 'delete_public_holiday':
        $controller->deletePublicHoliday();
        break;


    // ==========================================
    // INVALID ACTION
    // ==========================================

    default:

        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Invalid action.'
        ]);

        exit();
}