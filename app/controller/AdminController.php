<?php

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../model/Admin.php';
require_once __DIR__ . "/password_functions.php";

class AdminController
{
    private $adminModel;

    public function __construct($conn)
    {
        $this->adminModel = new Admin($conn);
    }


    // ==========================================
    // GET USERS
    // ==========================================
    public function getUsers()
    {
        header('Content-Type: application/json');

        $users = $this->adminModel->getUsers();

        echo json_encode([
            'success' => true,
            'users' => $users
        ]);

        exit();
    }


    // ==========================================
    // GET USER BY ID
    // ==========================================
    public function getUser()
    {
        header('Content-Type: application/json');

        $userId = isset($_GET['user_id'])
            ? (int) $_GET['user_id']
            : 0;

        if ($userId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid user ID.'
            ]);
            exit();
        }

        $user = $this->adminModel->getUserById($userId);

        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'User not found.'
            ]);
            exit();
        }

        echo json_encode([
            'success' => true,
            'user' => $user
        ]);

        exit();
    }

    public function getDepartments()
    {
        header('Content-Type: application/json');

        $departments = $this->adminModel->getDepartments();

        echo json_encode([
            'success' => true,
            'departments' => $departments
        ]);

        exit();
    }

    // ==========================================
    // GET USER STATISTICS
    // ==========================================
    public function getStats()
    {
        header('Content-Type: application/json');

        $stats = $this->adminModel->getUserStats();

        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);

        exit();
    }



// ==========================================
// UPDATE USER STATUS
// ==========================================
public function updateStatus()
{
    header('Content-Type: application/json');

    $userId = isset($_POST['user_id'])
        ? (int) $_POST['user_id']
        : 0;

    $status = trim($_POST['status'] ?? '');

    if ($userId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid user ID.'
        ]);
        exit();
    }

    if (!in_array($status, ['Active', 'Inactive', 'Suspended'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid account status.'
        ]);
        exit();
    }

    $success = $this->adminModel->updateUserStatus(
        $userId,
        $status
    );

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'User status updated successfully.'
            : 'Failed to update user status.'
    ]);

    exit();
}



// ==========================================
// DELETE USER
// ==========================================
public function deleteUser()
{
    header('Content-Type: application/json');

    $userId = isset($_POST['user_id'])
        ? (int) $_POST['user_id']
        : 0;

    if ($userId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid user ID.'
        ]);
        exit();
    }

    $success = $this->adminModel->deleteUser($userId);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'User deleted successfully.'
            : 'Failed to delete user.'
    ]);

    exit();
}

