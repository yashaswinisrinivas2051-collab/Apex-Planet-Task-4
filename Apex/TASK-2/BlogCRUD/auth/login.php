<?php
/**
 * User Login Page
 * 
 * Authenticates users and starts a session.
 * Features:
 * - CSRF protection
 * - Login attempt rate limiting
 * - Password verification using password_verify()
 * - Session regeneration after login
 * - Role-based session data
 * - Activity logging on successful login
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit();
}

require_once '../config/db.php';

$pageTitle = 'Login';
$error = '';
$username = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Protection ---
    if (!verify_csrf_token()) {
        $error = 'Security token expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // --- Login Rate Limiting ---
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $maxAttempts = 5;
        $lockoutMinutes = 15;
        $attempts = 0;
        
        try {
            // Clean up old attempts
            $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL :lockoutMinutes MINUTE)");
            $stmt->execute([':lockoutMinutes' => $lockoutMinutes]);
            
            // Check recent attempts from this IP/username combination
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = :ip AND username = :username AND attempted_at > DATE_SUB(NOW(), INTERVAL :lockoutMinutes MINUTE)");
            $stmt->execute([':ip' => $ip, ':username' => $username, ':lockoutMinutes' => $lockoutMinutes]);
            $attempts = (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            // Log table might not exist yet, silently continue without rate limiting
            log_error("Rate Limiting Warning: " . $e->getMessage());
        }
        
        if ($attempts >= $maxAttempts) {
            $error = "Too many login attempts. Please try again in $lockoutMinutes minutes.";
        }
        // Validate inputs
        elseif (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            // Fetch user from database (including role)
            $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            // Verify password
            if ($user && password_verify($password, $user['password'])) {
                // Clear login attempts on success
                $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = :ip AND username = :username");
                $stmt->execute([':ip' => $ip, ':username' => $username]);

                // Set session variables
                $_SESSION['user_id']  = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['_last_activity'] = time();

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Log login activity
                log_activity((int)$user['id'], 'login', "User '{$user['username']}' logged in.");

                // Redirect to dashboard
                header('Location: /dashboard.php');
                exit();
            } else {
                // Record failed attempt
                $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (:ip, :username)");
                $stmt->execute([':ip' => $ip, ':username' => $username]);
                
                $error = 'Invalid username or password.';
            }
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="auth-page">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card shadow-xl border-0 rounded-5">
                    <div class="card-body p-4 p-lg-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="auth-icon bg-sunset-soft rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-box-arrow-in-right text-sunset fs-2"></i>
                            </div>
                            <h3 class="fw-bold mb-1">Welcome Back</h3>
                            <p class="text-muted">Sign in to manage your blog posts</p>
                            <div class="page-header-divider mx-auto"></div>
                        </div>

                        <!-- Logged Out Alert Container -->
                        <div class="login-alert-container"></div>

                        <!-- Error Alert -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <div><?php echo htmlspecialchars($error); ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form action="/auth/login.php" method="POST" novalidate>
                            <?php csrf_field(); ?>
                            
                            <!-- Username -->
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-1"></i>Username
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 border-end-0 rounded-start-4">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-2 border-start-0 rounded-end-4 ps-0" 
                                           id="username" name="username" placeholder="Enter your username" 
                                           value="<?php echo htmlspecialchars($username); ?>" required>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-1"></i>Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 border-end-0 rounded-start-4">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-2 border-start-0 rounded-end-4 ps-0" 
                                           id="password" name="password" placeholder="Enter your password" required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-gradient w-100 btn-lg rounded-4 fw-semibold mb-3 btn-pulse">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>

                            <!-- Register Link -->
                            <p class="text-center text-muted mb-0">
                                Don't have an account? 
                                <a href="/auth/register.php" class="text-sunset text-decoration-none fw-semibold hover-lift d-inline-block">
                                    Create one <i class="bi bi-arrow-right small"></i>
                                </a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
