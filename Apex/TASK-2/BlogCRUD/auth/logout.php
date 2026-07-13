<?php
/**
 * Logout Script
 * 
 * Securely destroys the user session after logging the activity.
 * Features:
 * - CSRF token verification via GET (optional, for link-based logout)
 * - Activity logging before session destruction
 * - Complete session cleanup (variables, cookie, server-side)
 * - Redirect with logged_out=1 flag for UI feedback
 */

session_start();

// Verify CSRF token if provided (defense-in-depth)
$csrfValid = true;
if (isset($_GET['_csrf_token'])) {
    $submitted = $_GET['_csrf_token'];
    $stored = $_SESSION['_csrf_token'] ?? '';
    if (empty($stored) || !hash_equals($stored, $submitted)) {
        $csrfValid = false;
    }
    // Regenerate token after use
    unset($_SESSION['_csrf_token']);
}

// Log activity before destroying session
if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'Unknown';
    
    // Only load helpers if needed for logging
    if (!function_exists('log_activity')) {
        require_once __DIR__ . '/../helpers/logger.php';
    }
    
    log_activity($userId, 'logout', "User '$username' logged out.");
}

// If CSRF token was invalid but user is still trying to logout,
// we still let them logout (low-risk action), but log it
if (!$csrfValid && !isset($_GET['force'])) {
    // Still proceed with logout - low risk
    error_log("Logout attempted with invalid CSRF token from user: " . ($_SESSION['username'] ?? 'unknown'));
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

// Redirect to login page with success flag
header('Location: /auth/login.php?logged_out=1');
exit();
