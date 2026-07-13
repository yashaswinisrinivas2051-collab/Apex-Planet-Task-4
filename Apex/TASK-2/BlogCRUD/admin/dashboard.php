<?php
/**
 * Admin Dashboard
 * 
 * Administrative panel for managing users and all posts.
 * Access restricted to users with admin role only.
 * Features:
 * - View all users (ID, username, email, role, registered date)
 * - Change user roles (promote/demote between editor and admin)
 * - Delete users
 * - View all posts
 * - Delete any post
 * - View activity logs
 */

session_start();

// Require admin access
require_once '../config/db.php';
require_once '../middleware/auth.php';
require_once '../middleware/admin.php';
require_admin();

$pageTitle = 'Admin Dashboard';
$message = '';
$error = '';

// --- Handle User Role Update ---
if (isset($_GET['action'], $_GET['user_id']) && $_GET['action'] === 'toggle_role') {
    $targetUserId = (int)$_GET['user_id'];
    
    // Prevent self-demotion
    if ($targetUserId === (int)$_SESSION['user_id']) {
        $error = 'You cannot change your own role.';
    } elseif (!isset($_GET['_csrf_token']) || !verify_csrf_token_get()) {
        $error = 'Security token expired. Please try again.';
    } else {
        $stmt = $pdo->prepare("SELECT role, username FROM users WHERE id = :id");
        $stmt->execute([':id' => $targetUserId]);
        $targetUser = $stmt->fetch();
        
        if ($targetUser) {
            $currentRole = $targetUser['role'];
            // Toggle between 'editor' and 'admin'
            $newRole = ($currentRole === 'admin') ? 'editor' : 'admin';
            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute([':role' => $newRole, ':id' => $targetUserId]);
            
            // Log the role change
            log_activity(
                (int)$_SESSION['user_id'],
                'change_role',
                "Changed user '{$targetUser['username']}' role from '$currentRole' to '$newRole'."
            );
            
            $message = "User '{$targetUser['username']}' role changed to '$newRole' successfully.";
        } else {
            $error = 'User not found.';
        }
    }
}

