<?php
// Login Modal Component
$login_error = '';
$login_success = '';

// Handle login form submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_modal'])) {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $login_error = 'Please fill in all fields.';
    } else {
        // Get user from database
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            
            $login_success = 'Login successful! Redirecting...';
            
            // Return JSON response for AJAX
            if (isset($_POST['ajax_request'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => $user['user_type'] === 'admin' ? SITE_URL . '/admin/' : SITE_URL . '/pages/dashboard.php'
                ]);
                exit;
            }
        } else {
            $login_error = 'Invalid email or password.';
        }
    }
}
?>

<!-- Login Modal -->
<div class="login-modal" id="loginModal">
    <div class="modal-overlay" id="loginModalOverlay"></div>
    <div class="modal-container">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-logo">
                    <div class="logo-circle">
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" alt="Logo" class="logo">
                    </div>
                </div>
                <h2 class="modal-title">Welcome Back</h2>
                <p class="modal-subtitle">Sign in to your UbugeniPalace account</p>
                <button class="modal-close" id="closeLoginModal" aria-label="Close modal">
                    <span class="close-icon">×</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <div id="loginAlert" class="alert" style="display: none;"></div>

                <form class="login-form" id="loginForm" method="POST">
                    <input type="hidden" name="login_modal" value="1">
                    <input type="hidden" name="ajax_request" value="1">
                    
                    <div class="form-group">
                        <label for="loginEmail" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-icon">📧</span>
                            <input type="email" id="loginEmail" name="email" class="form-input" 
                                   placeholder="Enter your email" required>
                        </div>
                        <div class="field-error" id="emailError" style="display: none;"></div>
                    </div>

                    <div class="form-group">
                        <label for="loginPassword" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="loginPassword" name="password" class="form-input" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" id="loginPasswordToggle">
                                <span class="eye-icon">👁️</span>
                            </button>
                        </div>
                        <div class="field-error" id="passwordError" style="display: none;"></div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" class="checkbox-input">
                            <span class="checkbox-text">Remember me</span>
                        </label>
                        <a href="<?php echo SITE_URL; ?>/pages/forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" id="loginSubmitBtn">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loading" style="display: none;">
                            <span class="loading-spinner"></span>
                            Signing in...
                        </span>
                    </button>
                </form>

                <div class="modal-divider">
                    <span class="divider-text">or</span>
                </div>

                <div class="social-login">
                    <button class="btn btn-outline btn-social" type="button">
                        <span class="social-icon">📘</span>
                        Continue with Facebook
                    </button>
                    <button class="btn btn-outline btn-social" type="button">
                        <span class="social-icon">📧</span>
                        Continue with Google
                    </button>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <p class="modal-footer-text">
                    Don't have an account? 
                    <a href="pages/register.php" class="modal-link">Create one here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginModal = document.getElementById('loginModal');
    const loginForm = document.getElementById('loginForm');
    const closeModal = document.getElementById('closeLoginModal');
    const modalOverlay = document.getElementById('loginModalOverlay');
    const passwordToggle = document.getElementById('loginPasswordToggle');
    const loginPassword = document.getElementById('loginPassword');
    const loginSubmitBtn = document.getElementById('loginSubmitBtn');
    const loginAlert = document.getElementById('loginAlert');

    // Open modal function
    window.openLoginModal = function() {
        loginModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.getElementById('loginEmail').focus();
    };

    // Close modal function
    function closeLoginModal() {
        loginModal.classList.remove('active');
        document.body.style.overflow = '';
        loginForm.reset();
        hideAlert();
        clearErrors();
    }

    // Close modal events
    closeModal.addEventListener('click', closeLoginModal);
    modalOverlay.addEventListener('click', closeLoginModal);

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && loginModal.classList.contains('active')) {
            closeLoginModal();
        }
    });

    // Password toggle
    passwordToggle.addEventListener('click', function() {
        const type = loginPassword.type === 'password' ? 'text' : 'password';
        loginPassword.type = type;
        const eyeIcon = this.querySelector('.eye-icon');
        eyeIcon.textContent = type === 'password' ? '👁️' : '🙈';
    });

    // Form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value.trim();
        const password = loginPassword.value.trim();
        
        // Clear previous errors
        clearErrors();
        
        // Validation
        let isValid = true;
        
        if (!email) {
            showError('loginEmail', 'Email is required');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError('loginEmail', 'Please enter a valid email');
            isValid = false;
        }
        
        if (!password) {
            showError('loginPassword', 'Password is required');
            isValid = false;
        }
        
        if (!isValid) return;
        
        // Show loading state
        const btnText = loginSubmitBtn.querySelector('.btn-text');
        const btnLoading = loginSubmitBtn.querySelector('.btn-loading');
        
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        loginSubmitBtn.disabled = true;
        
        // Submit form via AJAX
        const formData = new FormData(loginForm);
        
        fetch('<?php echo SITE_URL; ?>/api/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                showAlert('error', data.message || 'Login failed. Please try again.');
            }
        })
        .catch(error => {
            showAlert('error', 'An error occurred. Please try again.'+error);
        })
        .finally(() => {
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
            loginSubmitBtn.disabled = false;
        });
    });

    // Helper functions
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function showError(fieldId, message) {
        const errorDiv = document.getElementById(fieldId + 'Error');
        const input = document.getElementById(fieldId);
        
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        input.classList.add('error');
    }

    function clearErrors() {
        const errorDivs = document.querySelectorAll('.field-error');
        const inputs = document.querySelectorAll('.form-input');
        
        errorDivs.forEach(div => div.style.display = 'none');
        inputs.forEach(input => input.classList.remove('error'));
    }

    function showAlert(type, message) {
        loginAlert.className = `alert alert-${type}`;
        loginAlert.textContent = message;
        loginAlert.style.display = 'block';
        
        if (type === 'success') {
            setTimeout(hideAlert, 3000);
        }
    }

    function hideAlert() {
        loginAlert.style.display = 'none';
    }
});
</script> 