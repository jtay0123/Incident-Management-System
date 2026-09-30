<?php

class LocationModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    // ==========================================
    // GET ALL LOCATIONS
    // ==========================================
    public function getLocations()
    {
        $stmt = $this->conn->prepare("
            SELECT
                location_id,
                location_name,
                building_name,
                floor_number,
                qr_code_value,
                status,
                created_at
            FROM locations
            ORDER BY location_id DESC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $locations = [];

        while ($row = $result->fetch_assoc()) {
            $locations[] = $row;
        }

        $stmt->close();

        return $locations;
    }


    // ==========================================
    // GET ONE LOCATION BY ID
    // ==========================================
    public function getLocationById($locationId)
    {
        $stmt = $this->conn->prepare("
            SELECT
                location_id,
                location_name,
                building_name,
                floor_number,
                qr_code_value,
                status,
                created_at
            FROM locations
            WHERE location_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "i",
            $locationId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $location = $result->fetch_assoc();

        $stmt->close();

        return $location ?: null;
    }


    // ==========================================
    // CHECK DUPLICATE LOCATION
    // ==========================================
    public function locationExists(
        $locationName,
        $buildingName,
        $floorNumber,
        $excludeLocationId = 0
    ) {
        $stmt = $this->conn->prepare("
            SELECT location_id
            FROM locations
            WHERE location_name = ?
              AND building_name = ?
              AND floor_number = ?
              AND location_id != ?
            LIMIT 1
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sssi",
            $locationName,
            $buildingName,
            $floorNumber,
            $excludeLocationId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }


    // ==========================================
    // CREATE LOCATION
    // ==========================================
    public function createLocation(
        $locationName,
        $buildingName,
        $floorNumber,
        $qrCodeValue
    ) {
        $status = 'Active';

        $stmt = $this->conn->prepare("
            INSERT INTO locations
            (
                location_name,
                building_name,
                floor_number,
                qr_code_value,
                status,
                created_at
            )
            VALUES
            (?, ?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sssss",
            $locationName,
            $buildingName,
            $floorNumber,
            $qrCodeValue,
            $status
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // UPDATE LOCATION STATUS
    // ==========================================
    public function updateLocationStatus(
        $locationId,
        $status
    ) {
        $allowedStatuses = [
            'Active',
            'Inactive'
        ];

        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE locations
            SET status = ?
            WHERE location_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $status,
            $locationId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // UPDATE LOCATION
    // ==========================================
    public function updateLocation(
        $locationId,
        $locationName,
        $buildingName,
        $floorNumber
    ) {
        $stmt = $this->conn->prepare("
            UPDATE locations
            SET
                location_name = ?,
                building_name = ?,
                floor_number = ?
            WHERE location_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sssi",
            $locationName,
            $buildingName,
            $floorNumber,
            $locationId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // DELETE LOCATION
    // ==========================================
    public function deleteLocation($locationId)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM locations
            WHERE location_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "i",
            $locationId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // GENERATE QR CODE VALUE
    // ==========================================
    public function generateQrCodeValue()
    {
        do {
            $qrCodeValue = 'LOC-' . strtoupper(
                substr(bin2hex(random_bytes(4)), 0, 6)
            );

            $stmt = $this->conn->prepare("
                SELECT location_id
                FROM locations
                WHERE qr_code_value = ?
                LIMIT 1
            ");

            if (!$stmt) {
                return false;
            }

            $stmt->bind_param(
                "s",
                $qrCodeValue
            );

            $stmt->execute();

            $result = $stmt->get_result();

            $exists = $result->num_rows > 0;

            $stmt->close();

        } while ($exists);

        return $qrCodeValue;
    }
}