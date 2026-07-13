# Blog Management System (BlogCRUD)

A modern, production-ready blog management system built with **PHP**, **MySQL**, **Bootstrap 5**, and **JavaScript**. This application provides complete CRUD functionality with secure user authentication and enterprise-grade security enhancements.

---

## ✨ Features

### 🔐 Authentication
- User registration with password hashing (`password_hash()`)
- Secure login with password verification (`password_verify()`)
- Session-based authentication with 30-minute timeout
- CSRF-protected logout with session destruction
- Login rate limiting (max 5 attempts per 15 min)

### 📝 CRUD Operations
- **Create**: Add new blog posts with title and content
- **Read**: View all posts in a responsive card layout with pagination
- **Update**: Edit existing posts with pre-filled forms
- **Delete**: Remove posts with Bootstrap modal confirmation

### 👑 Role-Based Access Control
| Role | Permissions |
|------|-------------|
| **Admin** | Full access: Create, Read, Update, Delete all posts; Manage users; Change roles; View activity logs |
| **Editor** | Create posts, Read all posts, Update/Delete own posts only |

### 🛡️ Security (Task 4 — Security Enhanced)
- **Prepared Statements**: All SQL queries use PDO prepared statements — prevents SQL injection
- **CSRF Protection**: Per-form tokens validated on all mutations — prevents cross-site request forgery
- **XSS Prevention**: Output encoded with `htmlspecialchars()` + Content Security Policy headers
- **Session Security**: Regeneration after login, HttpOnly cookies, SameSite=Strict, 30-min timeout
- **Input Validation**: Server-side + client-side validation on all forms
- **Input Sanitization**: `trim()`, `strip_tags()`, `htmlspecialchars()`, `filter_var()` throughout
- **Password Hashing**: `password_hash()` with bcrypt algorithm
- **Security Headers**: X-Frame-Options, X-XSS-Protection, X-Content-Type-Options, Referrer-Policy, CSP
- **Activity Logging**: Full audit trail of all user actions (login, logout, CRUD, role changes)
- **Error Handling**: Custom error handler logs internally, never exposes details to users

### 🎨 User Interface
- Modern, premium design with Sunset Orange theme
- Fully responsive for mobile, tablet, and desktop
- Bootstrap 5 cards, tables, and components
- Bootstrap Icons throughout
- Smooth hover animations and transitions
- Interactive modals and alerts
- Real-time password strength meter
- Real-time password match indicator

### 🔍 Extra Features
- Search posts by title and content
- Pagination (5 posts per page)
- Flash success/error messages with auto-dismiss
- Auto-resize textareas
- Smooth scroll animations (IntersectionObserver)

---

## 📋 Requirements

- **XAMPP** (or any PHP/MySQL stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge, Safari)

---

## 🚀 Installation Steps

### Step 1: Download & Extract
1. Download the project files
2. Extract the `BlogCRUD` folder into your web server directory:
   - **XAMPP**: `C:\xampp\htdocs\`
   - **WAMP**: `C:\wamp64\www\`
   - **MAMP**: `/Applications/MAMP/htdocs/`
   - **LAMP**: `/var/www/html/`

### Step 2: Create Database
Open phpMyAdmin or MySQL CLI and run the provided SQL file:

**Using phpMyAdmin:**
1. Open `http://localhost/phpmyadmin`
2. Click on "Import" tab
3. Choose the `blog.sql` file
4. Click "Go"

**Using MySQL CLI:**
```bash
mysql -u root -p < blog.sql
```

### Step 3: Configure Database
Edit `config/db.php` if your database credentials differ from default:

```php
define('DB_HOST', '127.0.0.1');   // Database host
define('DB_PORT', '3306');         // Database port
define('DB_NAME', 'blog');         // Database name
define('DB_USER', 'root');         // Database username
define('DB_PASS', '');             // Database password
```

### Step 4: Run the Application
Open your browser and navigate to:

```
http://localhost/BlogCRUD/
```

**Using PHP built-in server (for development):**
```bash
cd TASK-2/BlogCRUD
php -S localhost:8000
# Access at: http://localhost:8000/
```

### Step 5: Login with Sample Credentials
```
Username: admin
Password: Admin@123
```
> The admin account has full access. New registrations get the **editor** role by default.

Or create a new account from the registration page.

---

## 📁 Folder Structure

