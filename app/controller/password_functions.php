<?php
    include "../config/conn.php";

    //function to hash password
    //pass user input as arguement
    //hash the password
    function hashPassword(string $password) {
        //set the minimum length of password
        if(strlen($password) < 8){
            throw new InvalidArgumentException("Password must be at least 8 characters long.");
        }

        $hash = password_hash($password, PASSWORD_ARGON2I);

        return $hash;
    }
    
    //function to verify password
    //retrieve the hashed password from the database
    function isPasswordCorrect(string $password, string $hashedPassword){
        return password_verify($password, $hashedPassword);
    }
?>