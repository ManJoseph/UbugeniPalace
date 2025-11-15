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

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

try {
    // Get form data
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $specialization = sanitizeInput($_POST['specialization'] ?? '');
    
    // Validation
    if (empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'Full name is required']);
        exit;
    }
    
    if (!empty($phone) && !validatePhone($phone)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number']);
        exit;
    }
    
    // Handle profile image upload
    $profile_image_path = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['profile_image'], 'profile-photos');
        
        if ($upload_result === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image. Please check file size and format.']);
            exit;
        }
        
        $profile_image_path = $upload_result;
    }
    
    // Update user information
    $update_fields = ['full_name = ?'];
    $update_params = [$full_name];
    
    if (!empty($phone)) {
        $update_fields[] = 'phone = ?';
        $update_params[] = $phone;
    }
    
    if ($profile_image_path) {
        $update_fields[] = 'profile_image = ?';
        $update_params[] = $profile_image_path;
    }
    
    $update_params[] = $user_id;
    
    $update_success = $db->execute(
        "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?",
        $update_params
    );
    
    if (!$update_success) {
        echo json_encode(['success' => false, 'message' => 'Failed to update user information']);
        exit;
    }
    
    // Update artisan profile if user is an artisan
    if ($user_type === 'artisan') {
        $artisan_update_fields = [];
        $artisan_update_params = [];
        
        if (!empty($bio)) {
            $artisan_update_fields[] = 'bio = ?';
            $artisan_update_params[] = $bio;
        }
        
        if (!empty($location)) {
            $artisan_update_fields[] = 'location = ?';
            $artisan_update_params[] = $location;
        }
        
        if (!empty($specialization)) {
            $artisan_update_fields[] = 'specialization = ?';
            $artisan_update_params[] = $specialization;
        }
        
        if (!empty($artisan_update_fields)) {
            $artisan_update_params[] = $user_id;
            
            $artisan_update_success = $db->execute(
                "UPDATE artisans SET " . implode(', ', $artisan_update_fields) . " WHERE user_id = ?",
                $artisan_update_params
            );
            
            if (!$artisan_update_success) {
                echo json_encode(['success' => false, 'message' => 'Failed to update artisan profile']);
                exit;
            }
        }
    }
    
    // Update session data
    $_SESSION['full_name'] = $full_name;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully!',
        'data' => [
            'full_name' => $full_name,
            'phone' => $phone,
            'profile_image' => $profile_image_path,
            'bio' => $bio,
            'location' => $location,
            'specialization' => $specialization
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Profile update error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?> 