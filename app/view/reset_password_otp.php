
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../config/conn.php';
require_once '../controller/AuthController.php';

$auth = new AuthController($conn);

$error = '';
$message = '';
$success = false;
$otpToShow = null;

// Determine current step
$otpRequested = !empty($_SESSION['reset_email']);

$otpVerified = (
    !empty($_SESSION['reset_verified_user_id']) &&
    !empty($_SESSION['reset_verified_otp_id'])
);

// =========================================================
// HANDLE FORM SUBMISSIONS
// =========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---------------------------------
    // STEP 1: REQUEST OR REGENERATE OTP
    // ---------------------------------
    if (isset($_POST['request_otp'])) {

        // Use the submitted email, or the existing session email
        // when the user clicks Generate New OTP.
        $email = trim(
            $_POST['reset_email'] ??
            $_SESSION['reset_email'] ??
            ''
        );

        try {
            $auth->requestPasswordResetOtp($email);

            // Retrieve the newly generated verification code
            $otpToShow = $_SESSION['demo_reset_otp_display'] ?? null;

            unset($_SESSION['demo_reset_otp_display']);

            $otpRequested = true;
            $otpVerified = false;

            $message = 'A new verification code has been generated.';

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // ---------------------------------
    // STEP 2: VERIFY OTP
    // ---------------------------------
    elseif (isset($_POST['verify_otp'])) {

        $email = $_SESSION['reset_email'] ?? '';
        $otp = trim($_POST['otp'] ?? '');

        try {
            $auth->verifyPasswordResetOtp($email, $otp);

            $otpRequested = true;
            $otpVerified = true;

            $message = 'Verification successful. You can now reset your password.';

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // ---------------------------------
    // STEP 3: RESET PASSWORD
    // ---------------------------------
    elseif (isset($_POST['reset_password'])) {

        $email = $_SESSION['reset_email'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $otpVerified = (
            !empty($_SESSION['reset_verified_user_id']) &&
            !empty($_SESSION['reset_verified_otp_id'])
        );

        $passwordErrors = [];

        if (!$otpVerified) {

            $error = 'Please verify your OTP before resetting your password.';

        } else {

            if (strlen($newPassword) < 8) {
                $passwordErrors[] =
                    'Password must be at least 8 characters long.';
            }

            if (!preg_match('/[A-Z]/', $newPassword)) {
                $passwordErrors[] =
                    'Password must contain at least one uppercase letter (A-Z).';
            }

            if (!preg_match('/[a-z]/', $newPassword)) {
                $passwordErrors[] =
                    'Password must contain at least one lowercase letter (a-z).';
            }

            if (!preg_match('/[0-9]/', $newPassword)) {
                $passwordErrors[] =
                    'Password must contain at least one number (0-9).';
            }

            if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
                $passwordErrors[] =
                    'Password must contain at least one special character.';
            }

            if ($newPassword !== $confirmPassword) {
                $passwordErrors[] = 'Passwords do not match.';
            }

            if (!empty($passwordErrors)) {

                $error = implode("\n", $passwordErrors);

            } else {

                try {
                    if (
                        $auth->resetPassword(
                            $email,
                            $newPassword,
                            $confirmPassword
                        )
                    ) {
                        $message =
                            'Password reset successful! Please log in with your new password.';

                        $success = true;
                        $otpRequested = false;
                        $otpVerified = false;
                    }

                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
    }

    // ---------------------------------
    // CANCEL / START AGAIN
    // ---------------------------------
    elseif (isset($_POST['cancel_reset'])) {

        unset(
            $_SESSION['reset_email'],
            $_SESSION['reset_verified_user_id'],
            $_SESSION['reset_verified_otp_id'],
            $_SESSION['demo_reset_otp_display']
        );

        $otpRequested = false;
        $otpVerified = false;
        $message = '';
    }
}

// Recalculate current step after handling the submission.
// Preserve the success screen after a successful reset.
if (!$success) {

    $otpRequested = !empty($_SESSION['reset_email']);

    $otpVerified = (
        !empty($_SESSION['reset_verified_user_id']) &&
        !empty($_SESSION['reset_verified_otp_id'])
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Password - Incident Management System</title>

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
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .step-description {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .verification-notice {
            font-size: 12px;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe3ec;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: left;
        }

        .step-label {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #e9ecef;
            font-size: 12px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>
<div class="layout">
    <div class="login-side">

        <?php if (!$success): ?>

        <div class="login-card">

            <button
                type="button"
                class="back-button"
                onclick="window.location.href='<?php echo BASE_URL; ?>/app/view/login_page.php'"
            >
                ← Back to Login
            </button>

            <img
                src="<?php echo BASE_URL; ?>/assets/img/logo.jpg"
                alt="Logo"
            >

            <h2>Reset Password</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            'UTF-8'
                        )
                    );
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($message && !$success): ?>
                <div class="alert alert-success">
                    <?php
                    echo htmlspecialchars(
                        $message,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </div>
            <?php endif; ?>


            <!-- STEP 1: ENTER EMAIL -->
            <?php if (!$otpRequested): ?>

                <div class="step-label">Step 1 of 3</div>

                <p class="step-description">
                    Enter your registered campus email to begin password recovery.
                </p>

                <form method="POST">

                    <input
                        type="email"
                        name="reset_email"
                        placeholder="Enter your campus email"
                        autocomplete="email"
                        required
                    >

                    <button
                        type="submit"
                        name="request_otp"
                        class="main"
                    >
                        Get Verification Code
                    </button>

                </form>


            <!-- STEP 2: VERIFY OTP -->
            <?php elseif (!$otpVerified): ?>

                <div class="step-label">Step 2 of 3</div>

                <p class="step-description">
                    Enter the six-digit verification code to continue.
                </p>

                <div class="verification-notice">
                    The verification code will appear in a popup after it is generated.
                </div>

                <form method="POST">

                    <input
                        type="text"
                        name="otp"
                        placeholder="Enter 6-digit OTP"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        minlength="6"
                        maxlength="6"
                        autocomplete="one-time-code"
                        required
                    >

                    <button
                        type="submit"
                        name="verify_otp"
                        class="main"
                    >
                        Verify OTP
                    </button>

                </form>

                <!-- FIX: Include the email when generating another OTP -->
                <form method="POST" style="margin-top: 12px;">

                    <input
                        type="hidden"
                        name="reset_email"
                        value="<?php
                            echo htmlspecialchars(
                                $_SESSION['reset_email'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            );
                        ?>"
                    >

                    <button
                        type="submit"
                        name="request_otp"
                        class="main"
                        formnovalidate
                    >
                        Generate New OTP
                    </button>

                </form>

                <form method="POST" style="margin-top: 8px;">

                    <button
                        type="submit"
                        name="cancel_reset"
                        class="main"
                        formnovalidate
                    >
                        Cancel and Start Again
                    </button>

                </form>


            <!-- STEP 3: SET NEW PASSWORD -->
            <?php else: ?>

                <div class="step-label">Step 3 of 3</div>

                <p class="step-description">
                    Verification successful. Enter your new password below.
                </p>

                <form method="POST">

                    <input
                        type="password"
                        name="new_password"
                        id="reset_password"
                        placeholder="New Password"
                        autocomplete="new-password"
                        required
                    >

                    <ul class="password-requirements">
                        <li id="resetLength">At least 8 characters</li>
                        <li id="resetUppercase">At least 1 uppercase letter (A-Z)</li>
                        <li id="resetLowercase">At least 1 lowercase letter (a-z)</li>
                        <li id="resetNumber">At least 1 number (0-9)</li>
                        <li id="resetSpecial">At least 1 special character (!@#$%^&*)</li>
                    </ul>

                    <input
                        type="password"
                        name="confirm_password"
                        id="reset_confirm"
                        placeholder="Confirm New Password"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="submit"
                        name="reset_password"
                        class="main"
                    >
                        Reset Password
                    </button>

                </form>

                <form method="POST" style="margin-top: 12px;">

                    <button
                        type="submit"
                        name="cancel_reset"
                        class="main"
                        formnovalidate
                    >
                        Cancel
                    </button>

                </form>

            <?php endif; ?>

        </div>

        <?php else: ?>

        <div class="success-card">

            <img
                src="<?php echo BASE_URL; ?>/assets/img/logo.jpg"
                alt="Logo"
            >

            <h2>Password Reset Successful!</h2>

            <div class="alert alert-success">
                <?php
                echo htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </div>

            <button
                type="button"
                class="main"
                onclick="window.location.href='<?php echo BASE_URL; ?>/app/view/login_page.php'"
            >
                Return to Login
            </button>

        </div>

        <?php endif; ?>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    // Display the newly generated verification code once.
    <?php if ($otpToShow !== null): ?>
        alert(
            'Your verification code: <?php
                echo htmlspecialchars(
                    $otpToShow,
                    ENT_QUOTES,
                    'UTF-8'
                );
            ?>'
        );
    <?php endif; ?>


    const passwordInput = document.getElementById('reset_password');
    const confirmInput = document.getElementById('reset_confirm');

    if (passwordInput) {

        function updatePasswordRequirements() {

            const password = passwordInput.value;

            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };

            const elements = {
                length: document.getElementById('resetLength'),
                uppercase: document.getElementById('resetUppercase'),
                lowercase: document.getElementById('resetLowercase'),
                number: document.getElementById('resetNumber'),
                special: document.getElementById('resetSpecial')
            };

            Object.keys(checks).forEach(function (key) {

                if (elements[key]) {
                    elements[key].className =
                        checks[key] ? 'valid' : 'invalid';
                }

            });
        }

        passwordInput.addEventListener(
            'input',
            updatePasswordRequirements
        );

        updatePasswordRequirements();
    }

    if (confirmInput && passwordInput) {

        confirmInput.addEventListener('input', function () {

            if (this.value !== passwordInput.value) {
                this.style.borderColor = '#e74c3c';
            } else {
                this.style.borderColor = '#27ae60';
            }

        });
    }
});
</script>

</body>
</html>
