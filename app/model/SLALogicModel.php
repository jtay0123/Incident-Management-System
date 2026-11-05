<?php

class SLALogicModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    // ==========================================
    // GET INCIDENT
    // ==========================================
    public function getIncident($incidentId)
    {
        $stmt = $this->conn->prepare("
            SELECT
                incident_id,
                priority,
                status,
                submitted_at,
                resolved_at
            FROM incidents
            WHERE incident_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $incidentId);
        $stmt->execute();

        $result = $stmt->get_result();
        $incident = $result->fetch_assoc();

        $stmt->close();

        return $incident ?: null;
    }


    // ==========================================
    // GET SLA HOURS
    // ==========================================
    public function getSLAHours($priority)
    {
        $stmt = $this->conn->prepare("
            SELECT sla_hours
            FROM sla_settings
            WHERE priority = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $priority);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();

        return $row
            ? (int) $row['sla_hours']
            : null;
    }


    // ==========================================
    // GET WORKING HOURS
    // ==========================================
    public function getWorkingHours()
    {
        $stmt = $this->conn->prepare("
            SELECT
                day_of_week,
                start_time,
                end_time,
                is_working_day
            FROM working_hours
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $workingHours = [];

        while ($row = $result->fetch_assoc()) {
            $workingHours[$row['day_of_week']] = $row;
        }

        $stmt->close();

        return $workingHours;
    }


    // ==========================================
    // CHECK PUBLIC HOLIDAY
    // ==========================================
    public function isPublicHoliday($date)
    {
        $stmt = $this->conn->prepare("
            SELECT holiday_id
            FROM public_holidays
            WHERE holiday_date = ?
              AND status = 'Active'
            LIMIT 1
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $date);
        $stmt->execute();

        $result = $stmt->get_result();

        $isHoliday = $result->num_rows > 0;

        $stmt->close();

        return $isHoliday;
    }


    // ==========================================
    // GET EXISTING SLA RECORD
    // ==========================================
    public function getSLARecord($incidentId)
    {
        $stmt = $this->conn->prepare("
            SELECT
                sla_id,
                incident_id,
                priority,
                start_time,
                due_time,
                completed_time,
                sla_status,
                created_at
            FROM sla_records
            WHERE incident_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $incidentId);
        $stmt->execute();

        $result = $stmt->get_result();

        $record = $result->fetch_assoc();

        $stmt->close();

        return $record ?: null;
    }


    // ==========================================
    // CREATE SLA RECORD
    // ==========================================
    public function createSLA($incidentId)
    {
        /*
         * Get incident information.
         */
        $incident = $this->getIncident($incidentId);

        if (!$incident) {
            return [
                'success' => false,
                'message' => 'Incident not found.'
            ];
        }


        /*
         * Priority must exist because SLA hours
         * are based on incident priority.
         */
        if (empty($incident['priority'])) {
            return [
                'success' => false,
                'message' => 'Incident priority is not set.'
            ];
        }


        /*
         * Prevent duplicate SLA records.
         */
        if ($this->getSLARecord($incidentId)) {
            return [
                'success' => true,
                'message' => 'SLA record already exists.'
            ];
        }


        /*
         * Get SLA hours based on priority.
         */
        $slaHours = $this->getSLAHours(
            $incident['priority']
        );

        if ($slaHours === null) {
            return [
                'success' => false,
                'message' => 'SLA setting not found for this priority.'
            ];
        }


        /*
         * Get configured working hours.
         */
        $workingHours = $this->getWorkingHours();

        if (empty($workingHours)) {
            return [
                'success' => false,
                'message' => 'Working hours configuration not found.'
            ];
        }


        /*
         * SLA starts from incident submission time.
         */
        try {

            $startTime = new DateTime(
                $incident['submitted_at']
            );

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'Invalid incident submission time.'
            ];
        }


        /*
         * Calculate SLA due time.
         */
        $dueTime = $this->calculateDueTime(
            $startTime,
            $slaHours,
            $workingHours
        );


        $slaStatus = 'On Track';

        $startTimeValue = $startTime->format(
            'Y-m-d H:i:s'
        );

        $dueTimeValue = $dueTime->format(
            'Y-m-d H:i:s'
        );


        /*
         * Insert SLA record.
         */
        $stmt = $this->conn->prepare("
            INSERT INTO sla_records
            (
                incident_id,
                priority,
                start_time,
                due_time,
                completed_time,
                sla_status,
                created_at
            )
            VALUES
            (?, ?, ?, ?, NULL, ?, NOW())
        ");

        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Failed to prepare SLA record.'
            ];
        }


        $stmt->bind_param(
            "issss",
            $incidentId,
            $incident['priority'],
            $startTimeValue,
            $dueTimeValue,
            $slaStatus
        );


        $success = $stmt->execute();

        $stmt->close();


        return [
            'success' => $success,
            'message' => $success
                ? 'SLA record created successfully.'
                : 'Failed to create SLA record.'
        ];
    }

    
    // =====================================================
    // RECALCULATE SLA AFTER PRIORITY CHANGE
    // =====================================================

    public function recalculateSLAForPriorityChange(
        int $incidentId,
        string $newPriority
    ): bool {

        // -------------------------------------------------
        // Validate priority
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
            return false;
        }

        // -------------------------------------------------
        // Get incident information
        // -------------------------------------------------

        $incident = $this->getIncident($incidentId);

        if (!$incident) {
            return false;
        }

        // -------------------------------------------------
        // Get existing SLA record
        // -------------------------------------------------

        $sla = $this->getSLARecord($incidentId);

        // -------------------------------------------------
        // Get SLA hours for the new priority
        // -------------------------------------------------

        $slaHours = $this->getSLAHours($newPriority);

        if ($slaHours === null || $slaHours === false) {
            return false;
        }

        $slaHours = (int) $slaHours;

        if ($slaHours <= 0) {
            return false;
        }

        // -------------------------------------------------
        // Get working hours
        // -------------------------------------------------

        $workingHours = $this->getWorkingHours();

        if (empty($workingHours)) {
            return false;
        }

        // -------------------------------------------------
        // Determine original SLA start time
        // -------------------------------------------------

        if ($sla) {

            // Keep the original SLA start time.
            $startTime = new DateTime(
                $sla['start_time']
            );

        } else {

            // Create an SLA record if one does not exist.
            $startTime = new DateTime(
                $incident['submitted_at']
            );
        }

        // -------------------------------------------------
        // Calculate new SLA due time
        // -------------------------------------------------

        $dueTime = $this->calculateDueTime(
            $startTime,
            $slaHours,
            $workingHours
        );

        $dueTimeString = $dueTime->format(
            'Y-m-d H:i:s'
        );

        // -------------------------------------------------
        // Determine SLA status
        // -------------------------------------------------

        $incidentStatus = $incident['status'];

        $resolvedAt = $incident['resolved_at'];

        $completedTime = null;

        if (
            in_array(
                $incidentStatus,
                ['Resolved', 'Closed'],
                true
            )
        ) {

            // Use the actual resolution time when available.
            // Fall back to the existing completion time.
            if (!empty($resolvedAt)) {

                $completedDateTime = new DateTime(
                    $resolvedAt
                );

            } elseif (
                $sla &&
                !empty($sla['completed_time'])
            ) {

                $completedDateTime = new DateTime(
                    $sla['completed_time']
                );

            } else {

                // Do not invent a completion time for a
                // resolved incident without one.
                $completedDateTime = null;
            }

            if ($completedDateTime !== null) {

                $completedTime = $completedDateTime->format(
                    'Y-m-d H:i:s'
                );

                if (
                    $completedDateTime <= $dueTime
                ) {
                    $slaStatus = 'Completed';
                } else {
                    $slaStatus = 'Overdue';
                }

            } else {

                // Resolution timestamp is missing.
                // Keep the SLA incomplete rather than
                // inventing its completion time.
                $slaStatus = 'On Track';
            }

        } else {

            // Active incidents have no completion time.
            $now = new DateTime();

            if ($now > $dueTime) {
                $slaStatus = 'Overdue';
            } else {
                $slaStatus = 'On Track';
            }
        }

        // -------------------------------------------------
        // Update existing SLA record
        // Or create one if missing
        // -------------------------------------------------

        if ($sla) {

            $sql = "
                UPDATE sla_records
                SET
                    priority = ?,
                    due_time = ?,
                    completed_time = ?,
                    sla_status = ?
                WHERE incident_id = ?
            ";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                return false;
            }

            $stmt->bind_param(
                'ssssi',
                $newPriority,
                $dueTimeString,
                $completedTime,
                $slaStatus,
                $incidentId
            );

            $success = $stmt->execute();

            $stmt->close();

            return $success;

        } else {

            $sql = "
                INSERT INTO sla_records
                (
                    incident_id,
                    priority,
                    start_time,
                    due_time,
                    completed_time,
                    sla_status
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ";

            $startTimeString = $startTime->format(
                'Y-m-d H:i:s'
            );

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                return false;
            }

            $stmt->bind_param(
                'isssss',
                $incidentId,
                $newPriority,
                $startTimeString,
                $dueTimeString,
                $completedTime,
                $slaStatus
            );

            $success = $stmt->execute();

            $stmt->close();

            return $success;
        }
    }



    // ==========================================
    // CALCULATE SLA DUE TIME
    // ==========================================
    private function calculateDueTime(
        DateTime $startTime,
        int $slaHours,
        array $workingHours
    ) {
        $current = clone $startTime;

        $remainingSeconds = $slaHours * 60 * 60;


        while ($remainingSeconds > 0) {

            $dayName = $current->format('l');

            $date = $current->format('Y-m-d');


            // ------------------------------------------
            // SKIP PUBLIC HOLIDAY
            // ------------------------------------------

            if ($this->isPublicHoliday($date)) {

                $current->modify('+1 day');
                $current->setTime(0, 0, 0);

                continue;
            }


            // ------------------------------------------
            // SKIP NON-WORKING DAY
            // ------------------------------------------

            if (
                !isset($workingHours[$dayName]) ||
                (int) $workingHours[$dayName]['is_working_day'] !== 1
            ) {

                $current->modify('+1 day');
                $current->setTime(0, 0, 0);

                continue;
            }


            // ------------------------------------------
            // GET WORKING HOURS FOR CURRENT DAY
            // ------------------------------------------

            $workStart = new DateTime(
                $date . ' ' .
                $workingHours[$dayName]['start_time']
            );

            $workEnd = new DateTime(
                $date . ' ' .
                $workingHours[$dayName]['end_time']
            );


            // ------------------------------------------
            // BEFORE WORKING HOURS
            // ------------------------------------------

            if ($current < $workStart) {
                $current = clone $workStart;
            }


            // ------------------------------------------
            // AFTER WORKING HOURS
            // ------------------------------------------

            if ($current >= $workEnd) {

                $current->modify('+1 day');
                $current->setTime(0, 0, 0);

                continue;
            }


            // ------------------------------------------
            // CALCULATE AVAILABLE WORKING TIME
            // ------------------------------------------

            $availableSeconds =
                $workEnd->getTimestamp()
                - $current->getTimestamp();


            // ------------------------------------------
            // SLA CAN FINISH TODAY
            // ------------------------------------------

            if ($remainingSeconds <= $availableSeconds) {

                $current->modify(
                    '+' . $remainingSeconds . ' seconds'
                );

                $remainingSeconds = 0;

            } else {

                // Use all remaining working time today.
                $remainingSeconds -= $availableSeconds;

                // Move to next day.
                $current->modify('+1 day');
                $current->setTime(0, 0, 0);
            }
        }


        return $current;
    }


    // ==========================================
    // UPDATE SLA STATUS
    // ==========================================
    public function updateSLAStatus($incidentId)
    {
        /*
         * Get SLA record.
         */
        $record = $this->getSLARecord($incidentId);

        if (!$record) {
            return [
                'success' => false,
                'message' => 'SLA record not found.'
            ];
        }


        /*
         * Get incident.
         */
        $incident = $this->getIncident($incidentId);

        if (!$incident) {
            return [
                'success' => false,
                'message' => 'Incident not found.'
            ];
        }


        // ==========================================
        // INCIDENT RESOLVED / CLOSED
        // ==========================================

        /*
         * Incident statuses:
         *
         * Submitted
         * Assigned
         * In Progress
         * Resolved
         * Closed
         *
         * Resolved and Closed mean the incident
         * has been completed from the SLA perspective.
         */
        if (
            !empty($incident['resolved_at']) ||
            strtolower($incident['status']) === 'resolved' ||
            strtolower($incident['status']) === 'closed'
        ) {

            /*
             * Use resolved_at if available.
             * Otherwise use current time.
             */
            $completedTime = !empty($incident['resolved_at'])
                ? $incident['resolved_at']
                : date('Y-m-d H:i:s');


            try {

                $completedDate = new DateTime(
                    $completedTime
                );

                $dueDate = new DateTime(
                    $record['due_time']
                );

            } catch (Exception $e) {

                return [
                    'success' => false,
                    'message' => 'Invalid SLA date/time.'
                ];
            }


            /*
             * Completed within SLA = Completed.
             *
             * Completed after due time = Overdue.
             */
            $slaStatus =
                $completedDate <= $dueDate
                ? 'Completed'
                : 'Overdue';


            $stmt = $this->conn->prepare("
                UPDATE sla_records
                SET
                    completed_time = ?,
                    sla_status = ?
                WHERE incident_id = ?
            ");

            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Failed to prepare SLA update.'
                ];
            }


            $stmt->bind_param(
                "ssi",
                $completedTime,
                $slaStatus,
                $incidentId
            );


            $success = $stmt->execute();

            $stmt->close();


            return [
                'success' => $success,
                'message' => $success
                    ? 'SLA status updated successfully.'
                    : 'Failed to update SLA status.'
            ];
        }


        // ==========================================
        // INCIDENT STILL ACTIVE
        // ==========================================

        /*
         * Submitted / Assigned / In Progress
         * are still active SLA states.
         */
        try {

            $now = new DateTime();

            $dueTime = new DateTime(
                $record['due_time']
            );

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'Invalid SLA date/time.'
            ];
        }


        /*
         * If current time has passed the due time,
         * mark SLA as Overdue.
         */
        $slaStatus =
            $now > $dueTime
            ? 'Overdue'
            : 'On Track';


        $stmt = $this->conn->prepare("
            UPDATE sla_records
            SET
                sla_status = ?
            WHERE incident_id = ?
        ");

        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Failed to prepare SLA status update.'
            ];
        }


        $stmt->bind_param(
            "si",
            $slaStatus,
            $incidentId
        );


        $success = $stmt->execute();

        $stmt->close();


        return [
            'success' => $success,
            'message' => $success
                ? 'SLA status updated successfully.'
                : 'Failed to update SLA status.'
        ];
    }
}