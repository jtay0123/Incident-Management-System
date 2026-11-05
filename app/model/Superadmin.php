
<?php

class SuperAdminModel
{
    private $conn;


    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    // =========================================================
    // GET ADMIN STATISTICS
    // =========================================================

    public function getStats()
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
            WHERE role = 'Admin'
        ");


        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            $stats['total'] = (int) $row['total'];
            $stats['active'] = (int) $row['active'];
            $stats['inactive'] = (int) $row['inactive'];
            $stats['suspended'] = (int) $row['suspended'];
        }


        $stmt->close();


        return $stats;
    }


    // =========================================================
    // GET ALL ADMIN ACCOUNTS
    // =========================================================

    public function getAllAdmin()
    {
        $stmt = $this->conn->prepare("
            SELECT
                user_id,
                full_name,
                email,
                user_type,
                account_status,
                created_at
            FROM users
            WHERE role = 'Admin'
            ORDER BY user_id ASC
        ");


        $stmt->execute();

        $result = $stmt->get_result();

        $admins = $result->fetch_all(MYSQLI_ASSOC);


        $stmt->close();


        return $admins;
    }


    // =========================================================
    // DELETE ADMIN
    // =========================================================

    public function deleteAdmin($userId)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM users
            WHERE user_id = ?
            AND role = 'Admin'
        ");


        $stmt->bind_param(
            "i",
            $userId
        );


        $success = $stmt->execute();


        $stmt->close();


        return $success;
    }


    // =========================================================
    // TOGGLE ADMIN STATUS
    // =========================================================

    public function updateStatus($userId, $newStatus)
    {
        if (
            !in_array(
                $newStatus,
                ['Active', 'Inactive', 'Suspended']
            )
        ) {
            return false;
        }


        $stmt = $this->conn->prepare("
            UPDATE users
            SET account_status = ?
            WHERE user_id = ?
            AND role = 'Admin'
        ");


        $stmt->bind_param(
            "si",
            $newStatus,
            $userId
        );


        $success = $stmt->execute();


        $stmt->close();


        return $success;
    }
}

?>

