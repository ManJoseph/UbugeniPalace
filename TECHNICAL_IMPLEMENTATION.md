# UbugeniPalace - Technical Implementation Details

## Database Connection and Configuration

### Database Configuration (`config/database.php`)
```php
<?php
// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'ubumenyi_bwubugeni');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            
            // Set charset to UTF-8 for Kinyarwanda support
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
```

### Main Configuration (`config/config.php`)
```php
<?php
// Site configuration constants
define('SITE_NAME', 'UbugeniPalace');
define('SITE_TAGLINE', 'Discover Authentic Rwandan Craftsmanship');
define('SITE_URL', 'http://localhost/UbugeniPalace');
define('SITE_EMAIL', 'info@ubugenipalace.rw');

// File upload settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination settings
define('PRODUCTS_PER_PAGE', 12);
define('ARTISANS_PER_PAGE', 8);

// Helper functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
```

## User Authentication System

### Login API (`api/login.php`)
```php
<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit;
}

try {
    // Get user from database
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE email = ? AND is_active = 1",
        [$email]
    );
    
    if (!$user || !verifyPassword($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['full_name'] = $user['full_name'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $user['user_type'] === 'admin' ? SITE_URL . '/admin/' : SITE_URL . '/pages/dashboard.php'
    ]);
    
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
```

### User Registration (`pages/register.php`)
```php
<?php
// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $user_type = sanitizeInput($_POST['user_type']);
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists
        $existing_user = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing_user) {
            $error = 'An account with this email already exists.';
        } else {
            // Create user account
            $hashed_password = hashPassword($password);
            $username = strtolower(str_replace(' ', '', $full_name)) . '_' . time();
            
            if ($db->execute(
                "INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, ?)",
                [$username, $email, $hashed_password, $full_name, $user_type]
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
            }
        }
    }
}
```

## Product Management System

### Product Upload API (`api/my-products.php`)
```php
<?php
// Handle product upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    
    // Handle main image upload
    $main_image_path = null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['main_image'], 'products-images');
        if ($upload_result !== false) {
            $main_image_path = $upload_result;
        }
    }
    
    // Handle gallery images
    $gallery_images = [];
    if (isset($_FILES['gallery_images'])) {
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['gallery_images']['name'][$key],
                    'type' => $_FILES['gallery_images']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['gallery_images']['error'][$key],
                    'size' => $_FILES['gallery_images']['size'][$key]
                ];
                
                $upload_result = uploadImage($file, 'products-images');
                if ($upload_result !== false) {
                    $gallery_images[] = $upload_result;
                }
            }
        }
    }
    
    // Insert product into database
    $product_data = [
        'artisan_id' => $artisan_id,
        'category_id' => $category_id,
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'stock_quantity' => $stock_quantity,
        'main_image' => $main_image_path,
        'gallery_images' => json_encode($gallery_images)
    ];
    
    if ($db->execute(
        "INSERT INTO products (artisan_id, category_id, name, description, price, stock_quantity, main_image, gallery_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        array_values($product_data)
    )) {
        echo json_encode(['success' => true, 'message' => 'Product uploaded successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload product.']);
    }
}
```

### Product Display (`pages/products.php`)
```php
<?php
// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';

// Build query conditions
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = implode(' AND ', $where_conditions);

// Get products
$products_query = "
    SELECT p.*, c.name as category_name, u.full_name as artisan_name, a.rating as artisan_rating
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN artisans a ON p.artisan_id = a.id 
    JOIN users u ON a.user_id = u.id 
    WHERE {$where_clause}
    ORDER BY p.created_at DESC
";

$products = $db->fetchAll($products_query, $params);
```

## Shopping Cart System

