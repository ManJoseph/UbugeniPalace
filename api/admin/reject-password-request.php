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
$reason = sanitizeInput($input['reason'] ?? '');

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
    
    // Update password reset request status
    $db->execute(
        "UPDATE password_reset_requests 
         SET status = 'rejected', 
             admin_notes = ?, 
             processed_by = ?, 
             processed_at = NOW() 
         WHERE id = ?",
        [$reason, $_SESSION['user_id'], $request_id]
    );
    
    // Create notification for the user
    $rejection_message = "Your password reset request has been rejected.";
    if (!empty($reason)) {
        $rejection_message .= " Reason: " . $reason;
    }
    
    $db->execute(
        "INSERT INTO admin_notifications (type, user_id, title, message, data, is_read, created_at) 
         VALUES (?, ?, ?, ?, ?, 0, NOW())",
        [
            'password_reset_rejected',
            $request['user_id'],
            'Password Reset Rejected',
            $rejection_message,
            json_encode([
                'reason' => $reason,
                'rejected_by' => $_SESSION['full_name'],
                'rejected_at' => date('Y-m-d H:i:s')
            ])
        ]
    );
    
    // Send email notification to user (if email functionality is available)
    sendPasswordRejectionEmail($request['email'], $request['full_name'], $reason);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password reset request rejected successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Password reset rejection error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing the request']);
}

/**
 * Send password rejection email to user
 */
function sendPasswordRejectionEmail($email, $name, $reason) {
    // This is a placeholder for email functionality
    // In a real implementation, you would use a proper email library like PHPMailer
    
    $subject = 'Password Reset Request Rejected - UbugeniPalace';
    
    $reason_text = !empty($reason) ? "Reason: {$reason}" : "No specific reason provided.";
    
    $message = "
    Dear {$name},
    
    Your password reset request has been rejected by our administrator.
    
    {$reason_text}
    
    If you believe this was an error or need assistance, please contact our support team:
    - Email: support@ubugenipalace.rw
    - Phone: +250 788 123 456
    
    You can also try submitting a new password reset request with additional information to help us verify your identity.
    
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
    error_log("Password rejection email would be sent to {$email}: {$message}");
}
?> 