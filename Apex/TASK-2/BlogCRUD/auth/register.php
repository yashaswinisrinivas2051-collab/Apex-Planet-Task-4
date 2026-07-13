<?php
/**
 * User Registration Page
 * 
 * Allows new users to create an account.
 * Features:
 * - Server-side validation (email, username, password strength)
 * - CSRF protection
 * - Password hashing with password_hash()
 * - Duplicate username/email checking
 * - Prepared statements throughout
 * - Activity logging on successful registration
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit();
}

require_once '../config/db.php';

$pageTitle = 'Create Account';
$error = '';
$success = '';
$username = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Protection ---
    if (!verify_csrf_token()) {
        $error = 'Security token expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // --- Server-Side Validation ---

        // Validate username
        $usernameCheck = validate_username($username);
        if (!$usernameCheck['valid']) {
            $error = $usernameCheck['message'];
        }
        // Validate email
        elseif (empty($email)) {
            $error = 'Email address is required.';
        } elseif (!validate_email($email)) {
            $error = 'Please enter a valid email address.';
        }
        // Validate password
        elseif (empty($password)) {
            $error = 'Password is required.';
        } else {
            $pwCheck = validate_password_strength($password);
            if (!$pwCheck['valid']) {
                $error = $pwCheck['message'];
            }
        }
        // Confirm passwords match
        if (empty($error) && $password !== $confirm_password) {
            $error = 'Passwords do not match.';
        }

        // --- Check for duplicates ---
        if (empty($error)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->execute([':username' => $username, ':email' => $email]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Check which field is duplicate
                $stmt2 = $pdo->prepare("SELECT username, email FROM users WHERE username = :username OR email = :email");
                $stmt2->execute([':username' => $username, ':email' => $email]);
                $dup = $stmt2->fetch();
                
                if ($dup['username'] === $username) {
                    $error = 'Username already exists. Please choose a different one.';
                } elseif ($dup['email'] === $email) {
                    $error = 'An account with this email already exists. Please login instead.';
                }
            }
        }

        // --- Insert new user ---
        if (empty($error)) {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'editor')"
                );
                $stmt->execute([
                    ':username' => $username,
                    ':email'    => $email,
                    ':password' => $hashed_password,
                ]);

                // Log registration activity
                $newUserId = $pdo->lastInsertId();
                log_activity((int)$newUserId, 'registration', "User '$username' registered a new account.");

                $success = 'Registration successful! You can now <a href="/auth/login.php" class="alert-link">login</a>.';
                $username = ''; // Clear the form
                $email = '';
            } catch (PDOException $e) {
                // Log error without exposing details
                log_error("Registration Error: " . $e->getMessage());
                $error = 'Registration failed. Please try again later.';
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
                                <i class="bi bi-person-plus-fill text-sunset fs-2"></i>
                            </div>
                            <h3 class="fw-bold mb-1">Create Account</h3>
                            <p class="text-muted">Sign up to start managing your blog posts</p>
                            <div class="page-header-divider mx-auto"></div>
                        </div>

                        <!-- Error Alert -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <div><?php echo htmlspecialchars($error); ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Success Alert -->
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                <div><?php echo $success; ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <form action="/auth/register.php" method="POST" 
                              id="registerForm" novalidate>
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
                                           id="username" name="username" placeholder="Choose a username (letters, numbers, _)" 
                                           value="<?php echo htmlspecialchars($username); ?>" 
                                           minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+" required>
                                </div>
                                <div class="invalid-feedback">Username must be 3-50 characters (letters, numbers, underscores only).</div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-1"></i>Email Address
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 border-end-0 rounded-start-4">
                                        <i class="bi bi-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-2 border-start-0 rounded-end-4 ps-0" 
                                           id="email" name="email" placeholder="Enter your email address" 
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-1"></i>Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 border-end-0 rounded-start-4">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-2 border-start-0 rounded-end-4 ps-0" 
                                           id="password" name="password" placeholder="Min 8 chars, uppercase, lowercase, number &amp; special char" 
                                           minlength="8" required>
                                </div>
                                <div id="passwordStrength" class="form-text mt-1">
                                    <small>Must have: 8+ chars, uppercase, lowercase, number, special character</small>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>Confirm Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 border-end-0 rounded-start-4">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-2 border-start-0 rounded-end-4 ps-0" 
                                           id="confirm_password" name="confirm_password" placeholder="Re-enter your password" 
                                           minlength="8" required>
                                </div>
                                <!-- Password Match Indicator -->
                                <div id="passwordMatchIndicator" class="form-text mt-1"></div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-gradient w-100 btn-lg rounded-4 fw-semibold mb-3">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>

                            <!-- Login Link -->
                            <p class="text-center text-muted mb-0">
                                Already have an account? 
                                <a href="/auth/login.php" class="text-sunset text-decoration-none fw-semibold hover-lift d-inline-block">
                                    Login here <i class="bi bi-arrow-right small"></i>
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