### Cart Management (`api/cart.php`)
```php
<?php
// Add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = (int)$_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    
    switch ($action) {
        case 'add':
            $quantity = (int)$_POST['quantity'];
            
            // Check if item already in cart
            $existing_item = $db->fetchOne(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?",
                [$user_id, $product_id]
            );
            
            if ($existing_item) {
                // Update quantity
                $new_quantity = $existing_item['quantity'] + $quantity;
                $db->execute(
                    "UPDATE cart SET quantity = ? WHERE id = ?",
                    [$new_quantity, $existing_item['id']]
                );
            } else {
                // Add new item
                $db->execute(
                    "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)",
                    [$user_id, $product_id, $quantity]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Item added to cart']);
            break;
            
        case 'remove':
            $db->execute(
                "DELETE FROM cart WHERE user_id = ? AND product_id = ?",
                [$user_id, $product_id]
            );
            
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            break;
            
        case 'update':
            $quantity = (int)$_POST['quantity'];
            
            if ($quantity > 0) {
                $db->execute(
                    "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?",
                    [$quantity, $user_id, $product_id]
                );
            } else {
                $db->execute(
                    "DELETE FROM cart WHERE user_id = ? AND product_id = ?",
                    [$user_id, $product_id]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
            break;
    }
}
```

## Frontend JavaScript Implementation

### Main JavaScript (`assets/js/main.js`)
```javascript
/**
 * UbugeniPalace - Main JavaScript
 * Core functionality for the artisan marketplace
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeMobileMenu();
    initializeSearch();
    initializeDropdowns();
    initializeAnimations();
    initializeNotifications();
    initializeFooter();
});

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const searchInput = document.querySelector('.search-box input');
    const searchSuggestions = document.querySelector('.search-suggestions');
    
    if (searchInput) {
        // Debounce search input
        const debouncedSearch = debounce(function(query) {
            if (query.length >= 2) {
                fetchSearchSuggestions(query);
            } else {
                hideSearchSuggestions();
            }
        }, 300);
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            debouncedSearch(query);
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                hideSearchSuggestions();
            }
        });
    }
}

/**
 * Fetch search suggestions via AJAX
 */
function fetchSearchSuggestions(query) {
    fetch(`${SITE_URL}/api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchSuggestions(data.suggestions);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

/**
 * Display search suggestions
 */
function displaySearchSuggestions(suggestions) {
    const searchSuggestions = document.querySelector('.search-suggestions');
    
    if (suggestions.length === 0) {
        searchSuggestions.innerHTML = '<div class="suggestion-item">No results found</div>';
    } else {
        searchSuggestions.innerHTML = suggestions.map(item => `
            <div class="suggestion-item" onclick="window.location.href='${item.url}'">
                <img src="${item.image}" alt="${item.name}">
                <div class="suggestion-content">
                    <h4>${item.name}</h4>
                    <p>${item.category}</p>
                </div>
            </div>
        `).join('');
    }
    
    searchSuggestions.classList.add('show');
}

/**
 * Initialize mobile menu
 */
function initializeMobileMenu() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
        
        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                document.body.classList.remove('menu-open');
            });
        }
    }
}

/**
 * Initialize animations
 */
function initializeAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Utility function for debouncing
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
```

## CSS Implementation

### Main Styles (`assets/css/style.css`)
```css
/* CSS Variables for consistent theming */
:root {
    /* Colors */
    --primary: #2E7D32;
    --secondary: #4CAF50;
    --accent: #FF9800;
    --text-primary: #212121;
    --text-secondary: #757575;
    --text-white: #FFFFFF;
    --bg-primary: #FFFFFF;
    --bg-secondary: #F5F5F5;
    --border: #E0E0E0;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 8px rgba(0,0,0,0.15);
    --shadow-lg: 0 8px 16px rgba(0,0,0,0.2);
    
    /* Typography */
    --font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.125rem;
    --font-size-xl: 1.25rem;
    --font-size-2xl: 1.5rem;
    --font-size-3xl: 1.875rem;
    
    /* Spacing */
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
    --spacing-2xl: 3rem;
    
    /* Layout */
    --container-sm: 640px;
    --container-md: 768px;
    --container-lg: 1024px;
    --container-xl: 1280px;
    --container-2xl: 1536px;
    
    /* Transitions */
    --transition-fast: 0.15s ease;
    --transition-normal: 0.3s ease;
    --transition-slow: 0.5s ease;
    
    /* Border radius */
    --radius-sm: 0.25rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
}

