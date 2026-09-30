<?php
session_start();
require_once '../config/config.php';
require_once '../config/conn.php';
require_once '../controller/AuthController.php';

$auth = new AuthController($conn);
$error = '';
$message = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
            try {

        $auth->login(
            $email,
            $password,
            isset($_POST['remember'])
        );

    } catch (Exception $e) {

        $error = $e->getMessage();
    }
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Incident Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/login_page.css">
</head>
<body>
    <div class="layout">
        <div class="login-side">
            <div class="login-card">
                <img src="<?php echo BASE_URL; ?>/assets/img/logo.jpg" alt="logo">
                <h3>Incident Management System</h3>
                <h5>Login</h5>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <input type="email" name="email" placeholder="Email" required 
                           value="<?php echo isset($_COOKIE['user_login']) ? htmlspecialchars($_COOKIE['user_login']) : ''; ?>">
                    <input type="password" name="password" placeholder="Password" required>
                    
                    <div class="checkbox-container">
                        <label for="remember">
                            <input type="checkbox" name="remember" id="remember" 
                                   <?php echo isset($_COOKIE['user_login']) ? 'checked' : ''; ?>>
                            <span>Remember Me</span>
                        </label>
                    </div>
                    
                    <button type="submit" name="login" class="main">Log In</button>
                </form>
                
                <div class="links">
                    <a href="<?php echo BASE_URL; ?>/app/view/register.php">Create Account</a>
                </div>
                <div class="links">
                    <a href="<?php echo BASE_URL; ?>/app/view/reset_password.php">Forgot password?</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>