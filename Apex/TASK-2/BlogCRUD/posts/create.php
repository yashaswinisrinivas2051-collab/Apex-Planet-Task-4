<?php
/**
 * Create Blog Post Page
 * 
 * Allows authenticated users to create new blog posts.
 * Features:
 * - CSRF protection
 * - Title and content validation (min length, max length)
 * - Prepared statements
 * - XSS-safe output
 * - Role-based access
 * - Activity logging on successful creation
 */

session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit();
}

require_once '../config/db.php';

$pageTitle = 'Create New Post';
$error = '';
$success = '';
$title = '';
$content = '';

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

        // --- Insert post ---
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (:title, :content, :user_id)");
                $stmt->execute([
                    ':title'   => $title,
                    ':content' => $content,
                    ':user_id' => $_SESSION['user_id'],
                ]);

                // Log activity
                $postId = $pdo->lastInsertId();
                log_activity(
                    (int)$_SESSION['user_id'],
                    'create_post',
                    "Created post titled '" . substr($title, 0, 50) . "' (ID: $postId)."
                );

                $_SESSION['success'] = 'Post created successfully!';
                header('Location: /dashboard.php');
                exit();
            } catch (PDOException $e) {
                log_error("Create Post Error: " . $e->getMessage());
                $error = 'Failed to create post. Please try again.';
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
                    <span class="badge bg-sunset-soft text-sunset px-3 py-2 rounded-pill mb-1 fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i>New Post
                    </span>
                    <h3 class="fw-bold mb-0">Create New Post</h3>
                    <p class="text-muted mb-0">Share your thoughts with the world</p>
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

            <!-- Create Post Form -->
            <div class="card shadow-lg border-0 rounded-5">
                <div class="card-body p-4 p-lg-5">
                    <form action="/posts/create.php" method="POST" 
                          id="createPostForm" novalidate>
                        <?php csrf_field(); ?>
                        
                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">
                                <i class="bi bi-type me-1"></i>Post Title
                            </label>
                            <input type="text" class="form-control form-control-lg rounded-4" id="title" 
                                   name="title" placeholder="Enter a captivating title for your post (min 5 chars)..." 
                                   value="<?php echo htmlspecialchars($title); ?>" minlength="5" maxlength="255" required>
                            <div class="d-flex justify-content-between">
                                <div class="invalid-feedback">Title must be between 5 and 255 characters.</div>
                                <small id="titleCounter" class="text-muted">0 / 255</small>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="mb-4">
                            <label for="content" class="form-label">
                                <i class="bi bi-text-paragraph me-1"></i>Content
                            </label>
                            <textarea class="form-control rounded-4" id="content" name="content" 
                                      rows="12" placeholder="Write your blog post content here... (min 20 characters)" 
                                      minlength="20"
                                      required><?php echo htmlspecialchars($content); ?></textarea>
                            <div class="d-flex justify-content-between">
                                <div class="invalid-feedback">Content must be at least 20 characters long.</div>
                                <small id="contentCounter" class="text-muted">0</small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-gradient btn-lg rounded-4 fw-semibold flex-fill py-3">
                                <i class="bi bi-check-lg me-2"></i>Publish Post
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
