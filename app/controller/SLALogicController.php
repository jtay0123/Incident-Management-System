<?php

session_start();

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../model/SLALogicModel.php';


class SLALogicController
{
    private $slaLogicModel;


    public function __construct($conn)
    {
        $this->slaLogicModel = new SLALogicModel($conn);
    }


    // ==========================================
    // CREATE SLA FOR INCIDENT
    // ==========================================
    public function createSLA()
    {
        $incidentId = (int) ($_POST['incident_id'] ?? 0);


        if ($incidentId <= 0) {
            $this->jsonResponse(
                false,
                'Invalid incident ID.'
            );
        }


        $result = $this->slaLogicModel->createSLA(
            $incidentId
        );


        $this->jsonResponse(
            $result['success'] ?? false,
            $result['message'] ?? 'Failed to create SLA.'
        );
    }


    // ==========================================
    // UPDATE SLA STATUS
    // ==========================================
    public function updateSLAStatus()
    {
        $incidentId = (int) ($_POST['incident_id'] ?? 0);


        if ($incidentId <= 0) {
            $this->jsonResponse(
                false,
                'Invalid incident ID.'
            );
        }



        $result = $this->slaLogicModel->updateSLAStatus(
            $incidentId
        );

        if (!$result['success']) {
            $this->jsonResponse(
                false,
                $result['message'] ?? 'Failed to update SLA status.'
            );
        }

        $record = $this->slaLogicModel->getSLARecord(
            $incidentId
        );



        $this->jsonResponse(
            $result['success'] ?? false,
            $result['message'] ?? 'Failed to update SLA status.'
        );
    }


    // ==========================================
    // GET SLA RECORD
    // ==========================================
    public function getSLARecord()
    {
        $incidentId = (int) ($_GET['incident_id'] ?? 0);


        if ($incidentId <= 0) {
            $this->jsonResponse(
                false,
                'Invalid incident ID.'
            );
        }


        $record = $this->slaLogicModel->getSLARecord(
            $incidentId
        );


        if (!$record) {
            $this->jsonResponse(
                false,
                'SLA record not found.'
            );
        }


        $this->jsonResponse(
            true,
            'SLA record retrieved successfully.',
            [
                'sla' => $record
            ]
        );
    }


    // ==========================================
    // JSON RESPONSE
    // ==========================================
    private function jsonResponse(
        $success,
        $message,
        $data = []
    ) {
        header('Content-Type: application/json');


        echo json_encode(
            array_merge(
                [
                    'success' => $success,
                    'message' => $message
                ],
                $data
            )
        );


        exit();
    }
}


// ==================================================
// REQUEST HANDLER
// ==================================================

$controller = new SLALogicController($conn);

$action = $_GET['action'] ?? $_POST['action'] ?? '';


switch ($action) {

    case 'create_sla':

        $controller->createSLA();

        break;


    case 'update_sla_status':

        $controller->updateSLAStatus();

        break;


    case 'get_sla_record':

        $controller->getSLARecord();

        break;


    default:

        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Invalid action.'
        ]);

        exit();
}