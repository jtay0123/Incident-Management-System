<?php
    require_once 'config.php';
    
    try {
        // database connection
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if(!$conn){
            die("Connection failed: " . mysqli_connect_error());
        }
    }
    catch(Exception $e){
        error_log($e->getMessage());
        exit('Database connection failed. Please try again.');
    }
?>