<?php
/**
 * Logout Script
 */

require_once __DIR__ . '/../config/db.php';
session_start();

// Log activity before destroying session (capture user info first)
if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'Unknown';
    log_activity($userId, 'logout', "User '$username' logged out.");
}

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session completely
session_destroy();

// Redirect to login page
header('Location: /auth/login.php?logged_out=1');
exit();
