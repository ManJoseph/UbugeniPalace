<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL . '/pages/dashboard.php');
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
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
            
            // Redirect based on user type
            if ($user['user_type'] === 'admin') {
                redirectTo(SITE_URL . '/admin/');
            } else {
                redirectTo(SITE_URL . '/pages/dashboard.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$page_title = getPageTitle('login');
?>
<!DOCTYPE html>
<html lang="rw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="Login to your UbugeniPalace account to manage your profile and orders.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo/favicon.png">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
</head>
<body class="auth-page">
    <!-- Header Section -->
    <header class="main-header">
        <?php include '../includes/nav.php'; ?>
    </header>

    <!-- Main Content -->
    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1 class="auth-title">Login</h1>
                    <p class="auth-subtitle">Welcome back to UbugeniPalace</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-input-group">
                            <input type="password" id="password" name="password" class="form-input" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <span class="eye-icon">👁️</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" class="checkbox-input">
                            <span class="checkbox-text">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Login</button>
                </form>

                <div class="auth-footer">
                    <p class="auth-link-text">
                        Don't have an account? 
                        <a href="register.php" class="auth-link">Register here</a>
                    </p>
                    <p class="auth-link-text">
                        <a href="forgot-password.php" class="auth-link">Forgot your password?</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <footer class="main-footer">
        <?php include '../includes/footer.php'; ?>
    </footer>

    <!-- JavaScript Files -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/validation.js"></script>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('.eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = '👁️';
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.auth-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            form.addEventListener('submit', function(e) {
                let isValid = true;

                // Email validation
                if (!emailInput.value.trim()) {
                    showFieldError(emailInput, 'Email is required');
                    isValid = false;
                } else if (!isValidEmail(emailInput.value)) {
                    showFieldError(emailInput, 'Please enter a valid email');
                    isValid = false;
                } else {
                    clearFieldError(emailInput);
                }

                // Password validation
                if (!passwordInput.value.trim()) {
                    showFieldError(passwordInput, 'Password is required');
                    isValid = false;
                } else {
                    clearFieldError(passwordInput);
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showFieldError(input, message) {
            clearFieldError(input);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            input.parentNode.appendChild(errorDiv);
            input.classList.add('error');
        }

        function clearFieldError(input) {
            const existingError = input.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
            input.classList.remove('error');
        }
    </script>
</body>
</html>
