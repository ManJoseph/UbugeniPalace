<?php
require_once '../config/config.php';

// Set JSON header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is already logged in
if (isLoggedIn()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Already logged in',
        'redirect' => $_SESSION['user_type'] === 'admin' ? SITE_URL . '/admin/' : SITE_URL . '/pages/dashboard.php'
    ]);
    exit;
}

// Get form data
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validation
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit;
}

if (!validateEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

try {
    // Get user from database
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE email = ? AND is_active = 1",
        [$email]
    );
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    
    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store remember token in database
        $db->execute(
            "UPDATE users SET remember_token = ?, remember_expires = ? WHERE id = ?",
            [$token, date('Y-m-d H:i:s', $expires), $user['id']]
        );
        
        // Set cookie
        setcookie('remember_token', $token, $expires, '/', '', true, true);
    }
    
    // Log login activity
    $db->execute(
        "INSERT INTO user_activity (user_id, activity_type, ip_address, user_agent) VALUES (?, ?, ?, ?)",
        [$user['id'], 'login', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
    );
    
    // Return success response
    $redirect_url = $user['user_type'] === 'admin' ? SITE_URL . '/admin/' : SITE_URL . '/pages/dashboard.php';
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $redirect_url,
        'user' => [
            'id' => $user['id'],
            'name' => $user['full_name'],
            'email' => $user['email'],
            'type' => $user['user_type']
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?> 