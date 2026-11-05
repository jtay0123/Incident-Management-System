<?php

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../model/IncidentModel.php';
require_once __DIR__ . '/../model/LocationModel.php';
require_once __DIR__ . '/../model/SLALogicModel.php';



class IncidentController
{
    private $conn;
    private $incidentModel;
    private $locationModel;
    private $slaLogicModel;


    public function __construct($conn)
    {
        $this->conn = $conn;

        $this->incidentModel =
            new IncidentModel($conn);

        $this->locationModel =
            new LocationModel($conn);

        $this->slaLogicModel =
            new SLALogicModel($conn);
    }


    /* =========================================================
       CREATE INCIDENT
       ========================================================= */

    public function createIncident()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(
                false,
                'Please login first.'
            );
            return;
        }

        $reporterId =
            (int) $_SESSION['user_id'];

        $title =
            trim($_POST['title'] ?? '');

        $description =
            trim($_POST['description'] ?? '');

        $categoryId =
            (int) ($_POST['category_id'] ?? 0);

        $locationId =
            (int) ($_POST['location_id'] ?? 0);

        /*
         * Sensitive incidents are not selected
         * by Reporter for now.
         */
        $reporterApproved = 0;


        /* -------------------------
           Validation
           ------------------------- */

        if ($title === '') {
            $this->jsonResponse(
                false,
                'Please enter an incident title.'
            );
            return;
        }

        if ($description === '') {
            $this->jsonResponse(
                false,
                'Please enter an incident description.'
            );
            return;
        }

        if ($categoryId <= 0) {
            $this->jsonResponse(
                false,
                'Please select a category.'
            );
            return;
        }

        if ($locationId <= 0) {
            $this->jsonResponse(
                false,
                'Please select a location.'
            );
            return;
        }


        /*
         * Validate location using LocationModel.
         */
        $location =
            $this->locationModel->getLocationById(
                $locationId
            );

        if (
            !$location ||
            $location['status'] !== 'Active'
        ) {
            $this->jsonResponse(
                false,
                'Invalid or inactive location.'
            );
            return;
        }


        /*
         * Create incident.
         *
         * Priority is determined inside IncidentModel.
         * Department is determined from category.
         */
        $result =
            $this->incidentModel->createIncident(
                $reporterId,
                $categoryId,
                $locationId,
                $title,
                $description,
                $reporterApproved,
            );


        if (!$result['success']) {
            $this->jsonResponse(
                false,
                $result['message']
            );
            return;
        }


        $incidentId =
            $result['incident_id'];


        /* =====================================================
           AUTOMATIC ROUND-ROBIN ASSIGNMENT
           ===================================================== */

        $assignmentResult =
            $this->incidentModel->autoAssignIncident(
                $incidentId,
                $result['department_id']
            );


/* =====================================================
   INCIDENT IMAGE UPLOAD
   ===================================================== */

