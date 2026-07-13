<?php
/**
 * 404 Error Page
 * 
 * Custom error page for invalid routes.
 * Displays a user-friendly message with navigation options.
 * Note: Does NOT load the database connection, only loads
 * the security helper for CSRF token support in the navbar.
 */

session_start();
$pageTitle = 'Page Not Found';

// Only load the security helper for CSRF tokens (no DB connection needed)
require_once 'helpers/security.php';

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="py-5">
                <!-- 404 Illustration -->
                <div class="mb-4">
                    <div class="d-inline-block bg-sunset-soft rounded-5 p-5 mb-3" style="box-shadow: 0 20px 60px rgba(249, 115, 22, 0.15);">
                        <div class="display-1 fw-bold text-sunset" style="font-size: 6rem; line-height: 1;">404</div>
                    </div>
                </div>

                <!-- Error Message -->
                <h3 class="fw-bold mb-2">Oops! Page Not Found</h3>
                <p class="text-muted mb-4 fs-5">
                    The page you're looking for doesn't exist or has been moved.
                    Let's get you back on track!
                </p>

                <!-- Divider -->
                <div class="page-header-divider mx-auto mb-4"></div>

                <!-- Helpful Links -->
                <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
                    <a href="/index.php" class="btn btn-gradient rounded-4 fw-semibold px-4 py-2">
                        <i class="bi bi-house-door me-2"></i>Go Home
                    </a>
                    <a href="/posts/view.php" class="btn btn-outline-sunset rounded-4 fw-semibold px-4 py-2">
                        <i class="bi bi-file-text me-2"></i>Browse Posts
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/dashboard.php" class="btn btn-outline-secondary rounded-4 fw-semibold px-4 py-2">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    <?php else: ?>
                        <a href="/auth/login.php" class="btn btn-outline-secondary rounded-4 fw-semibold px-4 py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Quick Search -->
                <div class="card border-0 bg-light rounded-5 shadow-sm">
                    <div class="card-body p-4">
                        <p class="text-muted mb-3">
                            <i class="bi bi-search me-1"></i>
                            Search for posts instead:
                        </p>
                        <form action="/posts/view.php" method="GET" class="search-bar mx-auto" style="max-width: 400px;">
                            <input type="text" class="form-control form-control-lg shadow-sm" name="search" 
                                   placeholder="Search posts..." required>
                            <i class="bi bi-search search-icon"></i>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
