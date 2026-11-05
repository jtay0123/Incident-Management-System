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
        $isSensitive = 0;


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
                $isSensitive
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
            $_FILES['incident_image']['error']
                !== UPLOAD_ERR_NO_FILE
        ) {

            $image =
                $_FILES['incident_image'];


            if (
                $image['error'] !==
                UPLOAD_ERR_OK
            ) {
                $this->jsonResponse(
                    false,
                    'Failed to upload incident image.'
                );
                return;
            }


            $allowedTypes = [
                'image/jpeg',
                'image/png',
                'video/mp4'
            ];


            $finfo =
                new finfo(FILEINFO_MIME_TYPE);


            $actualMimeType =
                $finfo->file(
                    $image['tmp_name']
                );


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


            if (
                $image['size'] >
                5 * 1024 * 1024
            ) {
                $this->jsonResponse(
                    false,
                    'Image size must not exceed 5 MB.'
                );
                return;
            }


            $uploadDirectory =
                __DIR__ .
                '/../../uploads/incidents/';


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
                        'Failed to create image upload directory.'
                    );
                    return;
                }
            }


            $extension =
                (
                    $actualMimeType ===
                    'image/png'
                )
                    ? 'png'
                    : 'jpg';


            $newFileName =
                'incident_' .
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
                    $image['tmp_name'],
                    $filePath
                )
            ) {
                $this->jsonResponse(
                    false,
                    'Failed to save incident image.'
                );
                return;
            }


            $databaseFilePath =
                'uploads/incidents/' .
                $newFileName;


            $attachmentResult =
                $this->incidentModel->addAttachment(
                    $incidentId,
                    $reporterId,
                    'Evidence',
                    $image['name'],
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
                    'Image uploaded but failed to save attachment information.'
                );
                return;
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