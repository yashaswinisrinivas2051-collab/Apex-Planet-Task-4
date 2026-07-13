<?php
/**
 * Landing Page (Home)
 * 
 * The public-facing home page of the Blog Management System.
 * Displays:
 * - Hero section with call-to-action
 * - Featured/recent blog posts
 * - Login/Register prompts for unauthenticated users
 */

session_start();
require_once 'config/db.php';

$pageTitle = 'Home';

// Fetch recent posts for display
$stmt = $pdo->query("SELECT p.*, u.username FROM posts p 
                     JOIN users u ON p.user_id = u.id 
                     ORDER BY p.created_at DESC LIMIT 6");
$recentPosts = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero-section text-white py-5 position-relative overflow-hidden">
    <!-- Decorative Elements -->
    <div class="hero-decoration hero-decoration-1"></div>
    <div class="hero-decoration hero-decoration-2"></div>
    <div class="hero-decoration hero-decoration-3"></div>

    <div class="container py-5 position-relative" style="z-index: 2;">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-content">
                    <span class="badge bg-white text-sunset px-4 py-2 rounded-pill mb-4 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-pencil-square"></i>
                        Blog Management System
                    </span>
                    <h1 class="display-4 fw-bold mb-3 lh-1">
                        Write, Publish &amp; Manage<br>
                        <span class="text-white-50">Your Blog Posts</span>
                    </h1>
                    <p class="lead text-white-50 mb-4 fs-5 lh-base">
                        A powerful yet simple blog management system built with PHP and MySQL. 
                        Create, edit, and organize your content with ease — all in one place.
                    </p>
                    <div class="hero-cta d-flex flex-wrap gap-3">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="/dashboard.php" class="btn btn-light btn-lg rounded-4 fw-semibold px-4 py-3 shadow-lg hover-lift">
                                <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                            </a>
                        <?php else: ?>
                            <a href="/auth/register.php" class="btn btn-light btn-lg rounded-4 fw-semibold px-4 py-3 shadow-lg hover-lift">
                                <i class="bi bi-person-plus me-2"></i>Get Started Free
                            </a>
                            <a href="/auth/login.php" class="btn btn-outline-light btn-lg rounded-4 fw-semibold px-4 py-3 hover-lift">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-illustration text-center">
                    <div class="hero-card bg-white rounded-5 p-5 shadow-2xl d-inline-block" style="box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                        <div class="feature-icon bg-sunset-soft rounded-circle mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-journal-text text-sunset" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="text-dark fw-bold mt-3 mb-1">BlogCRUD</h5>
                        <p class="text-muted small mb-0">Manage your content effortlessly</p>
                        <div class="mt-3 d-flex justify-content-center gap-2">
                            <span class="badge bg-sunset-soft text-sunset px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle me-1"></i>Create
                            </span>
                            <span class="badge bg-sunset-soft text-sunset px-3 py-2 rounded-pill">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </span>
                            <span class="badge bg-sunset-soft text-sunset px-3 py-2 rounded-pill">
                                <i class="bi bi-search me-1"></i>Search
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section-padding bg-pattern">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-sunset-soft text-sunset px-4 py-2 rounded-pill mb-3 fw-semibold">
                <i class="bi bi-stars me-1"></i>Features
            </span>
            <h2 class="fw-bold display-6 mb-2">Everything You Need</h2>
            <p class="text-muted fs-5">Powerful features to manage your blog content seamlessly</p>
            <div class="page-header-divider mx-auto"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-4 stagger-1 animate-fade-in-up">
                <div class="card feature-card border-0 h-100 rounded-5 shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="feature-icon bg-sunset-soft rounded-circle mx-auto mb-4">
                            <i class="bi bi-shield-check text-sunset fs-2"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Secure Authentication</h5>
                        <p class="text-muted mb-0">Password hashing with bcrypt &amp; session management to keep your data safe and secure.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stagger-2 animate-fade-in-up">
                <div class="card feature-card border-0 h-100 rounded-5 shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="feature-icon bg-sunset-soft rounded-circle mx-auto mb-4">
                            <i class="bi bi-plus-circle-dotted text-sunset fs-2"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Full CRUD Operations</h5>
                        <p class="text-muted mb-0">Create, Read, Update, and Delete posts with an intuitive and user-friendly interface.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stagger-3 animate-fade-in-up">
                <div class="card feature-card border-0 h-100 rounded-5 shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="feature-icon bg-sunset-soft rounded-circle mx-auto mb-4">
                            <i class="bi bi-phone text-sunset fs-2"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Responsive Design</h5>
                        <p class="text-muted mb-0">Works seamlessly on desktop, tablet, and mobile devices with a modern, clean interface.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Posts Section -->
<section class="section-padding">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
            <div>
                <span class="badge bg-sunset-soft text-sunset px-4 py-2 rounded-pill mb-3 fw-semibold">
                    <i class="bi bi-newspaper me-1"></i>Latest Articles
                </span>
                <h3 class="fw-bold display-6 mb-1">Recent Posts</h3>
                <p class="text-muted mb-0">Latest articles from our community</p>
            </div>
            <a href="/posts/view.php" class="btn btn-outline-sunset rounded-4 fw-semibold px-4 py-2 hover-lift">
                View All Posts <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (count($recentPosts) > 0): ?>
            <div class="row g-4">
                <?php foreach ($recentPosts as $index => $post): ?>
                    <div class="col-md-6 col-lg-4 stagger-<?php echo min($index + 1, 6); ?> animate-fade-in-up">
                        <div class="card post-card border-0 shadow-sm rounded-5 h-100">
                            <div class="card-body p-4 d-flex flex-column">
                                <!-- Badge Row -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-sunset-soft text-sunset px-3 py-2 rounded-pill">
                                        <i class="bi bi-file-text me-1"></i>Article
                                    </span>
                                    <small class="text-muted d-flex align-items-center gap-1">
                                        <i class="bi bi-clock"></i>
                                        <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                    </small>
                                </div>

                                <!-- Title -->
                                <h5 class="card-title fw-bold mb-2">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </h5>

                                <!-- Preview -->
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo htmlspecialchars(substr($post['content'], 0, 150)); ?>
                                    <?php if (strlen($post['content']) > 150): ?>...<?php endif; ?>
                                </p>

                                <!-- Author & Link -->
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-sunset-soft d-flex align-items-center justify-content-center" 
                                             style="width: 32px; height: 32px;">
                                            <i class="bi bi-person-circle text-sunset"></i>
                                        </div>
                                        <small class="text-muted fw-medium">
                                            <?php echo htmlspecialchars($post['username']); ?>
                                        </small>
                                    </div>
                                    <a href="/posts/view.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-sm btn-outline-sunset rounded-pill px-3">
                                        Read More <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5 bg-pattern rounded-5">
                <div class="empty-state-icon bg-sunset-soft rounded-circle mx-auto mb-4">
                    <i class="bi bi-file-earmark-text text-sunset fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">No Posts Yet</h5>
                <p class="text-muted mb-4">Be the first to create a blog post!</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/posts/create.php" class="btn btn-gradient rounded-4 fw-semibold px-4 py-2">
                        <i class="bi bi-plus-lg me-2"></i>Create Your First Post
                    </a>
                <?php else: ?>
                    <a href="/auth/register.php" class="btn btn-gradient rounded-4 fw-semibold px-4 py-2">
                        <i class="bi bi-person-plus me-2"></i>Join to Start Writing
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
