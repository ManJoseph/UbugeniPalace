<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL . '/pages/dashboard.php');
}

$error = '';
$success = '';

// Handle forgot password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if user exists
        $user = $db->fetchOne(
            "SELECT id, full_name, email, user_type FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );
        
        if ($user) {
            // Create password reset request
            $request_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24 hours
            
            try {
                // Insert or update password reset request
                $db->execute(
                    "INSERT INTO password_reset_requests (user_id, email, request_token, expires_at, status, created_at) 
                     VALUES (?, ?, ?, ?, 'pending', NOW()) 
                     ON DUPLICATE KEY UPDATE 
                     request_token = VALUES(request_token), 
                     expires_at = VALUES(expires_at), 
                     status = 'pending', 
                     created_at = NOW()",
                    [$user['id'], $email, $request_token, $expires_at]
                );
                
                // Send notification to admin
                $admin_notification = [
                    'type' => 'password_reset_request',
                    'user_id' => $user['id'],
                    'user_name' => $user['full_name'],
                    'user_email' => $email,
                    'user_type' => $user['user_type'],
                    'request_token' => $request_token,
                    'message' => "Password reset requested for user: {$user['full_name']} ({$email})",
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Store admin notification
                $db->execute(
                    "INSERT INTO admin_notifications (type, user_id, title, message, data, is_read, created_at) 
                     VALUES (?, ?, ?, ?, ?, 0, NOW())",
                    [
                        'password_reset_request',
                        $user['id'],
                        'Password Reset Request',
                        $admin_notification['message'],
                        json_encode($admin_notification)
                    ]
                );
                
                $success = 'Your password reset request has been submitted successfully. An administrator will review your request and send you a new password within 24 hours.';
                
            } catch (Exception $e) {
                error_log('Password reset request error: ' . $e->getMessage());
                $error = 'An error occurred while processing your request. Please try again.';
            }
        } else {
            // Don't reveal if email exists or not for security
            $success = 'If an account with this email address exists, a password reset request has been submitted. An administrator will review your request and send you a new password within 24 hours.';
        }
    }
}

$page_title = 'Forgot Password - ' . SITE_NAME;
include '../includes/header.php';
?>

<main class="forgot-password-main">
    <div class="forgot-password-hero">
        <div class="forgot-password-hero-content">
            <h1 class="forgot-password-hero-title">Forgot Your Password?</h1>
            <p class="forgot-password-hero-subtitle">No worries! Request a password reset and we'll help you get back into your account.</p>
            <p class="forgot-password-hero-description">
                Enter your email address below and we'll send your request to our administrators. 
                They'll review your account and send you a new password within 24 hours.
            </p>
        </div>
    </div>

    <div class="forgot-password-content">
        <div class="forgot-password-layout">
            <div class="forgot-password-form-section">
                <div class="form-header">
                    <h2 class="form-title">Request Password Reset</h2>
                    <p class="form-description">
                        Provide your email address and we'll process your password reset request.
                    </p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <span class="alert-icon">⚠️</span>
                        <span class="alert-message"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <span class="alert-icon">✅</span>
                        <span class="alert-message"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <form class="forgot-password-form" method="POST" id="forgotPasswordForm">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-icon">📧</span>
                            <input type="email" id="email" name="email" class="form-input" 
                                   placeholder="Enter your registered email address" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                        </div>
                        <div class="field-error" id="emailError" style="display: none;"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                        <span class="btn-text">Submit Request</span>
                        <span class="btn-loading" style="display: none;">
                            <span class="loading-spinner"></span>
                            Submitting...
                        </span>
                    </button>
                </form>

                <div class="forgot-password-footer">
                    <p class="forgot-password-link-text">
                        Remember your password? 
                        <a href="login.php" class="forgot-password-link">Back to Login</a>
                    </p>
                    <p class="forgot-password-link-text">
                        Don't have an account? 
                        <a href="register.php" class="forgot-password-link">Create one here</a>
                    </p>
                </div>
            </div>

            <div class="forgot-password-info-section">
                <div class="info-card">
                    <div class="info-icon">🔐</div>
                    <h3 class="info-title">How It Works</h3>
                    <div class="info-steps">
                        <div class="info-step">
                            <span class="step-number">1</span>
                            <div class="step-content">
                                <h4>Submit Request</h4>
                                <p>Enter your email address and submit the form</p>
                            </div>
                        </div>
                        <div class="info-step">
                            <span class="step-number">2</span>
                            <div class="step-content">
                                <h4>Admin Review</h4>
                                <p>Our administrators will review your request</p>
                            </div>
                        </div>
                        <div class="info-step">
                            <span class="step-number">3</span>
                            <div class="step-content">
                                <h4>New Password</h4>
                                <p>You'll receive a new password within 24 hours</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">⏰</div>
                    <h3 class="info-title">Processing Time</h3>
                    <p class="info-description">
                        Password reset requests are typically processed within 24 hours during business days. 
                        You'll receive an email with your new password once approved.
                    </p>
                </div>

                <div class="info-card">
                    <div class="info-icon">📞</div>
                    <h3 class="info-title">Need Help?</h3>
                    <p class="info-description">
                        If you need immediate assistance, please contact our support team at 
                        <a href="mailto:support@ubugenipalace.rw">support@ubugenipalace.rw</a> or call us at 
                        <a href="tel:+250788123456">+250 788 123 456</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    const submitBtn = document.getElementById('submitBtn');
    const emailError = document.getElementById('emailError');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = emailInput.value.trim();
        
        // Clear previous errors
        clearError();
        
        // Validation
        if (!email) {
            showError('Please enter your email address');
            return;
        }
        
        if (!isValidEmail(email)) {
            showError('Please enter a valid email address');
            return;
        }
        
        // Show loading state
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        submitBtn.disabled = true;
        
        // Submit form
        form.submit();
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function showError(message) {
        emailError.textContent = message;
        emailError.style.display = 'block';
        emailInput.classList.add('error');
    }

    function clearError() {
        emailError.style.display = 'none';
        emailInput.classList.remove('error');
    }
});
</script> 