<?php
session_start();

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../model/LeaveModel.php';


class LeaveController
{
    private $leaveModel;

    public function __construct($conn)
    {
        $this->leaveModel = new LeaveModel($conn);
    }


    // ==========================================
    // GET MY LEAVE
    // ==========================================
    public function getMyLeave()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'] ?? 0;

        if ($userId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'User session not found.'
            ]);
            exit();
        }

        $leaves = $this->leaveModel->getLeavesByUserId($userId);

        echo json_encode([
            'success' => true,
            'leaves' => $leaves
        ]);

        exit();
    }


    // ==========================================
    // GET ONE LEAVE BY ID
    // ==========================================
    public function getLeave()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'] ?? 0;

        $leaveId = isset($_GET['leave_id'])
            ? (int) $_GET['leave_id']
            : 0;

        if ($userId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'User session not found.'
            ]);
            exit();
        }

        if ($leaveId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid leave ID.'
            ]);
            exit();
        }

        $leave = $this->leaveModel->getLeaveById(
            $leaveId,
            $userId
        );

        if (!$leave) {
            echo json_encode([
                'success' => false,
                'message' => 'Leave record not found.'
            ]);
            exit();
        }

        echo json_encode([
            'success' => true,
            'leave' => $leave
        ]);

        exit();
    }


    // ==========================================
    // CREATE LEAVE
    // ==========================================
    public function createLeave()
    {
        $userId = $_SESSION['user_id'] ?? 0;

        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if ($userId <= 0) {
            $this->redirectWithError(
                'User session not found.'
            );
        }

        if (
            empty($startDate) ||
            empty($endDate)
        ) {
            $this->redirectWithError(
                'Please fill in all required fields.'
            );
        }


        // ==========================================
        // CHECK DATE ORDER
        // ==========================================
        if ($startDate > $endDate) {
            $this->redirectWithError(
                'End date cannot be earlier than start date.'
            );
        }


        // ==========================================
        // CHECK OVERLAPPING LEAVE
        // ==========================================
        if (
            $this->leaveModel->hasOverlappingLeave(
                $userId,
                $startDate,
                $endDate
            )
        ) {
            $this->redirectWithError(
                'You already have a leave record during this period.'
            );
        }


        // ==========================================
        // CREATE LEAVE
        // ==========================================
        $success = $this->leaveModel->createLeave(
            $userId,
            $startDate,
            $endDate,
            $reason
        );


        if ($success) {
            $this->redirectWithSuccess(
                'Leave added successfully.'
            );
        }


        $this->redirectWithError(
            'Failed to add leave.'
        );
    }


    // ==========================================
    // UPDATE LEAVE
    // ==========================================
    public function updateLeave()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'] ?? 0;

        $leaveId = isset($_POST['leave_id'])
            ? (int) $_POST['leave_id']
            : 0;

        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if ($userId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'User session not found.'
            ]);
            exit();
        }

        if ($leaveId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid leave ID.'
            ]);
            exit();
        }

        if (
            empty($startDate) ||
            empty($endDate)
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields.'
            ]);
            exit();
        }


        // ==========================================
        // CHECK DATE ORDER
        // ==========================================
        if ($startDate > $endDate) {
            echo json_encode([
                'success' => false,
                'message' => 'End date cannot be earlier than start date.'
            ]);
            exit();
        }


        // ==========================================
        // CHECK LEAVE EXISTS
        // ==========================================
        $currentLeave = $this->leaveModel->getLeaveById(
            $leaveId,
            $userId
        );

        if (!$currentLeave) {
            echo json_encode([
                'success' => false,
                'message' => 'Leave record not found.'
            ]);
            exit();
        }


        // ==========================================
        // CHECK OVERLAPPING LEAVE
        // ==========================================
        if (
            $this->leaveModel->hasOverlappingLeave(
                $userId,
                $startDate,
                $endDate,
                $leaveId
            )
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'You already have a leave record during this period.'
            ]);
            exit();
        }


        // ==========================================
        // UPDATE LEAVE
        // ==========================================
        $success = $this->leaveModel->updateLeave(
            $leaveId,
            $userId,
            $startDate,
            $endDate,
            $reason
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Leave updated successfully.'
                : 'Failed to update leave.'
        ]);

        exit();
    }


    // ==========================================
    // DELETE LEAVE
    // ==========================================
    public function deleteLeave()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'] ?? 0;

        $leaveId = isset($_POST['leave_id'])
            ? (int) $_POST['leave_id']
            : 0;

        if ($userId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'User session not found.'
            ]);
            exit();
        }

        if ($leaveId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid leave ID.'
            ]);
            exit();
        }


        // ==========================================
        // DELETE ONLY MY OWN LEAVE
        // ==========================================
        $success = $this->leaveModel->deleteLeave(
            $leaveId,
            $userId
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Leave deleted successfully.'
                : 'Failed to delete leave.'
        ]);

        exit();
    }


    // ==========================================
    // REDIRECT WITH SUCCESS
    // ==========================================
    private function redirectWithSuccess($message)
    {
        $_SESSION['success_message'] = $message;

        header(
            'Location: ' .
            BASE_URL .
            '/app/view/dashboard.php?page=my_leave'
        );

        exit();
    }


    // ==========================================
    // REDIRECT WITH ERROR
    // ==========================================
    private function redirectWithError($message)
    {
        $_SESSION['error_message'] = $message;

        header(
            'Location: ' .
            BASE_URL .
            '/app/view/dashboard.php?page=my_leave'
        );

        exit();
    }
}


// ==================================================
// REQUEST HANDLER
// ==================================================

$controller = new LeaveController($conn);

$action = $_GET['action'] ?? $_POST['action'] ?? '';


switch ($action) {

    case 'get_my_leave':
        $controller->getMyLeave();
        break;

    case 'get_leave':
        $controller->getLeave();
        break;

    case 'create_leave':
        $controller->createLeave();
        break;

    case 'update_leave':
        $controller->updateLeave();
        break;

    case 'delete_leave':
        $controller->deleteLeave();
        break;

    default:
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Invalid action.'
        ]);

        exit();
}
