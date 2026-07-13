<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-sunset-gradient shadow-lg sticky-top" id="mainNavbar">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand fw-bold fs-4" href="/index.php">
            <i class="bi bi-pencil-square me-2"></i>BlogCRUD
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1 gap-lg-2">

                <!-- Public Links -->
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?> rounded-3" 
                       href="/index.php">
                        <i class="bi bi-house-door me-1"></i>Home
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Authenticated User Links -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?> rounded-3" 
                           href="/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'view.php' ? 'active' : ''; ?> rounded-3" 
                           href="/posts/view.php">
                            <i class="bi bi-file-text me-1"></i>Posts
                        </a>
                    </li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?> rounded-3" 
                               href="/admin/dashboard.php">
                                <i class="bi bi-shield me-1"></i>Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link rounded-3" href="/auth/logout.php?_csrf_token=<?php echo get_csrf_token(); ?>"
                           onclick="return confirm('Are you sure you want to logout?');">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                    <!-- User Badge -->
                    <li class="nav-item ms-lg-2">
                        <span class="nav-link text-sunset-light fw-semibold">
                            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                    </li>
                <?php else: ?>
                    <!-- Guest Links -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : ''; ?> rounded-3" 
                           href="/auth/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'register.php' ? 'active' : ''; ?> rounded-3" 
                           href="/auth/register.php">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
