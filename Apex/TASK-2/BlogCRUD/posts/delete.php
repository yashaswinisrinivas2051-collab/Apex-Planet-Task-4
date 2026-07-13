<?php
/**
 * Delete Blog Post Script
 * 
 * Handles post deletion with CSRF protection and ownership verification.
 * Features:
 * - CSRF token verification
 * - Owner/admin access control
 * - Prepared statements
 * - Redirect back with status messages
 * - Activity logging on successful deletion
 */

session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit();
}

require_once '../config/db.php';

// --- CSRF Protection ---
if (!isset($_GET['_csrf_token']) || !verify_csrf_token_get()) {
    $_SESSION['error'] = 'Security token expired. Please try again.';
    header('Location: /dashboard.php');
    exit();
}

// Get post ID from URL
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$post_id || $post_id < 1) {
    $_SESSION['error'] = 'Invalid post ID.';
    header('Location: /dashboard.php');
    exit();
}

// Fetch the post to verify ownership
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->execute([':id' => $post_id]);
$post = $stmt->fetch();

// Verify post exists
if (!$post) {
    $_SESSION['error'] = 'Post not found.';
    header('Location: /dashboard.php');
    exit();
}

// Verify ownership (owner or admin can delete)
$isOwner = ($post['user_id'] == $_SESSION['user_id']);
$isAdminUser = ($_SESSION['role'] ?? '') === 'admin';

if (!$isOwner && !$isAdminUser) {
    $_SESSION['error'] = 'You do not have permission to delete this post.';
    header('Location: /dashboard.php');
    exit();
}

// Log activity before deletion (capture post title first)
$postTitle = $post['title'];
log_activity(
    (int)$_SESSION['user_id'],
    'delete_post',
    "Deleted post titled '" . substr($postTitle, 0, 50) . "' (ID: $post_id)."
);

// Delete the post
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
$stmt->execute([':id' => $post_id]);

$_SESSION['success'] = 'Post deleted successfully!';
header('Location: /dashboard.php');
exit();
