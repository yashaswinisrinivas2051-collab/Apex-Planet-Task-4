<?php
/**
 * Sanitization Helper
 * 
 * Centralized input sanitization functions to prevent XSS and other injection attacks.
 * All functions follow defense-in-depth by combining multiple sanitization layers.
 */

/**
 * Sanitize a string for safe HTML output.
 * Use this when echoing user-supplied data in HTML context.
 * 
 * @param string|null $value The value to escape
 * @return string The escaped value safe for HTML output
 */
function e($value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Sanitize user input by trimming, stripping tags, and encoding special chars.
 * 
 * @param string $input The raw input string
 * @return string The sanitized string
 */
function sanitize_input(string $input): string {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return $input;
}

/**
 * Sanitize search input — removes HTML tags, trims, limits length,
 * removes null bytes, and escapes special LIKE characters.
 * 
 * @param string $keyword The search term to sanitize
 * @return string Sanitized search term
 */
function sanitize_search(string $keyword): string {
    $keyword = strip_tags($keyword);
    $keyword = trim($keyword);
    $keyword = substr($keyword, 0, 100);
    $keyword = str_replace("\0", '', $keyword);
    // Escape % and _ for LIKE clause (prevent wildcard injection)
    $keyword = str_replace(['%', '_'], ['\\%', '\\_'], $keyword);
    return $keyword;
}

/**
 * Validate and sanitize an integer ID from GET/POST.
 * 
 * @param mixed $value The raw ID value
 * @return int|null Returns the integer ID if valid, or null if invalid
 */
function sanitize_id($value): ?int {
    if ($value === null || $value === '') {
        return null;
    }
    $id = filter_var($value, FILTER_VALIDATE_INT);
    return ($id !== false && $id > 0) ? $id : null;
}

/**
 * Sanitize email for safe storage/output.
 * 
 * @param string $email The email to sanitize
 * @return string The sanitized email
 */
function sanitize_email(string $email): string {
    $email = trim($email);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    return $email !== false ? $email : '';
}
