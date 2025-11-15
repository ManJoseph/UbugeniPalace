<?php
require_once '../config/config.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'toggle':
        handleToggleWishlist($input);
        break;
    case 'add':
        handleAddToWishlist($input);
        break;
    case 'remove':
        handleRemoveFromWishlist($input);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleToggleWishlist($input) {
    global $db;
    
    $product_id = (int)($input['product_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        return;
    }
    
    // Check if product exists and is active
    $product = $db->fetchOne(
        "SELECT id FROM products WHERE id = ? AND status = 'active'",
        [$product_id]
    );
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    // Check if already in wishlist
    $existing_item = $db->fetchOne(
        "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?",
        [$user_id, $product_id]
    );
    
    if ($existing_item) {
        // Remove from wishlist
        $db->execute(
            "DELETE FROM wishlist WHERE id = ?",
            [$existing_item['id']]
        );
        $in_wishlist = false;
    } else {
        // Add to wishlist
        $db->execute(
            "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)",
            [$user_id, $product_id]
        );
        $in_wishlist = true;
    }
    
    // Get updated wishlist count
    $wishlist_count = $db->rowCount("SELECT COUNT(*) FROM wishlist WHERE user_id = ?", [$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => $in_wishlist ? 'Added to wishlist' : 'Removed from wishlist',
        'in_wishlist' => $in_wishlist,
        'count' => $wishlist_count
    ]);
}

function handleAddToWishlist($input) {
    global $db;
    
    $product_id = (int)($input['product_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        return;
    }
    
    // Check if product exists and is active
    $product = $db->fetchOne(
        "SELECT id FROM products WHERE id = ? AND status = 'active'",
        [$product_id]
    );
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    // Check if already in wishlist
    $existing_item = $db->fetchOne(
        "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?",
        [$user_id, $product_id]
    );
    
    if ($existing_item) {
        echo json_encode(['success' => false, 'message' => 'Product already in wishlist']);
        return;
    }
    
    $db->execute(
        "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)",
        [$user_id, $product_id]
    );
    
    // Get updated wishlist count
    $wishlist_count = $db->rowCount("SELECT COUNT(*) FROM wishlist WHERE user_id = ?", [$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Added to wishlist',
        'count' => $wishlist_count
    ]);
}

function handleRemoveFromWishlist($input) {
    global $db;
    
    $product_id = (int)($input['product_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        return;
    }
    
    $db->execute(
        "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?",
        [$user_id, $product_id]
    );
    
    // Get updated wishlist count
    $wishlist_count = $db->rowCount("SELECT COUNT(*) FROM wishlist WHERE user_id = ?", [$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Removed from wishlist',
        'count' => $wishlist_count
    ]);
}
?> 