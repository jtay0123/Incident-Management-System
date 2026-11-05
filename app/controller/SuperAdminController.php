<?php

session_start();

require_once __DIR__ . "/../config/conn.php";


class SuperAdminController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }


    // =========================
    // GET ADMIN STATISTICS
    // =========================
    public function getStats() {

        $stats = [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'suspended' => 0
        ];

        $result = $this->conn->query("
            SELECT
                COUNT(*) AS total,
                SUM(account_status = 'Active') AS active,
                SUM(account_status = 'Inactive') AS inactive,
                SUM(account_status = 'Suspended') AS suspended
            FROM users
            WHERE role = 'Admin'
        ");

        if ($result) {
            $stats = $result->fetch_assoc();
        }

        echo json_encode($stats);
    }


    // =========================
    // GET ALL ADMIN ACCOUNTS
    // =========================
    public function getAdmins() {

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

        echo json_encode($admins);
    }




// =========================
// UPDATE ADMIN STATUS
// =========================
public function updateStatus() {

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (
        empty($input['user_id']) ||
        empty($input['status'])
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing parameters.'
        ]);

        return;
    }


    $newStatus = $input['status'];


    // Only these three statuses are allowed
    if (
        !in_array(
            $newStatus,
            ['Active', 'Inactive', 'Suspended']
        )
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid account status.'
        ]);

        return;
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
        $input['user_id']
    );


    $success = $stmt->execute();


    $stmt->close();


    echo json_encode([
        'success' => $success
    ]);
}



    // =========================
    // DELETE ADMIN ACCOUNT
    // =========================
    public function deleteAdmin() {

        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (empty($input['user_id'])) {

            echo json_encode([
                'success' => false,
                'message' => 'Missing user ID.'
            ]);

            return;
        }

        $stmt = $this->conn->prepare("
            DELETE FROM users
            WHERE user_id = ?
            AND role = 'Admin'
        ");

        $stmt->bind_param(
            "i",
            $input['user_id']
        );

        $success = $stmt->execute();

        $stmt->close();

        echo json_encode([
            'success' => $success
        ]);
    }
}


// =========================
// ROUTER
// =========================

$action = $_GET['action'] ?? '';

$controller = new SuperAdminController($conn);

match ($action) {

    'getStats'
        => $controller->getStats(),

    'getAdmins'
        => $controller->getAdmins(),

    'updateStatus'
        => $controller->updateStatus(),

    'delete'
        => $controller->deleteAdmin(),

    default
        => $controller->getStats()
};

?>