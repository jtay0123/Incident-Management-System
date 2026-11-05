<?php

session_start();

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../model/IncidentModel.php';
require_once __DIR__ . '/../model/SLALogicModel.php';

class AdminIncidentController
{
    private $conn;
    private $incidentModel;
    private $slaLogicModel;


    public function __construct()
    {
        global $conn;

        $this->conn = $conn;

        $this->incidentModel =
            new IncidentModel(
                $this->conn
            );

        $this->slaLogicModel =
            new SLALogicModel(
                $this->conn
            );
    }


    // =====================================================
    // ADMIN ACCESS
    // =====================================================

    private function requireAdmin()
    {
        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['user_role'])
        ) {

            $this->jsonResponse([
                'success' => false,
                'message' => 'Unauthorized access.'
            ]);

            exit;
        }


        if (
            $_SESSION['user_role'] !== 'Admin'
        ) {

            $this->jsonResponse([
                'success' => false,
                'message' => 'Admin access required.'
            ]);

            exit;
        }
    }


    // =====================================================
    // GET ADMIN INCIDENT DETAILS
    // =====================================================

    public function getAdminIncidentDetails()
    {
        $this->requireAdmin();


        $incidentId =
            isset($_GET['incident_id'])
                ? (int) $_GET['incident_id']
                : 0;


        if ($incidentId <= 0) {

            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid incident ID.'
            ]);

            return;
        }


        $incident =
            $this->incidentModel
                ->getIncidentById(
                    $incidentId
                );


        if (!$incident) {

            $this->jsonResponse([
                'success' => false,
                'message' => 'Incident not found.'
            ]);

            return;
        }


        $attachments =
            $this->incidentModel
                ->getIncidentAttachments(
                    $incidentId
                );


        $updates =
            $this->incidentModel
                ->getIncidentUpdates(
                    $incidentId
                );


        $sla =
            $this->slaLogicModel
                ->getSLARecord(
                    $incidentId
                );


        $this->jsonResponse([
            'success' => true,
            'incident' => $incident,
            'attachments' => $attachments ?: [],
            'updates' => $updates ?: [],
            'sla' => $sla ?: null
        ]);
    }


    // =====================================================
    // GET ACTIVE DEPARTMENTS
    // =====================================================

    public function getDepartments()
    {
        $this->requireAdmin();


        $sql = "
            SELECT
                department_id,
                department_name
            FROM departments
            WHERE status = 'Active'
                AND department_id != 1
            ORDER BY department_name ASC
        ";


        $stmt =
            $this->conn->prepare(
                $sql
            );


        if (!$stmt) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Failed to prepare department query.'
            ]);

            return;
        }


        if (!$stmt->execute()) {

            $stmt->close();

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Failed to retrieve departments.'
            ]);

            return;
        }


        $result =
            $stmt->get_result();


        $departments = [];


        while (
            $row =
            $result->fetch_assoc()
        ) {

            $departments[] =
                $row;
        }


        $stmt->close();


        $this->jsonResponse([
            'success' => true,
            'departments' => $departments
        ]);
    }


    // =====================================================
    // GET AVAILABLE RESPONSE STAFF BY DEPARTMENT
    // =====================================================

    public function getResponseStaff()
    {
        $this->requireAdmin();


        $departmentId =
            isset($_GET['department_id'])
                ? (int) $_GET['department_id']
                : 0;


        if ($departmentId <= 0) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Invalid department ID.'
            ]);

            return;
        }


        $sql = "
            SELECT
                u.user_id,
                u.full_name,
                u.email,
                u.user_type,
                u.availability_status
            FROM users u
            WHERE u.role = 'Response Staff'
              AND u.account_status = 'Active'
              AND u.department_id = ?
              AND u.availability_status = 'Available'
              AND NOT EXISTS (
                    SELECT 1
                    FROM staff_leave sl
                    WHERE sl.user_id = u.user_id
                      AND CURDATE()
                          BETWEEN sl.start_date
                          AND sl.end_date
              )
            ORDER BY u.full_name ASC
        ";


        $stmt =
            $this->conn->prepare(
                $sql
            );


        if (!$stmt) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Failed to prepare staff query.'
            ]);

            return;
        }


        $stmt->bind_param(
            'i',
            $departmentId
        );


        if (!$stmt->execute()) {

            $stmt->close();

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Failed to retrieve response staff.'
            ]);

            return;
        }


        $result =
            $stmt->get_result();


        $staff = [];


        while (
            $row =
            $result->fetch_assoc()
        ) {

            $staff[] =
                $row;
        }


        $stmt->close();


        $this->jsonResponse([
            'success' => true,
            'staff' => $staff
        ]);
    }


    // =====================================================
    // REASSIGN INCIDENT
    // =====================================================

    public function reassignIncident()
    {
        $this->requireAdmin();


        $incidentId =
            (int) (
                $_POST['incident_id']
                ?? 0
            );


        $departmentId =
            (int) (
                $_POST['department_id']
                ?? 0
            );


        $assignedStaffId =
            (int) (
                $_POST['assigned_staff_id']
                ?? 0
            );


        $adminId =
            (int) $_SESSION['user_id'];


        if (
            $incidentId <= 0 ||
            $departmentId <= 0 ||
            $assignedStaffId <= 0
        ) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Incident, department and response staff are required.'
            ]);

            return;
        }


        // -------------------------------------------------
        // Get incident
        // -------------------------------------------------

        $incident =
            $this->incidentModel
                ->getIncidentById(
                    $incidentId
                );


        if (!$incident) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Incident not found.'
            ]);

            return;
        }


        // -------------------------------------------------
        // Closed incidents cannot be reassigned
        // -------------------------------------------------

        if (
            $incident['status'] === 'Closed'
        ) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Closed incidents cannot be reassigned.'
            ]);

            return;
        }


        // -------------------------------------------------
        // Verify selected staff
        // -------------------------------------------------

        $sql = "
            SELECT
                u.user_id,
                u.full_name
            FROM users u
            WHERE u.user_id = ?
              AND u.role = 'Response Staff'
              AND u.account_status = 'Active'
              AND u.department_id = ?
              AND u.availability_status = 'Available'
              AND NOT EXISTS (
                    SELECT 1
                    FROM staff_leave sl
                    WHERE sl.user_id = u.user_id
                      AND CURDATE()
                          BETWEEN sl.start_date
                          AND sl.end_date
              )
            LIMIT 1
        ";


        $stmt =
            $this->conn->prepare(
                $sql
            );


        if (!$stmt) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Failed to prepare staff validation.'
            ]);

            return;
        }


        $stmt->bind_param(
            'ii',
            $assignedStaffId,
            $departmentId
        );


        if (!$stmt->execute()) {

            $stmt->close();

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Failed to validate response staff.'
            ]);

            return;
        }


        $result =
            $stmt->get_result();


        $staff =
            $result->fetch_assoc();


        $stmt->close();


        if (!$staff) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Selected response staff is unavailable or invalid.'
            ]);

            return;
        }


        // -------------------------------------------------
        // Begin transaction
        // -------------------------------------------------

        $this->conn->begin_transaction();


        try {

            // =============================================
            // CLOSE PREVIOUS ACTIVE ASSIGNMENT
            // =============================================

            $sql = "
                UPDATE incident_assignments
                SET unassigned_at = NOW()
                WHERE incident_id = ?
                  AND unassigned_at IS NULL
            ";


            $stmt =
                $this->conn->prepare(
                    $sql
                );


            if (!$stmt) {

                throw new Exception(
                    'Failed to prepare previous assignment update.'
                );
            }


            $stmt->bind_param(
                'i',
                $incidentId
            );


            if (!$stmt->execute()) {

                $stmt->close();

                throw new Exception(
                    'Failed to close previous assignment.'
                );
            }


            $stmt->close();


            // =============================================
            // UPDATE INCIDENT
            // =============================================

            $sql = "
                UPDATE incidents
                SET
                    department_id = ?,
                    assigned_staff_id = ?,
                    status = 'Assigned'
                WHERE incident_id = ?
            ";


            $stmt =
                $this->conn->prepare(
                    $sql
                );


            if (!$stmt) {

                throw new Exception(
                    'Failed to prepare incident assignment update.'
                );
            }


            $stmt->bind_param(
                'iii',
                $departmentId,
                $assignedStaffId,
                $incidentId
            );


            if (!$stmt->execute()) {

                $stmt->close();

                throw new Exception(
                    'Failed to update incident assignment.'
                );
            }


            $stmt->close();


            // =============================================
            // ADD ASSIGNMENT HISTORY
            // =============================================

            $assignmentType =
                'Reassignment';


            $sql = "
                INSERT INTO incident_assignments
                (
                    incident_id,
                    assigned_by,
                    assigned_to,
                    department_id,
                    assignment_type
                )
                VALUES (?, ?, ?, ?, ?)
            ";


            $stmt =
                $this->conn->prepare(
                    $sql
                );


            if (!$stmt) {

                throw new Exception(
                    'Failed to prepare assignment history.'
                );
            }


            $stmt->bind_param(
                'iiiis',
                $incidentId,
                $adminId,
                $assignedStaffId,
                $departmentId,
                $assignmentType
            );


            if (!$stmt->execute()) {

                $stmt->close();

                throw new Exception(
                    'Failed to create assignment history.'
                );
            }


            $stmt->close();


            // =============================================
            // ADD INCIDENT UPDATE
            // =============================================

            $status =
                'Assigned';


            $message =
                'Incident reassigned to ' .
                $staff['full_name'] .
                '.';


            $sql = "
                INSERT INTO incident_updates
                (
                    incident_id,
                    user_id,
                    status,
                    update_message
                )
                VALUES (?, ?, ?, ?)
            ";


            $stmt =
                $this->conn->prepare(
                    $sql
                );


            if (!$stmt) {

                throw new Exception(
                    'Failed to prepare incident update.'
                );
            }


            $stmt->bind_param(
                'iiss',
                $incidentId,
                $adminId,
                $status,
                $message
            );


            if (!$stmt->execute()) {

                $stmt->close();

                throw new Exception(
                    'Failed to create incident update.'
                );
            }


            $stmt->close();


            // =============================================
            // COMMIT
            // =============================================

            $this->conn->commit();


            $this->jsonResponse([
                'success' => true,
                'message' =>
                    'Incident reassigned successfully.'
            ]);

        } catch (Exception $e) {

            $this->conn->rollback();


            $this->jsonResponse([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ]);
        }
    }


    
    // =====================================================
    // UPDATE INCIDENT PRIORITY
    // =====================================================

    public function updatePriority()
    {
        $this->requireAdmin();

        $incidentId = (int) (
            $_POST['incident_id'] ?? 0
        );

        $newPriority = trim(
            $_POST['priority'] ?? ''
        );

        $adminId = (int) $_SESSION['user_id'];

        // -------------------------------------------------
        // Validate input
        // -------------------------------------------------

        $allowedPriorities = [
            'Low',
            'Medium',
            'High',
            'Emergency'
        ];

        if (
            $incidentId <= 0 ||
            !in_array(
                $newPriority,
                $allowedPriorities,
                true
            )
        ) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid incident ID or priority.'
            ]);

            return;
        }

        // -------------------------------------------------
        // Get incident
        // -------------------------------------------------

        $incident = $this->incidentModel
            ->getIncidentById($incidentId);

        if (!$incident) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Incident not found.'
            ]);

            return;
        }

        // Closed incidents cannot be changed.

        if ($incident['status'] === 'Closed') {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Closed incidents cannot be updated.'
            ]);

            return;
        }

        $oldPriority = $incident['priority'];

        // -------------------------------------------------
        // No change required
        // -------------------------------------------------

        if ($oldPriority === $newPriority) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'The incident already has this priority.'
            ]);

            return;
        }

        // -------------------------------------------------
        // Begin transaction
        // -------------------------------------------------

        $this->conn->begin_transaction();

        try {

            // =============================================
            // UPDATE INCIDENT PRIORITY
            // =============================================

            $sql = "
                UPDATE incidents
                SET priority = ?
                WHERE incident_id = ?
            ";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare priority update.'
                );
            }

            $stmt->bind_param(
                'si',
                $newPriority,
                $incidentId
            );

            if (!$stmt->execute()) {
                $stmt->close();

                throw new Exception(
                    'Failed to update incident priority.'
                );
            }

            $stmt->close();

            // =============================================
            // RECALCULATE SLA
            // =============================================

            /*
             * This method must be added to SLALogicModel.php.
             * It must return true on success and false on failure.
             * It must not start or commit its own transaction.
             */

            if (
                !$this->slaLogicModel
                    ->recalculateSLAForPriorityChange(
                        $incidentId,
                        $newPriority
                    )
            ) {
                throw new Exception(
                    'Failed to recalculate the SLA.'
                );
            }

            // =============================================
            // ADD INCIDENT UPDATE HISTORY
            // =============================================

            $status = $incident['status'];

            $message =
                'Priority changed from ' .
                $oldPriority .
                ' to ' .
                $newPriority .
                ' by Admin.';

            $sql = "
                INSERT INTO incident_updates
                (
                    incident_id,
                    user_id,
                    status,
                    update_message
                )
                VALUES (?, ?, ?, ?)
            ";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare priority history.'
                );
            }

            $stmt->bind_param(
                'iiss',
                $incidentId,
                $adminId,
                $status,
                $message
            );

            if (!$stmt->execute()) {
                $stmt->close();

                throw new Exception(
                    'Failed to save priority history.'
                );
            }

            $stmt->close();

            // =============================================
            // COMMIT
            // =============================================

            $this->conn->commit();

            $this->jsonResponse([
                'success' => true,
                'message' =>
                    'Incident priority updated and SLA recalculated successfully.'
            ]);

        } catch (Exception $e) {

            $this->conn->rollback();

            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    //rework
    
public function returnIncidentToProgress()
{
    $this->requireAdmin();

    $incidentId = (int) ($_POST['incident_id'] ?? 0);
    $adminId = (int) $_SESSION['user_id'];

    if ($incidentId <= 0) {
        $this->jsonResponse([
            'success' => false,
            'message' => 'Invalid incident ID.'
        ]);
        return;
    }

    $incident = $this->incidentModel->getIncidentById($incidentId);

    if (!$incident || $incident['status'] !== 'Resolved') {
        $this->jsonResponse([
            'success' => false,
            'message' => 'Only resolved incidents can be returned to In Progress.'
        ]);
        return;
    }

    $this->conn->begin_transaction();

    try {
        $status = 'In Progress';
        $message = 'Admin returned the incident to In Progress after reviewing the Reporter feedback.';

        $sql = "
            UPDATE incidents
            SET status = ?
            WHERE incident_id = ?
              AND status = 'Resolved'
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Failed to prepare incident update.');
        }

        $stmt->bind_param('si', $status, $incidentId);

        if (!$stmt->execute() || $stmt->affected_rows !== 1) {
            $stmt->close();
            throw new Exception('Incident status was not changed.');
        }

        $stmt->close();

        $sql = "
            INSERT INTO incident_updates
            (incident_id, user_id, status, update_message)
            VALUES (?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Failed to prepare update history.');
        }

        $stmt->bind_param(
            'iiss',
            $incidentId,
            $adminId,
            $status,
            $message
        );

        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception('Failed to record incident history.');
        }

        $stmt->close();

        $this->conn->commit();

        $this->jsonResponse([
            'success' => true,
            'message' => 'Incident returned to In Progress successfully.'
        ]);

    } catch (Exception $e) {
        $this->conn->rollback();

        $this->jsonResponse([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}



    // =====================================================
    // CLOSE INCIDENT
    // =====================================================

    public function closeIncident()
    {
        $this->requireAdmin();


        $incidentId =
            (int) (
                $_POST['incident_id']
                ?? 0
            );


        $adminId =
            (int) $_SESSION['user_id'];


        if ($incidentId <= 0) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Invalid incident ID.'
            ]);

            return;
        }


        $incident =
            $this->incidentModel
                ->getIncidentById(
                    $incidentId
                );


        if (!$incident) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Incident not found.'
            ]);

            return;
        }


        // Only Resolved can be Closed

        if (
            $incident['status']
            !== 'Resolved'
        ) {

            $this->jsonResponse([
                'success' => false,
                'message' =>
                    'Only resolved incidents can be closed.'
            ]);

            return;
        }


        $this->conn->begin_transaction();


        try {

            // =============================================
            // CLOSE INCIDENT
            // =============================================

            $sql = "
                UPDATE incidents
                SET
                    status = 'Closed'
                WHERE incident_id = ?
            ";


            $stmt =
                $this->conn->prepare(
                    $sql
                );


            if (!$stmt) {

                throw new Exception(
                    'Failed to prepare incident closure.'
                );
            }


            $stmt->bind_param(
                'i',
                $incidentId
            );


            if (!$stmt->execute()) {

                $stmt->close();

                throw new Exception(
                    'Failed to close incident.'
                );
            }


            $stmt->close();


            // =============================================
            // ADD UPDATE HISTORY
            // =============================================

            $status =
                'Closed';


            $message =
                'Incident closed by Admin.';


            $sql = "
                INSERT INTO incident_updates
                (
                    incident_id,
                    user_id,
                    status,
                    update_message
                )
                VALUES (?, ?, ?, ?)
            ";


            $stmt =
                $this->conn->prepare(
                    $sql
                );


            if (!$stmt) {

                throw new Exception(
                    'Failed to prepare closure history.'
                );
            }


            $stmt->bind_param(
                'iiss',
                $incidentId,
                $adminId,
                $status,
                $message
            );


            if (!$stmt->execute()) {

                $stmt->close();

                throw new Exception(
                    'Failed to create closure update.'
                );
            }


            $stmt->close();


            // =============================================
            // COMMIT
            // =============================================

            $this->conn->commit();


            $this->jsonResponse([
                'success' => true,
                'message' =>
                    'Incident closed successfully.'
            ]);

        } catch (Exception $e) {

            $this->conn->rollback();


            $this->jsonResponse([
                'success' => false,
                'message' =>
                    $e->getMessage()
            ]);
        }
    }


    // =====================================================
    // JSON RESPONSE
    // =====================================================

    private function jsonResponse(
        $data
    ) {

        header(
            'Content-Type: application/json; charset=utf-8'
        );


        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
        );
    }


    // =====================================================
    // HANDLE REQUEST
    // =====================================================

    public function handleRequest()
    {
        $action =
            $_GET['action']
            ?? '';


        switch ($action) {

            case 'get_admin_incident_details':

                $this->getAdminIncidentDetails();

                break;


            case 'get_departments':

                $this->getDepartments();

                break;


            case 'get_response_staff':

                $this->getResponseStaff();

                break;


            case 'reassign_incident':

                $this->reassignIncident();

                break;

            
            case 'update_priority':

                $this->updatePriority();

                break;

            case 'return_incident_to_progress':
                  $this->returnIncidentToProgress();
                  break;



            case 'close_incident':

                $this->closeIncident();

                break;


            default:

                $this->jsonResponse([
                    'success' => false,
                    'message' =>
                        'Invalid action.'
                ]);

                break;
        }
    }
}


// =========================================================
// START CONTROLLER
// =========================================================

$controller =
    new AdminIncidentController();


$controller->handleRequest();

?>