if (
    isset($_FILES['incident_image']) &&
    !empty($_FILES['incident_image']['name'][0])
) {

    $images = $_FILES['incident_image'];

    $totalImages = count($images['name']);

    // Maximum 5 images per incident.
    if ($totalImages > 5) {
        $this->jsonResponse(
            false,
            'You can upload a maximum of 5 images.'
        );
        return;
    }

    $allowedTypes = [
        'image/jpg',
        'image/jpeg',
        'image/png'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $uploadDirectory =
        __DIR__ . '/../../uploads/incidents/';

    if (!is_dir($uploadDirectory)) {

        if (!mkdir($uploadDirectory, 0755, true)) {
            $this->jsonResponse(
                false,
                'Failed to create image upload directory.'
            );
            return;
        }
    }

    // Process each uploaded image.
    for ($i = 0; $i < $totalImages; $i++) {

        // Skip empty file inputs.
        if (
            $images['error'][$i] === UPLOAD_ERR_NO_FILE
        ) {
            continue;
        }

        if (
            $images['error'][$i] !== UPLOAD_ERR_OK
        ) {
            $this->jsonResponse(
                false,
                'Failed to upload one of the incident images.'
            );
            return;
        }

        $tmpName = $images['tmp_name'][$i];
        $fileSize = $images['size'][$i];
        $originalName = $images['name'][$i];

        // Check actual MIME type.
        $actualMimeType = $finfo->file($tmpName);

        if (
            !in_array(
                $actualMimeType,
                $allowedTypes,
                true
            )
        ) {
            $this->jsonResponse(
                false,
                'Only JPG and PNG images are allowed.'
            );
            return;
        }

        // Maximum 5 MB per image.
        if ($fileSize > 5 * 1024 * 1024) {
            $this->jsonResponse(
                false,
                'Each image must not exceed 5 MB.'
            );
            return;
        }

        // Determine file extension.
        $extension =
            ($actualMimeType === 'image/png')
                ? 'png'
                : 'jpg';

        // Generate unique filename.
        $newFileName =
            'incident_' .
            $incidentId .
            '_' .
            bin2hex(random_bytes(8)) .
            '.' .
            $extension;

        $filePath =
            $uploadDirectory .
            $newFileName;

        // Save image.
        if (
            !move_uploaded_file(
                $tmpName,
                $filePath
            )
        ) {
            $this->jsonResponse(
                false,
                'Failed to save one of the incident images.'
            );
            return;
        }

        $databaseFilePath =
            'uploads/incidents/' .
            $newFileName;

        // Save attachment information.
        $attachmentResult =
            $this->incidentModel->addAttachment(
                $incidentId,
                $reporterId,
                'Evidence',
                $originalName,
                $databaseFilePath,
                $actualMimeType
            );

        if (
            !$attachmentResult['success']
        ) {

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->jsonResponse(
                false,
                'Image uploaded but failed to save attachment information.'
            );
            return;
        }
    }
}


        /* =====================================================
           CREATE SLA
           ===================================================== */

        $slaResult =
            $this->slaLogicModel->createSLA(
                $incidentId
            );


        if (!$slaResult['success']) {

            $this->jsonResponse(
                true,
                'Incident submitted successfully, but SLA could not be created.',
                [
                    'incident_id' =>
                        $incidentId,

                    'priority' =>
                        $result['priority'],

                    'status' =>
                        $result['status'],

                    'category_id' =>
                        $result['category_id'],

                    'department_id' =>
                        $result['department_id'],

                    'sla_created' =>
                        false
                ]
            );

            return;
        }


        $this->jsonResponse(
            true,
            'Incident submitted successfully.',
            [
                'incident_id' =>
                    $incidentId,

                'priority' =>
                    $result['priority'],

                'status' =>
                    $result['status'],

                'category_id' =>
                    $result['category_id'],

                'department_id' =>
                    $result['department_id'],

                'sla_created' =>
                    true
            ]
        );
    }


    /* =========================================================
       GET ACTIVE CATEGORIES
       ========================================================= */

    public function getCategories()
    {
        $categories =
            $this->incidentModel
                ->getActiveCategories();


        $this->jsonResponse(
            true,
            'Categories retrieved successfully.',
            [
                'categories' =>
                    $categories
            ]
        );
    }


    /* =========================================================
       GET ACTIVE LOCATIONS
       ========================================================= */

    public function getLocations()
    {
        $locations =
            $this->locationModel
                ->getActiveLocations();


        $this->jsonResponse(
            true,
            'Locations retrieved successfully.',
            [
                'locations' =>
                    $locations
            ]
        );
    }


    /* =========================================================
       GET LOCATION BY QR CODE
       ========================================================= */

    public function getLocationByQr()
    {
        $qrCodeValue =
            trim(
                $_GET['qr_code_value'] ??
                $_POST['qr_code_value'] ??
                ''
            );


        if ($qrCodeValue === '') {
            $this->jsonResponse(
                false,
                'QR code value is required.'
            );
            return;
        }


        $location =
            $this->locationModel
                ->getLocationByQrCode(
                    $qrCodeValue
                );


        if (!$location) {
            $this->jsonResponse(
                false,
                'Location not found or inactive.'
            );
            return;
        }


        $this->jsonResponse(
            true,
            'Location found successfully.',
            [
                'location' =>
                    $location
            ]
        );
    }


    /* =========================================================
       SUGGEST CATEGORY
       ========================================================= */

    public function suggestCategory()
    {
        $title =
            trim(
                $_POST['title'] ?? ''
            );

        $description =
            trim(
                $_POST['description'] ?? ''
            );


        if (
            $title === '' &&
            $description === ''
        ) {
            $this->jsonResponse(
                false,
                'Please enter a title or description first.'
            );
            return;
        }


        $result =
            $this->incidentModel
                ->matchCategoryByKeywords(
                    $title,
                    $description
                );


        if ($result === null) {

            $this->jsonResponse(
                true,
                'No category suggestion found.',
                [
                    'matched' =>
                        false,

                    'category' =>
                        null
                ]
            );

            return;
        }


        $this->jsonResponse(
            true,
            'Category suggestion found.',
            [
                'matched' =>
                    true,

                'category' => [
                    'category_id' =>
                        $result['category_id'],

                    'category_name' =>
                        $result['category_name'],

                    'department_id' =>
                        $result['department_id'],

                    'department_name' =>
                        $result['department_name'],

                    'score' =>
                        $result['score']
                ]
            ]
        );
    }



    /* =========================================================
    GET SINGLE INCIDENT
    ========================================================= */

    public function getIncident()
    {
        $incidentId =
            (int) (
                $_GET['incident_id'] ?? 0
            );


        if ($incidentId <= 0) {

            $this->jsonResponse(
                false,
                'Invalid incident ID.'
            );

            return;
        }


        /*
        * Get incident information.
        */
        $incident =
            $this->incidentModel
                ->getIncidentById(
                    $incidentId
                );


        if (!$incident) {

            $this->jsonResponse(
                false,
                'Incident not found.'
            );

            return;
        }


        /*
        * Get SLA information.
        *
        * SLALogicModel already handles the
        * sla_records table, so we reuse it here.
        */
        $sla =
            $this->slaLogicModel
                ->getSLARecord(
                    $incidentId
                );


        /*
        * Add SLA information into the incident
        * response so Admin Incident Details JS
        * can access:
        *
        * incident.sla
        */
        $incident['sla'] =
            $sla ?: null;


        $this->jsonResponse(
            true,
            'Incident retrieved successfully.',
            [
                'incident' =>
                    $incident
            ]
        );
    }
    

    public function getIncidentUpdates()
    {
        $incidentId = (int) ($_GET['incident_id'] ?? 0);

        if ($incidentId <= 0) {
            $this->jsonResponse(false, 'Invalid incident ID.');
            return;
        }

        $incident = $this->incidentModel->getIncidentById($incidentId);

        if (!$incident) {
            $this->jsonResponse(false, 'Incident not found.');
            return;
        }

        $updates = $this->incidentModel->getIncidentUpdates($incidentId);

        $this->jsonResponse(
            true,
            'Incident updates retrieved successfully.',
            ['updates' => $updates]
        );
    }
    

