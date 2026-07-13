<?php
/**
 * Validation Helper
 * 
 * Centralized validation functions for all forms.
 * Each function returns ['valid' => bool, 'message' => string].
 */

/**
 * Validate an email address.
 * 
 * @param string $email The email to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validate_email(string $email): array {
    if (empty(trim($email))) {
        return ['valid' => false, 'message' => 'Email address is required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'Please enter a valid email address.'];
    }
    return ['valid' => true, 'message' => ''];
}

/**
 * Check if a password meets strength requirements.
 * Requirements: min 8 chars, at least 1 uppercase, 1 lowercase, 1 digit, 1 special char.
 * 
 * @param string $password The password to check
 * @return array ['valid' => bool, 'message' => string]
 */
function validate_password_strength(string $password): array {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters long.'];
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter.'];
    }
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter.'];
    }
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one number.'];
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one special character.'];
    }
    return ['valid' => true, 'message' => ''];
}

/**
 * Validate a username.
 * 
 * @param string $username The username to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validate_username(string $username): array {
    $username = trim($username);
    if (empty($username)) {
        return ['valid' => false, 'message' => 'Username is required.'];
    }
    if (strlen($username) < 3) {
        return ['valid' => false, 'message' => 'Username must be at least 3 characters long.'];
    }
    if (strlen($username) > 50) {
        return ['valid' => false, 'message' => 'Username must not exceed 50 characters.'];
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['valid' => false, 'message' => 'Username can only contain letters, numbers, and underscores.'];
    }
    return ['valid' => true, 'message' => ''];
}

/**
 * Validate a blog post title.
 * 
 * @param string $title The title to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validate_title(string $title): array {
    $title = trim($title);
    if (empty($title)) {
        return ['valid' => false, 'message' => 'Post title is required.'];
    }
    if (strlen($title) < 5) {
        return ['valid' => false, 'message' => 'Title must be at least 5 characters long.'];
    }
    if (strlen($title) > 255) {
        return ['valid' => false, 'message' => 'Title must not exceed 255 characters.'];
    }
    return ['valid' => true, 'message' => ''];
}

/**
 * Validate blog post content.
 * 
 * @param string $content The content to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validate_content(string $content): array {
    $content = trim($content);
    if (empty($content)) {
        return ['valid' => false, 'message' => 'Post content is required.'];
    }
    if (strlen($content) < 20) {
        return ['valid' => false, 'message' => 'Content must be at least 20 characters long.'];
    }
    return ['valid' => true, 'message' => ''];
}
