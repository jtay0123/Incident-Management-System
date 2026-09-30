
<?php

session_start();

require_once '../config/config.php';
require_once '../config/conn.php';

$error = $_SESSION['register_error'] ?? '';
$success = $_SESSION['register_success'] ?? '';

$name = $_SESSION['register_name'] ?? '';
$email = $_SESSION['register_email'] ?? '';

unset($_SESSION['register_error']);
unset($_SESSION['register_success']);
unset($_SESSION['register_name']);
unset($_SESSION['register_email']);

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Register - Incident Management System</title>

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

    </style>

</head>


<body>

    <div class="layout">

        <div class="login-side">

            <div class="login-card">

                <img
                    src="<?php echo BASE_URL; ?>/assets/img/logo.jpg"
                    alt="logo"
                >

                <h3>Incident Management System</h3>

                <h5>Create Account</h5>


                <!-- Error Message -->

                <?php if ($error): ?>

                    <div class="alert alert-danger">

                        <?php echo $error; ?>

                    </div>

                <?php endif; ?>


                <!-- Success Message -->

                <?php if ($success): ?>

                    <div class="alert alert-success">

                        <?php echo htmlspecialchars($success); ?>

                    </div>

                <?php endif; ?>


                <!-- Registration Form -->

                <form
                    action="../controller/UserController.php"
                    method="POST"
                >


                    <!-- Full Name -->

                    <input
                        type="text"
                        name="name"
                        placeholder="Full Name"
                        value="<?php
                            echo htmlspecialchars($name);
                        ?>"
                        required
                    >


                    <!-- Email -->

                    <input
                        type="email"
                        name="email"
                        placeholder="Campus Email"
                        value="<?php
                            echo htmlspecialchars($email);
                        ?>"
                        required
                    >


                    <!-- Password -->

                    <input
                        type="password"
                        name="password"
                        id="reg_password"
                        placeholder="Password"
                        required
                    >


                    <!-- Password Requirements -->

                    <ul
                        class="password-requirements"
                        id="regRequirements"
                    >

                        <li id="regLength">
                            At least 8 characters
                        </li>

                        <li id="regUppercase">
                            At least 1 uppercase letter (A-Z)
                        </li>

                        <li id="regLowercase">
                            At least 1 lowercase letter (a-z)
                        </li>

                        <li id="regNumber">
                            At least 1 number (0-9)
                        </li>

                        <li id="regSpecial">
                            At least 1 special character (!@#$%^&*)
                        </li>

                    </ul>


                    <!-- Confirm Password -->

                    <input
                        type="password"
                        name="confirm_password"
                        id="reg_confirm"
                        placeholder="Confirm Password"
                        required
                    >


                    <!-- Register Button -->

                    <button
                        type="submit"
                        name="register_student"
                        class="main"
                    >
                        Register
                    </button>

                </form>


                <!-- Login Link -->

                <div class="links">

                    <a
                        href="<?php
                            echo BASE_URL;
                        ?>/app/view/login_page.php"
                    >
                        Already have an account? Login
                    </a>

                </div>

            </div>

        </div>

    </div>


    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function() {

                const passwordInput =
                    document.getElementById('reg_password');

                const confirmInput =
                    document.getElementById('reg_confirm');


                // =========================
                // PASSWORD REQUIREMENTS
                // =========================

                if (passwordInput) {

                    passwordInput.addEventListener(
                        'input',
                        function() {

                            const password = this.value;

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
                                'regLength'
                            ).className =
                                checks.length
                                    ? 'valid'
                                    : 'invalid';


                            document.getElementById(
                                'regUppercase'
                            ).className =
                                checks.uppercase
                                    ? 'valid'
                                    : 'invalid';


                            document.getElementById(
                                'regLowercase'
                            ).className =
                                checks.lowercase
                                    ? 'valid'
                                    : 'invalid';


                            document.getElementById(
                                'regNumber'
                            ).className =
                                checks.number
                                    ? 'valid'
                                    : 'invalid';


                            document.getElementById(
                                'regSpecial'
                            ).className =
                                checks.special
                                    ? 'valid'
                                    : 'invalid';

                        }
                    );

                }


                // =========================
                // CONFIRM PASSWORD
                // =========================

                if (confirmInput && passwordInput) {

                    confirmInput.addEventListener(
                        'input',
                        function() {

                            if (
                                this.value !==
                                passwordInput.value
                            ) {

                                this.style.borderColor =
                                    '#e74c3c';

                                this.style.boxShadow =
                                    '0 0 0 3px rgba(231, 76, 60, 0.1)';

                            } else {

                                this.style.borderColor =
                                    '#27ae60';

                                this.style.boxShadow =
                                    '0 0 0 3px rgba(39, 174, 96, 0.1)';

                            }

                        }
                    );

                }

            }
        );

    </script>

</body>

</html>