/* =========================================================
   ADMIN - ADD INCIDENT NOTE
   ========================================================= */

    public function addAdminNote()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['user_role']) ||
            $_SESSION['user_role'] !== 'Admin'
        ) {
            $this->jsonResponse(false, 'Unauthorized.');
            return;
        }

        $incidentId = (int) ($_POST['incident_id'] ?? 0);
        $note = trim($_POST['message'] ?? '');
        $adminId = (int) $_SESSION['user_id'];

        if (
            $incidentId <= 0 ||
            $note === '' ||
            strlen($note) > 1000
        ) {
            $this->jsonResponse(false, 'Please enter a valid note.');
            return;
        }

        $result = $this->incidentModel->addAdminIncidentNote(
            $incidentId,
            $adminId,
            $note
        );

        $this->jsonResponse(
            $result['success'],
            $result['message']
        );
    }

    /* =========================================================
    GET INCIDENT ATTACHMENTS
    ========================================================= */

    public function getIncidentAttachments()
    {
        $incidentId =
            (int) (
                $_GET['incident_id'] ?? 0
            );


        if ($incidentId <= 0) {

            $this->jsonResponse(
                false,
                'Invalid incident ID.'
            );

            return;
        }


        /*
        * Make sure the incident exists first.
        */
        $incident =
            $this->incidentModel
                ->getIncidentById(
                    $incidentId
                );


        if (!$incident) {

            $this->jsonResponse(
                false,
                'Incident not found.'
            );

            return;
        }


        /*
        * Get all attachments belonging
        * to this incident.
        */
        $attachments =
            $this->incidentModel
                ->getIncidentAttachments(
                    $incidentId
                );


        $this->jsonResponse(
            true,
            'Incident attachments retrieved successfully.',
            [
                'attachments' =>
                    $attachments
            ]
        );
    }




        /* =========================================================
        REPORTER INCIDENTS
        ========================================================= */

        public function getMyIncidents()
        {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }


            if (!isset($_SESSION['user_id'])) {
                $this->jsonResponse(
                    false,
                    'Please login first.'
                );
                return;
            }


            $reporterId =
                (int) $_SESSION['user_id'];


            $incidents =
                $this->incidentModel
                    ->getIncidentsByReporter(
                        $reporterId
                    );


            $this->jsonResponse(
                true,
                'Incidents retrieved successfully.',
                [
                    'incidents' =>
                        $incidents
                ]
            );
        }

        /* =========================================================
   REPORTER - APPROVE OR REJECT RESOLUTION
   ========================================================= */

    public function reporterApproveIncident()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Only Reporter can perform this action.
        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['user_role']) ||
            $_SESSION['user_role'] !== 'Reporter'
        ) {
            $this->jsonResponse(
                false,
                'Unauthorized access.'
            );
            return;
        }

        $reporterId = (int) $_SESSION['user_id'];

        $incidentId = (int) ($_POST['incident_id'] ?? 0);

        $decision = trim($_POST['decision'] ?? '');

        $message = trim($_POST['message'] ?? '');

        if ($incidentId <= 0) {
            $this->jsonResponse(false, 'Invalid incident ID.');
            return;
        }

        if (!in_array($decision, ['approve', 'reject'], true)) {
            $this->jsonResponse(false, 'Invalid decision.');
            return;
        }

        // Check that this incident belongs to the Reporter.
        $incident = $this->incidentModel->getIncidentById($incidentId);

        if (
            !$incident ||
            (int) $incident['reporter_id'] !== $reporterId
        ) {
            $this->jsonResponse(
                false,
                'Incident not found.'
            );
            return;
        }

        // Only Resolved incidents can be reviewed.
        if ($incident['status'] !== 'Resolved') {
            $this->jsonResponse(
                false,
                'Only resolved incidents can be reviewed.'
            );
            return;
        }

        if ($decision === 'approve') {

            // Mark Reporter approval.
            $sql = "
                UPDATE incidents
                SET reporter_approved = 1
                WHERE incident_id = ?
                AND reporter_id = ?
                AND status = 'Resolved'
            ";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                $this->jsonResponse(false, 'Failed to prepare approval.');
                return;
            }

            $stmt->bind_param('ii', $incidentId, $reporterId);

            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                $this->jsonResponse(false, 'Failed to approve incident.');
                return;
            }

            $stmt->close();

            $updateMessage = 'Reporter confirmed that the incident has been fixed.';

            $status = 'Resolved';

        } else {

            if ($message === '') {
                $this->jsonResponse(
                    false,
                    'Please provide a reason why the incident is not fixed.'
                );
                return;
            }

            // Return incident to In Progress and reset approval.
            $this->conn->begin_transaction();

            try {

                $sql = "
                    UPDATE incidents
                    SET
                        status = 'In Progress',
                        reporter_approved = 0,
                        resolved_at = NULL
                    WHERE incident_id = ?
                    AND reporter_id = ?
                    AND status = 'Resolved'
                ";

                $stmt = $this->conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception('Failed to prepare incident update.');
                }

                $stmt->bind_param('ii', $incidentId, $reporterId);

                if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                    $stmt->close();
                    throw new Exception('Failed to return incident to In Progress.');
                }

                $stmt->close();

                $status = 'In Progress';

                $updateMessage = 'Reporter reported that the incident is not fixed: ' . $message;

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
                    throw new Exception('Failed to prepare incident history.');
                }

                $stmt->bind_param(
                    'iiss',
                    $incidentId,
                    $reporterId,
                    $status,
                    $updateMessage
                );

                if (!$stmt->execute()) {
                    $stmt->close();
                    throw new Exception('Failed to save incident history.');
                }

                $stmt->close();

                $this->conn->commit();

            } catch (Exception $e) {

                $this->conn->rollback();

                $this->jsonResponse(false, $e->getMessage());
                return;
            }
        }

        $this->jsonResponse(
            true,
            $decision === 'approve'
                ? 'Incident resolution approved successfully.'
                : 'Incident returned to In Progress.',
            [
                'status' => $status,
                'reporter_approved' => $decision === 'approve' ? 1 : 0
            ]
        );
    }


   
    /* =========================================================
       ADMIN - ALL INCIDENTS
       ========================================================= */

    public function getAllIncidents()
    {
        /*
         * Get all incidents first.
         */
        $incidents =
            $this->incidentModel
                ->getAllIncidents();


        /*
         * Refresh SLA status for each incident
         * before returning the list to JavaScript.
         */
        foreach ($incidents as $incident) {

            $incidentId =
                (int) ($incident['incident_id'] ?? 0);

            if ($incidentId > 0) {

                $this->slaLogicModel
                    ->updateSLAStatus($incidentId);
            }
        }


        /*
         * Retrieve the incidents again so the
         * response contains the updated SLA status.
         */
        $incidents =
            $this->incidentModel
                ->getAllIncidents();


        $this->jsonResponse(
            true,
            'Incidents retrieved successfully.',
            [
                'incidents' =>
                    $incidents
            ]
        );
    }



    /* =========================================================
       RESPONSE STAFF INCIDENTS
       ========================================================= */

    public function getStaffIncidents()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['user_role']) ||
            $_SESSION['user_role'] !==
                'Response Staff'
        ) {
            $this->jsonResponse(
                false,
                'Unauthorized access.'
            );
            return;
        }


        $staffId =
            (int) $_SESSION['user_id'];


        $incidents =
            $this->incidentModel
                ->getIncidentsByStaff(
                    $staffId
                );


        $this->jsonResponse(
            true,
            'Assigned incidents retrieved successfully.',
            [
                'incidents' =>
                    $incidents
            ]
        );
    }



