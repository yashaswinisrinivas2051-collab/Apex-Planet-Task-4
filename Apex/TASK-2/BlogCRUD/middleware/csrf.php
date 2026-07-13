<?php
/**
 * CSRF Middleware
 * 
 * Centralized CSRF protection for all forms.
 * Include this file to enable CSRF protection across the application.
 */

/**
 * Validate CSRF token from POST request with user-friendly error.
 * Redirects back if token is invalid.
 */
function require_valid_csrf(): void {
    if (!verify_csrf_token()) {
        $_SESSION['error'] = 'Security token expired. Please try again.';
        $redirect = $_SERVER['HTTP_REFERER'] ?? '/dashboard.php';
        header("Location: $redirect");
        exit();
    }
}

/**
 * Validate CSRF token from GET request with user-friendly error.
 * Redirects back if token is invalid.
 */
function require_valid_csrf_get(): void {
    if (!isset($_GET['_csrf_token']) || !verify_csrf_token_get()) {
        $_SESSION['error'] = 'Security token expired. Please try again.';
        $redirect = $_SERVER['HTTP_REFERER'] ?? '/dashboard.php';
        header("Location: $redirect");
        exit();
    }
}
