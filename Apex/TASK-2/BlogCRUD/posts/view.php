<?php
/**
 * View Posts Page
 * 
 * Displays all blog posts in a responsive card grid, or a single post
 * when an ID is provided.
 * Features:
 * - Search/filter posts by title and content
 * - Pagination (5 posts per page)
 * - Single post detail view
 * - Responsive card layout
 * - Edit and Delete action buttons for owners
 * - Search + Pagination work together
 */

session_start();

require_once '../config/db.php';

$pageTitle = 'All Posts';

// Check if viewing a single post
if (isset($_GET['id'])) {
    $post_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p 
                           JOIN users u ON p.user_id = u.id 
                           WHERE p.id = :id");
    $stmt->execute([':id' => $post_id]);
    $singlePost = $stmt->fetch();

    if ($singlePost) {
        $pageTitle = htmlspecialchars($singlePost['title']);
        include '../includes/header.php';
        include '../includes/navbar.php';
        ?>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <a href="/posts/view.php" class="btn btn-outline-secondary rounded-4 mb-4 hover-lift">
                        <i class="bi bi-arrow-left me-1"></i>Back to All Posts
                    </a>

                    <div class="card border-0 shadow-lg rounded-5">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                <span class="badge bg-sunset-soft text-sunset px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-file-text me-1"></i>Article
                                </span>
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-clock"></i>
                                    <?php echo date('F d, Y', strtotime($singlePost['created_at'])); ?>
                                </small>
                            </div>

                            <h2 class="fw-bold mb-3 lh-1"><?php echo htmlspecialchars($singlePost['title']); ?></h2>

                            <div class="d-flex align-items-center text-muted mb-4 pb-3 border-bottom">
                                <div class="rounded-circle bg-sunset-soft d-flex align-items-center justify-content-center me-2" 
                                     style="width: 36px; height: 36px;">
                                    <i class="bi bi-person-circle text-sunset"></i>
                                </div>
                                <span class="fw-medium"><?php echo htmlspecialchars($singlePost['username']); ?></span>
                            </div>

                            <div class="post-content lh-lg" style="white-space: pre-wrap; font-size: 1.05rem;">
                                <?php echo htmlspecialchars($singlePost['content']); ?>
                            </div>

                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $singlePost['user_id']): ?>
                                <div class="d-flex gap-2 mt-4 pt-4 border-top">
                                    <a href="/posts/edit.php?id=<?php echo $singlePost['id']; ?>" 
                                       class="btn btn-outline-primary rounded-4 hover-lift">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </a>
                                    <button type="button" class="btn btn-outline-danger rounded-4 hover-lift" 
                                            onclick="confirmDelete(<?php echo $singlePost['id']; ?>, '<?php echo htmlspecialchars(addslashes($singlePost['title'])); ?>')">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
        <?php
        include '../includes/footer.php';
        exit();
    }
}

// ============================================================
// List View with Search & Pagination
// ============================================================

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch and sanitize search query
$search = isset($_GET['search']) ? sanitize_search($_GET['search']) : '';

