<?php
/**
 * Authentication Middleware
 * 
 * Handles user authentication checks and session management.
 * Include this file on any page that requires authentication.
 */

/**
 * Require the user to be authenticated.
 * Redirects to login page if not logged in.
 */
function require_auth(): void {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Please log in to access this page.';
        header('Location: /auth/login.php');
        exit();
    }
}

/**
 * Require the user to be authenticated AND check session timeout.
 */
function require_auth_with_timeout(): void {
    require_auth();
    
    // Check session timeout (30 minutes of inactivity)
    $timeout = 30 * 60; // 30 minutes in seconds
    if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > $timeout) {
        // Session expired
        $_SESSION = [];
        session_destroy();
        $_SESSION['error'] = 'Your session has expired. Please log in again.';
        header('Location: /auth/login.php');
        exit();
    }
    
    // Update last activity timestamp
    $_SESSION['_last_activity'] = time();
}