/* Base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font-family);
    font-size: var(--font-size-base);
    line-height: 1.6;
    color: var(--text-primary);
    background-color: var(--bg-primary);
}

/* Button styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-sm) var(--spacing-lg);
    border: none;
    border-radius: var(--radius-md);
    font-size: var(--font-size-base);
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all var(--transition-fast);
    background-color: var(--bg-secondary);
    color: var(--text-primary);
}

.btn-primary {
    background-color: var(--primary);
    color: var(--text-white);
}

.btn-primary:hover {
    background-color: #1B5E20;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

/* Card styles */
.card {
    background-color: var(--bg-primary);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: all var(--transition-normal);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Form styles */
.form-group {
    margin-bottom: var(--spacing-lg);
}

.form-label {
    display: block;
    margin-bottom: var(--spacing-sm);
    font-weight: 500;
    color: var(--text-primary);
}

.form-input,
.form-textarea {
    width: 100%;
    padding: var(--spacing-md);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    font-size: var(--font-size-base);
    transition: border-color var(--transition-fast);
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
}

/* Header styles */
.header {
    background-color: var(--bg-primary);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 100;
    transition: all var(--transition-normal);
}

.header.scrolled {
    box-shadow: var(--shadow-md);
}

/* Navigation styles */
.nav {
    display: flex;
    align-items: center;
    gap: var(--spacing-xl);
}

.nav-link {
    color: var(--text-primary);
    text-decoration: none;
    font-weight: 500;
    transition: color var(--transition-fast);
    position: relative;
}

.nav-link::before {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 2px;
    background-color: var(--primary);
    transition: width var(--transition-fast);
}

.nav-link:hover::before,
.nav-link.active::before {
    width: 100%;
}

/* Product grid styles */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-xl);
    margin-top: var(--spacing-xl);
}

.product-card {
    background-color: var(--bg-primary);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-normal);
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.product-image {
    position: relative;
    overflow: hidden;
    aspect-ratio: 1;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-normal);
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

/* Responsive design */
@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .nav {
        display: none;
    }
    
    .mobile-menu {
        display: block;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }
    
    .btn {
        padding: var(--spacing-md) var(--spacing-lg);
        font-size: var(--font-size-lg);
    }
}
```

## Security Implementation

### Input Sanitization and Validation
```php
// Input sanitization function
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Email validation
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Password validation
function validatePassword($password) {
    return strlen($password) >= 6;
}

// File upload security
function uploadImage($file, $folder = 'general') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Validate file type
    $allowed_types = ALLOWED_IMAGE_TYPES;
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = UPLOAD_PATH . $folder . '/';
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    $filepath = $upload_path . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $folder . '/' . $filename;
    }
    
    return false;
}
```

### SQL Injection Prevention
```php
// Using prepared statements for all database queries
$user = $db->fetchOne(
    "SELECT * FROM users WHERE email = ? AND is_active = 1",
    [$email]
);

$products = $db->fetchAll(
    "SELECT p.*, c.name as category_name, u.full_name as artisan_name 
     FROM products p 
     JOIN categories c ON p.category_id = c.id 
     JOIN artisans a ON p.artisan_id = a.id 
     JOIN users u ON a.user_id = u.id 
     WHERE p.status = 'active' AND p.category_id = ?
     ORDER BY p.created_at DESC",
    [$category_id]
);
```

### Session Security
```php
// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

// Session validation
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

function isArtisan() {
    return isLoggedIn() && $_SESSION['user_type'] === 'artisan';
}

// CSRF protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
```

This technical implementation demonstrates the comprehensive use of modern web technologies to create a secure, scalable, and user-friendly artisan marketplace platform. 