<?php
require_once '../../config/config.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$request_id = $input['request_id'] ?? null;

if (!$request_id) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit;
}

try {
    // Get the password reset request
    $request = $db->fetchOne(
        "SELECT prr.*, u.full_name, u.email, u.user_type 
         FROM password_reset_requests prr 
         JOIN users u ON prr.user_id = u.id 
         WHERE prr.id = ? AND prr.status = 'pending'",
        [$request_id]
    );
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Password reset request not found or already processed']);
        exit;
    }
    
    // Generate a new secure password
    $new_password = generateSecurePassword();
    $hashed_password = hashPassword($new_password);
    
    // Update user's password
    $db->execute(
        "UPDATE users SET password = ? WHERE id = ?",
        [$hashed_password, $request['user_id']]
    );
    
    // Update password reset request status
    $db->execute(
        "UPDATE password_reset_requests 
         SET status = 'approved', 
             new_password = ?, 
             processed_by = ?, 
             processed_at = NOW() 
         WHERE id = ?",
        [$new_password, $_SESSION['user_id'], $request_id]
    );
    
    // Create notification for the user
    $db->execute(
        "INSERT INTO admin_notifications (type, user_id, title, message, data, is_read, created_at) 
         VALUES (?, ?, ?, ?, ?, 0, NOW())",
        [
            'password_reset_approved',
            $request['user_id'],
            'Password Reset Approved',
            "Your password reset request has been approved. Your new password is: {$new_password}",
            json_encode([
                'new_password' => $new_password,
                'approved_by' => $_SESSION['full_name'],
                'approved_at' => date('Y-m-d H:i:s')
            ])
        ]
    );
    
    // Send email notification to user (if email functionality is available)
    sendPasswordResetEmail($request['email'], $request['full_name'], $new_password);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password reset request approved successfully',
        'new_password' => $new_password
    ]);
    
} catch (Exception $e) {
    error_log('Password reset approval error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing the request']);
}

/**
 * Generate a secure random password
 */
function generateSecurePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    
    // Ensure at least one character from each category
    $password .= $chars[rand(0, 25)]; // lowercase
    $password .= $chars[rand(26, 51)]; // uppercase
    $password .= $chars[rand(52, 61)]; // number
    $password .= $chars[rand(62, 69)]; // special character
    
    // Fill the rest randomly
    for ($i = 4; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    // Shuffle the password
    return str_shuffle($password);
}

/**
 * Send password reset email to user
 */
function sendPasswordResetEmail($email, $name, $new_password) {
    // This is a placeholder for email functionality
    // In a real implementation, you would use a proper email library like PHPMailer
    
    $subject = 'Password Reset Approved - UbugeniPalace';
    $message = "
    Dear {$name},
    
    Your password reset request has been approved by our administrator.
    
    Your new password is: {$new_password}
    
    Please log in with this new password and change it to something you can remember.
    
    For security reasons, we recommend:
    - Changing your password immediately after logging in
    - Using a strong password with a mix of letters, numbers, and symbols
    - Not sharing your password with anyone
    
    If you did not request this password reset, please contact our support team immediately.
    
    Best regards,
    The UbugeniPalace Team
    
    ---
    This is an automated message. Please do not reply to this email.
    ";
    
    $headers = 'From: noreply@ubugenipalace.rw' . "\r\n" .
               'Reply-To: support@ubugenipalace.rw' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    // Uncomment the line below when email functionality is properly configured
    // mail($email, $subject, $message, $headers);
    
    // For now, just log the email content
    error_log("Password reset email would be sent to {$email}: {$message}");
}
?> 