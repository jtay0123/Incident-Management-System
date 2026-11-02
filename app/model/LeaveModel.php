<?php

class LeaveModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    // ==========================================
    // GET ALL MY LEAVE
    // ==========================================
    public function getLeavesByUserId($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT
                leave_id,
                user_id,
                start_date,
                end_date,
                reason,
                created_at
            FROM staff_leave
            WHERE user_id = ?
            ORDER BY start_date DESC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param(
            "i",
            $userId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $leaves = [];

        while ($row = $result->fetch_assoc()) {
            $leaves[] = $row;
        }

        $stmt->close();

        return $leaves;
    }


    // ==========================================
    // GET ONE LEAVE BY ID
    // ==========================================
    public function getLeaveById(
        $leaveId,
        $userId
    ) {
        $stmt = $this->conn->prepare("
            SELECT
                leave_id,
                user_id,
                start_date,
                end_date,
                reason,
                created_at
            FROM staff_leave
            WHERE leave_id = ?
              AND user_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "ii",
            $leaveId,
            $userId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $leave = $result->fetch_assoc();

        $stmt->close();

        return $leave ?: null;
    }


    // ==========================================
    // CHECK OVERLAPPING LEAVE
    // ==========================================
    public function hasOverlappingLeave(
        $userId,
        $startDate,
        $endDate,
        $excludeLeaveId = 0
    ) {
        $stmt = $this->conn->prepare("
            SELECT leave_id
            FROM staff_leave
            WHERE user_id = ?
              AND leave_id != ?
              AND start_date <= ?
              AND end_date >= ?
            LIMIT 1
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "iiss",
            $userId,
            $excludeLeaveId,
            $endDate,
            $startDate
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }


    // ==========================================
    // CREATE LEAVE
    // ==========================================
    public function createLeave(
        $userId,
        $startDate,
        $endDate,
        $reason
    ) {
        $stmt = $this->conn->prepare("
            INSERT INTO staff_leave
            (
                user_id,
                start_date,
                end_date,
                reason,
                created_at
            )
            VALUES
            (?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "isss",
            $userId,
            $startDate,
            $endDate,
            $reason
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // UPDATE LEAVE
    // ==========================================
    public function updateLeave(
        $leaveId,
        $userId,
        $startDate,
        $endDate,
        $reason
    ) {
        $stmt = $this->conn->prepare("
            UPDATE staff_leave
            SET
                start_date = ?,
                end_date = ?,
                reason = ?
            WHERE leave_id = ?
              AND user_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sssii",
            $startDate,
            $endDate,
            $reason,
            $leaveId,
            $userId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // ==========================================
    // DELETE LEAVE
    // ==========================================
    public function deleteLeave(
        $leaveId,
        $userId
    ) {
        $stmt = $this->conn->prepare("
            DELETE FROM staff_leave
            WHERE leave_id = ?
              AND user_id = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ii",
            $leaveId,
            $userId
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }
}
