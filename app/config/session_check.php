<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/conn.php";

// =========================
// SESSION TIMEOUT SETTINGS
// =========================
$timeoutDuration = 30 * 60; // 30 minutes

// Only check authenticated users
if (
    isset($_SESSION['user_id']) &&
    isset($_SESSION['session_id'])
) {

    $userId = (int) $_SESSION['user_id'];
    $sessionId = (int) $_SESSION['session_id'];
    $currentTime = time();

    // Get the latest recorded activity from PHP session
    $lastActivity = isset($_SESSION['last_activity'])
        ? (int) $_SESSION['last_activity']
        : $currentTime;

    // Check whether the session has expired
    if (($currentTime - $lastActivity) >= $timeoutDuration) {

        // Mark the session as expired in the database
        $stmt = $conn->prepare("
            UPDATE sessions
            SET
                logout_time = NOW(),
                last_activity = NOW(),
                status = 'Expired'
            WHERE
                session_id = ?
                AND user_id = ?
                AND status = 'Active'
        ");

        $stmt->bind_param("ii", $sessionId, $userId);
        $stmt->execute();
        $stmt->close();

        // Clear session data
        $_SESSION = [];

        // Clear session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        // Redirect to login page
        header(
            "Location: " .
            BASE_URL .
            "/app/view/login_page.php?timeout=1"
        );

        exit();
    }

    // Confirm the database session is still active
    $stmt = $conn->prepare("
        SELECT status
        FROM sessions
        WHERE session_id = ?
          AND user_id = ?
        LIMIT 1
    ");

    $stmt->bind_param("ii", $sessionId, $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $sessionRecord = $result->fetch_assoc();

    $stmt->close();

    // Reject sessions that are no longer active
    if (!$sessionRecord || $sessionRecord['status'] !== 'Active') {

        $_SESSION = [];
        session_destroy();

        header(
            "Location: " .
            BASE_URL .
            "/app/view/login_page.php"
        );

        exit();
    }

    // Update activity timestamp
    $_SESSION['last_activity'] = $currentTime;

    $stmt = $conn->prepare("
        UPDATE sessions
        SET last_activity = NOW()
        WHERE session_id = ?
          AND user_id = ?
          AND status = 'Active'
    ");

    $stmt->bind_param("ii", $sessionId, $userId);
    $stmt->execute();
    $stmt->close();
}

?>