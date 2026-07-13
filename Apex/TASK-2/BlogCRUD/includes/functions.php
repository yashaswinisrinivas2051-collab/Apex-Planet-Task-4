<?php
/**
 * Functions Loader (Backward Compatibility)
 * 
 * This file re-exports all security and utility functions from the helpers directory.
 * It exists for backward compatibility — all functionality has been moved to:
 *   - helpers/validator.php   (validation functions)
 *   - helpers/sanitizer.php   (sanitization functions)
 *   - helpers/security.php    (CSRF, session, security headers)
 *   - helpers/logger.php      (error/activity logging)
 * 
 * The helpers are also loaded in config/db.php.
 */

require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/sanitizer.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/logger.php';