/* =========================================================
   RESPONSE STAFF - INCIDENT DETAILS
   ========================================================= */

public function getStaffIncidentDetails()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check whether the user is Response Staff.
    if (
        !isset($_SESSION['user_id']) ||
        !isset($_SESSION['user_role']) ||
        $_SESSION['user_role'] !== 'Response Staff'
    ) {
        $this->jsonResponse(
            false,
            'Unauthorized access.'
        );
        return;
    }

    $staffId = (int) $_SESSION['user_id'];

    // Get the incident ID.
    $incidentId = (int) (
        $_GET['incident_id'] ??
        $_POST['incident_id'] ??
        0
    );

    if ($incidentId <= 0) {
        $this->jsonResponse(
            false,
            'Invalid incident ID.'
        );
        return;
    }

    // Get the incident assigned to this staff member.
    $incident = $this->incidentModel->getIncidentForStaff(
        $incidentId,
        $staffId
    );

    if (!$incident) {
        $this->jsonResponse(
            false,
            'Incident not found or not assigned to you.'
        );
        return;
    }

    // Refresh the SLA status.
    $this->slaLogicModel->updateSLAStatus($incidentId);

    // Retrieve the SLA record.
    $sla = $this->slaLogicModel->getSLARecord($incidentId);

    // Get incident attachments.
    $attachments = $this->incidentModel->getIncidentAttachments(
        $incidentId
    );

    // Get incident updates.
    $updates = $this->incidentModel->getIncidentUpdates(
        $incidentId
    );

    // Return the incident details and SLA information.
    $this->jsonResponse(
        true,
        'Incident details retrieved successfully.',
        [
            'incident' => $incident,
            'sla' => $sla ?: null,
            'attachments' => $attachments,
            'updates' => $updates
        ]
    );
}

    /* =========================================================
       RESPONSE STAFF - UPDATE STATUS
       ========================================================= */

    public function updateStatus()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['user_role'])
        ) {
            $this->jsonResponse(
                false,
                'Please login first.'
            );
            return;
        }


        if (
            $_SESSION['user_role'] !==
            'Response Staff'
        ) {
            $this->jsonResponse(
                false,
                'Unauthorized access.'
            );
            return;
        }


        $userId =
            (int) $_SESSION['user_id'];


        $incidentId =
            (int) (
                $_POST['incident_id'] ?? 0
            );


        $newStatus =
            trim(
                $_POST['status'] ?? ''
            );


        /*
         * IMPORTANT:
         * JavaScript sends "message".
         */
        $updateMessage =
            trim(
                $_POST['message'] ?? ''
            );


        if ($incidentId <= 0) {
            $this->jsonResponse(
                false,
                'Invalid incident ID.'
            );
            return;
        }


        if ($newStatus === '') {
            $this->jsonResponse(
                false,
                'Please select a status.'
            );
            return;
        }


        /*
         * Response Staff can only:
         *
         * Assigned -> In Progress
         * In Progress -> Resolved
         */
        if (
            !in_array(
                $newStatus,
                [
                    'In Progress',
                    'Resolved'
                ],
                true
            )
        ) {
            $this->jsonResponse(
                false,
                'Invalid status for Response Staff.'
            );
            return;
        }


        /*
         * Check assignment.
         */
        $incident =
            $this->incidentModel
                ->getIncidentForStaff(
                    $incidentId,
                    $userId
                );


        if (!$incident) {
            $this->jsonResponse(
                false,
                'Incident not found or not assigned to you.'
            );
            return;
        }


        $currentStatus =
            $incident['status'];


        /*
         * Assigned -> In Progress
         */
        if (
            $newStatus === 'In Progress' &&
            $currentStatus !== 'Assigned'
        ) {
            $this->jsonResponse(
                false,
                'Only assigned incidents can be changed to In Progress.'
            );
            return;
        }


        /*
         * In Progress -> Resolved
         */
        if (
            $newStatus === 'Resolved' &&
            $currentStatus !== 'In Progress'
        ) {
            $this->jsonResponse(
                false,
                'Only incidents in progress can be marked as Resolved.'
            );
            return;
        }


        /*
         * Completion proof is required before
         * an incident can be resolved.
         */
        if (
            $newStatus === 'Resolved'
        ) {

            $attachments =
                $this->incidentModel
                    ->getIncidentAttachments(
                        $incidentId
                    );


            $hasCompletionProof = false;


            foreach (
                $attachments
                as $attachment
            ) {

                if (
                    $attachment['attachment_type'] ===
                    'Completion Proof'
                ) {
                    $hasCompletionProof = true;
                    break;
                }

            }


            if (!$hasCompletionProof) {
                $this->jsonResponse(
                    false,
                    'Completion proof is required before the incident can be resolved.'
                );
                return;
            }


            /*
             * Resolution should contain a message.
             */
            if ($updateMessage === '') {
                $this->jsonResponse(
                    false,
                    'Please provide an update message describing the work completed.'
                );
                return;
            }
        }


        /*
         * Start Work does not require the staff
         * to type a message.
         *
         * Add a default update message instead.
         */
        if (
            $newStatus === 'In Progress' &&
            $updateMessage === ''
        ) {
            $updateMessage =
                'Started work on the incident.';
        }


        /*
         * Update incident.
         */
        $result =
            $this->incidentModel
                ->updateIncidentStatus(
                    $incidentId,
                    $newStatus,
                    $userId,
                    $updateMessage
                );


        if (!$result['success']) {
            $this->jsonResponse(
                false,
                $result['message']
            );
            return;
        }


        /*
         * Update SLA.
         */
        $slaResult =
            $this->slaLogicModel
                ->updateSLAStatus(
                    $incidentId
                );


        $this->jsonResponse(
            true,
            'Incident status updated successfully.',
            [
                'status' =>
                    $newStatus,

                'sla_updated' =>
                    $slaResult['success']
            ]
        );
    }


    /* =========================================================
       RESPONSE STAFF - UPLOAD COMPLETION PROOF
       ========================================================= */

    public function uploadCompletionProof()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['user_role'])
        ) {
            $this->jsonResponse(
                false,
                'Please login first.'
            );
            return;
        }


        if (
            $_SESSION['user_role'] !==
            'Response Staff'
        ) {
            $this->jsonResponse(
                false,
                'Unauthorized access.'
            );
            return;
        }


        $staffId =
            (int) $_SESSION['user_id'];


        $incidentId =
            (int) (
                $_POST['incident_id'] ?? 0
            );


        if ($incidentId <= 0) {
            $this->jsonResponse(
                false,
                'Invalid incident ID.'
            );
            return;
        }


        $incident =
            $this->incidentModel
                ->getIncidentForStaff(
                    $incidentId,
                    $staffId
                );


        if (!$incident) {
            $this->jsonResponse(
                false,
                'Incident not found or not assigned to you.'
            );
            return;
        }


        /*
         * Completion proof should only be uploaded
         * while the incident is being worked on.
         */
        if (
            $incident['status'] !==
            'In Progress'
        ) {
            $this->jsonResponse(
                false,
                'Completion proof can only be uploaded while the incident is In Progress.'
            );
            return;
        }


        /*
         * IMPORTANT:
         * JavaScript sends "completion_file".
         */
        if (
            !isset($_FILES['completion_file']) ||
            $_FILES['completion_file']['error'] ===
                UPLOAD_ERR_NO_FILE
        ) {
            $this->jsonResponse(
                false,
                'Please select a completion proof file.'
            );
            return;
        }


        $file =
            $_FILES['completion_file'];


        if (
            $file['error'] !==
            UPLOAD_ERR_OK
        ) {
            $this->jsonResponse(
                false,
                'Failed to upload completion proof.'
            );
            return;
        }


        /*
         * Maximum file size: 5 MB.
         */
        if (
            $file['size'] >
            5 * 1024 * 1024
        ) {
            $this->jsonResponse(
                false,
                'File size must not exceed 5 MB.'
            );
            return;
        }


        /*
         * Detect actual MIME type.
         */
        $finfo =
            new finfo(FILEINFO_MIME_TYPE);


        $actualMimeType =
            $finfo->file(
                $file['tmp_name']
            );


        $allowedTypes = [
            'image/jpeg' =>
                'jpg',

            'image/png' =>
                'png',
            
            'video/mp4' =>
                'mp4',

            'application/pdf' =>
                'pdf',

            'application/msword' =>
                'doc',

            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                => 'docx'
        ];


        if (
            !array_key_exists(
                $actualMimeType,
                $allowedTypes
            )
        ) {
            $this->jsonResponse(
                false,
                'Only JPG, PNG, PDF, DOC and DOCX files are allowed.'
            );
            return;
        }


        $extension =
            $allowedTypes[
                $actualMimeType
            ];


        /*
         * Completion proof directory.
         */
        $uploadDirectory =
            __DIR__ .
            '/../../uploads/completion_proof/';


        if (!is_dir($uploadDirectory)) {

            if (
                !mkdir(
                    $uploadDirectory,
                    0755,
                    true
                )
            ) {
                $this->jsonResponse(
                    false,
                    'Failed to create completion proof upload directory.'
                );
                return;
            }
        }


        /*
         * Generate safe filename.
         */
        $newFileName =
            'completion_' .
            $incidentId .
            '_' .
            bin2hex(
                random_bytes(8)
            ) .
            '.' .
            $extension;


        $filePath =
            $uploadDirectory .
            $newFileName;


        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $filePath
            )
        ) {
            $this->jsonResponse(
                false,
                'Failed to save completion proof.'
            );
            return;
        }


        /*
         * Path saved in database.
         */
        $databaseFilePath =
            'uploads/completion_proof/' .
            $newFileName;


        /*
         * Save attachment information.
         */
        $attachmentResult =
            $this->incidentModel
                ->addAttachment(
                    $incidentId,
                    $staffId,
                    'Completion Proof',
                    $file['name'],
                    $databaseFilePath,
                    $actualMimeType
                );


        if (
            !$attachmentResult['success']
        ) {

            if (
                file_exists($filePath)
            ) {
                unlink($filePath);
            }


            $this->jsonResponse(
                false,
                'File uploaded but failed to save attachment information.'
            );
            return;
        }


        $this->jsonResponse(
            true,
            'Completion proof uploaded successfully.',
            [
                'attachment' =>
                    [
                        'file_name' =>
                            $file['name'],

                        'file_path' =>
                            $databaseFilePath,

                        'file_type' =>
                            $actualMimeType
                    ]
            ]
        );
    }


    /* =========================================================
       RESPONSE STAFF - RAISE INCIDENT TO ADMIN
       ========================================================= */

    public function raiseIncidentToAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (
            !isset($_SESSION['user_id']) ||
            !isset($_SESSION['user_role'])
        ) {
            $this->jsonResponse(
                false,
                'Please login first.'
            );
            return;
        }


        if (
            $_SESSION['user_role'] !==
            'Response Staff'
        ) {
            $this->jsonResponse(
                false,
                'Unauthorized access.'
            );
            return;
        }


        $staffId =
            (int) $_SESSION['user_id'];


        $incidentId =
            (int) (
                $_POST['incident_id'] ?? 0
            );


        $reason =
            trim(
                $_POST['reason'] ?? ''
            );


        if ($incidentId <= 0) {
            $this->jsonResponse(
                false,
                'Invalid incident ID.'
            );
            return;
        }


        if ($reason === '') {
            $this->jsonResponse(
                false,
                'Please provide a reason for raising the incident.'
            );
            return;
        }


        $incident =
            $this->incidentModel
                ->getIncidentForStaff(
                    $incidentId,
                    $staffId
                );


        if (!$incident) {
            $this->jsonResponse(
                false,
                'Incident not found or not assigned to you.'
            );
            return;
        }


        /*
         * Cannot raise a closed incident.
         */
        if (
            $incident['status'] ===
            'Closed'
        ) {
            $this->jsonResponse(
                false,
                'Closed incidents cannot be raised to Admin.'
            );
            return;
        }


        /*
         * Cannot raise a resolved incident.
         */
        if (
            $incident['status'] ===
            'Resolved'
        ) {
            $this->jsonResponse(
                false,
                'Resolved incidents cannot be raised to Admin.'
            );
            return;
        }



        $result =
            $this->incidentModel
                ->raiseIncidentToAdmin(
                    $incidentId,
                    $staffId,
                    $reason
                );


        if (!$result) {
            $this->jsonResponse(
                false,
                'Failed to raise incident to Admin. Please try again.'
            );
            return;
        }


        $this->jsonResponse(
            true,
            'Incident has been raised to Admin successfully.'
        );

    }


    /* =========================================================
       JSON RESPONSE
       ========================================================= */

    private function jsonResponse(
        $success,
        $message,
        $data = []
    ) {
        header(
            'Content-Type: application/json'
        );


        echo json_encode(
            array_merge(
                [
                    'success' =>
                        $success,

                    'message' =>
                        $message
                ],
                $data
            )
        );


        exit;
    }

    


