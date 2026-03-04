<?php
/**
 * UbugeniPalace - Main Configuration
 * Artisan Marketplace Configuration Settings
 */

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Load Composer autoloader if it exists
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Load .env file if it exists
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }
}

// site configuration constants
if (!defined('SITE_NAME')) define('SITE_NAME', getenv('SITE_NAME') ?: 'UbugeniPalace');
if (!defined('SITE_TAGLINE')) define('SITE_TAGLINE', 'Discover Authentic Rwandan Craftsmanship');
if (!defined('SITE_URL')) define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/UbugeniPalace');
if (!defined('SITE_EMAIL')) define('SITE_EMAIL', 'info@ubugenipalace.rw');
if (!defined('SITE_PHONE')) define('SITE_PHONE', '+250 788 123 456');

// Cloudinary configuration
if (!defined('CLOUDINARY_CLOUD_NAME')) define('CLOUDINARY_CLOUD_NAME', getenv('CLOUDINARY_CLOUD_NAME'));
if (!defined('CLOUDINARY_API_KEY')) define('CLOUDINARY_API_KEY', getenv('CLOUDINARY_API_KEY'));
if (!defined('CLOUDINARY_API_SECRET')) define('CLOUDINARY_API_SECRET', getenv('CLOUDINARY_API_SECRET'));
if (!defined('USE_CLOUDINARY')) define('USE_CLOUDINARY', true);

// File upload settings
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', __DIR__ . '/../uploads/');
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
if (!defined('ALLOWED_IMAGE_TYPES')) define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination settings
if (!defined('PRODUCTS_PER_PAGE')) define('PRODUCTS_PER_PAGE', 12);
if (!defined('ARTISANS_PER_PAGE')) define('ARTISANS_PER_PAGE', 8);

// Currency settings
if (!defined('CURRENCY')) define('CURRENCY', 'RWF');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', 'RWF');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Kigali');

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

function isArtisan() {
    return isLoggedIn() && $_SESSION['user_type'] === 'artisan';
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $db;
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = ?", 
        [$_SESSION['user_id']]
    );
    return $user ?: null;
}

function redirectTo($url) {
    header("Location: " . $url);
    exit();
}

function formatPrice($price) {
    return number_format($price, 0, '.', ',') . ' ' . CURRENCY_SYMBOL;
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

function generateOrderNumber() {
    return 'UPAL' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
}

function uploadImage($file, $folder = 'general') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowed_types = ALLOWED_IMAGE_TYPES;
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    // Use Cloudinary if credentials are provided
    if (CLOUDINARY_CLOUD_NAME && CLOUDINARY_API_KEY && CLOUDINARY_API_SECRET) {
        try {
            // Check if Cloudinary class exists (installed via composer)
            if (class_exists('Cloudinary\Cloudinary')) {
                $cloudinary = new Cloudinary\Cloudinary([
                    'cloud' => [
                        'cloud_name' => CLOUDINARY_CLOUD_NAME,
                        'api_key'    => CLOUDINARY_API_KEY,
                        'api_secret' => CLOUDINARY_API_SECRET,
                    ],
                ]);

                $result = $cloudinary->uploadApi()->upload($file['tmp_name'], [
                    'folder' => 'ubugenipalace/' . $folder,
                    'resource_type' => 'image'
                ]);

                return $result['secure_url'];
            }
        } catch (Exception $e) {
            error_log("Cloudinary Upload Error: " . $e->getMessage());
            // Fallback to local upload if Cloudinary fails
        }
    }
    
    $upload_dir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $folder . '/' . $filename;
    }
    
    return false;
}

