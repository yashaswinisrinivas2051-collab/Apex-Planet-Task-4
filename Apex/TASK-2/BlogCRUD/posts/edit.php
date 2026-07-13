<?php
/**
 * Edit Blog Post Page
 * 
 * Allows authenticated users to edit their own blog posts (admin can edit any).
 * Features:
 * - CSRF protection
 * - Title and content validation
 * - Owner/admin access control
 * - Prepared statements
 * - XSS-safe output
 * - Activity logging on successful update
 */

session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit();
}

require_once '../config/db.php';

$pageTitle = 'Edit Post';
$error = '';
$success = '';

// Get post ID from URL
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$post_id || $post_id < 1) {
    $_SESSION['error'] = 'Invalid post ID.';
    header('Location: /dashboard.php');
    exit();
}

// Fetch existing post data
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->execute([':id' => $post_id]);
$post = $stmt->fetch();

// Redirect if post not found
if (!$post) {
    $_SESSION['error'] = 'Post not found.';
    header('Location: /dashboard.php');
    exit();
}

// Check if the current user owns this post or is admin
$isOwner = ($post['user_id'] == $_SESSION['user_id']);
$isAdminUser = ($_SESSION['role'] ?? '') === 'admin';

if (!$isOwner && !$isAdminUser) {
    $_SESSION['error'] = 'You do not have permission to edit this post.';
    header('Location: /dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Protection ---
    if (!verify_csrf_token()) {
        $error = 'Security token expired. Please try again.';
    } else {
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        // --- Server-side validation ---
        $titleCheck = validate_title($title);
        if (!$titleCheck['valid']) {
            $error = $titleCheck['message'];
        } else {
            $contentCheck = validate_content($content);
            if (!$contentCheck['valid']) {
                $error = $contentCheck['message'];
            }
        }

        // --- Update post ---
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :id");
                $stmt->execute([
                    ':title'   => $title,
                    ':content' => $content,
                    ':id'      => $post_id,
                ]);

                // Log activity
                log_activity(
                    (int)$_SESSION['user_id'],
                    'edit_post',
                    "Edited post titled '" . substr($title, 0, 50) . "' (ID: $post_id)."
                );

                $_SESSION['success'] = 'Post updated successfully!';
                header('Location: /dashboard.php');
                exit();
            } catch (PDOException $e) {
                log_error("Edit Post Error: " . $e->getMessage());
                $error = 'Failed to update post. Please try again.';
            }
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Page Header -->
            <div class="d-flex align-items-center mb-4">
                <div class="me-3">
                    <a href="/dashboard.php" class="btn btn-outline-secondary rounded-circle p-2 hover-lift">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                </div>
                <div>
                    <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-1 fw-semibold">
                        <i class="bi bi-pencil me-1"></i>Edit Post
                    </span>
                    <h3 class="fw-bold mb-0">Edit Post</h3>
                    <p class="text-muted mb-0">Update your blog post content</p>
                </div>
            </div>

            <!-- Error Alert -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm rounded-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Edit Post Form -->
            <div class="card shadow-lg border-0 rounded-5">
                <div class="card-body p-4 p-lg-5">
                    <form action="/posts/edit.php?id=<?php echo (int)$post_id; ?>" 
                          method="POST" id="editPostForm" novalidate>
                        <?php csrf_field(); ?>
                        
                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">
                                <i class="bi bi-type me-1"></i>Post Title
                            </label>
                            <input type="text" class="form-control form-control-lg rounded-4" id="title" 
                                   name="title" placeholder="Enter a captivating title for your post (min 5 chars)..." 
                                   value="<?php echo htmlspecialchars($post['title']); ?>" 
                                   minlength="5" maxlength="255" required>
                            <div class="invalid-feedback">Title must be between 5 and 255 characters.</div>
                        </div>

                        <!-- Content -->
                        <div class="mb-4">
                            <label for="content" class="form-label">
                                <i class="bi bi-text-paragraph me-1"></i>Content
                            </label>
                            <textarea class="form-control rounded-4" id="content" name="content" 
                                      rows="16" placeholder="Write your blog post content here... (min 20 characters)" 
                                      minlength="20"
                                      required><?php echo htmlspecialchars($post['content']); ?></textarea>
                            <div class="invalid-feedback">Content must be at least 20 characters long.</div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-gradient btn-lg rounded-4 fw-semibold flex-fill py-3">
                                <i class="bi bi-check-lg me-2"></i>Update Post
                            </button>
                            <a href="/dashboard.php" class="btn btn-outline-secondary btn-lg rounded-4 fw-semibold px-4 py-3">
                                <i class="bi bi-x-lg me-1"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
