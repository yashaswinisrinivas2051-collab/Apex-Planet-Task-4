<?php
/**
 * Logger Helper
 * 
 * Centralized logging functions for:
 * - Error logging (internal, never exposed to users)
 * - Activity logging (user actions for audit trail)
 * - User-friendly error display
 */

// ============================================================
// ACTIVITY LOGGING
// ============================================================

/**
 * Log a user activity to the database.
 * 
 * @param int    $userId      The ID of the user performing the action
 * @param string $action      The action type (e.g., 'login', 'create_post', 'delete_post')
 * @param string $description A human-readable description of the action
 * @param PDO|null $pdo       Optional PDO connection (uses global $pdo if not provided)
 */
function log_activity(int $userId, string $action, string $description, ?PDO $pdo = null): void {
    global $pdo;
    $db = $pdo ?? $GLOBALS['pdo'] ?? null;
    
    if (!$db) {
        // Fall back to file-based logging if no database connection
        log_error("Activity Logging: No database connection available for action: $action");
        return;
    }
    
    try {
        $stmt = $db->prepare(
            "INSERT INTO activity_logs (user_id, action, description) VALUES (:user_id, :action, :description)"
        );
        $stmt->execute([
            ':user_id'     => $userId,
            ':action'      => $action,
            ':description' => $description,
        ]);
    } catch (PDOException $e) {
        // Log the error silently — don't interrupt the user flow
        log_error("Activity Logging: " . $e->getMessage());
    }
}

/**
 * Get recent activity logs for a specific user.
 * 
 * @param int $userId The user ID
 * @param int $limit  Maximum number of logs to return (default: 20)
 * @return array Array of activity log entries
 */
function get_user_activity(int $userId, int $limit = 20): array {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare(
            "SELECT * FROM activity_logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        log_error("Get User Activity: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all recent activity logs (admin only).
 * 
 * @param int $limit Maximum number of logs to return (default: 50)
 * @return array Array of activity log entries with user info
 */
function get_all_activity(int $limit = 50): array {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare(
            "SELECT al.*, u.username FROM activity_logs al 
             JOIN users u ON al.user_id = u.id 
             ORDER BY al.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        log_error("Get All Activity: " . $e->getMessage());
        return [];
    }
}

// ============================================================
// ERROR LOGGING
// ============================================================

/**
 * Log an internal error to the error log file.
 * Error details are never exposed to the user.
 * 
 * @param string $message The error message to log
 * @param string $level   The error level (e.g., 'Error', 'Warning', 'Info')
 */
function log_error(string $message, string $level = 'Error'): void {
    $logMessage = date('Y-m-d H:i:s') . " [$level] $message" . PHP_EOL;
    $logDir = dirname(__DIR__) . '/logs';
    $logFile = $logDir . '/error.log';
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// ============================================================
// ERROR HANDLER
// ============================================================

/**
 * Set a custom error handler that logs errors instead of displaying them.
 */
function set_secure_error_handler(): void {
    set_error_handler(function ($severity, $message, $file, $line) {
        log_error("$message in $file:$line", severity_to_string($severity));
        // Don't display errors to the user (production-safe)
        return false;
    });
    
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

/**
 * Convert PHP error severity level to readable string.
 * 
 * @param int $severity The PHP error level
 * @return string Readable error type
 */
function severity_to_string(int $severity): string {
    $levels = [
        E_WARNING       => 'Warning',
        E_NOTICE        => 'Notice',
        E_USER_ERROR    => 'User Error',
        E_USER_WARNING  => 'User Warning',
        E_USER_NOTICE   => 'User Notice',
        E_STRICT        => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED    => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];
    return $levels[$severity] ?? 'Unknown';
}

/**
 * Display a user-friendly error message and log the actual error internally.
 * 
 * @param string $userMessage    Message shown to the user
 * @param string $internalMessage Internal message for logging (optional)
 */
function safe_error(string $userMessage = 'An unexpected error occurred. Please try again later.', string $internalMessage = ''): void {
    if (!empty($internalMessage)) {
        log_error($internalMessage, 'SafeError');
    }
    
    $_SESSION['error'] = $userMessage;
}

// ============================================================
// FLASH MESSAGES
// ============================================================

/**
 * Set a success flash message.
 * 
 * @param string $message The success message
 */
function set_success(string $message): void {
    $_SESSION['success'] = $message;
}

/**
 * Set an error flash message.
 * 
 * @param string $message The error message
 */
function set_error(string $message): void {
    $_SESSION['error'] = $message;
}
