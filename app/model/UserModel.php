<?php

class UserModel {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    // Get user by email for login
    public function getUserByEmail($email){
        $stmt = $this->conn->prepare("
            SELECT
                user_id,
                full_name,
                email,
                password,
                role,
                user_type,
                department_id,
                account_status,
                availability_status,
                must_change_password
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();

        return $row ?: null;
    }


// Update user password
    public function updatePassword($email, $hashedPassword){

        $stmt = $this->conn->prepare("
            UPDATE users
            SET
                password = ?,
                must_change_password = 0
            WHERE email = ?
        ");

        $stmt->bind_param(
            "ss",
            $hashedPassword,
            $email
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }
}
?>