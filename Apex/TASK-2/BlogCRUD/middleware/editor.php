<?php
/**
 * Editor Middleware
 * 
 * Handles editor-specific authorization checks.
 * Editors can create, read, and update their own posts.
 */

/**
 * Check if the current user has editor role.
 * 
 * @return bool True if user has editor role
 */
function is_editor(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'editor';
}

/**
 * Check if the current user can edit/delete a specific post.
 * Editors can only edit/delete their own posts; admins can edit/delete any.
 * 
 * @param int $postOwnerId The user_id of the post owner
 * @return bool True if user can manage the post
 */
function can_manage_post(int $postOwnerId): bool {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Admin can manage any post
    if (is_admin()) {
        return true;
    }
    
    // Editor can only manage their own posts
    return (int)$_SESSION['user_id'] === $postOwnerId;
}

/**
 * Require the user to be able to manage a specific post.
 * Redirects with error message if unauthorized.
 * 
 * @param int $postOwnerId The user_id of the post owner
 */
function require_post_access(int $postOwnerId): void {
    if (!can_manage_post($postOwnerId)) {
        $_SESSION['error'] = 'You do not have permission to access this post.';
        header('Location: /dashboard.php');
        exit();
    }
}
