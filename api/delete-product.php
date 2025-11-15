<?php
require_once '../config/config.php';

// Check if user is logged in and is an artisan
if (!isLoggedIn() || !isArtisan()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$product_id = intval($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    // Get artisan profile
    $artisan = $db->fetchOne("SELECT * FROM artisans WHERE user_id = ?", [$_SESSION['user_id']]);
    
    if (!$artisan) {
        echo json_encode(['success' => false, 'message' => 'Artisan profile not found']);
        exit;
    }
    
    // Check if product belongs to this artisan
    $product = $db->fetchOne("SELECT * FROM products WHERE id = ? AND artisan_id = ?", [$product_id, $artisan['id']]);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found or you do not have permission to delete it']);
        exit;
    }
    
    // Soft delete by setting status to 'deleted'
    $result = $db->execute("UPDATE products SET status = 'deleted' WHERE id = ?", [$product_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
    }
    
} catch (Exception $e) {
    error_log('Error deleting product: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the product']);
}
?> 