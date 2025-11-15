<?php
// Simple Login Modal Component - No AJAX
$login_error = '';
$login_success = '';

// Handle login form submission via traditional POST
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
            
            // Redirect based on user type
            $redirect_url = $user['user_type'] === 'admin' ? SITE_URL . '/admin/' : SITE_URL . '/pages/dashboard.php';
            header("Location: " . $redirect_url);
            exit;
        } else {
            $login_error = 'Invalid email or password.';
        }
    }
}
?>

<!-- Simple Login Modal -->
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
                <?php if ($login_error): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($login_success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($login_success); ?>
                    </div>
                <?php endif; ?>

                <form class="login-form" method="POST" action="">
                    <input type="hidden" name="login_modal" value="1">
                    
                    <div class="form-group">
                        <label for="loginEmail" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-icon">📧</span>
                            <input type="email" id="loginEmail" name="email" class="form-input" 
                                   placeholder="Enter your email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
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
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" class="checkbox-input">
                            <span class="checkbox-text">Remember me</span>
                        </label>
                        <a href="<?php echo SITE_URL; ?>/pages/forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <span class="btn-text">Sign In</span>
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
    const closeModal = document.getElementById('closeLoginModal');
    const modalOverlay = document.getElementById('loginModalOverlay');
    const passwordToggle = document.getElementById('loginPasswordToggle');
    const loginPassword = document.getElementById('loginPassword');

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
});
</script> 