// --- Handle User Deletion ---
if (isset($_GET['action'], $_GET['user_id']) && $_GET['action'] === 'delete_user') {
    $targetUserId = (int)$_GET['user_id'];
    
    // Prevent self-deletion
    if ($targetUserId === (int)$_SESSION['user_id']) {
        $error = 'You cannot delete your own account.';
    } elseif (!isset($_GET['_csrf_token']) || !verify_csrf_token_get()) {
        $error = 'Security token expired. Please try again.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
            $stmt->execute([':id' => $targetUserId]);
            $targetUser = $stmt->fetch();
            $targetUsername = $targetUser['username'] ?? 'Unknown';
            
            // Delete user's posts first
            $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = :id");
            $stmt->execute([':id' => $targetUserId]);
            
            // Delete user's activity logs
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE user_id = :id");
            $stmt->execute([':id' => $targetUserId]);
            
            // Delete user
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $targetUserId]);
            
            // Log the user deletion
            log_activity(
                (int)$_SESSION['user_id'],
                'delete_user',
                "Deleted user '$targetUsername' (ID: $targetUserId) and all associated data."
            );
            
            $message = "User '$targetUsername' deleted successfully.";
        } catch (PDOException $e) {
            log_error("Admin Delete User Error: " . $e->getMessage());
            $error = 'Failed to delete user. Please try again.';
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Fetch all posts with author info
$stmt = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
$allPosts = $stmt->fetchAll();

// Fetch recent activity logs
$recentLogs = get_all_activity(30);

// Fetch stats
$totalUsers = count($users);
$totalPosts = count($allPosts);
$adminCount = 0;
$editorCount = 0;
foreach ($users as $u) {
    if ($u['role'] === 'admin') $adminCount++;
    else $editorCount++;
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">

    <!-- Flash Messages -->
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm rounded-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div><?php echo htmlspecialchars($message); ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm rounded-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <div class="me-3">
            <div class="rounded-circle bg-danger-soft d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="bi bi-shield-shaded text-danger fs-3"></i>
            </div>
        </div>
        <div>
            <span class="badge bg-danger-soft text-danger px-3 py-2 rounded-pill mb-1 fw-semibold">
                <i class="bi bi-shield-fill me-1"></i>Admin Panel
            </span>
            <h3 class="fw-bold mb-0">Admin Dashboard</h3>
            <p class="text-muted mb-0">Manage users, monitor activity, and oversee all posts</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 stagger-1 animate-fade-in-up">
            <div class="card stat-card border-0 shadow-sm rounded-5 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-danger-soft rounded-4 p-3 me-3">
                            <i class="bi bi-people text-danger fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 fw-semibold">Total Users</p>
                            <h3 class="fw-bold mb-0 text-danger display-6"><?php echo $totalUsers; ?></h3>
                            <small class="text-muted"><?php echo $adminCount; ?> admin(s), <?php echo $editorCount; ?> editor(s)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 stagger-2 animate-fade-in-up">
            <div class="card stat-card border-0 shadow-sm rounded-5 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-soft rounded-4 p-3 me-3">
                            <i class="bi bi-file-text text-primary fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 fw-semibold">Total Posts</p>
                            <h3 class="fw-bold mb-0 text-primary display-6"><?php echo $totalPosts; ?></h3>
                            <small class="text-muted">Across all users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 stagger-3 animate-fade-in-up">
            <div class="card stat-card border-0 shadow-sm rounded-5 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success-soft rounded-4 p-3 me-3">
                            <i class="bi bi-activity text-success fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 fw-semibold">Avg Posts/User</p>
                            <h3 class="fw-bold mb-0 text-success display-6">
                                <?php echo $totalUsers > 0 ? round($totalPosts / $totalUsers, 1) : 0; ?>
                            </h3>
                            <small class="text-muted">Posting activity</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 stagger-4 animate-fade-in-up">
            <div class="card stat-card border-0 shadow-sm rounded-5 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning-soft rounded-4 p-3 me-3">
                            <i class="bi bi-clock-history text-warning fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 fw-semibold">Recent Activity</p>
                            <h3 class="fw-bold mb-0 text-warning display-6"><?php echo count($recentLogs); ?></h3>
                            <small class="text-muted">Events logged</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Management -->
    <div class="card border-0 shadow-sm rounded-5 mb-4">
        <div class="card-header bg-transparent border-0 p-4 pb-0">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-people me-2 text-danger"></i>User Management
            </h5>
            <p class="text-muted small mb-0">Manage registered users and their roles</p>
        </div>
        <div class="card-body p-4">
            <?php if (count($users) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light rounded-4">
                            <tr>
                                <th class="fw-semibold ps-3">ID</th>
                                <th class="fw-semibold">Username</th>
                                <th class="fw-semibold">Email</th>
                                <th class="fw-semibold">Role</th>
                                <th class="fw-semibold">Registered</th>
                                <th class="fw-semibold text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="ps-3 fw-medium text-muted"><?php echo (int)$user['id']; ?></td>
                                    <td class="fw-semibold">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <?php if ((int)$user['id'] === (int)$_SESSION['user_id']): ?>
                                            <span class="badge bg-info-soft text-info ms-1">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger-soft text-danger rounded-pill px-3 py-1">
                                                <i class="bi bi-shield-fill me-1"></i>Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-sunset-soft text-sunset rounded-pill px-3 py-1">
                                                <i class="bi bi-pencil me-1"></i>Editor
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                            <a href="/admin/dashboard.php?action=toggle_role&user_id=<?php echo (int)$user['id']; ?>&_csrf_token=<?php echo get_csrf_token(); ?>" 
                                               class="btn btn-sm btn-outline-warning rounded-3 hover-lift me-1" 
                                               title="Toggle role (make <?php echo $user['role'] === 'admin' ? 'editor' : 'admin'; ?>)"
                                               onclick="return confirm('Change role for <?php echo htmlspecialchars(addslashes($user['username'])); ?>?');">
                                                <i class="bi bi-arrow-left-right"></i>
                                            </a>
                                            <a href="/admin/dashboard.php?action=delete_user&user_id=<?php echo (int)$user['id']; ?>&_csrf_token=<?php echo get_csrf_token(); ?>" 
                                               class="btn btn-sm btn-outline-danger rounded-3 hover-lift" 
                                               title="Delete user permanently"
                                               onclick="return confirm('Delete user <?php echo htmlspecialchars(addslashes($user['username'])); ?> permanently? All their posts and activity logs will also be deleted.');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-3 mb-0">No users found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- All Posts Management -->
    <div class="card border-0 shadow-sm rounded-5 mb-4">
        <div class="card-header bg-transparent border-0 p-4 pb-0">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-journal-text me-2 text-primary"></i>All Posts (Global)
            </h5>
            <p class="text-muted small mb-0">View and manage posts from all users</p>
        </div>
        <div class="card-body p-4">
            <?php if (count($allPosts) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light rounded-4">
                            <tr>
                                <th class="fw-semibold ps-3">#</th>
                                <th class="fw-semibold">Title</th>
                                <th class="fw-semibold">Author</th>
                                <th class="fw-semibold">Created</th>
                                <th class="fw-semibold text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allPosts as $index => $post): ?>
                                <tr>
                                    <td class="ps-3 fw-medium text-muted"><?php echo $index + 1; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td>
                                        <span class="badge bg-sunset-soft text-sunset rounded-pill px-3 py-1">
                                            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($post['username']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="/posts/view.php?id=<?php echo (int)$post['id']; ?>" 
                                           class="btn btn-sm btn-outline-info rounded-3 hover-lift me-1" title="View Post">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/posts/edit.php?id=<?php echo (int)$post['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-3 hover-lift me-1" title="Edit Post">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3 hover-lift" 
                                                title="Delete Post"
                                                onclick="confirmDelete(<?php echo (int)$post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['title'])); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-3 mb-0">No posts found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="card border-0 shadow-sm rounded-5">
        <div class="card-header bg-transparent border-0 p-4 pb-0">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history me-2 text-warning"></i>Recent Activity Logs
            </h5>
            <p class="text-muted small mb-0">Audit trail of user actions across the system</p>
        </div>
        <div class="card-body p-4">
            <?php if (count($recentLogs) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light rounded-4">
                            <tr>
                                <th class="fw-semibold ps-3">User</th>
                                <th class="fw-semibold">Action</th>
                                <th class="fw-semibold">Description</th>
                                <th class="fw-semibold text-end pe-3">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-medium">
                                            <i class="bi bi-person-circle me-1 text-muted"></i>
                                            <?php echo htmlspecialchars($log['username'] ?? 'System'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $actionBadge = match($log['action']) {
                                            'login' => 'bg-success-soft text-success',
                                            'logout' => 'bg-secondary bg-opacity-10 text-secondary',
                                            'registration' => 'bg-info-soft text-info',
                                            'create_post' => 'bg-primary-soft text-primary',
                                            'edit_post' => 'bg-warning-soft text-warning',
                                            'delete_post' => 'bg-danger-soft text-danger',
                                            'change_role' => 'bg-warning-soft text-warning',
                                            'delete_user' => 'bg-danger-soft text-danger',
                                            default => 'bg-light text-dark'
                                        };
                                        ?>
                                        <span class="badge rounded-pill px-3 py-1 <?php echo $actionBadge; ?>">
                                            <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($log['action']))); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo htmlspecialchars($log['description'] ?? ''); ?>
                                    </td>
                                    <td class="text-end pe-3 text-muted small">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <div class="empty-state-sm bg-warning-soft rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bi bi-clock-history text-warning fs-3"></i>
                    </div>
                    <p class="text-muted mb-0">No activity logs yet. Activity is recorded as users interact with the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
