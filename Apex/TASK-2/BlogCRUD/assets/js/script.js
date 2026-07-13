/**
 * Blog Management System - Security Enhanced JavaScript
 * 
 * Features:
 * - Client-side form validation with real-time feedback
 * - Email format validation
 * - Password strength indicator
 * - Password match validation
 * - Bootstrap 5 form validation enhancement
 * - Auto-dismiss alerts with slide animation
 * - Navbar scroll effect (glass morphism on scroll)
 * - Smooth scroll behavior
 * - Confirmation dialogs
 * - Textarea auto-resize
 * - Intersection Observer for scroll animations
 * - CSRF-aware delete confirmation
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // 1. Navbar Glass Effect on Scroll
    // ============================================================
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ============================================================
    // 2. Bootstrap 5 Form Validation Enhancement
    // ============================================================
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // ============================================================
    // 3. Enhanced Password Validation (Strength & Match)
    // ============================================================
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const password = document.getElementById('password');
            const confirm = document.getElementById('confirm_password');

            // Check password match
            if (password && confirm && password.value !== confirm.value) {
                e.preventDefault();
                confirm.setCustomValidity('Passwords do not match.');
                confirm.reportValidity();
                return false;
            } else if (confirm) {
                confirm.setCustomValidity('');
            }

            // Check password strength via client-side regex
            if (password && password.value.length < 8) {
                e.preventDefault();
                password.setCustomValidity('Password must be at least 8 characters.');
                password.reportValidity();
                return false;
            }
            
            if (password && !/(?=.*[a-z])/.test(password.value)) {
                e.preventDefault();
                password.setCustomValidity('Password must contain a lowercase letter.');
                password.reportValidity();
                return false;
            }
            
            if (password && !/(?=.*[A-Z])/.test(password.value)) {
                e.preventDefault();
                password.setCustomValidity('Password must contain an uppercase letter.');
                password.reportValidity();
                return false;
            }
            
            if (password && !/(?=.*\d)/.test(password.value)) {
                e.preventDefault();
                password.setCustomValidity('Password must contain a number.');
                password.reportValidity();
                return false;
            }
            
            if (password && !/(?=.*[^A-Za-z0-9])/.test(password.value)) {
                e.preventDefault();
                password.setCustomValidity('Password must contain a special character.');
                password.reportValidity();
                return false;
            }
        });
    }

    // Real-time password strength indicator
    const passwordField = document.getElementById('password');
    const passwordStrength = document.getElementById('passwordStrength');

    function updatePasswordStrength() {
        if (!passwordStrength || !passwordField) return;
        
        const val = passwordField.value;
        let strength = 0;
        let message = '';
        
        if (val.length >= 8) strength++;
        if (/(?=.*[a-z])/.test(val)) strength++;
        if (/(?=.*[A-Z])/.test(val)) strength++;
        if (/(?=.*\d)/.test(val)) strength++;
        if (/(?=.*[^A-Za-z0-9])/.test(val)) strength++;
        
        if (val.length === 0) {
            message = '<small>Must have: 8+ chars, uppercase, lowercase, number, special character</small>';
            passwordStrength.innerHTML = message;
            passwordStrength.className = 'form-text mt-1';
            return;
        }
        
        if (strength <= 2) {
            message = '<span class="text-danger"><i class="bi bi-shield-exclamation me-1"></i>Weak password</span>';
            passwordStrength.className = 'form-text mt-1';
        } else if (strength <= 3) {
            message = '<span class="text-warning"><i class="bi bi-shield me-1"></i>Medium password</span>';
            passwordStrength.className = 'form-text mt-1';
        } else if (strength <= 4) {
            message = '<span class="text-info"><i class="bi bi-shield-check me-1"></i>Strong password</span>';
            passwordStrength.className = 'form-text mt-1';
        } else {
            message = '<span class="text-success"><i class="bi bi-shield-fill-check me-1"></i>Very strong password</span>';
            passwordStrength.className = 'form-text mt-1';
        }
        
        passwordStrength.innerHTML = message;
        passwordStrength.style.animation = 'fadeIn 0.3s ease-out';
    }

    if (passwordField && passwordStrength) {
        passwordField.addEventListener('input', updatePasswordStrength);
        updatePasswordStrength(); // Initial evaluation
    }

    // Real-time password match indicator
    const confirmField = document.getElementById('confirm_password');
    const passwordIndicator = document.getElementById('passwordMatchIndicator');

    function updatePasswordMatchIndicator() {
        if (!passwordIndicator || !passwordField || !confirmField) return;

        if (!confirmField.value) {
            passwordIndicator.innerHTML = '';
            confirmField.classList.remove('is-valid', 'is-invalid');
            return;
        }

        if (passwordField.value === confirmField.value) {
            confirmField.setCustomValidity('');
            confirmField.classList.remove('is-invalid');
            confirmField.classList.add('is-valid');
            passwordIndicator.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Passwords match</span>';
            passwordIndicator.style.animation = 'fadeIn 0.3s ease-out';
        } else {
            confirmField.setCustomValidity('Passwords do not match.');
            confirmField.classList.remove('is-valid');
            confirmField.classList.add('is-invalid');
            passwordIndicator.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i>Passwords do not match</span>';
            passwordIndicator.style.animation = 'fadeIn 0.3s ease-out';
        }
    }

    if (passwordField && confirmField) {
        confirmField.addEventListener('input', updatePasswordMatchIndicator);
        passwordField.addEventListener('input', updatePasswordMatchIndicator);
    }

    // ============================================================
    // 4. Create/Edit Post Validation Enhancement
    // ============================================================
    const postForms = ['createPostForm', 'editPostForm'];
    postForms.forEach(function (formId) {
        var form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function (e) {
                var title = document.getElementById('title');
                var content = document.getElementById('content');
                
                if (title && title.value.trim().length < 5) {
                    e.preventDefault();
                    title.setCustomValidity('Title must be at least 5 characters.');
                    title.reportValidity();
                    return false;
                } else if (title) {
                    title.setCustomValidity('');
                }
                
                if (content && content.value.trim().length < 20) {
                    e.preventDefault();
                    content.setCustomValidity('Content must be at least 20 characters.');
                    content.reportValidity();
                    return false;
                } else if (content) {
                    content.setCustomValidity('');
                }
            });
        }
    });

    // ============================================================
    // 5. Auto-Dismiss Alerts with Animation
    // ============================================================
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            if (alert && alert.parentNode) {
                alert.style.transition = 'all 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function () {
                    const bsAlert = bootstrap.Alert.getInstance(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    } else {
                        alert.remove();
                    }
                }, 500);
            }
        }, 5000);
    });

    // ============================================================
    // 6. Smooth Scroll for Anchor Links
    // ============================================================
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }
            }
        });
    });

    // ============================================================
    // 7. Search Input - Escape Key & Clear Button
    // ============================================================
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.closest('form').submit();
            }
        });

        // Sanitize search input on submit (remove HTML tags)
        searchInput.closest('form')?.addEventListener('submit', function (e) {
            searchInput.value = searchInput.value.replace(/<[^>]*>/g, '').trim();
        });
    }

    // ============================================================
    // 8. Tooltip Initialization
    // ============================================================
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) {
        return new bootstrap.Tooltip(el);
    });

    // ============================================================
    // 9. Textarea Auto-Resize
    // ============================================================
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(function (textarea) {
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 800) + 'px';
        });
        
        // Initial resize if it has content
        if (textarea.value) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 800) + 'px';
        }
    });

    // ============================================================
    // 10. Scroll-triggered Animations (via IntersectionObserver)
    // ============================================================
    if ('IntersectionObserver' in window) {
        const animElements = document.querySelectorAll('.animate-fade-in-up, .stagger-1, .stagger-2, .stagger-3, .stagger-4, .stagger-5, .stagger-6');
        
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.animationPlayState = 'running';
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        animElements.forEach(function (el) {
            el.style.opacity = '1';
            el.style.animationPlayState = 'running';
            observer.observe(el);
        });
    }

    // ============================================================
    // 11. Cross-site Request Forgery (CSRF) Logout Protection
    // ============================================================
    // The logout link now includes a CSRF token in the URL.
    // The confirm dialog adds an extra layer of protection.

    console.log('BlogCRUD Security Enhanced UI initialized successfully! 🔒');
});

// ============================================================
// Centralized Delete Confirmation (CSRF-Aware)
// ============================================================
function confirmDelete(postId, postTitle) {
    var messageEl = document.getElementById('deleteMessage');
    var confirmBtn = document.getElementById('confirmDeleteBtn');
    var modalEl = document.getElementById('deleteModal');

    if (messageEl && confirmBtn && modalEl) {
        messageEl.textContent = 'Are you sure you want to delete "' + postTitle + '"? This action cannot be undone.';
        
        // Include CSRF token from the meta tag
        var csrfToken = getCsrfToken();
        var basePath = '';
        
        var deleteUrl = basePath + '/posts/delete.php?id=' + postId;
        if (csrfToken) {
            deleteUrl += '&_csrf_token=' + encodeURIComponent(csrfToken);
        }
        
        confirmBtn.href = deleteUrl;

        var deleteModal = new bootstrap.Modal(modalEl);
        deleteModal.show();
    }
}

// ============================================================
// Get CSRF Token from meta tag
// ============================================================
function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// ============================================================
// Logged Out Alert on Login Page
// ============================================================
function checkLoggedOut() {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('logged_out') === '1') {
        var container = document.querySelector('.login-alert-container');
        if (container) {
            var alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm rounded-4';
            alert.setAttribute('role', 'alert');
            alert.innerHTML = '<i class="bi bi-check-circle-fill me-2 fs-5"></i> You have been logged out successfully. <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            container.appendChild(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function () {
                alert.style.transition = 'all 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function () {
                    if (alert.parentNode) alert.remove();
                }, 500);
            }, 5000);
        }
    }
}

checkLoggedOut();
