<?php

function require_login($redirect = '../auth/login.php') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        header("Location: $redirect");
        exit;
    }
}

function current_user_id() {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_role() {
    return $_SESSION['role'] ?? null;
}

function require_admin($redirect = '../auth/login.php') {
    require_login($redirect);

    if (current_user_role() !== 'admin') {
        header("Location: $redirect");
        exit;
    }
}

function is_event_owner($conn, $event_id) {
    $event_id = (int) $event_id;
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM events WHERE id = ?");
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    return $row && isset($row['user_id']) && (int) $row['user_id'] === current_user_id();
}

function require_admin_or_owner($conn, $event_id, $redirect = '../auth/login.php') {
    require_login($redirect);

    if (current_user_role() === 'admin') {
        return;
    }

    if (!is_event_owner($conn, $event_id)) {
        header("Location: $redirect");
        exit;
    }
}
