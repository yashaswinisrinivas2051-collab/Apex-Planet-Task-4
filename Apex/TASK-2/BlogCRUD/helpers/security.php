<?php
/**
 * Security Helper
 * 
 * Centralized security functions for:
 * - CSRF token generation & verification
 * - Session security configuration
 * - Security headers
 * - Password utilities
 */

// ============================================================
// CSRF PROTECTION
// ============================================================

/**
 * Generate a CSRF token and store it in the session.
 * A new token is generated per request for enhanced security.
 * 
 * @return string The CSRF token
 */
function generate_csrf_token(): string {
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$token] = time();
    
    // Limit stored tokens to prevent session bloat (keep last 10)
    if (count($_SESSION['csrf_tokens']) > 10) {
        array_shift($_SESSION['csrf_tokens']);
    }
    
    return $token;
}

/**
 * Get or create a persistent CSRF token for forms.
 * This token persists for the duration of the session.
 * 
 * @return string The CSRF token
 */
function get_csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Render a hidden CSRF input field.
 */
function csrf_field(): void {
    echo '<input type="hidden" name="_csrf_token" value="' . get_csrf_token() . '">';
}

/**
 * Verify a CSRF token from a POST request.
 * Uses timing-safe comparison.
 * 
 * @return bool True if the token is valid
 */
function verify_csrf_token(): bool {
    $submitted = $_POST['_csrf_token'] ?? '';
    
    if (empty($submitted) || empty($_SESSION['_csrf_token'])) {
        return false;
    }
    
    // Timing-safe comparison
    $valid = hash_equals($_SESSION['_csrf_token'], $submitted);
    
    // Regenerate token after verification (one-time use)
    if ($valid) {
        unset($_SESSION['_csrf_token']);
    }
    
    return $valid;
}

/**
 * Verify a CSRF token from a GET request (for delete operations).
 * 
 * @return bool True if the token is valid
 */
function verify_csrf_token_get(): bool {
    $submitted = $_GET['_csrf_token'] ?? '';
    
    if (empty($submitted) || empty($_SESSION['_csrf_token'])) {
        return false;
    }
    
    $valid = hash_equals($_SESSION['_csrf_token'], $submitted);
    
    if ($valid) {
        unset($_SESSION['_csrf_token']);
    }
    
    return $valid;
}

// ============================================================
// SESSION SECURITY
// ============================================================

/**
 * Configure secure session settings.
 * Should be called before session_start().
 */
function configure_secure_session(): void {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
               || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie (expires on browser close)
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps, // Only send over HTTPS if available
        'httponly' => true,     // Not accessible via JavaScript
        'samesite' => 'Strict', // Prevent CSRF via cross-site requests
    ]);
}

/**
 * Regenerate session ID securely after login.
 */
function regenerate_session(): void {
    session_regenerate_id(true);
}

/**
 * Check if the session has timed out (30 minute inactivity limit).
 * 
 * @param int $timeoutMinutes Timeout in minutes (default: 30)
 * @return bool True if session has expired
 */
function is_session_expired(int $timeoutMinutes = 30): bool {
    $lastActivity = $_SESSION['_last_activity'] ?? 0;
    $maxInactiveTime = $timeoutMinutes * 60;
    
    if (time() - $lastActivity > $maxInactiveTime) {
        return true;
    }
    
    // Update last activity timestamp
    $_SESSION['_last_activity'] = time();
    return false;
}

/**
 * Check session timeout and redirect to login if expired.
 */
function check_session_timeout(): void {
    if (isset($_SESSION['user_id']) && is_session_expired()) {
        session_unset();
        session_destroy();
        $_SESSION['error'] = 'Your session has expired. Please log in again.';
        header('Location: /auth/login.php');
        exit();
    }
}

// ============================================================
// SECURITY HEADERS
// ============================================================

/**
 * Send security headers to protect against common web vulnerabilities.
 */
function send_security_headers(): void {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Enable XSS filter in older browsers
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevent MIME-type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Control referrer information
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
           "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; " .
           "img-src 'self' data:; " .
           "connect-src 'self' https://cdn.jsdelivr.net; " .
           "frame-src 'none'; " .
           "object-src 'none'");
    
    // HSTS (only if HTTPS)
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// ============================================================
// PASSWORD UTILITIES
// ============================================================

/**
 * Hash a password using bcrypt.
 * 
 * @param string $password The plain text password
 * @return string The hashed password
 */
function hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against its hash.
 * 
 * @param string $password The plain text password to verify
 * @param string $hash The stored password hash
 * @return bool True if password matches
 */
function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}
