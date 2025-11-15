/**
 * UbugeniPalace - Form Validation JavaScript
 * Client-side form validation for the artisan marketplace
 */

class FormValidator {
    constructor(formSelector, options = {}) {
        this.form = document.querySelector(formSelector);
        this.options = {
            validateOnInput: true,
            validateOnBlur: true,
            showErrors: true,
            ...options
        };
        
        if (this.form) {
            this.init();
        }
    }
    
    init() {
        this.bindEvents();
        this.setupValidationRules();
    }
    
    bindEvents() {
        if (!this.form) return;
        
        // Form submission
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                this.showFormErrors();
            }
        });
        
        // Real-time validation
        if (this.options.validateOnInput) {
            this.form.addEventListener('input', (e) => {
                this.validateField(e.target);
            });
        }
        
        if (this.options.validateOnBlur) {
            this.form.addEventListener('blur', (e) => {
                this.validateField(e.target);
            }, true);
        }
        
        // Focus events for better UX
        this.form.addEventListener('focus', (e) => {
            this.clearFieldError(e.target);
        }, true);
    }
    
    setupValidationRules() {
        this.rules = {
            // Email validation
            email: {
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                message: 'Please enter a valid email address'
            },
            
            // Password validation
            password: {
                minLength: 8,
                pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
                message: 'Password must be at least 8 characters with uppercase, lowercase, number, and special character'
            },
            
            // Required field validation
            required: {
                message: 'This field is required'
            },
            
            // Name validation
            name: {
                pattern: /^[a-zA-Z\s'-]+$/,
                minLength: 2,
                message: 'Please enter a valid name (letters, spaces, hyphens, apostrophes only)'
            },
            
            // Phone validation
            phone: {
                pattern: /^[\+]?[1-9][\d]{0,15}$/,
                message: 'Please enter a valid phone number'
            },
            
            // URL validation
            url: {
                pattern: /^https?:\/\/.+/,
                message: 'Please enter a valid URL starting with http:// or https://'
            },
            
            // Number validation
            number: {
                pattern: /^\d+$/,
                message: 'Please enter a valid number'
            },
            
            // Price validation
            price: {
                pattern: /^\d+(\.\d{1,2})?$/,
                message: 'Please enter a valid price (e.g., 10.99)'
            },
            
            // File validation
            file: {
                maxSize: 5 * 1024 * 1024, // 5MB
                allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                message: 'Please select a valid image file (JPEG, PNG, GIF, WebP) under 5MB'
            }
        };
    }
    
    validateForm() {
        const fields = this.form.querySelectorAll('input, select, textarea');
        let isValid = true;
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(field) {
        const value = field.value.trim();
        const fieldType = field.type;
        const fieldName = field.name;
        const validationRules = this.getValidationRules(field);
        
        // Clear previous errors
        this.clearFieldError(field);
        
        // Check if field is required
        if (field.hasAttribute('required') && !value) {
            this.showFieldError(field, this.rules.required.message);
            return false;
        }
        
        // Skip validation if field is empty and not required
        if (!value && !field.hasAttribute('required')) {
            return true;
        }
        
        // Apply validation rules
        for (const rule of validationRules) {
            if (!this.applyValidationRule(field, value, rule)) {
                return false;
            }
        }
        
        // Custom validation for specific field types
        if (fieldType === 'file') {
            if (!this.validateFile(field)) {
                return false;
            }
        }
        
        // Show success state
        this.showFieldSuccess(field);
        return true;
    }
    
    getValidationRules(field) {
        const rules = [];
        const fieldType = field.type;
        const fieldName = field.name.toLowerCase();
        
        // Add rules based on field type
        if (fieldType === 'email') {
            rules.push('email');
        }
        
        if (fieldType === 'password') {
            rules.push('password');
        }
        
        if (fieldType === 'tel') {
            rules.push('phone');
        }
        
        if (fieldType === 'url') {
            rules.push('url');
        }
        
        if (fieldType === 'number') {
            rules.push('number');
        }
        
        // Add rules based on field name
        if (fieldName.includes('name') || fieldName.includes('first') || fieldName.includes('last')) {
            rules.push('name');
        }
        
        if (fieldName.includes('price') || fieldName.includes('cost')) {
            rules.push('price');
        }
        
        if (fieldName.includes('file') || fieldName.includes('image')) {
            rules.push('file');
        }
        
        // Add custom rules from data attributes
        const customRules = field.dataset.validation;
        if (customRules) {
            rules.push(...customRules.split(' '));
        }
        
        return rules;
    }
    
    applyValidationRule(field, value, ruleName) {
        const rule = this.rules[ruleName];
        if (!rule) return true;
        
        // Pattern validation
        if (rule.pattern && !rule.pattern.test(value)) {
            this.showFieldError(field, rule.message);
            return false;
        }
        
        // Length validation
        if (rule.minLength && value.length < rule.minLength) {
            this.showFieldError(field, `${rule.message} (minimum ${rule.minLength} characters)`);
            return false;
        }
        
        if (rule.maxLength && value.length > rule.maxLength) {
            this.showFieldError(field, `${rule.message} (maximum ${rule.maxLength} characters)`);
            return false;
        }
        
        return true;
    }
    
    validateFile(field) {
        const file = field.files[0];
        if (!file) return true;
        
        const rule = this.rules.file;
        
        // Check file size
        if (file.size > rule.maxSize) {
            this.showFieldError(field, `File size must be less than ${this.formatFileSize(rule.maxSize)}`);
            return false;
        }
        
        // Check file type
        if (!rule.allowedTypes.includes(file.type)) {
            this.showFieldError(field, `File type not allowed. Please use: ${rule.allowedTypes.join(', ')}`);
            return false;
        }
        
        return true;
    }
    
    showFieldError(field, message) {
        if (!this.options.showErrors) return;
        
        // Remove existing error
        this.clearFieldError(field);
        
        // Add error class
        field.classList.add('error');
        
        // Create error message
        const errorElement = document.createElement('div');
        errorElement.className = 'form-error';
        errorElement.textContent = message;
        
        // Insert error message
        const fieldContainer = field.closest('.form-group') || field.parentNode;
        fieldContainer.appendChild(errorElement);
        
        // Add aria attributes
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', errorElement.id || 'error-' + Date.now());
    }
    
    showFieldSuccess(field) {
        field.classList.remove('error');
        field.classList.add('success');
        field.setAttribute('aria-invalid', 'false');
    }
    
    clearFieldError(field) {
        field.classList.remove('error');
        field.classList.remove('success');
        
        const fieldContainer = field.closest('.form-group') || field.parentNode;
        const errorElement = fieldContainer.querySelector('.form-error');
        
        if (errorElement) {
            errorElement.remove();
        }
    }
    
    showFormErrors() {
        const firstError = this.form.querySelector('.form-error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Show notification
        if (window.showNotification) {
            window.showNotification('Please correct the errors in the form', 'error');
        }
    }
    
    formatFileSize(bytes) {
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        if (bytes === 0) return '0 Bytes';
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    }
}

// Specific form validators
class LoginValidator extends FormValidator {
    constructor(formSelector) {
        super(formSelector, {
            validateOnInput: true,
            validateOnBlur: true
        });
    }
    
    setupValidationRules() {
        super.setupValidationRules();
        
        // Custom login validation
        this.rules.loginEmail = {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        };
        
        this.rules.loginPassword = {
            minLength: 1,
            message: 'Password is required'
        };
    }
}

class RegistrationValidator extends FormValidator {
    constructor(formSelector) {
        super(formSelector, {
            validateOnInput: true,
            validateOnBlur: true
        });
    }
    
    setupValidationRules() {
        super.setupValidationRules();
        
        // Custom registration validation
        this.rules.confirmPassword = {
            message: 'Passwords do not match'
        };
        
        this.rules.terms = {
            message: 'You must accept the terms and conditions'
        };
    }
    
    validateField(field) {
        const isValid = super.validateField(field);
        
        // Custom validation for password confirmation
        if (field.name === 'confirm_password') {
            const password = this.form.querySelector('input[name="password"]');
            if (password && field.value !== password.value) {
                this.showFieldError(field, this.rules.confirmPassword.message);
                return false;
            }
        }
        
        // Custom validation for terms checkbox
        if (field.name === 'terms' && field.type === 'checkbox') {
            if (!field.checked) {
                this.showFieldError(field, this.rules.terms.message);
                return false;
            }
        }
        
        return isValid;
    }
}

class ProductFormValidator extends FormValidator {
    constructor(formSelector) {
        super(formSelector, {
            validateOnInput: true,
            validateOnBlur: true
        });
    }
    
    setupValidationRules() {
        super.setupValidationRules();
        
        // Custom product validation
        this.rules.productName = {
            minLength: 3,
            maxLength: 100,
            message: 'Product name must be between 3 and 100 characters'
        };
        
        this.rules.productDescription = {
            minLength: 10,
            maxLength: 1000,
            message: 'Description must be between 10 and 1000 characters'
        };
        
        this.rules.productPrice = {
            pattern: /^\d+(\.\d{1,2})?$/,
            message: 'Please enter a valid price'
        };
    }
}

// Initialize validators when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Login form validation
    const loginForm = document.querySelector('#loginForm, .login-form');
    if (loginForm) {
        window.loginValidator = new LoginValidator('#loginForm, .login-form');
    }
    
    // Registration form validation
    const registerForm = document.querySelector('#registerForm, .register-form');
    if (registerForm) {
        window.registerValidator = new RegistrationValidator('#registerForm, .register-form');
    }
    
    // Product form validation
    const productForm = document.querySelector('#productForm, .product-form');
    if (productForm) {
        window.productValidator = new ProductFormValidator('#productForm, .product-form');
    }
    
    // Contact form validation
    const contactForm = document.querySelector('#contactForm, .contact-form');
    if (contactForm) {
        window.contactValidator = new FormValidator('#contactForm, .contact-form');
    }
    
    // Newsletter form validation
    const newsletterForm = document.querySelector('#newsletterForm, .newsletter-form');
    if (newsletterForm) {
        window.newsletterValidator = new FormValidator('#newsletterForm, .newsletter-form');
    }
});

