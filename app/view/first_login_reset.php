
<?php

session_start();

require_once '../config/config.php';
require_once '../config/conn.php';
require_once '../controller/AuthController.php';

$auth = new AuthController($conn);

$error = '';
$message = '';
$success = false;


// =========================================================
// CHECK USER SESSION
// =========================================================

$email = $_SESSION['user_email'] ?? '';


// =========================================================
// HANDLE PASSWORD RESET
// =========================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['reset_password'])
) {

    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $passwordErrors = [];


    // Check session email

    if (empty($email)) {

        $error =
            "User session not found. Please login again.";

    } else {

        // =============================================
        // PASSWORD VALIDATION
        // =============================================

        if (strlen($newPassword) < 8) {

            $passwordErrors[] =
                "Password must be at least 8 characters long";
        }

        if (!preg_match('/[A-Z]/', $newPassword)) {

            $passwordErrors[] =
                "Password must contain at least one uppercase letter (A-Z)";
        }

        if (!preg_match('/[a-z]/', $newPassword)) {

            $passwordErrors[] =
                "Password must contain at least one lowercase letter (a-z)";
        }

        if (!preg_match('/[0-9]/', $newPassword)) {

            $passwordErrors[] =
                "Password must contain at least one number (0-9)";
        }

        if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {

            $passwordErrors[] =
                "Password must contain at least one special character (!@#$%^&*)";
        }

        if ($newPassword !== $confirmPassword) {

            $passwordErrors[] =
                "Passwords do not match";
        }


        // =============================================
        // PROCESS PASSWORD CHANGE
        // =============================================

        if (!empty($passwordErrors)) {

            $error = implode("<br>", $passwordErrors);

        } else {

            try {

                if (
                    $auth->resetFirstLoginPassword(
                        $newPassword,
                        $confirmPassword
                    )
                ) {

                    $message =
                        "Password changed successfully! Please login with your new password.";

                    $success = true;

                    // Remove first-login requirement
                    $_SESSION['password_changed'] = true;
                }

            } catch (Exception $e) {

                $error = $e->getMessage();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Change Password - Incident Management System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>/assets/css/login_page.css"
    >

    <style>

        .password-requirements {

            text-align: left;

            font-size: 12px;

            color: #7f8c8d;

            padding: 0;

            margin: -10px 0 10px 0;

            list-style: none;
        }


        .password-requirements li {

            padding: 3px 0;
        }


        .password-requirements li.valid {

            color: #27ae60;
        }


        .password-requirements li.invalid {

            color: #e74c3c;
        }


        .password-requirements li::before {

            content: "✗ ";
        }


        .password-requirements li.valid::before {

            content: "✓ ";

            color: #27ae60;
        }


        .password-requirements li.invalid::before {

            content: "✗ ";

            color: #e74c3c;
        }


        .success-card {

            background: #ffffff;

            padding: 40px 30px;

            border-radius: 16px;

            text-align: center;

            width: 480px;

            max-width: 100%;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.3);
        }


        .account-email {

            background: #f8f9fa;

            border: 1px solid #e0e0e0;

            border-radius: 8px;

            padding: 10px;

            margin-bottom: 15px;

            text-align: left;

            font-size: 14px;
        }

    </style>

</head>


<body>

    <div class="layout">

        <div class="login-side">


            <?php if (!$success): ?>


            <!-- =====================================================
                 CHANGE PASSWORD FORM
            ====================================================== -->

            <div class="login-card">


                <button
                    class="back-button"
                    onclick="window.location.href='<?php echo BASE_URL; ?>/app/view/login_page.php'"
                >
                    ← Back to Login
                </button>


                <img
                    src="<?php echo BASE_URL; ?>/assets/img/logo.jpg"
                    alt="Logo"
                >


                <h2>
                    Change Password
                </h2>


                <?php if ($error): ?>

                    <div class="alert alert-danger">

                        <?php echo $error; ?>

                    </div>

                <?php endif; ?>


                <!-- ACCOUNT EMAIL -->

                <div class="account-email">

                    <strong>Account:</strong><br>

                    <?php
                    echo htmlspecialchars($email);
                    ?>

                </div>


                <form
                    action=""
                    method="POST"
                >


                    <!-- NEW PASSWORD -->

                    <input
                        type="password"
                        name="new_password"
                        id="reset_password"
                        placeholder="New Password"
                        required
                    >


                    <!-- PASSWORD REQUIREMENTS -->

                    <ul
                        class="password-requirements"
                        id="resetRequirements"
                    >

                        <li id="resetLength">
                            At least 8 characters
                        </li>

                        <li id="resetUppercase">
                            At least 1 uppercase letter (A-Z)
                        </li>

                        <li id="resetLowercase">
                            At least 1 lowercase letter (a-z)
                        </li>

                        <li id="resetNumber">
                            At least 1 number (0-9)
                        </li>

                        <li id="resetSpecial">
                            At least 1 special character (!@#$%^&*)
                        </li>

                    </ul>


                    <!-- CONFIRM PASSWORD -->

                    <input
                        type="password"
                        name="confirm_password"
                        id="reset_confirm"
                        placeholder="Confirm New Password"
                        required
                    >


                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        name="reset_password"
                        class="main"
                    >
                        Change Password
                    </button>


                </form>

            </div>


            <?php else: ?>


            <!-- =====================================================
                 SUCCESS MESSAGE
            ====================================================== -->

            <div class="success-card">


                <img
                    src="<?php echo BASE_URL; ?>/assets/img/logo.jpg"
                    alt="Logo"
                >


                <h2>
                    Password Changed Successfully!
                </h2>


                <div class="alert alert-success">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>


                <button
                    class="main"
                    onclick="window.location.href='<?php echo BASE_URL; ?>/app/view/login_page.php'"
                >
                    Return to Login
                </button>


            </div>


            <?php endif; ?>


        </div>

    </div>


    <!-- =========================================================
         PASSWORD VALIDATION JAVASCRIPT
    ========================================================== -->

    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function() {

                const passwordInput =
                    document.getElementById(
                        'reset_password'
                    );

                const confirmInput =
                    document.getElementById(
                        'reset_confirm'
                    );


                // =============================================
                // PASSWORD REQUIREMENTS
                // =============================================

                if (passwordInput) {

                    passwordInput.addEventListener(
                        'input',
                        function() {

                            const password =
                                this.value;


                            const checks = {

                                length:
                                    password.length >= 8,

                                uppercase:
                                    /[A-Z]/.test(password),

                                lowercase:
                                    /[a-z]/.test(password),

                                number:
                                    /[0-9]/.test(password),

                                special:
                                    /[^A-Za-z0-9]/.test(password)

                            };


                            document.getElementById(
                                'resetLength'
                            ).className =
                                checks.length
                                    ? 'valid'
                                    : 'invalid';


                            document.getElementById(
                                'resetUppercase'
                            ).className =
                                checks.uppercase
                                    ? 'valid'
                                    : 'invalid';


                            document.getElementById(
                                'resetLowercase'
                            ).className =
                                checks.lowercase
                                    ? 'valid'
                                    : 'invalid';


                            document.getElementById(
                                'resetNumber'
                            ).className =
                                checks.number
                                    ? 'valid'
                                    : 'invalid';


                            document.getElementById(
                                'resetSpecial'
                            ).className =
                                checks.special
                                    ? 'valid'
                                    : 'invalid';

                        }
                    );

                }


                // =============================================
                // CONFIRM PASSWORD
                // =============================================

                if (
                    confirmInput &&
                    passwordInput
                ) {

                    confirmInput.addEventListener(
                        'input',
                        function() {

                            if (
                                this.value !==
                                passwordInput.value
                            ) {

                                this.style.borderColor =
                                    '#e74c3c';

                            } else {

                                this.style.borderColor =
                                    '#27ae60';

                            }

                        }
                    );

                }

            }
        );

    </script>

</body>

</html>