function getImageUrl($image_path) {
    if (empty($image_path)) {
        return SITE_URL . '/assets/images/icons/user.svg';
    }
    
    // Check if it's already a full URL (like Cloudinary secure_url)
    $is_remote = (strpos($image_path, 'http') === 0 || strpos($image_path, 'https') === 0);

    // Fallback logic if Cloudinary is disabled
    if (!USE_CLOUDINARY && $is_remote) {
        if (strpos($image_path, 'ubugenipalace/categories/') !== false) {
            $filename = basename($image_path);
            $category_name = pathinfo($filename, PATHINFO_FILENAME);
            $mapping = [
                'pottery' => 'products/pottery/pot1.jpeg',
                'baskets' => 'products/baskets/basket1.jpeg',
                'jewelry' => 'products/jewelry/jewery1.jpeg',
                'textiles' => 'products/textiles/textile1.jpeg',
                'home_decor' => 'products/home/home1.jpeg',
                'paintings' => 'products/paints/paint1.jpeg'
            ];
            if (isset($mapping[$category_name])) {
                return SITE_URL . '/assets/images/' . $mapping[$category_name];
            }
        }
        return $image_path;
    }

    if ($is_remote) {
        return $image_path;
    }

    // If Cloudinary is configured, we can construct the URL if the path looks like a Cloudinary public ID
    // or if we want to serve our local assets via Cloudinary (if they've been migrated)
    if (defined('CLOUDINARY_CLOUD_NAME') && CLOUDINARY_CLOUD_NAME && USE_CLOUDINARY) {
        // If the path contains 'res.cloudinary.com', it's already a Cloudinary URL
        if (strpos($image_path, 'res.cloudinary.com') !== false) {
            return $image_path;
        }
    }
    
    // Check if it's an uploaded image (with or without uploads/ prefix)
    if (strpos($image_path, 'uploads/') === 0) {
        return SITE_URL . '/' . $image_path;
    }
    
    // Check if it's a product image or profile image (without uploads/ prefix)
    if (strpos($image_path, 'products-images/') === 0 || 
        strpos($image_path, 'profile-photos/') === 0 || 
        strpos($image_path, 'artisan-photos/') === 0) {
        return SITE_URL . '/uploads/' . $image_path;
    }
    
    // Handle default avatar
    if ($image_path === 'default-avatar.jpg') {
        return SITE_URL . '/assets/images/icons/user.svg';
    }
    
    // Default to local assets folder
    return SITE_URL . '/assets/images/' . $image_path;
}

function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<div class="alert alert-' . $alert['type'] . '">' . $alert['message'] . '</div>';
        unset($_SESSION['alert']);
    }
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    // Rwandan phone number validation
    $pattern = '/^(\+250|250)?[7][0-9]{8}$/';
    return preg_match($pattern, $phone);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Page titles for different sections
function getPageTitle($page = '') {
    $titles = [
        'home' => 'Home - Discover Authentic Rwandan Craftsmanship',
        'artisans' => 'Artisans - Meet Our Talented Creators',
        'products' => 'Products - Handcrafted Treasures',
        'cart' => 'Shopping Cart - Your Selected Items',
        'checkout' => 'Checkout - Complete Your Order',
        'about' => 'About Us - Our Story and Mission',
        'contact' => 'Contact Us - Get in Touch',
        'login' => 'Login - Access Your Account',
        'register' => 'Register - Join Our Community',
        'dashboard' => 'Dashboard - Manage Your Profile',
        'add-product' => 'Add Product - Showcase Your Craftsmanship'
    ];
    
    if (isset($titles[$page])) {
        return $titles[$page] . ' | ' . SITE_NAME;
    }
    
    return SITE_NAME . ' - ' . SITE_TAGLINE;
}

// Navigation menu items
function getNavigationMenu() {
    return [
        'home' => [
            'url' => SITE_URL,
            'title' => 'Ahabanza',
            'title_en' => 'Home'
        ],
        'artisans' => [
            'url' => SITE_URL . '/pages/artisans.php',
            'title' => 'Abageni',
            'title_en' => 'Artisans'
        ],
        'products' => [
            'url' => SITE_URL . '/pages/products.php',
            'title' => 'Ibicuruzwa',
            'title_en' => 'Products'
        ],
        'about' => [
            'url' => SITE_URL . '/pages/about.php',
            'title' => 'Ibyanduye',
            'title_en' => 'About'
        ],
        'contact' => [
            'url' => SITE_URL . '/pages/contact.php',
            'title' => 'Tuvugishe',
            'title_en' => 'Contact'
        ]
    ];
}

// Get categories for navigation
function getCategories() {
    global $db;
    return $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name");
}

// Initialize global variables
$current_user = getCurrentUser();
$navigation_menu = getNavigationMenu();
$categories = getCategories();
?>