// Global validation functions
window.validateForm = function(formSelector) {
    const validator = new FormValidator(formSelector);
    return validator.validateForm();
};

window.validateField = function(field) {
    const validator = new FormValidator(field.closest('form'));
    return validator.validateField(field);
};

// Password strength indicator
class PasswordStrengthIndicator {
    constructor(passwordField, strengthIndicator) {
        this.passwordField = passwordField;
        this.strengthIndicator = strengthIndicator;
        this.init();
    }
    
    init() {
        this.passwordField.addEventListener('input', () => {
            this.updateStrength();
        });
    }
    
    updateStrength() {
        const password = this.passwordField.value;
        const strength = this.calculateStrength(password);
        this.displayStrength(strength);
    }
    
    calculateStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        if (score < 2) return 'weak';
        if (score < 4) return 'medium';
        return 'strong';
    }
    
    displayStrength(strength) {
        if (!this.strengthIndicator) return;
        
        this.strengthIndicator.className = `password-strength ${strength}`;
        this.strengthIndicator.textContent = `Password strength: ${strength}`;
    }
}

// Initialize password strength indicators
document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        const strengthIndicator = field.parentNode.querySelector('.password-strength');
        if (strengthIndicator) {
            new PasswordStrengthIndicator(field, strengthIndicator);
        }
    });
});

// Real-time password confirmation
document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('input[name="password"]');
    const confirmFields = document.querySelectorAll('input[name="confirm_password"]');
    
    passwordFields.forEach(passwordField => {
        confirmFields.forEach(confirmField => {
            const checkMatch = () => {
                if (confirmField.value && passwordField.value !== confirmField.value) {
                    confirmField.classList.add('error');
                    confirmField.setAttribute('aria-invalid', 'true');
                } else {
                    confirmField.classList.remove('error');
                    confirmField.setAttribute('aria-invalid', 'false');
                }
            };
            
            passwordField.addEventListener('input', checkMatch);
            confirmField.addEventListener('input', checkMatch);
        });
    });
});
