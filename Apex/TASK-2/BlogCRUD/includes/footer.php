    <!-- Footer -->
    <footer class="bg-sunset-gradient text-white pt-5 pb-4 mt-auto">
        <div class="container">
            <div class="row g-4 pb-4 border-bottom border-white border-opacity-10">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-pencil-square me-2"></i>BlogCRUD
                    </h5>
                    <p class="text-white-50 small mb-0">
                        A powerful blog management system built with PHP, MySQL, and Bootstrap 5. 
                        Create, manage, and share your thoughts with the world.
                    </p>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-semibold mb-3 text-sunset-light">Quick Links</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="/index.php" class="text-white-50 text-decoration-none small hover-lift d-inline-block">Home</a></li>
                        <li class="mb-2"><a href="/posts/view.php" class="text-white-50 text-decoration-none small hover-lift d-inline-block">All Posts</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="mb-2"><a href="/dashboard.php" class="text-white-50 text-decoration-none small hover-lift d-inline-block">Dashboard</a></li>
                            <li><a href="/posts/create.php" class="text-white-50 text-decoration-none small hover-lift d-inline-block">Create Post</a></li>
                        <?php else: ?>
                            <li class="mb-2"><a href="/auth/login.php" class="text-white-50 text-decoration-none small hover-lift d-inline-block">Login</a></li>
                            <li><a href="/auth/register.php" class="text-white-50 text-decoration-none small hover-lift d-inline-block">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <h6 class="fw-semibold mb-3 text-sunset-light">Features</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><span class="text-white-50 small"><i class="bi bi-shield-check me-1"></i> Secure Auth</span></li>
                        <li class="mb-2"><span class="text-white-50 small"><i class="bi bi-plus-circle me-1"></i> Full CRUD</span></li>
                        <li class="mb-2"><span class="text-white-50 small"><i class="bi bi-search me-1"></i> Search Posts</span></li>
                        <li><span class="text-white-50 small"><i class="bi bi-phone me-1"></i> Responsive Design</span></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-semibold mb-3 text-sunset-light">Connect</h6>
                    <p class="text-white-50 small mb-3">Built with passion for the blogging community.</p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2" style="width:36px;height:36px;" title="GitHub">
                            <i class="bi bi-github"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2" style="width:36px;height:36px;" title="Twitter">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2" style="width:36px;height:36px;" title="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2" style="width:36px;height:36px;" title="Email">
                            <i class="bi bi-envelope"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row pt-4">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0 text-white-50 small">
                        <i class="bi bi-pencil-square me-1"></i>
                        <strong class="text-white">BlogCRUD</strong> &copy; <?php echo date('Y'); ?> 
                        All Rights Reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 small text-sunset-light">
                        Made with <i class="bi bi-heart-fill text-danger"></i> using 
                        PHP, MySQL &amp; Bootstrap 5
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="/assets/js/script.js"></script>
</body>
</html>