// ==========================================
// UPDATE USER
// ==========================================
public function updateUser()
{
    header('Content-Type: application/json');


    // ==========================================
    // GET FORM DATA
    // ==========================================

    $userId = isset($_POST['user_id'])
        ? (int) $_POST['user_id']
        : 0;

    $fullName = trim($_POST['full_name'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $userType = trim($_POST['user_type'] ?? '');

    $role = trim($_POST['role'] ?? '');

    $departmentId = isset($_POST['department_id'])
        ? (int) $_POST['department_id']
        : 0;


    // ==========================================
    // VALIDATION
    // ==========================================

    if ($userId <= 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid user ID.'
        ]);

        exit();
    }


    if (
        empty($fullName) ||
        empty($email) ||
        empty($userType) ||
        empty($role)
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields.'
        ]);

        exit();
    }


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ]);

        exit();
    }


    // ==========================================
    // ALLOWED USER TYPES
    // ==========================================

    $allowedUserTypes = [
        'Lecturer',
        'Maintenance',
        'IT Support',
        'Cleaning',
        'Security'
    ];


    if (!in_array($userType, $allowedUserTypes)) {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid user type.'
        ]);

        exit();
    }


    // ==========================================
    // FIXED ROLE MAPPING
    // ==========================================

    $roleMapping = [
        'Lecturer' => 'Reporter',
        'Maintenance' => 'Response Staff',
        'IT Support' => 'Response Staff',
        'Cleaning' => 'Response Staff',
        'Security' => 'Response Staff'
    ];


    if (
        !isset($roleMapping[$userType]) ||
        $role !== $roleMapping[$userType]
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid role for the selected user type.'
        ]);

        exit();
    }


    // ==========================================
    // DEPARTMENT
    // ==========================================

    if ($departmentId <= 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Please select a department.'
        ]);

        exit();
    }


    // ==========================================
    // CHECK DUPLICATE EMAIL
    // ==========================================

    /*
     * The current emailExists() checks every user.
     * We need to allow the current user to keep
     * their existing email.
     */

    $currentUser = $this->adminModel->getUserById($userId);


    if (!$currentUser) {

        echo json_encode([
            'success' => false,
            'message' => 'User not found.'
        ]);

        exit();
    }


    if (
        strtolower($email) !==
        strtolower($currentUser['email']) &&
        $this->adminModel->emailExists($email)
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'This email address is already registered.'
        ]);

        exit();
    }


    // ==========================================
    // UPDATE USER
    // ==========================================

    $success = $this->adminModel->updateUser(
        $userId,
        $fullName,
        $email,
        $role,
        $userType,
        $departmentId
    );


    // ==========================================
    // RESPONSE
    // ==========================================

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'User updated successfully.'
            : 'Failed to update user.'
    ]);

    exit();
}
    // ==========================================
    // CREATE STAFF / LECTURER
    // ==========================================
    public function createUser()
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = trim($_POST['role'] ?? '');
        $userType = trim($_POST['user_type'] ?? '');

        $departmentId = isset($_POST['department_id'])
            ? (int) $_POST['department_id']
            : 0;


        // ==========================================
        // VALIDATION
        // ==========================================

        if (
            empty($fullName) ||
            empty($email) ||
            
            empty($role) ||
            empty($userType)
        ) {
            $this->redirectWithError(
                'Please fill in all required fields.'
            );
        }


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError(
                'Please enter a valid email address.'
            );
        }


        // ==========================================
        // ALLOWED USER TYPES
        // ==========================================

        $allowedUserTypes = [
            'Lecturer',
            'Maintenance',
            'IT Support',
            'Cleaning',
            'Security'
        ];

        if (!in_array($userType, $allowedUserTypes)) {
            $this->redirectWithError(
                'Invalid user type.'
            );
        }


        // ==========================================
        // FIXED ROLE MAPPING
        // ==========================================

        $roleMapping = [
            'Lecturer' => 'Reporter',
            'Maintenance' => 'Response Staff',
            'IT Support' => 'Response Staff',
            'Cleaning' => 'Response Staff',
            'Security' => 'Response Staff'
        ];

        if (
            !isset($roleMapping[$userType]) ||
            $role !== $roleMapping[$userType]
        ) {
            $this->redirectWithError(
                'Invalid role for the selected user type.'
            );
        }


        // ==========================================
        // DEPARTMENT
        // ==========================================

        if ($departmentId <= 0) {
            $this->redirectWithError(
                'Please select a department.'
            );
        }


        // ==========================================
        // CHECK DUPLICATE EMAIL
        // ==========================================

        if ($this->adminModel->emailExists($email)) {
            $this->redirectWithError(
                'This email address is already registered.'
            );
        }


        // ==========================================
        // PASSWORD
        // ==========================================

        $password = 'Testing1!';

        $hashedPassword = hashPassword($password);


        // ==========================================
        // CREATE USER
        // ==========================================

        $success = $this->adminModel->createUser(
            $fullName,
            $email,
            $hashedPassword,
            $role,
            $userType,
            $departmentId
        );


        if ($success) {
            $this->redirectWithSuccess(
                'User account created successfully.'
            );
        }


        $this->redirectWithError(
            'Failed to create user account.'
        );
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
            '/app/view/dashboard.php?page=manage_users'
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
            '/app/view/dashboard.php?page=manage_users'
        );

        exit();
    }
}


// ==================================================
// REQUEST HANDLER
// ==================================================

$controller = new AdminController($conn);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    
    case 'get_departments':
    $controller->getDepartments();
        break;

    case 'get_users':
        $controller->getUsers();
        break;

    case 'get_user':
        $controller->getUser();
        break;

    case 'get_stats':
        $controller->getStats();
        break;

    case 'updateStatus':
        $controller->updateStatus();
        break;

    case 'delete':
        $controller->deleteUser();
        break;

    case 'update_user':
        $controller->updateUser();
        break;

    case 'create_user':
        $controller->createUser();
        break;

    default:
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Invalid action.'
        ]);

        exit();
}