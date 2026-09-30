
<?php

require_once __DIR__ . "/../config/conn.php";
require_once __DIR__ . "/password_functions.php";
require_once __DIR__ . "/../model/UserModel.php";

class AuthController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // =========================
    // LOGIN
    // =========================
    public function login($email, $password, $remember = false) {

        try {

            if (empty($email) || empty($password)) {
                throw new Exception('All fields are required.');
            }

            $email = trim($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format.');
            }

            $userModel = new UserModel($this->conn);
            $row = $userModel->getUserByEmail($email);

            // Check email and password
            if (!$row || !isPasswordCorrect($password, $row['password'])) {
                throw new Exception('Invalid email or password.');
            }

            // Check account status
            if ($row['account_status'] !== 'Active') {
                throw new Exception(
                    'Your account is ' .
                    $row['account_status'] .
                    '. Please contact your administrator.'
                );
            }

            // Start session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Prevent session fixation
            session_regenerate_id(true);

            // Store user information in session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_role'] = $row['role'];

            // Record login session
            $stmt = $this->conn->prepare("
                INSERT INTO sessions (
                    user_id,
                    login_time,
                    status
                )
                VALUES (?, NOW(), 'Active')
            ");

            $stmt->bind_param("i", $row['user_id']);
            $stmt->execute();

            $_SESSION['session_id'] = $this->conn->insert_id;

            $stmt->close();
            
            // Force first password change only for lecturers, admins, and staff
            if (
                $row['must_change_password'] &&
                in_array($row['user_type'], ['Maintenance', 'Lecturer', 'Staff','IT Support','Cleaning','Security'])
            ) {

                header(
                    "Location: " .
                    BASE_URL .
                    "/app/view/first_login_reset.php"
                );

                exit();
            }

            // Remember Me
            if ($remember) {

                setcookie(
                    'user_login',
                    $email,
                    time() + (86400 * 30),
                    "/"
                );

            } else {

                setcookie(
                    'user_login',
                    '',
                    time() - 3600,
                    "/"
                );
            }

            // Redirect based on role
            $redirectUrl =
                BASE_URL .
                "/app/view/dashboard.php?page=" .
                $this->getDefaultPage($row['role']);

            header("Location: $redirectUrl");
            exit();

        } catch (Exception $e) {

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['login_error'] = $e->getMessage();

            header(
                "Location: " .
                BASE_URL .
                "/app/view/login_page.php"
            );

            exit();
        }
    }


    // =========================
    // DEFAULT PAGE BY ROLE
    // =========================
    private function getDefaultPage($role) {

        $defaultPages = [

            'Super Admin'    => 'manage_accounts',

            'Admin'          => 'dashboard',

            'Response Staff' => 'my_incidents',

            'Reporter'       => 'my_reports',


        ];

        return $defaultPages[$role] ?? 'dashboard';
    }

    // =========================
    // LOGOUT
    // =========================
    public function logout() {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Update current active session
        if (
            isset($_SESSION['user_id']) &&
            isset($_SESSION['session_id'])
        ) {

            $stmt = $this->conn->prepare("
                UPDATE sessions
                SET
                    logout_time = NOW(),
                    status = 'Logged Out'
                WHERE
                    session_id = ?
                    AND user_id = ?
                    AND status = 'Active'
            ");

            $stmt->bind_param(
                "ii",
                $_SESSION['session_id'],
                $_SESSION['user_id']
            );

            $stmt->execute();
            $stmt->close();
        }

        // Clear session
        $_SESSION = [];

        session_destroy();

        // Return to login page
        header(
            "Location: " .
            BASE_URL .
            "/app/view/login_page.php"
        );

        exit();
    }

    // =========================
    // RESET PASSWORD
    // =========================
    public function resetPassword(
        $email,
        $newPassword,
        $confirmPassword
    ) {

        if (
            empty($email) ||
            empty($newPassword) ||
            empty($confirmPassword)
        ) {
            throw new Exception('All fields are required.');
        }

        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception('Passwords do not match.');
        }

        if (strlen($newPassword) < 8) {
            throw new Exception(
                'Password must be at least 8 characters long.'
            );
        }

        if (!preg_match('/[A-Z]/', $newPassword)) {
            throw new Exception(
                'Password must contain at least one uppercase letter (A-Z).'
            );
        }

        if (!preg_match('/[a-z]/', $newPassword)) {
            throw new Exception(
                'Password must contain at least one lowercase letter (a-z).'
            );
        }

        if (!preg_match('/[0-9]/', $newPassword)) {
            throw new Exception(
                'Password must contain at least one number (0-9).'
            );
        }

        if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            throw new Exception(
                'Password must contain at least one special character (!@#$%^&*).'
            );
        }

        $userModel = new UserModel($this->conn);

        $user = $userModel->getUserByEmail($email);

        if (!$user) {
            throw new Exception('Email not found.');
        }

        // Hash new password
        $hashedPassword = hashPassword($newPassword);

        // Update password
        if (
            $userModel->updatePassword(
                $email,
                $hashedPassword
            )
        ) {
            return true;
        }

        throw new Exception(
            'Password reset failed. Please try again.'
        );
    }
}




// =========================
// HANDLE LOGOUT REQUEST
// =========================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['logout'])
) {

    $auth = new AuthController($conn);

    $auth->logout();
}



?>
