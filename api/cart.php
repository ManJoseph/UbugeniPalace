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
    case 'add':
        handleAddToCart($input);
        break;
    case 'remove':
        handleRemoveFromCart($input);
        break;
    case 'update':
        handleUpdateCart($input);
        break;
    case 'clear':
        handleClearCart();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleAddToCart($input) {
    global $db;
    
    $product_id = (int)($input['product_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 1);
    $user_id = $_SESSION['user_id'];
    
    if (!$product_id || $quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        return;
    }
    
    // Check if product exists and is active
    $product = $db->fetchOne(
        "SELECT id, price, stock_quantity FROM products WHERE id = ? AND status = 'active'",
        [$product_id]
    );
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        return;
    }
    
    // Check if item already in cart
    $existing_item = $db->fetchOne(
        "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?",
        [$user_id, $product_id]
    );
    
    if ($existing_item) {
        // Update quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        if ($new_quantity > $product['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            return;
        }
        
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
    
    // Get updated cart count
    $cart_count = $db->rowCount("SELECT COUNT(*) FROM cart WHERE user_id = ?", [$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
        'cart_count' => $cart_count
    ]);
}

function handleRemoveFromCart($input) {
    global $db;
    
    $product_id = (int)($input['product_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        return;
    }
    
    $db->execute(
        "DELETE FROM cart WHERE user_id = ? AND product_id = ?",
        [$user_id, $product_id]
    );
    
    // Get updated cart count
    $cart_count = $db->rowCount("SELECT COUNT(*) FROM cart WHERE user_id = ?", [$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Product removed from cart',
        'cart_count' => $cart_count
    ]);
}

function handleUpdateCart($input) {
    global $db;
    
    $product_id = (int)($input['product_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 1);
    $user_id = $_SESSION['user_id'];
    
    if (!$product_id || $quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        return;
    }
    
    // Check if product exists and is active
    $product = $db->fetchOne(
        "SELECT id, stock_quantity FROM products WHERE id = ? AND status = 'active'",
        [$product_id]
    );
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        return;
    }
    
    $db->execute(
        "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?",
        [$quantity, $user_id, $product_id]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated'
    ]);
}

function handleClearCart() {
    global $db;
    
    $user_id = $_SESSION['user_id'];
    
    $db->execute("DELETE FROM cart WHERE user_id = ?", [$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared',
        'cart_count' => 0
    ]);
}
?> 