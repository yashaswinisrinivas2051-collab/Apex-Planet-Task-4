<?php
/**
 * Admin Middleware
 * 
 * Handles admin-specific authorization checks.
 * Include this file on any page that requires admin privileges.
 */

/**
 * Check if the current user has admin role.
 * 
 * @return bool True if user has admin role
 */
function is_admin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require the user to have admin role.
 * Shows a 403 Access Denied page if not admin.
 */
function require_admin(): void {
    require_auth_with_timeout();
    
    if (!is_admin()) {
        http_response_code(403);
        $pageTitle = 'Access Denied';
        include dirname(__DIR__) . '/includes/header.php';
        include dirname(__DIR__) . '/includes/navbar.php';
        ?>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="py-5">
                        <div class="display-1 text-danger fw-bold mb-3">403</div>
                        <h3 class="fw-bold mb-3"><i class="bi bi-shield-exclamation me-2"></i>Access Denied</h3>
                        <p class="text-muted mb-4">You do not have permission to access this page. This area is restricted to administrators only.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="/dashboard.php" class="btn btn-outline-secondary rounded-4 px-4">
                                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                            </a>
                            <a href="/index.php" class="btn btn-sunset rounded-4 px-4">
                                <i class="bi bi-house me-1"></i>Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include dirname(__DIR__) . '/includes/footer.php';
        exit();
    }
}
