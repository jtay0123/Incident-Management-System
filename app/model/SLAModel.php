<?php

class SLAModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    // ==========================================
    // GET ALL SLA SETTINGS
    // ==========================================
    public function getSLASettings()
    {
        $stmt = $this->conn->prepare("
            SELECT
                setting_id,
                priority,
                sla_hours,
                updated_at,
                updated_by
            FROM sla_settings
            ORDER BY
                FIELD(
                    priority,
                    'Emergency',
                    'High',
                    'Medium',
                    'Low'
                )
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $settings = [];

        while ($row = $result->fetch_assoc()) {
            $settings[] = $row;
        }

        $stmt->close();

        return $settings;
    }


    // ==========================================
    // UPDATE SLA SETTINGS
    // ==========================================
    public function updateSLASettings($settings, $updatedBy = null)
{
    $stmt = $this->conn->prepare("
        UPDATE sla_settings
        SET sla_hours = ?, updated_by = ?
        WHERE priority = ?
    ");

    if (!$stmt) {
        return false;
    }

    $this->conn->begin_transaction();   // NEW

    foreach ($settings as $priority => $slaHours) {

        $slaHours = (int) $slaHours;

        $stmt->bind_param("iis", $slaHours, $updatedBy, $priority);

        if (!$stmt->execute()) {
            $this->conn->rollback();    // NEW
            $stmt->close();
            return false;
        }
    }

    $this->conn->commit();              // NEW
    $stmt->close();

    return true;
}


    // ==========================================
    // GET ALL WORKING HOURS
    // ==========================================
    public function getWorkingHours()
    {
        $stmt = $this->conn->prepare("
            SELECT
                working_hour_id,
                day_of_week,
                start_time,
                end_time,
                is_working_day
            FROM working_hours
            ORDER BY
                FIELD(
                    day_of_week,
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday'
                )
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $workingHours = [];

        while ($row = $result->fetch_assoc()) {
            $workingHours[] = $row;
        }

        $stmt->close();

        return $workingHours;
    }


    // ==========================================
    // UPDATE WORKING HOURS
    // ==========================================
    public function updateWorkingHours($workingHours)
    {
        $stmt = $this->conn->prepare("
            UPDATE working_hours
            SET
                start_time = ?,
                end_time = ?,
                is_working_day = ?
            WHERE day_of_week = ?
        ");

        if (!$stmt) {
            return false;
        }

        foreach ($workingHours as $day => $data) {

            $isWorkingDay = isset($data['is_working_day'])
                ? (int) $data['is_working_day']
                : 0;

            if ($isWorkingDay === 1) {

                $startTime = trim(
                    $data['start_time'] ?? ''
                );

                $endTime = trim(
                    $data['end_time'] ?? ''
                );

            } else {

                $startTime = null;
                $endTime = null;
            }


            $stmt->bind_param(
                "ssis",
                $startTime,
                $endTime,
                $isWorkingDay,
                $day
            );

            if (!$stmt->execute()) {
                $stmt->close();
                return false;
            }
        }

        $stmt->close();

        return true;
    }


    // ==========================================
    // GET ALL PUBLIC HOLIDAYS
    // ==========================================
    public function getPublicHolidays()
    {
        $stmt = $this->conn->prepare("
            SELECT
                holiday_id,
                holiday_date,
                holiday_name,
                status,
                created_at
            FROM public_holidays
            ORDER BY holiday_date ASC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $holidays = [];

        while ($row = $result->fetch_assoc()) {
            $holidays[] = $row;
        }

        $stmt->close();

        return $holidays;
    }


    // ==========================================
    // GET ONE PUBLIC HOLIDAY BY ID
    // ==========================================
    public function getPublicHolidayById($holidayId)
    {
        $stmt = $this->conn->prepare("
            SELECT
                holiday_id,
                holiday_date,
                holiday_name,
                status,
                created_at
            FROM public_holidays
            WHERE holiday_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "i",
            $holidayId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $holiday = $result->fetch_assoc();

        $stmt->close();

        return $holiday ?: null;
    }


    // ==========================================
    // CHECK DUPLICATE PUBLIC HOLIDAY
    // ==========================================
    public function holidayExists(
        $holidayDate,
        $excludeHolidayId = 0
    ) {
        $stmt = $this->conn->prepare("
            SELECT holiday_id
            FROM public_holidays
            WHERE holiday_date = ?
              AND holiday_id != ?
            LIMIT 1
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $holidayDate,
            $excludeHolidayId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }


    // ==========================================
    // CREATE PUBLIC HOLIDAY
    // ==========================================
    public function createPublicHoliday(
        $holidayDate,
        $holidayName
    ) {
        $status = 'Active';

        $stmt = $this->conn->prepare("
            INSERT INTO public_holidays
            (
                holiday_date,
                holiday_name,
                status,
                created_at
            )
            VALUES
            (?, ?, ?, NOW())
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sss",
            $holidayDate,
            $holidayName,
            $status
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // UPDATE PUBLIC HOLIDAY
    // ==========================================
    public function updatePublicHoliday(
        $holidayId,
        $holidayDate,
        $holidayName
    ) {
        $stmt = $this->conn->prepare("
            UPDATE public_holidays
            SET
                holiday_date = ?,
                holiday_name = ?
            WHERE holiday_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ssi",
            $holidayDate,
            $holidayName,
            $holidayId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // UPDATE PUBLIC HOLIDAY STATUS
    // ==========================================
    public function updatePublicHolidayStatus(
        $holidayId,
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
            UPDATE public_holidays
            SET
                status = ?
            WHERE holiday_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $status,
            $holidayId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // DELETE PUBLIC HOLIDAY
    // ==========================================
    public function deletePublicHoliday($holidayId)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM public_holidays
            WHERE holiday_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "i",
            $holidayId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }
}