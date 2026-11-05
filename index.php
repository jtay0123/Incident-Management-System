<?php
    session_start();
    require_once 'app/config/config.php';

    //if the session does not contain the user_id, redirect to login page
    if(!isset($_SESSION['user_id'])){
        header('Location: app/view/login_page.php');
        exit();
    }

    header('Location: app/view/dashboard.php');
    exit();
?>