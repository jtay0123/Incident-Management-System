<?php

class Admin
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    // ==========================================
    // GET ALL USERS
    // ==========================================
    public function getUsers()
    {
        $stmt = $this->conn->prepare("
            SELECT
                u.user_id,
                u.full_name,
                u.email,
                u.role,
                u.user_type,
                u.department_id,
                d.department_name AS department,
                u.account_status,
                u.availability_status,
                u.created_at
            FROM users u
            LEFT JOIN departments d
                ON u.department_id = d.department_id
            WHERE u.role != 'Super Admin' AND u.role != 'Admin'
            ORDER BY u.user_id DESC
        ");

        $stmt->execute();

        $result = $stmt->get_result();

        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        $stmt->close();

        return $users;
    }


    // ==========================================
    // GET ONE USER BY ID
    // ==========================================
    public function getUserById($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT
                u.user_id,
                u.full_name,
                u.email,
                u.role,
                u.user_type,
                u.department_id,
                d.department_name AS department,
                u.account_status,
                u.availability_status,
                u.created_at,
                u.updated_at,
                u.must_change_password
            FROM users u
            LEFT JOIN departments d
                ON u.department_id = d.department_id
            WHERE u.user_id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $userId);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();

        return $user ?: null;
    }


    // ==========================================
    // GET ACTIVE DEPARTMENTS
    // ==========================================
    public function getDepartments()
    {
        $stmt = $this->conn->prepare("
            SELECT
                department_id,
                department_name,
                description,
                status
            FROM departments
            WHERE status = 'Active'
            ORDER BY department_name ASC
        ");

        $stmt->execute();

        $result = $stmt->get_result();

        $departments = [];

        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }

        $stmt->close();

        return $departments;
    }


    // ==========================================
    // CHECK DEPARTMENT EXISTS
    // ==========================================
    private function departmentExists($departmentId)
    {
        if ($departmentId <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare("
            SELECT department_id
            FROM departments
            WHERE department_id = ?
              AND status = 'Active'
            LIMIT 1
        ");

        $stmt->bind_param("i", $departmentId);

        $stmt->execute();

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }


    // ==========================================
    // CREATE STAFF / LECTURER ACCOUNT
    // ==========================================
    public function createUser(
        $fullName,
        $email,
        $password,
        $role,
        $userType,
        $departmentId
    ) {
        $accountStatus = 'Active';
        $availabilityStatus = null;
        $mustChangePassword = 1;



        
            /*
             * Response Staff and lecture  must have
             * a valid active department.
             */
            if (!$this->departmentExists($departmentId)) {

                return false;

            }

        


        $stmt = $this->conn->prepare("
            INSERT INTO users
            (
                full_name,
                email,
                password,
                role,
                user_type,
                department_id,
                account_status,
                availability_status,
                must_change_password,
                created_at,
                updated_at
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");


        if (!$stmt) {
            return false;
        }


        /*
         * Parameter types:
         *
         * fullName             = s
         * email                = s
         * password             = s
         * role                 = s
         * userType             = s
         * departmentId         = i
         * accountStatus        = s
         * availabilityStatus   = s
         * mustChangePassword    = i
         */
        $stmt->bind_param(
        "sssssissi",
        $fullName,
        $email,
        $password,
        $role,
        $userType,
        $departmentId,
        $accountStatus,
        $availabilityStatus,
        $mustChangePassword
    );


        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


   // ==========================================
// UPDATE ACCOUNT STATUS
// ==========================================
    public function updateUserStatus($userId, $status)
    {
        $allowedStatuses = [
            'Active',
            'Inactive',
            'Suspended'
        ];

        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE users
            SET account_status = ?,
                updated_at = NOW()
            WHERE user_id = ?
            AND role != 'Super Admin'
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $status,
            $userId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // UPDATE USER
    // ==========================================
    public function updateUser(
        $userId,
        $fullName,
        $email,
        $role,
        $userType,
        $departmentId
    ) {

        /*
        * User must have a valid active department.
        */
        if (!$this->departmentExists($departmentId)) {

            return false;

        }


        /*
        * Only non-Super Admin and admin users can be updated.
        */
        $stmt = $this->conn->prepare("
            UPDATE users
            SET
                full_name = ?,
                email = ?,
                role = ?,
                user_type = ?,
                department_id = ?,
                updated_at = NOW()
            WHERE user_id = ?
            AND role != 'Super Admin'
            AND role != 'Admin'
        ");


        if (!$stmt) {

            return false;

        }


        $stmt->bind_param(
            "ssssii",
            $fullName,
            $email,
            $role,
            $userType,
            $departmentId,
            $userId
        );


        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }
    // ==========================================
    // DELETE USER
    // ==========================================
    public function deleteUser($userId)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM users
            WHERE user_id = ?
              AND role != 'Super Admin'
        ");


        if (!$stmt) {
            return false;
        }


        $stmt->bind_param(
            "i",
            $userId
        );


        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // CHECK DUPLICATE EMAIL
    // ==========================================
    public function emailExists($email)
    {
        $stmt = $this->conn->prepare("
            SELECT user_id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");


        if (!$stmt) {
            return false;
        }


        $stmt->bind_param(
            "s",
            $email
        );


        $stmt->execute();

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }


    // ==========================================
    // GET USER STATISTICS
    // ==========================================
    public function getUserStats()
    {
        $stats = [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'suspended' => 0
        ];


        $stmt = $this->conn->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(account_status = 'Active') AS active,
                SUM(account_status = 'Inactive') AS inactive,
                SUM(account_status = 'Suspended') AS suspended
            FROM users
            WHERE role != 'Super Admin'
        ");


        if (!$stmt) {
            return $stats;
        }


        $stmt->execute();

        $result = $stmt->get_result();

        $row = $result->fetch_assoc();


        if ($row) {

            $stats['total'] =
                (int)$row['total'];

            $stats['active'] =
                (int)$row['active'];

            $stats['inactive'] =
                (int)$row['inactive'];

            $stats['suspended'] =
                (int)$row['suspended'];

        }


        $stmt->close();

        return $stats;
    }
}