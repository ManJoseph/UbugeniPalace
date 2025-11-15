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
    // Start transaction
    $db->execute("START TRANSACTION");
    
    // Delete artisan profile if user is an artisan
    if ($user_type === 'artisan') {
        $db->execute("DELETE FROM artisans WHERE user_id = ?", [$user_id]);
    }
    
    // Delete user's cart items
    $db->execute("DELETE FROM cart WHERE user_id = ?", [$user_id]);
    
    // Delete user's wishlist items
    $db->execute("DELETE FROM wishlist WHERE user_id = ?", [$user_id]);
    
    // Delete user's reviews
    $db->execute("DELETE FROM reviews WHERE user_id = ?", [$user_id]);
    
    // Delete user's orders (and order items)
    $orders = $db->fetchAll("SELECT id FROM orders WHERE user_id = ?", [$user_id]);
    foreach ($orders as $order) {
        $db->execute("DELETE FROM order_items WHERE order_id = ?", [$order['id']]);
    }
    $db->execute("DELETE FROM orders WHERE user_id = ?", [$user_id]);
    
    // Delete user's products if they are an artisan
    if ($user_type === 'artisan') {
        $products = $db->fetchAll("SELECT id FROM products WHERE artisan_id = ?", [$user_id]);
        foreach ($products as $product) {
            $db->execute("DELETE FROM order_items WHERE product_id = ?", [$product['id']]);
            $db->execute("DELETE FROM reviews WHERE product_id = ?", [$product['id']]);
            $db->execute("DELETE FROM wishlist WHERE product_id = ?", [$product['id']]);
        }
        $db->execute("DELETE FROM products WHERE artisan_id = ?", [$user_id]);
    }
    
    // Delete password reset requests
    $db->execute("DELETE FROM password_reset_requests WHERE user_id = ?", [$user_id]);
    
    // Delete admin notifications
    $db->execute("DELETE FROM admin_notifications WHERE user_id = ?", [$user_id]);
    
    // Finally, delete the user
    $delete_success = $db->execute("DELETE FROM users WHERE id = ?", [$user_id]);
    
    if (!$delete_success) {
        $db->execute("ROLLBACK");
        echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
        exit;
    }
    
    // Commit transaction
    $db->execute("COMMIT");
    
    // Destroy session
    session_destroy();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Account deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $db->execute("ROLLBACK");
    error_log('Account deletion error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?> 