```
BlogCRUD/
│
├── assets/
│   ├── css/
│   │   └── style.css              # Custom styles (Sunset Orange theme)
│   ├── js/
│   │   └── script.js              # CSRF-aware delete, validation, animations
│   └── images/                    # Image assets
│
├── config/
│   └── db.php                     # Database connection + loads all helpers
│
├── helpers/                       # <-- NEW (Task 4)
│   ├── validator.php              #   Form validation functions
│   ├── sanitizer.php              #   Input/output sanitization
│   ├── security.php               #   CSRF, sessions, headers, passwords
│   └── logger.php                 #   Activity logging & error handling
│
├── middleware/                    # <-- NEW (Task 4)
│   ├── auth.php                   #   Authentication checks + session timeout
│   ├── admin.php                  #   Admin authorization (403 page)
│   ├── editor.php                 #   Editor authorization + post ownership
│   └── csrf.php                   #   CSRF validation middleware
│
├── auth/
│   ├── register.php               # User registration (editor role by default)
│   ├── login.php                  # User login with rate limiting
│   └── logout.php                 # Secure logout with activity logging
│
├── posts/
│   ├── create.php                 # Create new post (activity logged)
│   ├── edit.php                   # Edit existing post (activity logged)
│   ├── delete.php                 # Delete post (activity logged)
│   └── view.php                   # View all posts with search & pagination
│
├── admin/
│   └── dashboard.php              # Admin panel + activity log viewer
│
├── includes/
│   ├── functions.php              # Re-exports all helpers (backward compat)
│   ├── header.php                 # HTML head with CSRF meta tag
│   ├── navbar.php                 # Role-aware navigation bar
│   └── footer.php                 # Footer with Bootstrap JS
│
├── logs/
│   └── error.log                  # Internal error log (not web-accessible)
│
├── dashboard.php                  # User dashboard (role-aware)
├── index.php                      # Landing page with hero section
├── SECURITY.md                    # <-- NEW — Full security documentation
├── README.md                      # This file
└── blog.sql                       # Database schema + sample data
```

---

## 🗄️ Database Schema

### Users Table
| Column     | Type              | Description                          |
|------------|-------------------|--------------------------------------|
| id         | INT (PK)          | Auto-increment user ID               |
| username   | VARCHAR(100)      | Unique username                      |
| email      | VARCHAR(255)      | Unique email address                 |
| password   | VARCHAR(255)      | Hashed password (bcrypt)             |
| role       | ENUM('admin','editor') | User role (DEFAULT 'editor')    |
| created_at | TIMESTAMP         | Account creation timestamp           |

### Posts Table
| Column    | Type         | Description              |
|-----------|--------------|--------------------------|
| id        | INT (PK)     | Auto-increment post ID   |
| title     | VARCHAR(255) | Post title               |
| content   | TEXT         | Post content             |
| user_id   | INT (FK)     | Reference to users.id    |
| created_at| TIMESTAMP    | Post creation timestamp  |

### Login Attempts Table
| Column       | Type         | Description                           |
|--------------|--------------|---------------------------------------|
| id           | INT (PK)     | Auto-increment ID                     |
| ip_address   | VARCHAR(45)  | IP address of the attempt             |
| username     | VARCHAR(100) | Username attempted                    |
| attempted_at | TIMESTAMP    | When the attempt occurred             |

### Activity Logs Table ← NEW (Task 4)
| Column     | Type         | Description                          |
|------------|--------------|--------------------------------------|
| id         | INT (PK)     | Auto-increment log ID                |
| user_id    | INT (FK)     | Reference to users.id                |
| action     | VARCHAR(100) | Action type (login, create_post, etc.)|
| description| TEXT         | Human-readable description           |
| created_at | TIMESTAMP    | When the action occurred             |

---

## 🔒 Task 4 — Security Enhancements (Before vs After)

### 1. 📁 Folder Restructuring

| Before | After |
|--------|-------|
| `includes/functions.php` (monolithic) | `helpers/validator.php`, `sanitizer.php`, `security.php`, `logger.php` |
| No middleware files | `middleware/auth.php`, `admin.php`, `editor.php`, `csrf.php` |
| No security docs | `SECURITY.md` — comprehensive guide |

### 2. 👑 Role-Based Access Control

| Aspect | Before | After |
|--------|--------|-------|
| Available roles | `admin`, `user` | `admin`, `editor` |
| Default role for new users | `user` | `editor` |
| Admin panel | Basic user/post management | + Activity log viewer, role change logging |

### 3. 📝 Activity Logging (New Feature)

| Before | After |
|--------|-------|
| ❌ No audit trail | ✅ `activity_logs` table tracks: Login, Logout, Registration, Create/Edit/Delete Post, Role Changes, User Deletion |
| ❌ No log viewer | ✅ Admin Dashboard has color-coded activity log table |

### 4. 🔒 Session Security Upgrade

| Before | After |
|--------|-------|
| Session regeneration on login | ✅ Same + `$_SESSION['_last_activity']` tracking |
| No timeout enforcement | ✅ 30-minute session inactivity timeout + redirect to login |

