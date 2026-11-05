
<?php

require_once __DIR__ . "/../config/conn.php";
require_once __DIR__ . "/password_functions.php";


class UserController
{
    private $conn;


    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    // =========================================================
    // VALIDATE CAMPUS EMAIL
    // =========================================================

    private function validateCampusEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            throw new Exception(
                "Please enter a valid email address."
            );
        }


        if (!str_ends_with(strtolower($email), "@campus.edu.my")) {

            throw new Exception(
                "Please use your campus email address (@campus.edu.my)."
            );
        }
    }


    // =========================================================
    // REGISTER STUDENT
    // =========================================================

    public function registerStudent(
        $fullName,
        $email,
        $password,
        $confirmPassword
    ) {

        if (
            empty($fullName) ||
            empty($email) ||
            empty($password) ||
            empty($confirmPassword)
        ) {

            throw new Exception(
                "All fields are required."
            );
        }


        $fullName = trim($fullName);
        $email = trim($email);


        $this->validateCampusEmail($email);


        if (strlen($password) < 8) {

            throw new Exception(
                "Password must be at least 8 characters long."
            );
        }


        if ($password !== $confirmPassword) {

            throw new Exception(
                "Passwords do not match."
            );
        }


        // Check existing email

        $stmt = $this->conn->prepare("
            SELECT user_id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $stmt->close();

            throw new Exception(
                "Email already registered."
            );
        }

        $stmt->close();


        // Hash password

        $hashedPassword = hashPassword($password);


        // Create Reporter account

        $stmt = $this->conn->prepare("
            INSERT INTO users (
                full_name,
                email,
                password,
                role,
                user_type,
                department_id,
                account_status,
                availability_status,
                must_change_password
            )
            VALUES (
                ?,
                ?,
                ?,
                'Reporter',
                'Student',
                NULL,
                'Active',
                NULL,
                FALSE
            )
        ");


        $stmt->bind_param(
            "sss",
            $fullName,
            $email,
            $hashedPassword
        );


        if (!$stmt->execute()) {

            $error = $stmt->error;

            $stmt->close();

            throw new Exception(
                "Registration failed: " . $error
            );
        }


        $stmt->close();

        return true;
    }


    // =========================================================
    // CREATE ADMIN
    // =========================================================

    public function createAdmin($fullName, $email)
    {

        if (
            empty($fullName) ||
            empty($email)
        ) {

            throw new Exception(
                "All fields are required."
            );
        }


        $fullName = trim($fullName);
        $email = trim($email);


        // Validate campus email

        $this->validateCampusEmail($email);


        // Check existing email

        $stmt = $this->conn->prepare("
            SELECT user_id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");


        $stmt->bind_param(
            "s",
            $email
        );


        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows > 0) {

            $stmt->close();

            throw new Exception(
                "Email already registered."
            );
        }


        $stmt->close();


        // Default password

        $defaultPassword = "Testing1!";


        // Hash default password

        $hashedPassword = hashPassword(
            $defaultPassword
        );


        // Create Admin account

        $stmt = $this->conn->prepare("
            INSERT INTO users (
                full_name,
                email,
                password,
                role,
                user_type,
                department_id,
                account_status,
                availability_status,
                must_change_password
            )
            VALUES (
                ?,
                ?,
                ?,
                'Admin',
                'Staff',
                NULL,
                'Active',
                NULL,
                TRUE
            )
        ");


        $stmt->bind_param(
            "sss",
            $fullName,
            $email,
            $hashedPassword
        );


        if (!$stmt->execute()) {

            $error = $stmt->error;

            $stmt->close();

            throw new Exception(
                "Admin account creation failed: " . $error
            );
        }


        $stmt->close();

        return true;
    }
}


/*
|--------------------------------------------------------------------------
| STUDENT REGISTRATION
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["register_student"])
) {

    $userController = new UserController($conn);


    try {

        $userController->registerStudent(
            $_POST["name"] ?? "",
            $_POST["email"] ?? "",
            $_POST["password"] ?? "",
            $_POST["confirm_password"] ?? ""
        );


        session_start();


        $_SESSION["register_success"] =
            "Registration successful! Please login.";


        header(
            "Location: " .
            BASE_URL .
            "/app/view/login_page.php"
        );

        exit();


    } catch (Exception $e) {

        session_start();


        $_SESSION["register_error"] =
            $e->getMessage();


        $_SESSION["register_name"] =
            $_POST["name"] ?? "";


        $_SESSION["register_email"] =
            $_POST["email"] ?? "";


        header(
            "Location: " .
            BASE_URL .
            "/app/view/register.php"
        );

        exit();
    }
}


/*
|--------------------------------------------------------------------------
| CREATE ADMIN
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["create_admin"])
) {

    $userController = new UserController($conn);


    try {

        $userController->createAdmin(
            $_POST["full_name"] ?? "",
            $_POST["email"] ?? ""
        );


        session_start();


        $_SESSION["admin_success"] =
            "Admin account created successfully.";


        header(
            "Location: " .
            BASE_URL .
            "/app/view/dashboard.php?page=manage_accounts"
        );

        exit();


    } catch (Exception $e) {

        session_start();


        $_SESSION["admin_error"] =
            $e->getMessage();


        header(
            "Location: " .
            BASE_URL .
            "/app/view/dashboard.php?page=manage_accounts"
        );

        exit();
    }
}

?>