public function getStaffAlerts()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (
        !isset($_SESSION['user_id']) ||
        !isset($_SESSION['user_role']) ||
        $_SESSION['user_role'] !== 'Response Staff'
    ) {
        $this->jsonResponse(false, 'Unauthorized access.');
        return;
    }

    $staffId = (int) $_SESSION['user_id'];

    $incidents = $this->incidentModel->getIncidentsByStaff($staffId);

    // Debug: Check incident information
    error_log("STAFF INCIDENTS: " . json_encode(
        array_map(function ($incident) {
            return [
                'id' => $incident['incident_id'],
                'status' => $incident['status'],
                'sla_status' => $incident['sla_status'],
                'reporter_approved' => $incident['reporter_approved'] ?? null
            ];
        }, $incidents)
    ));

    $assignmentAlerts = [];
    $adminAlerts = [];
    $attentionAlerts = [];
    $reworkAlerts = [];

    foreach ($incidents as $incident) {

        $status = strtolower(trim($incident['status'] ?? ''));
        $sla = strtolower(trim($incident['sla_status'] ?? ''));

        // Exclude resolved and closed incidents from all alerts.
        if (in_array($status, ['resolved', 'closed'], true)) {
            continue;
        }

        // Rework Required
        if (
            array_key_exists('reporter_approved', $incident) &&
            $incident['reporter_approved'] !== null &&
            (int) $incident['reporter_approved'] === 0 &&
            $status === 'in progress'
        ) {
            $reworkAlerts[] = $incident;
        }

        // New / Reassigned Incidents
        if ($status === 'assigned') {
            $assignmentAlerts[] = $incident;
        }

        // Admin Updates
        $updates = $this->incidentModel->getIncidentUpdates(
            $incident['incident_id']
        );

        $latestAdminUpdate = null;
        $latestIncidentUpdate = null;

        foreach ($updates as $update) {

            // Get the latest update from any user.
            if (
                $latestIncidentUpdate === null ||
                strtotime($update['created_at']) >
                strtotime($latestIncidentUpdate['created_at']) ||
                (
                    strtotime($update['created_at']) ===
                    strtotime($latestIncidentUpdate['created_at']) &&
                    $update['update_id'] > $latestIncidentUpdate['update_id']
                )
            ) {
                $latestIncidentUpdate = $update;
            }

            // Get the latest update from Admin.
            if (($update['role'] ?? '') === 'Admin') {

                if (
                    $latestAdminUpdate === null ||
                    strtotime($update['created_at']) >
                    strtotime($latestAdminUpdate['created_at']) ||
                    (
                        strtotime($update['created_at']) ===
                        strtotime($latestAdminUpdate['created_at']) &&
                        $update['update_id'] > $latestAdminUpdate['update_id']
                    )
                ) {
                    $latestAdminUpdate = $update;
                }
            }
        }

        // Show Admin Updates only when the latest incident activity
        // was made by Admin.
        if (
            $latestAdminUpdate !== null &&
            $latestIncidentUpdate !== null &&
            $latestIncidentUpdate['update_id'] ===
            $latestAdminUpdate['update_id']
        ) {
            $adminAlerts[] = [
                'incident_id' => $incident['incident_id'],
                'title' => $incident['title'],
                'update_message' =>
                    $latestAdminUpdate['update_message'] ?? '',
                'created_at' =>
                    $latestAdminUpdate['created_at'] ?? ''
            ];
        }

        // SLA Overdue Incidents
        if ($sla === 'overdue') {
            $attentionAlerts[] = $incident;
        }
    }

    // Debug: Check alert results
    error_log("STAFF ALERTS: " . json_encode([
        'assignments' => array_column($assignmentAlerts, 'incident_id'),
        'admin_updates' => array_column($adminAlerts, 'incident_id'),
        'attention' => array_column($attentionAlerts, 'incident_id'),
        'rework' => array_column($reworkAlerts, 'incident_id')
    ]));

    // Return alert data
    $this->jsonResponse(
        true,
        'Staff alerts retrieved successfully.',
        [
            'assignments' => $assignmentAlerts,
            'admin_updates' => $adminAlerts,
            'attention' => $attentionAlerts,
            'rework' => $reworkAlerts
        ]
    );
}





    /* =========================================================
       ACTION ROUTER
       ========================================================= */

    public function handleRequest()
    {
        $action =
            $_GET['action'] ??
            $_POST['action'] ??
            '';


        switch ($action) {

            /* -------------------------
               Reporter
               ------------------------- */

            case 'create_incident':
                $this->createIncident();
                break;


            case 'get_categories':
                $this->getCategories();
                break;


            case 'get_locations':
                $this->getLocations();
                break;


            case 'get_location_by_qr':
                $this->getLocationByQr();
                break;


            case 'suggest_category':
                $this->suggestCategory();
                break;


            case 'get_incident':
                $this->getIncident();
                break;


            case 'get_my_incidents':
                $this->getMyIncidents();
                break;

            case 'reporter_approve_incident':
                $this->reporterApproveIncident();
                break;


            /* -------------------------
               Admin
               ------------------------- */

            case 'get_all_incidents':
                $this->getAllIncidents();
                break;


            case 'get_incident_attachments':
                $this->getIncidentAttachments();
                break;


            case 'get_incident_updates':
                $this->getIncidentUpdates();
                break;



            case 'assign_incident':
                $this->assignIncident();
                break;

            // ADMIN ADD NOTES
            case 'add_admin_note':
                $this->addAdminNote();
                break;


            /* -------------------------
               Response Staff
               ------------------------- */

            case 'get_staff_incidents':
                $this->getStaffIncidents();
                break;


            case 'get_staff_incident_details':
                $this->getStaffIncidentDetails();
                break;

            case 'get_staff_alerts':
                $this->getStaffAlerts();
                break;


            /*
             * IMPORTANT:
             * This now matches the JavaScript:
             *
             * action=update_staff_incident_status
             */
            case 'update_staff_incident_status':
                $this->updateStatus();
                break;


            case 'upload_completion_proof':
                $this->uploadCompletionProof();
                break;


            case 'raise_incident_to_admin':
                $this->raiseIncidentToAdmin();
                break;


            /* -------------------------
               Invalid
               ------------------------- */

            default:
                $this->jsonResponse(
                    false,
                    'Invalid action.'
                );
        }
    }
}



/* =========================================================
   DIRECT REQUEST HANDLER
   ========================================================= */

$controller =
    new IncidentController($conn);

$controller->handleRequest();