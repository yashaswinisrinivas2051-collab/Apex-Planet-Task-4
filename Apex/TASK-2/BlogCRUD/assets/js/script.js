/**
 * Blog Management System - Enhanced JavaScript (Task 5 Final)
 * 
 * Features:
 * - Client-side form validation with real-time feedback
 * - Password strength indicator & match validation
 * - Loading spinner indicators on form submissions
 * - Bootstrap 5 toast notification system
 * - Auto-dismiss alerts with slide animation
 * - Character counters for title & content fields
 * - Navbar scroll effect (glass morphism on scroll)
 * - Smooth scroll behavior
 * - CSRF-aware delete confirmation modal
 * - Textarea auto-resize
 * - Intersection Observer for scroll animations
 * - Logged-out alert display
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
    // 3. Loading Spinner on Form Submissions
    // ============================================================
    // Listen on form submit instead of button click to ensure
    // client-side validation (Bootstrap's checkValidity) runs first
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            // If form has Bootstrap validation and it's not valid, skip
            if (form.classList.contains('was-validated') && !form.checkValidity()) {
                return;
            }
            
            // Find the submit button
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            
            // Save original content
            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }
            
            // Add loading state
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...';
            button.disabled = true;

            // Safety net: re-enable after 30 seconds
            setTimeout(function () {
                if (button.disabled) {
                    button.innerHTML = button.dataset.originalHtml || 'Submit';
                    button.disabled = false;
                }
            }, 30000);
        });
    });

    // ============================================================
    // 4. Enhanced Password Validation (Strength & Match)
    // ============================================================
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const password = document.getElementById('password');
            const confirm = document.getElementById('confirm_password');
            let hasError = false;

            // Check password match
            if (password && confirm && password.value !== confirm.value) {
                e.preventDefault();
                confirm.setCustomValidity('Passwords do not match.');
                confirm.reportValidity();
                hasError = true;
                return false;
            } else if (confirm) {
                confirm.setCustomValidity('');
            }

            // Check password strength via client-side regex
            if (password && password.value.length < 8) {
                if (!hasError) { e.preventDefault(); }
                password.setCustomValidity('Password must be at least 8 characters.');
                password.reportValidity();
                return false;
            }
            
            if (password && !/(?=.*[a-z])/.test(password.value)) {
                if (!hasError) { e.preventDefault(); }
                password.setCustomValidity('Password must contain a lowercase letter.');
                password.reportValidity();
                return false;
            }
            
            if (password && !/(?=.*[A-Z])/.test(password.value)) {
                if (!hasError) { e.preventDefault(); }
                password.setCustomValidity('Password must contain an uppercase letter.');
                password.reportValidity();
                return false;
            }
            
            if (password && !/(?=.*\d)/.test(password.value)) {
                if (!hasError) { e.preventDefault(); }
                password.setCustomValidity('Password must contain a number.');
                password.reportValidity();
                return false;
            }
            
            if (password && !/(?=.*[^A-Za-z0-9])/.test(password.value)) {
                if (!hasError) { e.preventDefault(); }
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
    // 5. Create/Edit Post Validation Enhancement
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
    // 6. Character Counters for Title & Content
    // ============================================================
    function setupCharacterCounter(inputId, counterId, maxChars) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        if (!input || !counter) return;

        function updateCounter() {
            const len = input.value.length;
            counter.textContent = len + ' / ' + maxChars;
            
            if (len > maxChars * 0.9) {
                counter.style.color = '#ef4444';
                counter.style.fontWeight = '600';
            } else if (len > maxChars * 0.75) {
                counter.style.color = '#f59e0b';
                counter.style.fontWeight = '500';
            } else {
                counter.style.color = '#9ca3af';
                counter.style.fontWeight = '400';
            }
        }

        input.addEventListener('input', updateCounter);
        updateCounter(); // Initial update
    }

    setupCharacterCounter('title', 'titleCounter', 255);
    setupCharacterCounter('content', 'contentCounter', 100000);

    // ============================================================
    // 7. Auto-Dismiss Alerts with Animation
    // ============================================================
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            if (alert && alert.parentNode) {
                alert.style.transition = 'all 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function () {
                    // Use Bootstrap alert if available
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
    // 8. Smooth Scroll for Anchor Links
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
    // 9. Search Input - Escape Key & Clear Button
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
    // 10. Tooltip Initialization
    // ============================================================
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) {
        return new bootstrap.Tooltip(el);
    });

    // ============================================================
    // 11. Textarea Auto-Resize
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
    // 12. Scroll-triggered Animations (via IntersectionObserver)
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
    // 13. Toast Notification System
    // ============================================================
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        // Create toast container if it doesn't exist
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    /**
     * Show a Bootstrap toast notification
     * @param {string} message - The message to display
     * @param {string} type - 'success', 'error', 'warning', 'info'
     */
    window.showToast = function (message, type) {
        type = type || 'info';
        const container = document.getElementById('toastContainer');
        
        const iconMap = {
            'success': 'bi-check-circle-fill text-success',
            'error': 'bi-exclamation-triangle-fill text-danger',
            'warning': 'bi-exclamation-circle-fill text-warning',
            'info': 'bi-info-circle-fill text-primary'
        };
        
        const bgMap = {
            'success': 'border-success',
            'error': 'border-danger',
            'warning': 'border-warning',
            'info': 'border-primary'
        };
        
        const icon = iconMap[type] || iconMap['info'];
        const border = bgMap[type] || bgMap['info'];
        
        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center border-0 ' + border;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.style.borderLeft = '4px solid';
        toastEl.innerHTML = '<div class="d-flex">' +
            '<div class="toast-body d-flex align-items-center gap-2">' +
                '<i class="bi ' + icon + ' fs-5"></i>' +
                '<span>' + message + '</span>' +
            '</div>' +
            '<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
        '</div>';
        
        container.appendChild(toastEl);
        
        const toast = new bootstrap.Toast(toastEl, {
            animation: true,
            autohide: true,
            delay: 4000
        });
        
        toast.show();
        
        // Remove from DOM after hidden
        toastEl.addEventListener('hidden.bs.toast', function () {
            toastEl.remove();
        });
    };

    console.log('BlogCRUD Final UI initialized successfully! 🚀');
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
