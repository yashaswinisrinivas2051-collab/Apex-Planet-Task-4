<?php
/**
 * Dashboard Page
 * 
 * Main management hub for authenticated users.
 * - Regular users see their own posts
 * - Admin users see all posts and admin panel link
 */

session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

require_once 'config/db.php';

$pageTitle = 'Dashboard';

$userId = (int)$_SESSION['user_id'];
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

// Fetch total posts count
if ($isAdmin) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM posts");
    $totalPosts = (int)$stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
    $userPosts = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM posts WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $totalPosts = (int)$stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $userId]);
    $userPosts = $stmt->fetchAll();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">

    <!-- Success/Error Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm rounded-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm rounded-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-sunset-gradient-animated border-0 rounded-5 shadow-lg overflow-hidden">
                <div class="card-body p-4 p-lg-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div class="mb-3 mb-md-0">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center animate-float"
                                 style="width: 56px; height: 56px; background: rgba(255,255,255,0.15);">
                                <i class="bi bi-person-circle text-white fs-3"></i>
                            </div>
                            <div>
                                <h3 class="text-white fw-bold mb-0">
                                    Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                                </h3>
                                <p class="text-white-50 mb-0">
                                    <i class="bi bi-calendar me-1"></i>
                                    <?php echo date('l, F j, Y'); ?>
                                    <?php if ($isAdmin): ?>
                                        <span class="badge bg-light text-danger ms-2 rounded-pill">
                                            <i class="bi bi-shield-fill me-1"></i>Admin
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="/posts/create.php" class="btn btn-light btn-lg rounded-4 fw-semibold px-4 shadow-sm hover-lift">
                            <i class="bi bi-plus-lg me-2"></i>Create New Post
                        </a>
                        <?php if ($isAdmin): ?>
                            <a href="/admin/dashboard.php" class="btn btn-outline-light btn-lg rounded-4 fw-semibold px-4 shadow-sm hover-lift">
                                <i class="bi bi-shield me-2"></i>Admin Panel
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4 stagger-1 animate-fade-in-up">
            <div class="card stat-card border-0 shadow-sm rounded-5 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-sunset-soft rounded-4 p-3 me-3">
                            <i class="bi bi-file-text text-sunset fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 fw-semibold"><?php echo $isAdmin ? 'Total Posts' : 'Your Posts'; ?></p>
                            <h3 class="fw-bold mb-0 text-sunset display-6"><?php echo $totalPosts; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 stagger-2 animate-fade-in-up">
            <div class="card stat-card border-0 shadow-sm rounded-5 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-soft rounded-4 p-3 me-3">
                            <i class="bi bi-eye text-primary fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 fw-semibold">Quick View</p>
                            <a href="/posts/view.php" class="fw-bold text-decoration-none text-primary fs-5 hover-lift d-inline-block">
                                View All Posts <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 stagger-3 animate-fade-in-up">
            <div class="card stat-card border-0 shadow-sm rounded-5 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning-soft rounded-4 p-3 me-3">
                            <i class="bi bi-plus-circle text-warning fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 fw-semibold">New Post</p>
                            <a href="/posts/create.php" class="fw-bold text-decoration-none text-warning fs-5 hover-lift d-inline-block">
                                Create Now <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts List -->
    <div class="card border-0 shadow-sm rounded-5 stagger-4 animate-fade-in-up">
        <div class="card-header bg-transparent border-0 p-4 pb-0">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-journal-text me-2 text-sunset"></i><?php echo $isAdmin ? 'All Posts' : 'Your Recent Posts'; ?>
                    </h5>
                    <p class="text-muted small mb-0">Manage and organize your content</p>
                </div>
                <a href="/posts/create.php" class="btn btn-sunset rounded-4 fw-semibold px-3">
                    <i class="bi bi-plus-lg me-1"></i>New Post
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if (count($userPosts) > 0): ?>
                <!-- Mobile: Card View -->
                <div class="d-md-none">
                    <?php foreach ($userPosts as $post): ?>
                        <div class="card post-card border-0 bg-light rounded-4 mb-3">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($post['title']); ?></h6>
                                <p class="text-muted small mb-2">
                                    <?php echo htmlspecialchars(substr($post['content'], 0, 100)); ?>...
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted d-flex align-items-center gap-1">
                                        <i class="bi bi-clock"></i>
                                        <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                    </small>
                                    <div class="d-flex gap-1">
                                        <a href="/posts/edit.php?id=<?php echo (int)$post['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-3">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3" 
                                                onclick="confirmDelete(<?php echo (int)$post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['title'])); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Desktop: Table View -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light rounded-4">
                            <tr>
                                <th class="fw-semibold ps-3 rounded-start-4">#</th>
                                <th class="fw-semibold">Title</th>
                                <th class="fw-semibold">Content Preview</th>
                                <th class="fw-semibold">Created</th>
                                <th class="fw-semibold text-end pe-3 rounded-end-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userPosts as $index => $post): ?>
                                <tr class="border-bottom">
                                    <td class="ps-3 fw-medium text-muted"><?php echo $index + 1; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td>
                                        <span class="text-muted small">
                                            <?php echo htmlspecialchars(substr($post['content'], 0, 80)); ?>...
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="/posts/edit.php?id=<?php echo (int)$post['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary rounded-3 hover-lift" title="Edit Post">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="/posts/view.php?id=<?php echo (int)$post['id']; ?>" 
                                               class="btn btn-sm btn-outline-info rounded-3 hover-lift" title="View Post">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 hover-lift" 
                                                    title="Delete Post"
                                                    onclick="confirmDelete(<?php echo (int)$post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['title'])); ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="empty-state-icon bg-sunset-soft rounded-circle mx-auto mb-4">
                        <i class="bi bi-journal-plus text-sunset fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-2">No Posts Yet</h5>
                    <p class="text-muted mb-4">You haven't created any posts yet. Start writing!</p>
                    <a href="/posts/create.php" class="btn btn-gradient rounded-4 fw-semibold px-4 py-2">
                        <i class="bi bi-plus-lg me-2"></i>Create Your First Post
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-5 shadow-2xl">
            <div class="modal-body text-center p-4">
                <div class="delete-icon bg-danger-soft rounded-circle mx-auto mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Confirm Delete</h5>
                <p class="text-muted mb-4" id="deleteMessage">Are you sure you want to delete this post?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary rounded-4 px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger rounded-4 px-4">
                        <i class="bi bi-trash me-1"></i>Delete
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
