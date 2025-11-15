<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL . '/pages/dashboard.php');
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = sanitizeInput($_POST['user_type']);
    
    // Handle profile image upload
    $profile_image_path = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['profile_image'], 'profile-photos');
        if ($upload_result !== false) {
            $profile_image_path = $upload_result;
        }
    }
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($user_type, ['customer', 'artisan'])) {
        $error = 'Please select a valid user type.';
    } else {
        // Check if email already exists
        $existing_user = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing_user) {
            $error = 'An account with this email already exists.';
        } else {
            // Create user account
            $hashed_password = hashPassword($password);
            $username = strtolower(str_replace(' ', '', $full_name)) . '_' . time();
            
            $user_data = [
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'full_name' => $full_name,
                'phone' => $phone,
                'profile_image' => $profile_image_path,
                'user_type' => $user_type
            ];
            
            if ($db->execute(
                "INSERT INTO users (username, email, password, full_name, phone, profile_image, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)",
                array_values($user_data)
            )) {
                $user_id = $db->lastInsertId();
                
                // If registering as artisan, create artisan profile
                if ($user_type === 'artisan') {
                    $db->execute(
                        "INSERT INTO artisans (user_id, specialization, location) VALUES (?, ?, ?)",
                        [$user_id, 'General Crafts', 'Rwanda']
                    );
                }
                
                $success = 'Account created successfully! You can now login.';
                
                // Auto-login after registration
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_type'] = $user_type;
                $_SESSION['full_name'] = $full_name;
                
                redirectTo(SITE_URL . '/pages/dashboard.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$page_title = getPageTitle('register');

// Include the header which contains the complete HTML structure
include '../includes/header.php';
?>

    <!-- Main Content -->
    <main class="register-main">
        <!-- Hero Section -->
        <section class="register-hero">
            <div class="container">
                <div class="register-hero-content">
                    <h1 class="register-hero-title">Join Our Community</h1>
                    <p class="register-hero-subtitle">Connect with Rwandan artisans and discover authentic craftsmanship</p>
                    <div class="register-hero-description">
                        <p>Whether you're looking to purchase unique handmade products or showcase your craft to the world, we're here to connect artisans with customers globally.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Registration Form Section -->
        <section class="register-content">
            <div class="container">
                <div class="register-layout">
                    <!-- Registration Form -->
                    <div class="register-form-section">
                        <div class="form-header">
                            <h2 class="form-title">Create Your Account</h2>
                            <p class="form-description">Join the UbugeniPalace community today</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-error">
                                <span class="alert-icon">⚠️</span>
                                <span class="alert-message"><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <span class="alert-icon">✅</span>
                                <span class="alert-message"><?php echo htmlspecialchars($success); ?></span>
                            </div>
                        <?php endif; ?>

                        <form class="register-form" method="POST" action="" id="registerForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" id="full_name" name="full_name" class="form-input" 
                                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                                           required>
                                    <div class="field-error" id="full_name_error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-input" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           required>
                                    <div class="field-error" id="email_error"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-input" 
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                       placeholder="+250 788 123 456">
                                <div class="field-error" id="phone_error"></div>
                            </div>

                            <div class="form-group" id="profileImageGroup" style="display: none;">
                                <label for="profile_image" class="form-label">Profile Picture</label>
                                <div class="profile-image-upload">
                                    <div class="current-image">
                                        <img src="../assets/images/icons/user.svg" alt="Profile preview" id="profilePreview">
                                    </div>
                                    <div class="upload-controls">
                                        <label for="profile_image" class="btn btn-outline">Choose Image</label>
                                        <input type="file" id="profile_image" name="profile_image" class="form-file" 
                                               accept="image/*" style="display: none;">
                                        <p class="upload-help">Recommended: Square image, 400x400px or larger</p>
                                    </div>
                                </div>
                                <div class="field-error" id="profile_image_error"></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">I want to join as *</label>
                                <div class="user-type-selection">
                                    <label class="user-type-option">
                                        <input type="radio" name="user_type" value="customer" 
                                               <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'customer') ? 'checked' : ''; ?> 
                                               required>
                                        <div class="user-type-card">
                                            <div class="user-type-icon">🛒</div>
                                            <div class="user-type-content">
                                                <h4>Customer</h4>
                                                <p>Browse and purchase unique handmade products from talented artisans</p>
                                                <ul class="user-type-benefits">
                                                    <li>Discover authentic Rwandan crafts</li>
                                                    <li>Support local artisans</li>
                                                    <li>Secure payment options</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="user-type-option">
                                        <input type="radio" name="user_type" value="artisan" 
                                               <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'artisan') ? 'checked' : ''; ?> 
                                               required>
                                        <div class="user-type-card">
                                            <div class="user-type-icon">🎨</div>
                                            <div class="user-type-content">
                                                <h4>Artisan</h4>
                                                <p>Sell your handmade crafts and grow your business globally</p>
                                                <ul class="user-type-benefits">
                                                    <li>Reach global customers</li>
                                                    <li>Manage your products easily</li>
                                                    <li>Fair commission rates</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="field-error" id="user_type_error"></div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password" class="form-label">Password *</label>
                                    <div class="password-input-group">
                                        <input type="password" id="password" name="password" class="form-input" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                            <span class="eye-icon">👁️</span>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="passwordStrength">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="strengthFill"></div>
                                        </div>
                                        <span class="strength-text" id="strengthText">Password strength</span>
                                    </div>
                                    <div class="field-error" id="password_error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <div class="password-input-group">
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                            <span class="eye-icon">👁️</span>
                                        </button>
                                    </div>
                                    <div class="field-error" id="confirm_password_error"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="terms" class="checkbox-input" required>
                                    <span class="checkbox-text">
                                        I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and 
                                        <a href="privacy.php" target="_blank">Privacy Policy</a>
                                    </span>
                                </label>
                                <div class="field-error" id="terms_error"></div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                                    <span class="btn-text">Create Account</span>
                                    <span class="btn-loading" style="display: none;">Creating Account...</span>
                                </button>
                            </div>
                        </form>

                        <div class="register-footer">
                            <p class="register-link-text">
                                Already have an account? 
                                <a href="login.php" class="register-link">Login here</a>
                            </p>
                        </div>
                    </div>

                    <!-- Registration Benefits -->
                    <div class="register-benefits-section">
                        <div class="benefits-header">
                            <h2 class="benefits-title">Why Join UbugeniPalace?</h2>
                            <p class="benefits-description">Discover the benefits of being part of our community</p>
                        </div>
                        
                        <div class="benefits-grid">
                            <div class="benefit-card">
                                <div class="benefit-icon">🌟</div>
                                <div class="benefit-content">
                                    <h3>Authentic Craftsmanship</h3>
                                    <p>Connect with genuine Rwandan artisans and discover unique, handcrafted products that tell a story.</p>
                                </div>
                            </div>
                            
                            <div class="benefit-card">
                                <div class="benefit-icon">🌍</div>
                                <div class="benefit-content">
                                    <h3>Global Reach</h3>
                                    <p>Artisans can reach customers worldwide while customers discover products from Rwanda's finest craftspeople.</p>
                                </div>
                            </div>
                            
                            <div class="benefit-card">
                                <div class="benefit-icon">💝</div>
                                <div class="benefit-content">
                                    <h3>Support Local Economy</h3>
                                    <p>Every purchase directly supports Rwandan artisans and helps preserve traditional crafts and skills.</p>
                                </div>
                            </div>
                            
                            <div class="benefit-card">
                                <div class="benefit-icon">🔒</div>
                                <div class="benefit-content">
                                    <h3>Secure Platform</h3>
                                    <p>Safe and secure transactions with multiple payment options and buyer protection.</p>
                                </div>
                            </div>
                            
                            <div class="benefit-card">
                                <div class="benefit-icon">📱</div>
                                <div class="benefit-content">
                                    <h3>Easy Management</h3>
                                    <p>Artisans can easily manage their products, orders, and customer communications all in one place.</p>
                                </div>
                            </div>
                            
                            <div class="benefit-card">
                                <div class="benefit-icon">🎯</div>
                                <div class="benefit-content">
                                    <h3>Quality Assurance</h3>
                                    <p>All products are carefully curated and reviewed to ensure the highest quality standards.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stats-section">
                            <h3 class="stats-title">Our Community Impact</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-number">500+</div>
                                    <div class="stat-label">Artisans</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">10,000+</div>
                                    <div class="stat-label">Products</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">50+</div>
                                    <div class="stat-label">Countries</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">98%</div>
                                    <div class="stat-label">Satisfaction</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Password Toggle Functionality
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = passwordInput.parentNode.querySelector('.eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = '👁️';
            }
        }

        // Password Strength Indicator
        function updatePasswordStrength(password) {
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let strengthLabel = '';
            let strengthColor = '';
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                    strengthLabel = 'Very Weak';
                    strengthColor = '#ef4444';
                    break;
                case 2:
                    strengthLabel = 'Weak';
                    strengthColor = '#f97316';
                    break;
                case 3:
                    strengthLabel = 'Fair';
                    strengthColor = '#eab308';
                    break;
                case 4:
                    strengthLabel = 'Good';
                    strengthColor = '#22c55e';
                    break;
                case 5:
                case 6:
                    strengthLabel = 'Strong';
                    strengthColor = '#16a34a';
                    break;
            }
            
            const percentage = (strength / 6) * 100;
            strengthFill.style.width = percentage + '%';
            strengthFill.style.backgroundColor = strengthColor;
            strengthText.textContent = strengthLabel;
            strengthText.style.color = strengthColor;
        }

        // Form Validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const emailInput = document.getElementById('email');
            const fullNameInput = document.getElementById('full_name');
            const phoneInput = document.getElementById('phone');
            const userTypeInputs = document.querySelectorAll('input[name="user_type"]');
            const termsInput = document.querySelector('input[name="terms"]');
            const submitBtn = document.getElementById('submitBtn');


            // Password strength monitoring
            passwordInput.addEventListener('input', function() {
                updatePasswordStrength(this.value);
            });

            // Real-time validation for Full Name (no numbers allowed)
            fullNameInput.addEventListener('input', function() {
                const nameRegex = /^[A-Za-z\s'-]+$/;
                if (!this.value.trim()) {
                    showFieldError(this, 'full_name', 'Full name is required');
                } else if (!nameRegex.test(this.value)) {
                    showFieldError(this, 'full_name', 'Name can only contain letters, spaces, apostrophes, and hyphens');
                } else {
                    clearFieldError(this, 'full_name');
                }
            });

            // Real-time validation for Email
            emailInput.addEventListener('input', function() {
                if (!this.value.trim()) {
                    showFieldError(this, 'email', 'Email is required');
                } else if (!isValidEmail(this.value)) {
                    showFieldError(this, 'email', 'Please enter a valid email address');
                } else {
                    clearFieldError(this, 'email');
                }
            });

            // Real-time validation for Phone (no letters allowed)
            phoneInput.addEventListener('input', function() {
                const phoneRegex = /^\+?[\d\s\-\(\)]+$/;
                if (this.value && !phoneRegex.test(this.value)) {
                    showFieldError(this, 'phone', 'Phone number can only contain numbers, spaces, +, -, and parentheses');
                } else {
                    clearFieldError(this, 'phone');
                }
            });

            // Real-time validation for Confirm Password
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value && passwordInput.value !== this.value) {
                    showFieldError(this, 'confirm_password', 'Passwords do not match');
                } else {
                    clearFieldError(this, 'confirm_password');
                }
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                let isValid = true;

                // Validate all fields
                if (!validateField(fullNameInput, 'full_name', 'Full name is required')) isValid = false;
                if (!validateField(emailInput, 'email', 'Please enter a valid email address')) isValid = false;
                if (!validateField(passwordInput, 'password', 'Password must be at least 6 characters')) isValid = false;
                if (!validateField(confirmPasswordInput, 'confirm_password', 'Please confirm your password')) isValid = false;
                
                // Check user type selection
                const selectedUserType = document.querySelector('input[name="user_type"]:checked');
                if (!selectedUserType) {
                    showFieldError(null, 'user_type', 'Please select a user type');
                    isValid = false;
                } else {
                    clearFieldError(null, 'user_type');
                }

                // Check terms agreement
                if (!termsInput.checked) {
                    showFieldError(termsInput, 'terms', 'Please agree to the terms and conditions');
                    isValid = false;
                } else {
                    clearFieldError(termsInput, 'terms');
                }

                if (!isValid) {
                    e.preventDefault();
                } else {
                    // Show loading state
                    submitBtn.querySelector('.btn-text').style.display = 'none';
                    submitBtn.querySelector('.btn-loading').style.display = 'inline';
                    submitBtn.disabled = true;
                }
            });

            // User type selection styling and profile image toggle
            userTypeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    document.querySelectorAll('.user-type-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    if (this.checked) {
                        this.closest('.user-type-option').querySelector('.user-type-card').classList.add('selected');
                        
                        // Show profile image upload for artisans
                        const profileImageGroup = document.getElementById('profileImageGroup');
                        if (this.value === 'artisan') {
                            profileImageGroup.style.display = 'block';
                        } else {
                            profileImageGroup.style.display = 'none';
                        }
                    }
                });
            });

            // Initialize user type selection styling
            const checkedUserType = document.querySelector('input[name="user_type"]:checked');
            if (checkedUserType) {
                checkedUserType.closest('.user-type-option').querySelector('.user-type-card').classList.add('selected');
                
                // Show profile image upload if artisan is selected
                if (checkedUserType.value === 'artisan') {
                    document.getElementById('profileImageGroup').style.display = 'block';
                }
            }
            
            // Profile image preview
            const profileImageInput = document.getElementById('profile_image');
            if (profileImageInput) {
                profileImageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.getElementById('profilePreview').src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });

        function validateField(input, fieldId, errorMessage) {
            let isValid = true;
            
            if (!input.value.trim()) {
                showFieldError(input, fieldId, errorMessage);
                isValid = false;
            } else if (fieldId === 'email' && !isValidEmail(input.value)) {
                showFieldError(input, fieldId, 'Please enter a valid email address');
                isValid = false;
            } else if (fieldId === 'password' && input.value.length < 6) {
                showFieldError(input, fieldId, 'Password must be at least 6 characters');
                isValid = false;
            } else if (fieldId === 'confirm_password' && input.value !== document.getElementById('password').value) {
                showFieldError(input, fieldId, 'Passwords do not match');
                isValid = false;
            } else {
                clearFieldError(input, fieldId);
            }
            
            return isValid;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidPhone(phone) {
            const phoneRegex = /^\+?[\d\s\-\(\)]+$/;
            return phoneRegex.test(phone);
        }

        function showFieldError(input, fieldId, message) {
            const errorElement = document.getElementById(fieldId + '_error');
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }
            if (input) {
                input.classList.add('error');
            }
        }

        function clearFieldError(input, fieldId) {
            const errorElement = document.getElementById(fieldId + '_error');
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
            }
            if (input) {
                input.classList.remove('error');
            }
        }
    </script>

<?php include '../includes/footer.php'; ?>