### 5. 🛡️ Additional Security Header

| Before | After |
|--------|-------|
| X-Frame-Options, X-Content-Type-Options, CSP, Referrer-Policy | ✅ **Added**: `X-XSS-Protection: 1; mode=block` |

### 6. 🐞 Bugs Fixed

| Issue | Fix |
|-------|-----|
| Admin password hash didn't match stated password | ✅ Regenerated hash for `Admin@123` |
| Hardcoded `/BlogCRUD/` paths caused 404s | ✅ All 81 paths replaced with root-relative URLs |
| Database port mismatch (3307 vs actual 3306) | ✅ Port corrected in `config/db.php` |

---

## 🎯 Usage Guide

### Registration
1. Click "Register" in the navigation bar
2. Fill in username, email, and password (min 8 chars, with uppercase, lowercase, number, special char)
3. Confirm password and submit
4. You're registered as an **Editor** — can create posts and manage your own content
5. Login with your new credentials

### Creating a Post
1. Login to your account
2. Click "Create New Post" on the dashboard
3. Enter a title (min 5 chars) and content (min 20 chars)
4. Click "Publish Post" — activity is logged

### Managing Posts
- **View**: Click "Posts" in navbar or "View All" on dashboard
- **Edit**: Click the pencil icon on any post you own
- **Delete**: Click the trash icon and confirm via Bootstrap modal (CSRF-protected)

### Searching Posts
1. Go to "All Blog Posts" page
2. Type in the search box
3. Press Enter or click the search icon
4. Press Escape or click "X" to clear search

### Admin: Managing Users
1. Login as admin
2. Click "Admin Panel" on the dashboard
3. **Change roles**: Click toggle button to promote/demote users
4. **Delete users**: Click trash icon (you cannot delete yourself)
5. **View activity**: Scroll to "Recent Activity Logs" section for full audit trail

---

## 🎨 Theme Colors
- **Sunset Orange**: `#f97316` / `#ea580c` / `#c2410c` (Primary)
- **Amber Accent**: `#f59e0b` / `#d97706`
- **White**: `#ffffff` (Background)
- **Light Gray**: `#f3f4f6` (Section backgrounds)

---

## 🛠️ Technologies Used
| Technology     | Purpose                         |
|----------------|---------------------------------|
| HTML5          | Structure and semantics         |
| CSS3           | Styling and animations          |
| Bootstrap 5    | Responsive layout & UI          |
| Bootstrap Icons| Icon library                    |
| JavaScript     | Client-side validation & UI     |
| PHP 8.x        | Server-side logic               |
| MySQL          | Database storage                |
| PDO            | Database abstraction layer      |

---

## 🔒 Complete Security Checklist

| # | Feature | Status |
|---|---------|--------|
| 1 | Prepared Statements (SQL Injection prevention) | ✅ |
| 2 | Server-side Validation | ✅ |
| 3 | Client-side Validation | ✅ |
| 4 | Input Sanitization (`trim`, `htmlspecialchars`, `filter_input`) | ✅ |
| 5 | CSRF Protection (tokens on all forms) | ✅ |
| 6 | Session Security (regeneration, timeout, HttpOnly, SameSite) | ✅ |
| 7 | Role-Based Access Control (admin/editor) | ✅ |
| 8 | Authorization middleware (auth, admin, editor) | ✅ |
| 9 | Error Handling (internal logging, user-friendly messages) | ✅ |
| 10 | Secure Password Storage (bcrypt via `password_hash`) | ✅ |
| 11 | Security Headers (X-Frame-Options, CSP, HSTS, etc.) | ✅ |
| 12 | Delete Confirmation (Bootstrap modal) | ✅ |
| 13 | Activity Logging (full audit trail) | ✅ |
| 14 | Modular code structure (helpers, middleware) | ✅ |
| 15 | SECURITY.md documentation | ✅ |
| 16 | Login Rate Limiting (5 attempts / 15 min) | ✅ |

---

## 📱 Responsive Breakpoints
- **Mobile**: < 576px (Single column, compact cards)
- **Tablet**: 768px - 991px (Two columns, card layout)
- **Desktop**: > 992px (Full layout, table view on dashboard)

---

## 📸 Screenshots
*(Screenshots can be added to the `assets/images/` folder)*

- `homepage.png` — Landing page hero section
- `dashboard.png` — User dashboard
- `posts.png` — Posts listing with search
- `login.png` — Login form
- `admin.png` — Admin dashboard with activity logs
- `mobile.png` — Mobile responsive view

---

Built with ❤️ using **PHP**, **MySQL** & **Bootstrap 5**

*Happy Blogging! 📝*