// Get total count and paginated results
if (!empty($search)) {
    $searchTerm = "%{$search}%";
    
    // Count total matching posts
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts p 
                                JOIN users u ON p.user_id = u.id 
                                WHERE p.title LIKE :title OR p.content LIKE :content");
    $countStmt->execute([':title' => $searchTerm, ':content' => $searchTerm]);
    $totalPosts = (int)$countStmt->fetchColumn();
    
    // Get paginated results with search filter
    $stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p 
                           JOIN users u ON p.user_id = u.id 
                           WHERE p.title LIKE :title OR p.content LIKE :content
                           ORDER BY p.created_at DESC 
                           LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':title', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':content', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    // Count total posts
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $totalPosts = (int)$totalStmt->fetchColumn();
    
    // Get paginated results
    $stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p 
                           JOIN users u ON p.user_id = u.id 
                           ORDER BY p.created_at DESC 
                           LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$posts = $stmt->fetchAll();
$totalPages = max(1, (int)ceil($totalPosts / $limit));

// If current page exceeds total pages, redirect to last page
if ($page > $totalPages && $totalPages > 0) {
    $params = $_GET;
    $params['page'] = $totalPages;
    header('Location: view.php?' . http_build_query($params));
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <!-- Page Header & Search -->
    <div class="row mb-4 align-items-end">
        <div class="col-md-7 mb-3 mb-md-0">
            <span class="badge bg-sunset-soft text-sunset px-3 py-2 rounded-pill mb-2 fw-semibold">
                <i class="bi bi-newspaper me-1"></i>Blog Posts
            </span>
            <h3 class="fw-bold mb-1 display-6">
                <i class="bi bi-file-text me-2 text-sunset"></i>All Blog Posts
            </h3>
            <div class="page-header-divider"></div>
            <p class="text-muted mb-0">
                <?php echo $totalPosts; ?> post<?php echo $totalPosts !== 1 ? 's' : ''; ?> found
                <?php if (!empty($search)): ?>
                    <span class="search-result-count ms-2">
                        <i class="bi bi-search"></i> Showing results for '<strong><?php echo htmlspecialchars($search); ?></strong>'
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-5">
            <!-- Search Form (resets to page 1 on new search) -->
            <form action="/posts/view.php" method="GET" class="search-bar">
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="page" value="1">
                <?php endif; ?>
                <input type="text" class="form-control form-control-lg shadow-sm" name="search" 
                       placeholder="Search posts by title or content..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <i class="bi bi-search search-icon"></i>
                <?php if (!empty($search)): ?>
                    <a href="/posts/view.php" 
                       class="btn btn-outline-secondary rounded-pill position-absolute end-0 top-50 translate-middle-y me-1 border-0"
                       title="Clear search" style="z-index: 6;">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Posts Grid -->
    <?php if (count($posts) > 0): ?>
        <div class="row g-4">
            <?php foreach ($posts as $index => $post): ?>
                <div class="col-md-6 col-lg-4 stagger-<?php echo min($index + 1, 6); ?> animate-fade-in-up">
                    <div class="card post-card h-100 border-0 shadow-sm rounded-5">
                        <div class="card-body p-4 d-flex flex-column">
                            <!-- Top Row: Badge + Date -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-sunset-soft text-sunset px-3 py-2 rounded-pill">
                                    <i class="bi bi-file-text me-1"></i>Article
                                </span>
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-clock"></i>
                                    <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                </small>
                            </div>

                            <!-- Post Title -->
                            <h5 class="card-title fw-bold mb-2 truncate-2">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </h5>

                            <!-- Post Content Preview -->
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo htmlspecialchars(substr($post['content'], 0, 150)); ?>
                                <?php if (strlen($post['content']) > 150): ?>...<?php endif; ?>
                            </p>

                            <!-- Author & Actions -->
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

                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                    <div class="d-flex gap-1 btn-group-actions">
                                        <a href="/posts/edit.php?id=<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-3 hover-lift" 
                                           title="Edit Post">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3 hover-lift" 
                                                title="Delete Post"
                                                onclick="confirmDelete(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['title'])); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Click card to read full post -->
                        <a href="view.php?id=<?php echo $post['id']; ?>" 
                           class="stretched-link" style="text-decoration: none;"></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-5">
            <nav aria-label="Blog posts pagination">
                <ul class="pagination pagination-lg">
                    <!-- Previous Button -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link rounded-4 px-3" 
                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                           aria-label="Previous" tabindex="<?php echo $page <= 1 ? '-1' : '0'; ?>">
                            <i class="bi bi-chevron-left me-1"></i> Previous
                        </a>
                    </li>

                    <!-- Numbered Pages -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link rounded-4" 
                               href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link rounded-4 px-3" 
                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                           aria-label="Next" tabindex="<?php echo $page >= $totalPages ? '-1' : '0'; ?>">
                            Next <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-5 bg-pattern rounded-5">
            <div class="empty-state-icon bg-sunset-soft rounded-circle mx-auto mb-4">
                <i class="bi bi-file-earmark-text text-sunset fs-1"></i>
            </div>
            <h5 class="fw-bold mb-2">
                <?php echo !empty($search) ? 'No Posts Found' : 'No Posts Yet'; ?>
            </h5>
            <p class="text-muted mb-4">
                <?php echo !empty($search) 
                    ? 'No posts match your search query. Try different keywords.'
                    : 'Start by creating your first blog post!'; ?>
            </p>
            <?php if (!empty($search)): ?>
                <a href="/posts/view.php" class="btn btn-outline-secondary rounded-4 px-4 me-2">
                    <i class="bi bi-x-lg me-1"></i>Clear Search
                </a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/posts/create.php" class="btn btn-gradient rounded-4 fw-semibold px-4 py-2">
                    <i class="bi bi-plus-lg me-2"></i>Create New Post
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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

<?php include '../includes/footer.php'; ?>
