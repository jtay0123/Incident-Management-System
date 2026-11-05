<?php

class IncidentModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    /* =========================================================
       CATEGORY
       ========================================================= */

    public function getActiveCategories()
    {
        $sql = "SELECT 
                    c.category_id,
                    c.category_name,
                    c.description,
                    c.department_id,
                    c.keywords,
                    d.department_name
                FROM categories c
                INNER JOIN departments d
                    ON c.department_id = d.department_id
                WHERE c.status = 'Active'
                ORDER BY c.category_name ASC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function getCategoryById($categoryId)
    {
        $sql = "SELECT 
                    c.category_id,
                    c.category_name,
                    c.description,
                    c.department_id,
                    c.keywords,
                    c.status,
                    d.department_name
                FROM categories c
                INNER JOIN departments d
                    ON c.department_id = d.department_id
                WHERE c.category_id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $categoryId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }


    public function categoryExists($categoryId)
    {
        $sql = "SELECT category_id
                FROM categories
                WHERE category_id = ?
                AND status = 'Active'
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $categoryId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }


    public function matchCategoryByKeywords(
        $title,
        $description
    ) {
        $text = strtolower(
            trim($title . ' ' . $description)
        );

        if ($text === '') {
            return null;
        }

        $categories = $this->getActiveCategories();

        $bestCategory = null;
        $bestScore = 0;
        $bestMatchedKeywords = 0;

        foreach ($categories as $category) {

            if (empty($category['keywords'])) {
                continue;
            }

            $keywords = preg_split(
                '/\s*,\s*/',
                strtolower($category['keywords'])
            );

            $score = 0;
            $matchedKeywords = 0;

            foreach ($keywords as $keyword) {

                $keyword = trim($keyword);

                if ($keyword === '') {
                    continue;
                }

                if (strpos($text, $keyword) !== false) {

                    $wordCount = str_word_count($keyword);

                    if ($wordCount > 1) {
                        $score += 3;
                    } else {
                        $score += 1;
                    }

                    $matchedKeywords++;
                }
            }

            if (
                $score > $bestScore ||
                (
                    $score === $bestScore &&
                    $matchedKeywords > $bestMatchedKeywords
                )
            ) {
                $bestScore = $score;
                $bestMatchedKeywords = $matchedKeywords;
                $bestCategory = $category;
            }
        }

        if ($bestCategory === null) {
            return null;
        }

        return [
            'category_id' => $bestCategory['category_id'],
            'category_name' => $bestCategory['category_name'],
            'department_id' => $bestCategory['department_id'],
            'department_name' => $bestCategory['department_name'],
            'score' => $bestScore
        ];
    }


    
    /* =========================================================
    PRIORITY
    ========================================================= */

    public function determinePriority($categoryId)
    {
        $sql = "SELECT default_priority
                FROM categories
                WHERE category_id = ?
                AND status = 'Active'
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $categoryId);

        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $category = $result->fetch_assoc();

        $stmt->close();

        return $category['default_priority'] ?? null;
    }



    /* =========================================================
       CREATE INCIDENT
       ========================================================= */

    public function createIncident(
        $reporterId,
        $categoryId,
        $locationId,
        $title,
        $description,
        $isSensitive = 0
    ) {

        $category = $this->getCategoryById(
            $categoryId
        );

        if (
            !$category ||
            $category['status'] !== 'Active'
        ) {
            return [
                'success' => false,
                'message' => 'Invalid or inactive category.'
            ];
        }

        /*
         * Location is validated by IncidentController
         * using LocationModel.
         */

        $priority = $this->determinePriority($categoryId);

        if ($priority === null) {
            return [
                'success' => false,
                'message' => 'Failed to determine incident priority.'
            ];
        }

        $status = 'Submitted';

        $departmentId = (int) $category['department_id'];

        $sql = "INSERT INTO incidents (
                    reporter_id,
                    category_id,
                    department_id,
                    assigned_staff_id,
                    location_id,
                    title,
                    description,
                    priority,
                    status,
                    is_sensitive
                )
                VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Failed to prepare incident creation.'
            ];
        }

        $stmt->bind_param(
            "iiiissssi",
            $reporterId,
            $categoryId,
            $departmentId,
            $locationId,
            $title,
            $description,
            $priority,
            $status,
            $isSensitive
        );

        if (!$stmt->execute()) {
            return [
                'success' => false,
                'message' => 'Failed to create incident.'
            ];
        }

        return [
            'success' => true,
            'incident_id' => $this->conn->insert_id,
            'priority' => $priority,
            'status' => $status,
            'category_id' => $categoryId,
            'department_id' => $departmentId
        ];
    }


    /* =========================================================
       GET INCIDENT
       ========================================================= */

    public function getIncidentById($incidentId)
    {
        $sql = "SELECT
                    i.*,
                    c.category_name,
                    d.department_name,
                    l.location_name,
                    l.building_name,
                    l.floor_number,
                    u.full_name AS reporter_name,
                    s.full_name AS assigned_staff_name
                FROM incidents i

                INNER JOIN categories c
                    ON i.category_id = c.category_id

                LEFT JOIN departments d
                    ON i.department_id = d.department_id

                INNER JOIN locations l
                    ON i.location_id = l.location_id

                INNER JOIN users u
                    ON i.reporter_id = u.user_id

                LEFT JOIN users s
                    ON i.assigned_staff_id = s.user_id

                WHERE i.incident_id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "i",
            $incidentId
        );

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }


    /* =========================================================
       REPORTER
       ========================================================= */

    public function getIncidentsByReporter($reporterId)
    {
        $sql = "SELECT
                    i.incident_id,
                    i.title,
                    i.description,
                    i.priority,
                    i.status,
                    i.submitted_at,
                    i.resolved_at,
                    c.category_name,
                    d.department_name,
                    l.location_name,
                    l.building_name,
                    l.floor_number,
                    s.full_name AS assigned_staff_name
                FROM incidents i

                INNER JOIN categories c
                    ON i.category_id = c.category_id

                LEFT JOIN departments d
                    ON i.department_id = d.department_id

                INNER JOIN locations l
                    ON i.location_id = l.location_id

                LEFT JOIN users s
                    ON i.assigned_staff_id = s.user_id

                WHERE i.reporter_id = ?

                ORDER BY i.submitted_at DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param(
            "i",
            $reporterId
        );

        $stmt->execute();

        return $stmt->get_result()->fetch_all(
            MYSQLI_ASSOC
        );
    }


    /* =========================================================
       ADMIN
       ========================================================= */

    public function getAllIncidents()
    {
        $sql = "
            SELECT
                i.incident_id,
                i.reporter_id,
                i.category_id,
                i.department_id,
                i.assigned_staff_id,

                i.title,
                i.description,
                i.priority,
                i.status,

                i.escalation_status,
                i.escalation_reason,

                i.is_sensitive,
                i.submitted_at,
                i.resolved_at,
                i.updated_at,

                c.category_name,

                d.department_name,

                l.location_name,
                l.building_name,
                l.floor_number,

                r.full_name AS reporter_name,

                s.full_name AS assigned_staff_name,

                sr.sla_status,
                sr.start_time AS sla_start_time,
                sr.due_time AS sla_due_time,
                sr.completed_time AS sla_completed_time

            FROM incidents i

            INNER JOIN categories c
                ON i.category_id = c.category_id

            LEFT JOIN departments d
                ON i.department_id = d.department_id

            INNER JOIN locations l
                ON i.location_id = l.location_id

            INNER JOIN users r
                ON i.reporter_id = r.user_id

            LEFT JOIN users s
                ON i.assigned_staff_id = s.user_id

            LEFT JOIN sla_records sr
                ON i.incident_id = sr.incident_id

            ORDER BY i.submitted_at DESC
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $incidents = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $incidents;
    }




    /* =========================================================
        RESPONSE STAFF
        ========================================================= */

    public function getIncidentsByStaff($staffId)
    {
        $sql = "SELECT
                    i.incident_id,
                    i.title,
                    i.description,
                    i.priority,
                    i.status,
                    i.escalation_status,
                    i.escalation_reason,
                    i.is_sensitive,
                    i.submitted_at,
                    i.resolved_at,
                    c.category_name,
                    d.department_name,
                    l.location_name,
                    l.building_name,
                    l.floor_number,
                    r.full_name AS reporter_name,

                    sr.sla_status,
                    sr.start_time AS sla_start_time,
                    sr.due_time AS sla_due_time,
                    sr.completed_time AS sla_completed_time

                FROM incidents i

                INNER JOIN categories c
                    ON i.category_id = c.category_id

                LEFT JOIN departments d
                    ON i.department_id = d.department_id

                INNER JOIN locations l
                    ON i.location_id = l.location_id

                INNER JOIN users r
                    ON i.reporter_id = r.user_id

                LEFT JOIN sla_records sr
                    ON i.incident_id = sr.incident_id

                WHERE i.assigned_staff_id = ?

                ORDER BY i.submitted_at DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param(
            "i",
            $staffId
        );

        $stmt->execute();

        return $stmt->get_result()->fetch_all(
            MYSQLI_ASSOC
        );
    }


    /* =========================================================
        RESPONSE STAFF - SINGLE INCIDENT
        ========================================================= */

    public function getIncidentForStaff(
        $incidentId,
        $staffId
    ) {
        $sql = "SELECT
                    i.incident_id,
                    i.reporter_id,
                    i.category_id,
                    i.department_id,
                    i.assigned_staff_id,
                    i.location_id,
                    i.title,
                    i.description,
                    i.priority,
                    i.status,
                    i.is_sensitive,
                    i.submitted_at,
                    i.resolved_at,

                    c.category_name,

                    d.department_name,

                    l.location_name,
                    l.building_name,
                    l.floor_number,

                    r.full_name AS reporter_name,

                    s.full_name AS assigned_staff_name

                FROM incidents i

                INNER JOIN categories c
                    ON i.category_id = c.category_id

                LEFT JOIN departments d
                    ON i.department_id = d.department_id

                INNER JOIN locations l
                    ON i.location_id = l.location_id

                INNER JOIN users r
                    ON i.reporter_id = r.user_id

                LEFT JOIN users s
                    ON i.assigned_staff_id = s.user_id

                WHERE i.incident_id = ?
                AND i.assigned_staff_id = ?

                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "ii",
            $incidentId,
            $staffId
        );

        $stmt->execute();

        $incident =
            $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $incident ?: null;
    }


    /* =========================================================
       ROUND ROBIN STAFF
       ========================================================= */

    public function getNextRoundRobinStaff($departmentId)
    {
        $sql = "SELECT
                    u.user_id,
                    u.full_name,
                    u.department_id,
                    u.availability_status,
                    MAX(ia.assigned_at) AS last_assigned_at
                FROM users u
                LEFT JOIN incident_assignments ia
                    ON u.user_id = ia.assigned_to
                    AND ia.unassigned_at IS NULL
                WHERE u.role = 'Response Staff'
                AND u.account_status = 'Active'
                AND u.availability_status = 'Available'
                AND u.department_id = ?
                AND NOT EXISTS (
                    SELECT 1
                    FROM staff_leave sl
                    WHERE sl.user_id = u.user_id
                        AND CURDATE() BETWEEN sl.start_date
                        AND sl.end_date
                )
                GROUP BY
                    u.user_id,
                    u.full_name,
                    u.department_id,
                    u.availability_status
                ORDER BY
                    MAX(ia.assigned_at) IS NOT NULL ASC,
                    MAX(ia.assigned_at) ASC,
                    u.user_id ASC
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "i",
            $departmentId
        );

        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();

        $staff = $result->fetch_assoc();

        $stmt->close();

        return $staff ?: null;
    }


    /* =========================================================
       AUTOMATIC INCIDENT ASSIGNMENT
       ========================================================= */

    public function autoAssignIncident(
        $incidentId,
        $departmentId
    ) {

        $staff = $this->getNextRoundRobinStaff(
            $departmentId
        );

        if (!$staff) {
            return [
                'success' => false,
                'assigned' => false,
                'message' => 'No available response staff found.'
            ];
        }

        $staffId = (int) $staff['user_id'];

        $this->conn->begin_transaction();

        try {

            $sql = "UPDATE incidents
                    SET assigned_staff_id = ?,
                        status = 'Assigned'
                    WHERE incident_id = ?";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare automatic assignment: ' .
                    $this->conn->error
                );
            }

            $stmt->bind_param(
                "ii",
                $staffId,
                $incidentId
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to assign incident automatically: ' .
                    $stmt->error
                );
            }

            $stmt->close();

            $sql = "INSERT INTO incident_assignments (
                        incident_id,
                        assigned_by,
                        assigned_to,
                        department_id,
                        assignment_type,
                        assigned_at
                    )
                    VALUES (?, NULL, ?, ?, 'Automatic', NOW())";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare assignment history: ' .
                    $this->conn->error
                );
            }

            $stmt->bind_param(
                "iii",
                $incidentId,
                $staffId,
                $departmentId
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to create assignment history: ' .
                    $stmt->error
                );
            }

            $stmt->close();

            $this->conn->commit();

            return [
                'success' => true,
                'assigned' => true,
                'staff_id' => $staffId,
                'staff_name' => $staff['full_name'],
                'message' => 'Incident assigned automatically.'
            ];

        } catch (Exception $e) {

            $this->conn->rollback();

            return [
                'success' => false,
                'assigned' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    /* =========================================================
       ASSIGN INCIDENT
       ========================================================= */

    public function assignIncident(
        $incidentId,
        $staffId,
        $assignedBy,
        $assignmentType = 'Manual',
        $remarks = null
    ) {

        $incident = $this->getIncidentById(
            $incidentId
        );

        if (!$incident) {
            return [
                'success' => false,
                'message' => 'Incident not found.'
            ];
        }

        $sql = "SELECT
                    user_id,
                    role,
                    account_status
                FROM users
                WHERE user_id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Failed to check staff account.'
            ];
        }

        $stmt->bind_param(
            "i",
            $staffId
        );

        $stmt->execute();

        $staff = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$staff) {
            return [
                'success' => false,
                'message' => 'Staff account not found.'
            ];
        }

        if ($staff['role'] !== 'Response Staff') {
            return [
                'success' => false,
                'message' => 'Selected user is not Response Staff.'
            ];
        }

        if ($staff['account_status'] !== 'Active') {
            return [
                'success' => false,
                'message' => 'Selected staff account is not active.'
            ];
        }

        $this->conn->begin_transaction();

        try {

            $sql = "UPDATE incidents
                    SET assigned_staff_id = ?,
                        status = 'Assigned'
                    WHERE incident_id = ?";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare incident assignment.'
                );
            }

            $stmt->bind_param(
                "ii",
                $staffId,
                $incidentId
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to assign incident.'
                );
            }

            $stmt->close();

            /*
             * NOTE:
             * Your current incident_assignments table schema
             * does not include a remarks column.
             *
             * Therefore remarks is not inserted here.
             */

            $sql = "INSERT INTO incident_assignments (
                        incident_id,
                        assigned_by,
                        assigned_to,
                        department_id,
                        assignment_type
                    )
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare assignment history.'
                );
            }

            $departmentId =
                $incident['department_id'];

            $stmt->bind_param(
                "iiiis",
                $incidentId,
                $assignedBy,
                $staffId,
                $departmentId,
                $assignmentType
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to create assignment history.'
                );
            }

            $stmt->close();

            $sql = "INSERT INTO incident_updates (
                        incident_id,
                        user_id,
                        status,
                        update_message
                    )
                    VALUES (?, ?, 'Assigned', ?)";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare incident update.'
                );
            }

            $message =
                'Incident assigned to response staff.';

            $stmt->bind_param(
                "iis",
                $incidentId,
                $assignedBy,
                $message
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to create incident update.'
                );
            }

            $stmt->close();

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Incident assigned successfully.'
            ];

        } catch (Exception $e) {

            $this->conn->rollback();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    /* =========================================================
       UPDATE STATUS
       ========================================================= */

    public function updateIncidentStatus(
        $incidentId,
        $newStatus,
        $userId,
        $updateMessage = null
    ) {

        $allowedStatuses = [
            'Submitted',
            'Assigned',
            'In Progress',
            'Resolved',
            'Closed'
        ];

        if (!in_array(
            $newStatus,
            $allowedStatuses,
            true
        )) {
            return [
                'success' => false,
                'message' => 'Invalid incident status.'
            ];
        }

        $incident = $this->getIncidentById(
            $incidentId
        );

        if (!$incident) {
            return [
                'success' => false,
                'message' => 'Incident not found.'
            ];
        }

        if (
            $newStatus === 'Resolved' ||
            $newStatus === 'Closed'
        ) {

            $sql = "UPDATE incidents
                    SET status = ?,
                        resolved_at = COALESCE(
                            resolved_at,
                            NOW()
                        )
                    WHERE incident_id = ?";

        } else {

            $sql = "UPDATE incidents
                    SET status = ?,
                        resolved_at = NULL
                    WHERE incident_id = ?";
        }

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Failed to prepare status update.'
            ];
        }

        $stmt->bind_param(
            "si",
            $newStatus,
            $incidentId
        );

        if (!$stmt->execute()) {
            return [
                'success' => false,
                'message' => 'Failed to update incident status.'
            ];
        }

        $stmt->close();

        $sql = "INSERT INTO incident_updates (
                    incident_id,
                    user_id,
                    status,
                    update_message
                )
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "iiss",
                $incidentId,
                $userId,
                $newStatus,
                $updateMessage
            );

            $stmt->execute();

            $stmt->close();
        }

        return [
            'success' => true,
            'message' => 'Incident status updated successfully.',
            'status' => $newStatus
        ];
    }


    /* =========================================================
       RESPONSE STAFF - ATTACHMENTS
       ========================================================= */

    public function getIncidentAttachments($incidentId)
    {
        $sql = "SELECT
                    ia.attachment_id,
                    ia.incident_id,
                    ia.uploaded_by,
                    ia.attachment_type,
                    ia.file_name,
                    ia.file_path,
                    ia.file_type,
                    ia.uploaded_at,
                    u.full_name AS uploaded_by_name
                FROM incident_attachments ia
                LEFT JOIN users u
                    ON ia.uploaded_by = u.user_id
                WHERE ia.incident_id = ?
                ORDER BY ia.uploaded_at ASC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param(
            "i",
            $incidentId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $attachments =
            $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $attachments;
    }


    /* =========================================================
       RESPONSE STAFF - INCIDENT UPDATES
       ========================================================= */

    public function getIncidentUpdates($incidentId)
    {
        $sql = "SELECT
                    iu.update_id,
                    iu.user_id,
                    iu.status,
                    iu.update_message,
                    iu.created_at,
                    u.full_name
                FROM incident_updates iu

                INNER JOIN users u
                    ON iu.user_id = u.user_id

                WHERE iu.incident_id = ?

                ORDER BY iu.created_at ASC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param(
            "i",
            $incidentId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $updates =
            $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $updates;
    }


    /* =========================================================
       RESPONSE STAFF - UPDATE STATUS
       ========================================================= */

    public function updateStaffIncidentStatus(
        $incidentId,
        $staffId,
        $newStatus,
        $message
    ) {

        $allowedStatuses = [
            'In Progress',
            'Resolved'
        ];

        if (!in_array(
            $newStatus,
            $allowedStatuses,
            true
        )) {
            return false;
        }

        $stmt = $this->conn->prepare("
            SELECT
                status
            FROM incidents
            WHERE incident_id = ?
              AND assigned_staff_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ii",
            $incidentId,
            $staffId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $incident = $result->fetch_assoc();

        $stmt->close();

        if (!$incident) {
            return false;
        }

        $currentStatus = $incident['status'];

        /*
         * Only allow:
         *
         * Assigned -> In Progress
         * In Progress -> Resolved
         */

        if (
            $currentStatus === 'Assigned' &&
            $newStatus !== 'In Progress'
        ) {
            return false;
        }

        if (
            $currentStatus === 'In Progress' &&
            $newStatus !== 'Resolved'
        ) {
            return false;
        }

        if (
            $currentStatus !== 'Assigned' &&
            $currentStatus !== 'In Progress'
        ) {
            return false;
        }

        $this->conn->begin_transaction();

        try {

            $resolvedAt = null;

            if ($newStatus === 'Resolved') {
                $resolvedAt = date('Y-m-d H:i:s');
            }

            $stmt = $this->conn->prepare("
                UPDATE incidents
                SET
                    status = ?,
                    resolved_at = ?
                WHERE incident_id = ?
                  AND assigned_staff_id = ?
            ");

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare incident update.'
                );
            }

            $stmt->bind_param(
                "ssii",
                $newStatus,
                $resolvedAt,
                $incidentId,
                $staffId
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to update incident.'
                );
            }

            $stmt->close();

            /*
             * Add incident update history.
             */

            $stmt = $this->conn->prepare("
                INSERT INTO incident_updates
                (
                    incident_id,
                    user_id,
                    status,
                    update_message,
                    created_at
                )
                VALUES
                (?, ?, ?, ?, NOW())
            ");

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare incident history.'
                );
            }

            $stmt->bind_param(
                "iiss",
                $incidentId,
                $staffId,
                $newStatus,
                $message
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to create incident history.'
                );
            }

            $stmt->close();

            $this->conn->commit();

            return true;

        } catch (Exception $e) {

            $this->conn->rollback();

            return false;
        }
    }


    /* =========================================================
       RESPONSE STAFF - RAISE TO ADMIN
       ========================================================= */

    public function raiseIncidentToAdmin(
        $incidentId,
        $staffId,
        $reason
    ) {

        if (trim($reason) === '') {
            return false;
        }

        $stmt = $this->conn->prepare("
            SELECT
                status,
                escalation_status
            FROM incidents
            WHERE incident_id = ?
              AND assigned_staff_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ii",
            $incidentId,
            $staffId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $incident = $result->fetch_assoc();

        $stmt->close();

        if (!$incident) {
            return false;
        }

        /*
         * Cannot raise a resolved or closed incident.
         */
        if (
            $incident['status'] === 'Resolved' ||
            $incident['status'] === 'Closed'
        ) {
            return false;
        }

        /*
         * Do not raise the same incident again.
         */
        if (
            isset($incident['escalation_status']) &&
            $incident['escalation_status'] === 'Raised'
        ) {
            return false;
        }

        $this->conn->begin_transaction();

        try {

            $stmt = $this->conn->prepare("
                UPDATE incidents
                SET
                    escalation_status = 'Raised',
                    escalation_reason = ?
                WHERE incident_id = ?
                  AND assigned_staff_id = ?
            ");

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare escalation.'
                );
            }

            $stmt->bind_param(
                "sii",
                $reason,
                $incidentId,
                $staffId
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to raise incident.'
                );
            }

            $stmt->close();

            /*
             * Record escalation in incident updates.
             */

            $status = $incident['status'];

            $message =
                "Incident raised to Admin. Reason: " .
                trim($reason);

            $stmt = $this->conn->prepare("
                INSERT INTO incident_updates
                (
                    incident_id,
                    user_id,
                    status,
                    update_message,
                    created_at
                )
                VALUES
                (?, ?, ?, ?, NOW())
            ");

            if (!$stmt) {
                throw new Exception(
                    'Failed to prepare escalation history.'
                );
            }

            $stmt->bind_param(
                "iiss",
                $incidentId,
                $staffId,
                $status,
                $message
            );

            if (!$stmt->execute()) {
                throw new Exception(
                    'Failed to create escalation history.'
                );
            }

            $stmt->close();

            $this->conn->commit();

            return true;

        } catch (Exception $e) {

            $this->conn->rollback();

            return false;
        }
    }


    /* =========================================================
       ATTACHMENT
       ========================================================= */

    public function addAttachment(
        $incidentId,
        $uploadedBy,
        $attachmentType,
        $fileName,
        $filePath,
        $fileType
    ) {

        $sql = "INSERT INTO incident_attachments (
                    incident_id,
                    uploaded_by,
                    attachment_type,
                    file_name,
                    file_path,
                    file_type
                )
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [
                'success' => false,
                'message' =>
                    'Failed to prepare attachment query.'
            ];
        }

        $stmt->bind_param(
            "iissss",
            $incidentId,
            $uploadedBy,
            $attachmentType,
            $fileName,
            $filePath,
            $fileType
        );

        if (!$stmt->execute()) {
            return [
                'success' => false,
                'message' =>
                    'Failed to save attachment.'
            ];
        }

        return [
            'success' => true,
            'attachment_id' => $stmt->insert_id
        ];
    }


/* =========================================================
   ADMIN - ADD INCIDENT NOTE
   ========================================================= */

public function addAdminIncidentNote(
    $incidentId,
    $adminId,
    $note
) {

    $note = trim($note);

    if (
        $incidentId <= 0 ||
        $adminId <= 0 ||
        $note === ''
    ) {
        return [
            'success' => false,
            'message' => 'Invalid incident or note.'
        ];
    }

    /*
     * Get the current incident status.
     * The note must not change the status.
     */
    $incident = $this->getIncidentById(
        $incidentId
    );

    if (!$incident) {
        return [
            'success' => false,
            'message' => 'Incident not found.'
        ];
    }

    $currentStatus = $incident['status'];

    $sql = "INSERT INTO incident_updates (
                incident_id,
                user_id,
                status,
                update_message
            )
            VALUES (?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Failed to prepare admin note.'
        ];
    }

    $stmt->bind_param(
        "iiss",
        $incidentId,
        $adminId,
        $currentStatus,
        $note
    );

    if (!$stmt->execute()) {
        $stmt->close();

        return [
            'success' => false,
            'message' => 'Failed to save admin note.'
        ];
    }

    $stmt->close();

    return [
        'success' => true,
        'message' => 'Admin note added successfully.'
    ];
}
}