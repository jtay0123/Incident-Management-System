<?php

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../model/LocationModel.php';


class LocationController
{
    private $locationModel;

    public function __construct($conn)
    {
        $this->locationModel = new LocationModel($conn);
    }


    // ==========================================
    // GET LOCATIONS
    // ==========================================
    public function getLocations()
    {
        header('Content-Type: application/json');

        $locations = $this->locationModel->getLocations();

        echo json_encode([
            'success' => true,
            'locations' => $locations
        ]);

        exit();
    }


    // ==========================================
    // GET LOCATION BY ID
    // ==========================================
    public function getLocation()
    {
        header('Content-Type: application/json');

        $locationId = isset($_GET['location_id'])
            ? (int) $_GET['location_id']
            : 0;

        if ($locationId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid location ID.'
            ]);
            exit();
        }

        $location = $this->locationModel->getLocationById($locationId);

        if (!$location) {
            echo json_encode([
                'success' => false,
                'message' => 'Location not found.'
            ]);
            exit();
        }

        echo json_encode([
            'success' => true,
            'location' => $location
        ]);

        exit();
    }


    // ==========================================
    // CREATE LOCATION
    // ==========================================
    public function createLocation()
    {
        $locationName = trim($_POST['location_name'] ?? '');
        $buildingName = trim($_POST['building_name'] ?? '');
        $floorNumber = trim($_POST['floor_number'] ?? '');


        // ==========================================
        // VALIDATION
        // ==========================================

        if (
            empty($locationName) ||
            empty($buildingName) ||
            $floorNumber === ''
        ) {
            $this->redirectWithError(
                'Please fill in all required fields.'
            );
        }


        // ==========================================
        // CHECK DUPLICATE LOCATION
        // ==========================================

        if (
            $this->locationModel->locationExists(
                $locationName,
                $buildingName,
                $floorNumber
            )
        ) {
            $this->redirectWithError(
                'This location already exists.'
            );
        }


        // ==========================================
        // GENERATE QR CODE VALUE
        // ==========================================

        $qrCodeValue = $this->locationModel->generateQrCodeValue();

        if (!$qrCodeValue) {
            $this->redirectWithError(
                'Failed to generate QR code value.'
            );
        }


        // ==========================================
        // CREATE LOCATION
        // ==========================================

        $success = $this->locationModel->createLocation(
            $locationName,
            $buildingName,
            $floorNumber,
            $qrCodeValue
        );


        if ($success) {
            $this->redirectWithSuccess(
                'Location created successfully.'
            );
        }


        $this->redirectWithError(
            'Failed to create location.'
        );
    }


    // ==========================================
    // UPDATE LOCATION
    // ==========================================
    public function updateLocation()
    {
        header('Content-Type: application/json');

        $locationId = isset($_POST['location_id'])
            ? (int) $_POST['location_id']
            : 0;

        $locationName = trim($_POST['location_name'] ?? '');
        $buildingName = trim($_POST['building_name'] ?? '');
        $floorNumber = trim($_POST['floor_number'] ?? '');


        // ==========================================
        // VALIDATION
        // ==========================================

        if ($locationId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid location ID.'
            ]);
            exit();
        }

        if (
            empty($locationName) ||
            empty($buildingName) ||
            $floorNumber === ''
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields.'
            ]);
            exit();
        }


        // ==========================================
        // CHECK LOCATION EXISTS
        // ==========================================

        $currentLocation = $this->locationModel->getLocationById(
            $locationId
        );

        if (!$currentLocation) {
            echo json_encode([
                'success' => false,
                'message' => 'Location not found.'
            ]);
            exit();
        }


        // ==========================================
        // CHECK DUPLICATE LOCATION
        // ==========================================

        if (
            $this->locationModel->locationExists(
                $locationName,
                $buildingName,
                $floorNumber,
                $locationId
            )
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'This location already exists.'
            ]);
            exit();
        }


        // ==========================================
        // UPDATE LOCATION
        // ==========================================

        $success = $this->locationModel->updateLocation(
            $locationId,
            $locationName,
            $buildingName,
            $floorNumber
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Location updated successfully.'
                : 'Failed to update location.'
        ]);

        exit();
    }


    // ==========================================
    // UPDATE LOCATION STATUS
    // ==========================================
    public function updateStatus()
    {
        header('Content-Type: application/json');

        $locationId = isset($_POST['location_id'])
            ? (int) $_POST['location_id']
            : 0;

        $status = trim($_POST['status'] ?? '');


        // ==========================================
        // VALIDATION
        // ==========================================

        if ($locationId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid location ID.'
            ]);
            exit();
        }

        if (!in_array($status, ['Active', 'Inactive'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid location status.'
            ]);
            exit();
        }


        // ==========================================
        // UPDATE STATUS
        // ==========================================

        $success = $this->locationModel->updateLocationStatus(
            $locationId,
            $status
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Location status updated successfully.'
                : 'Failed to update location status.'
        ]);

        exit();
    }


    // ==========================================
    // DELETE LOCATION
    // ==========================================
    public function deleteLocation()
    {
        header('Content-Type: application/json');

        $locationId = isset($_POST['location_id'])
            ? (int) $_POST['location_id']
            : 0;


        // ==========================================
        // VALIDATION
        // ==========================================

        if ($locationId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid location ID.'
            ]);
            exit();
        }


        // ==========================================
        // DELETE LOCATION
        // ==========================================

        $success = $this->locationModel->deleteLocation(
            $locationId
        );


        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Location deleted successfully.'
                : 'Failed to delete location.'
        ]);

        exit();
    }


    // ==========================================
    // REDIRECT WITH SUCCESS
    // ==========================================
    private function redirectWithSuccess($message)
    {
        $_SESSION['success_message'] = $message;

        header(
            'Location: ' .
            BASE_URL .
            '/app/view/dashboard.php?page=manage_locations'
        );

        exit();
    }


    // ==========================================
    // REDIRECT WITH ERROR
    // ==========================================
    private function redirectWithError($message)
    {
        $_SESSION['error_message'] = $message;

        header(
            'Location: ' .
            BASE_URL .
            '/app/view/dashboard.php?page=manage_locations'
        );

        exit();
    }
}


// ==================================================
// REQUEST HANDLER
// ==================================================

$controller = new LocationController($conn);

$action = $_GET['action'] ?? $_POST['action'] ?? '';


switch ($action) {

    case 'get_locations':
        $controller->getLocations();
        break;

    case 'get_location':
        $controller->getLocation();
        break;

    case 'create_location':
        $controller->createLocation();
        break;

    case 'update_location':
        $controller->updateLocation();
        break;

    case 'updateStatus':
        $controller->updateStatus();
        break;

    case 'delete':
        $controller->deleteLocation();
        break;

    default:
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Invalid action.'
        ]);

        exit();
}