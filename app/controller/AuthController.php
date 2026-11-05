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
            $_SESSION['last_activity'] = time();

            // Record login session and last activity
            $stmt = $this->conn->prepare("
                INSERT INTO sessions (
                    user_id,
                    login_time,
                    last_activity,
                    status
                )
                VALUES (?, NOW(), NOW(), 'Active')
            ");

            $stmt->bind_param("i", $row['user_id']);
            $stmt->execute();

            $_SESSION['session_id'] = $this->conn->insert_id;

            $stmt->close();

            // Force first password change for designated user types
            if (
                $row['must_change_password'] &&
                in_array(
                    $row['user_type'],
                    ['Maintenance', 'Lecturer', 'Staff', 'IT Support', 'Cleaning', 'Security']
                )
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

        // Clear session data
        $_SESSION = [];

        // Clear session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

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
    // GENERATE OTP
    // Maximum 3 requests within 15 minutes
    // =========================
    public function requestPasswordResetOtp($email)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        if (!str_ends_with($email, '@campus.edu.my')) {
            throw new Exception('Please use your campus email address.');
        }

        $userModel = new UserModel($this->conn);
        $user = $userModel->getUserByEmail($email);

        if (!$user) {
            throw new Exception('Email not found.');
        }

        $userId = (int) $user['user_id'];

        // 1. Check whether the user is locked out.
        $stmt = $this->conn->prepare("
            SELECT
                locked_until,
                (locked_until > NOW()) AS is_locked
            FROM password_reset_otps
            WHERE user_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $latestRecord = $result->fetch_assoc();
        $stmt->close();

        if ($latestRecord && (int) $latestRecord['is_locked'] === 1) {
            throw new Exception(
                'Too many incorrect OTP attempts. Please try again after 15 minutes.'
            );
        }

        // 2. Count requests made within the last 15 minutes.
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS request_count
            FROM password_reset_requests
            WHERE user_id = ?
              AND requested_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $requestData = $result->fetch_assoc();
        $stmt->close();

        if ((int) $requestData['request_count'] >= 3) {
            throw new Exception(
                'You have requested OTP too many times. Please try again after 15 minutes.'
            );
        }

        // 3. Generate and hash a six-digit OTP.
        $otp = (string) random_int(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);

        // 4. Invalidate any previous OTP for this user.
        $stmt = $this->conn->prepare("
            DELETE FROM password_reset_otps
            WHERE user_id = ?
        ");

        $stmt->bind_param("i", $userId);

        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception('Unable to generate OTP. Please try again.');
        }

        $stmt->close();

        // 5. Save the new OTP. It expires after 5 minutes.
        $stmt = $this->conn->prepare("
            INSERT INTO password_reset_otps (
                user_id,
                otp_hash,
                expires_at,
                attempts,
                locked_until
            )
            VALUES (
                ?,
                ?,
                DATE_ADD(NOW(), INTERVAL 5 MINUTE),
                0,
                NULL
            )
        ");

        $stmt->bind_param("is", $userId, $otpHash);

        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception('Unable to generate OTP. Please try again.');
        }

        $stmt->close();

        // 6. Record this accepted OTP request.
        $stmt = $this->conn->prepare("
            INSERT INTO password_reset_requests (user_id)
            VALUES (?)
        ");

        $stmt->bind_param("i", $userId);

        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception('Unable to record OTP request.');
        }

        $stmt->close();

        // 7. Clear previous verification state.
        unset(
            $_SESSION['reset_verified_user_id'],
            $_SESSION['reset_verified_otp_id']
        );

        $_SESSION['reset_email'] = $email;

        // Local demonstration only; no email is sent.
        $_SESSION['demo_reset_otp_display'] = $otp;

        return true;
    }





    // =========================
    // VERIFY SIMULATED OTP
    // =========================
    
    
    // =========================
    // VERIFY OTP
    // 5 incorrect attempts = 15-minute lockout
    // =========================
    public function verifyPasswordResetOtp($email, $otp)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = strtolower(trim($email));
        $otp = trim($otp);

        if (
            empty($_SESSION['reset_email']) ||
            $_SESSION['reset_email'] !== $email
        ) {
            throw new Exception('Please request a new OTP first.');
        }

        if (!preg_match('/^\d{6}$/', $otp)) {
            throw new Exception('Please enter a valid six-digit OTP.');
        }

        $userModel = new UserModel($this->conn);
        $user = $userModel->getUserByEmail($email);

        if (!$user) {
            throw new Exception('Email not found.');
        }

        $userId = (int) $user['user_id'];

        // Get the latest OTP record.
        $stmt = $this->conn->prepare("
            SELECT
                id,
                otp_hash,
                attempts,
                verified_at,
                expires_at,
                locked_until,
                (expires_at > NOW()) AS is_valid_time,
                (locked_until > NOW()) AS is_locked
            FROM password_reset_otps
            WHERE user_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $record = $result->fetch_assoc();
        $stmt->close();

        if (!$record) {
            throw new Exception('No OTP found. Please request a new OTP.');
        }

        // Check lockout before checking OTP validity.
        if ((int) $record['is_locked'] === 1) {
            throw new Exception(
                'Too many incorrect OTP attempts. Please try again after 15 minutes.'
            );
        }

        if ((int) $record['attempts'] >= 5) {
            throw new Exception(
                'Too many incorrect attempts. Please request a new OTP.'
            );
        }

        if ((int) $record['is_valid_time'] !== 1) {
            throw new Exception('OTP has expired. Please request a new one.');
        }

        if ($record['verified_at'] !== null) {
            throw new Exception('This OTP has already been used.');
        }

        $otpId = (int) $record['id'];

        // Incorrect OTP: increase attempts and lock after the fifth failure.
        if (!password_verify($otp, $record['otp_hash'])) {
            $stmt = $this->conn->prepare("
                UPDATE password_reset_otps
                SET
                    attempts = attempts + 1,
                    locked_until = CASE
                        WHEN attempts + 1 >= 5
                        THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                        ELSE locked_until
                    END
                WHERE id = ?
                  AND user_id = ?
                  AND attempts < 5
                  AND verified_at IS NULL
                  AND expires_at > NOW()
                  AND (locked_until IS NULL OR locked_until <= NOW())
            ");

            $stmt->bind_param("ii", $otpId, $userId);
            $stmt->execute();
            $stmt->close();

            if (((int) $record['attempts'] + 1) >= 5) {
                throw new Exception(
                    'You have entered the wrong OTP 5 times. Please try again after 15 minutes.'
                );
            }

            throw new Exception('Incorrect OTP. Please try again.');
        }

        // Correct OTP: mark it as verified.
        $stmt = $this->conn->prepare("
            UPDATE password_reset_otps
            SET verified_at = NOW()
            WHERE id = ?
              AND user_id = ?
              AND verified_at IS NULL
              AND expires_at > NOW()
              AND attempts < 5
              AND (locked_until IS NULL OR locked_until <= NOW())
        ");

        $stmt->bind_param("ii", $otpId, $userId);
        $stmt->execute();

        $updated = $stmt->affected_rows;
        $stmt->close();

        if ($updated !== 1) {
            throw new Exception(
                'OTP could not be verified. Please request a new OTP.'
            );
        }

        // Allow the password-reset method to identify this verified OTP.
        $_SESSION['reset_verified_user_id'] = $userId;
        $_SESSION['reset_verified_otp_id'] = $otpId;

        return true;
    }

    
    // =====================================================
    // FIRST LOGIN PASSWORD RESET (NO OTP REQUIRED)
    // =====================================================
    public function resetFirstLoginPassword(
        $newPassword,
        $confirmPassword
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Require an authenticated user session.
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $email = strtolower(trim($_SESSION['user_email'] ?? ''));

        if ($userId <= 0 || $email === '') {
            throw new Exception('Session expired. Please login again.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception('Passwords do not match.');
        }

        // Apply the same password requirements.
        if (strlen($newPassword) < 8) {
            throw new Exception('Password must be at least 8 characters long.');
        }

        if (!preg_match('/[A-Z]/', $newPassword)) {
            throw new Exception('Password must contain at least one uppercase letter (A-Z).');
        }

        if (!preg_match('/[a-z]/', $newPassword)) {
            throw new Exception('Password must contain at least one lowercase letter (a-z).');
        }

        if (!preg_match('/[0-9]/', $newPassword)) {
            throw new Exception('Password must contain at least one number (0-9).');
        }

        if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            throw new Exception('Password must contain at least one special character.');
        }

        $userModel = new UserModel($this->conn);
        $user = $userModel->getUserByEmail($email);

        // Ensure the account matches the authenticated session.
        if (
            !$user ||
            (int) $user['user_id'] !== $userId
        ) {
            throw new Exception('Invalid user session. Please login again.');
        }

        // Only designated user types can use this first-login flow.
        $allowedTypes = [
            'Maintenance',
            'Lecturer',
            'Staff',
            'IT Support',
            'Cleaning',
            'Security'
        ];

        if (
            empty($user['must_change_password']) ||
            !in_array($user['user_type'], $allowedTypes, true)
        ) {
            throw new Exception('First-login password change is not required for this account.');
        }

        if ($user['account_status'] !== 'Active') {
            throw new Exception('Your account is not active. Please contact your administrator.');
        }

        $hashedPassword = hashPassword($newPassword);

        // updatePassword() should also set must_change_password = 0.
        if (!$userModel->updatePassword($email, $hashedPassword)) {
            throw new Exception('Password change failed. Please try again.');
        }

        return true;
    }



    
    // =========================
    // RESET PASSWORD
    // =========================
    public function resetPassword(
        $email,
        $newPassword,
        $confirmPassword
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (
            empty($email) ||
            empty($newPassword) ||
            empty($confirmPassword)
        ) {
            throw new Exception('All fields are required.');
        }

        $email = strtolower(trim($email));

        // Require server-side OTP verification
        if (
            empty($_SESSION['reset_email']) ||
            $_SESSION['reset_email'] !== $email ||
            empty($_SESSION['reset_verified_user_id']) ||
            empty($_SESSION['reset_verified_otp_id'])
        ) {
            throw new Exception('Please verify your OTP before resetting your password.');
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception('Passwords do not match.');
        }

        if (strlen($newPassword) < 8) {
            throw new Exception('Password must be at least 8 characters long.');
        }

        if (!preg_match('/[A-Z]/', $newPassword)) {
            throw new Exception('Password must contain at least one uppercase letter (A-Z).');
        }

        if (!preg_match('/[a-z]/', $newPassword)) {
            throw new Exception('Password must contain at least one lowercase letter (a-z).');
        }

        if (!preg_match('/[0-9]/', $newPassword)) {
            throw new Exception('Password must contain at least one number (0-9).');
        }

        if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            throw new Exception('Password must contain at least one special character.');
        }

        $userModel = new UserModel($this->conn);
        $user = $userModel->getUserByEmail($email);

        if (
            !$user ||
            (int) $user['user_id'] !== (int) $_SESSION['reset_verified_user_id']
        ) {
            throw new Exception('Invalid password reset session. Please start again.');
        }

        // Confirm that this OTP was verified recently
        $stmt = $this->conn->prepare("
            SELECT id
            FROM password_reset_otps
            WHERE id = ?
              AND user_id = ?
              AND verified_at IS NOT NULL
              AND verified_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            LIMIT 1
        ");

        $stmt->bind_param(
            "ii",
            $_SESSION['reset_verified_otp_id'],
            $user['user_id']
        );

        $stmt->execute();
        $result = $stmt->get_result();
        $verifiedRecord = $result->fetch_assoc();
        $stmt->close();

        if (!$verifiedRecord) {
            throw new Exception('Verification expired. Please request a new OTP.');
        }

        // Hash and update password
        $hashedPassword = hashPassword($newPassword);

        if (!$userModel->updatePassword($email, $hashedPassword)) {
            throw new Exception('Password reset failed. Please try again.');
        }


        $stmt = $this->conn->prepare("
            DELETE FROM password_reset_otps
            WHERE id = ? AND user_id = ?
        ");

        
        // Delete only the OTP used for this successful reset.
        $otpId = (int) $_SESSION['reset_verified_otp_id'];
        $userId = (int) $user['user_id'];

        $stmt = $this->conn->prepare("
            DELETE FROM password_reset_otps
            WHERE id = ?
              AND user_id = ?
        ");

        $stmt->bind_param("ii", $otpId, $userId);
        $stmt->execute();
        $stmt->close();

        // Clear reset-related session data.
        unset(
            $_SESSION['reset_email'],
            $_SESSION['demo_reset_otp_display'],
            $_SESSION['reset_verified_user_id'],
            $_SESSION['reset_verified_otp_id']
        );


        return true;
    }
}

// =========================
// HANDLE LOGOUT REQUEST
// =========================
if (
    isset($_SERVER['REQUEST_METHOD']) &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['logout'])
) {

    $auth = new AuthController($conn);
    $auth->logout();
}

