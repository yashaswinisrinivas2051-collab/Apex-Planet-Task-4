<?php
/**
 * Database Configuration
 * 
 * This file establishes a PDO connection to the MySQL database,
 * configures secure session settings, initializes error handling,
 * sends security headers, and loads all helper modules.
 * All database interactions throughout the application use this connection.
 */

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'blog');
define('DB_USER', 'root');
define('DB_PASS', '');

// Define base URL for absolute paths (change if deployed to different path)
define('BASE_URL', '');

// ============================================================
// SECURITY HEADERS
// Sent before any HTML output to ensure they take effect
// ============================================================
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self' https://cdn.jsdelivr.net; frame-src 'none'; object-src 'none'");

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

// ============================================================
// Load Helper Modules
// ============================================================
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/sanitizer.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/logger.php';

// ============================================================
// Error Handling
// ============================================================
set_secure_error_handler();

// ============================================================
// Database Connection (PDO)
// ============================================================

/**
 * Get PDO database connection
 * 
 * @return PDO Returns a PDO instance connected to the database
 */
function getConnection(): PDO {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Log the actual error internally
        log_error("Database Connection Error: " . $e->getMessage());
        
        // Show user-friendly message
        die("Unable to connect to the database. Please try again later.");
    }
}

// Initialize the connection for global use
$pdo = getConnection();
