# Security Documentation

## Blog Manageme  nt System - Security Enhancements

This document describes the security measures implemented in the BlogCRUD application to protect against common web vulnerabilities.

---

## Table of Contents

1. [Prepared Statements (SQL Injection Prevention)](#1-prepared-statements)
2. [CSRF Protection](#2-csrf-protection)
3. [XSS Prevention](#3-xss-prevention)
4. [Session Security](#4-session-security)
5. [Role-Based Access Control](#5-role-based-access-control)
6. [Password Hashing](#6-password-hashing)
7. [Input Validation](#7-input-validation)
8. [Input Sanitization](#8-input-sanitization)
9. [Security Headers](#9-security-headers)
10. [Rate Limiting](#10-rate-limiting)
11. [Activity Logging](#11-activity-logging)
12. [Error Handling](#12-error-handling)

---

## 1. Prepared Statements

### What They Do

Prepared statements separate SQL logic from user data. The database server compiles the SQL query template once, then executes it with bound parameters. This prevents malicious input from being interpreted as SQL commands.

### Implementation

All database queries use PDO (PHP Data Objects) with prepared statements:

```php
// Safe — uses prepared statement with bound parameters
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);

// Unsafe — vulnerable to SQL injection (never used in this application)
// $result = $pdo->query("SELECT * FROM users WHERE username = '$username'");
```

### Vulnerabilities Prevented

- **SQL Injection**: Attacker cannot inject malicious SQL via input fields
- **Second-Order Injection**: Data retrieved from DB is safely handled by subsequent queries

### Files Secured

- `auth/login.php` — User authentication
- `auth/register.php` — User registration
- `posts/create.php` — Post creation
- `posts/edit.php` — Post editing
- `posts/delete.php` — Post deletion
- `posts/view.php` — Post viewing with search and pagination
- `dashboard.php` — Dashboard statistics
- `admin/dashboard.php` — User and post management
- `helpers/logger.php` — Activity logging

---

## 2. CSRF Protection

### What It Does

Cross-Site Request Forgery (CSRF) protection ensures that form submissions originate from the application itself, not from a malicious third-party site.

### Implementation

1. **Token Generation**: A unique, cryptographically secure random token is generated and stored in the session:

```php
function get_csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}
```

2. **Token Injection**: Every form includes a hidden field with the CSRF token:

```php
function csrf_field(): void {
    echo '<input type="hidden" name="_csrf_token" value="' . get_csrf_token() . '">';
}
```

3. **Token Verification**: On form submission, the submitted token is compared against the session token using timing-safe comparison:

```php
function verify_csrf_token(): bool {
    $submitted = $_POST['_csrf_token'] ?? '';
    if (empty($submitted) || empty($_SESSION['_csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['_csrf_token'], $submitted);
}
```

4. **One-Time Use**: Tokens are regenerated after each successful verification.

### Vulnerabilities Prevented

- **Cross-Site Request Forgery (CSRF)**: Attackers cannot trick authenticated users into performing unintended actions
- **Login CSRF**: Registration and login forms are also protected

---

## 3. XSS Prevention

### What It Does

Cross-Site Scripting (XSS) prevention ensures that user-supplied data cannot be interpreted as executable code (JavaScript, HTML) by the browser.

### Implementation

1. **Output Escaping**: All user data is escaped when output in HTML context:

```php
echo htmlspecialchars($user['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
```

2. **Content Security Policy**: A strict CSP header restricts which resources can be loaded:

```
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; object-src 'none'; frame-src 'none'
```

3. **Input Sanitization**: User input is sanitized before processing:

```php
function sanitize_input(string $input): string {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return $input;
}
```

4. **Cookie Security**: Session cookies are set with `httponly` flag, preventing JavaScript access.

### Vulnerabilities Prevented

- **Stored XSS**: Malicious scripts cannot be stored in the database
- **Reflected XSS**: Malicious scripts in URLs cannot execute
- **DOM-based XSS**: JavaScript input is sanitized on the client side

---

## 4. Session Security

### What It Does

Secure session management prevents session hijacking, fixation, and other session-based attacks.

### Implementation

1. **Session Regeneration**: Session ID is regenerated after login to prevent session fixation:

```php
session_regenerate_id(true);
```

2. **Secure Cookie Configuration**:

```php
session_set_cookie_params([
    'lifetime' => 0,     // Session cookie (expires on browser close)
    'httponly' => true,  // Not accessible via JavaScript
    'samesite' => 'Strict', // Prevent CSRF via cross-site requests
    'secure'   => $isHttps, // Only send over HTTPS
]);
```

3. **Session Timeout**: Sessions expire after 30 minutes of inactivity:

```php
function is_session_expired(int $timeoutMinutes = 30): bool {
    $lastActivity = $_SESSION['_last_activity'] ?? 0;
    return (time() - $lastActivity) > ($timeoutMinutes * 60);
}
```

4. **Complete Logout**: Logout destroys the session and clears the session cookie:

```php
$_SESSION = [];
session_destroy();
setcookie(session_name(), '', time() - 42000, ...);
```

### Vulnerabilities Prevented

- **Session Fixation**: Attacker cannot force a known session ID
- **Session Hijacking**: HttpOnly and Secure flags protect the session cookie
- **Session Replay**: Timeout limits the window for session reuse

---

## 5. Role-Based Access Control

### What It Does

RBAC ensures users can only perform actions allowed by their assigned role.

### Role Definitions

| Role | Permissions |
|------|-------------|
| **Admin** | Full access: Create, Read, Update, Delete all posts; Manage users; Change roles; Access admin panel |
| **Editor** | Limited access: Create posts, Read all posts, Update own posts |

### Implementation

1. **Role Check Middleware**: Protected pages verify user role:

```php
function require_admin(): void {
    require_auth();
    if (!is_admin()) {
        http_response_code(403);
        // Show Access Denied page
        exit();
    }
}
```

2. **Post Ownership Verification**: Editors can only modify their own posts:

```php
function can_manage_post(int $postOwnerId): bool {
    if (is_admin()) return true;
    return (int)$_SESSION['user_id'] === $postOwnerId;
}
```

3. **Database Schema**: Role is stored as an ENUM:

```sql
role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor'
```

### Vulnerabilities Prevented

- **Privilege Escalation**: Users cannot access admin-only pages
- **Unauthorized Data Access**: Users cannot modify other users' posts

---

## 6. Password Hashing

### What It Does

Passwords are never stored in plain text. They are hashed using bcrypt, a computationally expensive algorithm designed for password storage.

### Implementation

1. **Hashing on Registration**:

```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

2. **Verification on Login**:

```php
if ($user && password_verify($password, $user['password'])) {
    // Authentication successful
}
```

### Why Bcrypt?

- **Slow Hash**: Bcrypt is intentionally slow, making brute-force attacks impractical
- **Automatic Salting**: Each password gets a unique random salt
- **Future-Proof**: `PASSWORD_DEFAULT` allows the algorithm to be updated automatically

### Vulnerabilities Prevented

- **Credential Theft**: Stolen database does not reveal passwords
- **Rainbow Table Attacks**: Unique salts prevent precomputed hash lookups
- **Brute-Force Attacks**: Computational cost makes massive attempts infeasible

---

## 7. Input Validation

### What It Does

All user input is validated on both the client side (JavaScript) and server side (PHP) before processing.

### Server-Side Validation Rules

#### Registration

| Field | Rules |
|-------|-------|
| Username | Required, 3-50 characters, alphanumeric + underscores |
| Email | Required, valid email format |
| Password | Required, min 8 characters, must contain uppercase, lowercase, number, special character |
| Confirm Password | Must match password |

#### Login

| Field | Rules |
|-------|-------|
| Username | Required (empty check) |
| Password | Required (empty check) |

#### Create/Edit Post

| Field | Rules |
|-------|-------|
| Title | Required, 5-255 characters |
| Content | Required, min 20 characters |

### Client-Side Validation

- Real-time password strength meter
- Real-time password match indicator
- Bootstrap validation styles (`was-validated` class)
- Character length enforcement via `minlength` / `maxlength` attributes
- Form submission interception with detailed error messages

### Vulnerabilities Prevented

- **Invalid Data**: Malformed data is rejected before processing
- **Resource Exhaustion**: Maximum lengths prevent excessive storage consumption

---

## 8. Input Sanitization

### What It Does

Input is sanitized at multiple layers to ensure it's safe for processing and storage.

### Sanitization Functions

```php
// Strip HTML tags
strip_tags($input);

// Encode special HTML characters
htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

// Remove whitespace
trim($input);

// Validate integer IDs
filter_var($id, FILTER_VALIDATE_INT);

// Escape LIKE wildcards for search
str_replace(['%', '_'], ['\\%', '\\_'], $keyword);

// Limit search query length
substr($keyword, 0, 100);
```

### Vulnerabilities Prevented

- **XSS Attacks**: HTML/JavaScript code is neutralized
- **Like Injection**: Wildcard characters are escaped in search queries

---

## 9. Security Headers

### What They Do

HTTP security headers instruct the browser to enforce security policies.

### Headers Implemented

| Header | Value | Purpose |
|--------|-------|---------|
| `X-Frame-Options` | `DENY` | Prevents clickjacking by blocking iframe embedding |
| `X-XSS-Protection` | `1; mode=block` | Enables browser XSS filter |
| `X-Content-Type-Options` | `nosniff` | Prevents MIME-type sniffing |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Controls referrer information |
| `Content-Security-Policy` | See config | Restricts resource loading sources |

---

## 10. Rate Limiting

### What It Does

Limits the number of login attempts to prevent brute-force password guessing.

### Implementation

- Tracks failed login attempts by IP address and username combination
- Allows a maximum of 5 failed attempts within a 15-minute window
- Old attempts are automatically cleaned up
- Successful login clears the attempt history

```php
$maxAttempts = 5;
$lockoutMinutes = 15;
```

### Vulnerabilities Prevented

- **Brute-Force Attacks**: Password guessing is rate-limited
- **Credential Stuffing**: Automated login attempts are blocked

---

## 11. Activity Logging

### What It Does

All significant user actions are logged to an audit trail for security monitoring.

### Events Logged

| Action | Description |
|--------|-------------|
| Registration | New user account created |
| Login | User authenticated |
| Logout | User session ended |
| Create Post | New blog post published |
| Edit Post | Existing post modified |
| Delete Post | Post removed |
| Change Role | Admin changed a user's role |
| Delete User | Admin removed a user |

### Implementation

```php
function log_activity(int $userId, string $action, string $description, ?PDO $pdo = null): void {
    // Insert into activity_logs table
}
```

### Database Table

```sql
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 12. Error Handling

### What It Does

All errors are logged internally without exposing sensitive information to users.

### Implementation

1. **Custom Error Handler**: PHP errors are caught and logged:

```php
function set_secure_error_handler(): void {
    set_error_handler(function ($severity, $message, $file, $line) {
        log_error("$message in $file:$line");
        return false; // Let PHP handle internally
    });
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
```

2. **User-Friendly Messages**: Database errors show a generic message:

```php
try {
    // Database operation
} catch (PDOException $e) {
    log_error("Database Error: " . $e->getMessage());
    $_SESSION['error'] = 'An unexpected error occurred. Please try again later.';
}
```

### Vulnerabilities Prevented

- **Information Disclosure**: Database structure, credentials, and stack traces are never exposed
- **Debug Information**: Internal paths and configurations remain hidden

---

## Folder Structure

```
BlogCRUD/
├── config/
│   └── db.php                  # Database connection, security headers, helper loader
├── helpers/
│   ├── validator.php           # Form validation functions
│   ├── sanitizer.php           # Input/output sanitization
│   ├── security.php            # CSRF, session, password utilities
│   └── logger.php              # Error/activity logging
├── middleware/
│   ├── auth.php                # Authentication checks
│   ├── admin.php               # Admin authorization
│   ├── editor.php              # Editor authorization
│   └── csrf.php                # CSRF validation
├── includes/
│   ├── functions.php           # Legacy function loader (backward compat)
│   ├── header.php              # HTML head with CSRF meta tag
│   ├── navbar.php              # Navigation bar with role-based links
│   └── footer.php              # Footer with JS
├── auth/
│   ├── register.php            # User registration
│   ├── login.php               # User login with rate limiting
│   └── logout.php              # Secure logout
├── posts/
│   ├── create.php              # Create post
│   ├── edit.php                # Edit post
│   ├── delete.php              # Delete post
│   └── view.php                # View posts with search & pagination
├── admin/
│   └── dashboard.php           # Admin panel with activity logs
├── assets/
│   ├── css/style.css           # Custom styles
│   └── js/script.js            # Client-side validation & interactivity
├── logs/
│   └── error.log               # Internal error log (not web-accessible)
├── SECURITY.md                 # This document
└── blog.sql                    # Database schema
```

---

## Framework & Tools Used

- **PHP 8.x**: Server-side scripting
- **PDO**: Database abstraction with prepared statements
- **MySQL**: Relational database
- **Bootstrap 5**: Responsive UI framework
- **Bootstrap Icons**: Icon library
- **JavaScript (Vanilla)**: Client-side validation and interactivity

---

*Documentation updated: July 2026*

*For any security concerns, please contact the system administrator.*
