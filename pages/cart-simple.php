<?php
require_once '../config/config.php';

// Handle cart operations via traditional POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        // Add item to cart
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        if (isLoggedIn()) {
            // Check if item already exists in cart
            $existing_item = $db->fetchOne(
                "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
                [$_SESSION['user_id'], $product_id]
            );
            
            if ($existing_item) {
                // Update quantity
                $db->execute(
                    "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?",
                    [$quantity, $_SESSION['user_id'], $product_id]
                );
            } else {
                // Add new item
                $db->execute(
                    "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)",
                    [$_SESSION['user_id'], $product_id, $quantity]
                );
            }
            
            showAlert('Item added to cart successfully!', 'success');
        } else {
            showAlert('Please login to add items to cart.', 'error');
        }
        
        // Redirect back to prevent form resubmission
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
        
    } elseif (isset($_POST['update_cart'])) {
        // Update cart quantities
        if (isLoggedIn()) {
            foreach ($_POST['quantities'] as $cart_id => $quantity) {
                $cart_id = (int)$cart_id;
                $quantity = (int)$quantity;
                
                if ($quantity > 0) {
                    $db->execute(
                        "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?",
                        [$quantity, $cart_id, $_SESSION['user_id']]
                    );
                } else {
                    $db->execute(
                        "DELETE FROM cart WHERE id = ? AND user_id = ?",
                        [$cart_id, $_SESSION['user_id']]
                    );
                }
            }
            
            showAlert('Cart updated successfully!', 'success');
        }
        
    } elseif (isset($_POST['remove_item'])) {
        // Remove item from cart
        $cart_id = (int)$_POST['cart_id'];
        
        if (isLoggedIn()) {
            $db->execute(
                "DELETE FROM cart WHERE id = ? AND user_id = ?",
                [$cart_id, $_SESSION['user_id']]
            );
            
            showAlert('Item removed from cart.', 'success');
        }
        
        // Redirect back to prevent form resubmission
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Get cart items
$cart_items = [];
$cart_total = 0;

if (isLoggedIn()) {
    $cart_items = $db->fetchAll("
        SELECT c.*, p.name, p.price, p.main_image, p.stock_quantity,
               u.full_name as artisan_name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        JOIN artisans a ON p.artisan_id = a.id
        JOIN users u ON a.user_id = u.id
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC
    ", [$_SESSION['user_id']]);
    
    // Calculate total
    foreach ($cart_items as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
}

$page_title = getPageTitle('cart');
include '../includes/header.php';
?>

<main class="cart-main">
    <!-- Page Header -->
    <section class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Shopping Cart</h1>
            <p class="page-description">Review and manage your selected items</p>
        </div>
    </section>

    <!-- Cart Content -->
    <section class="cart-content">
        <div class="container">
            <?php displayAlert(); ?>
            
            <?php if (empty($cart_items)): ?>
                <!-- Empty Cart -->
                <div class="empty-cart">
                    <div class="empty-cart-content">
                        <div class="empty-cart-icon">🛒</div>
                        <h2 class="empty-cart-title">Your cart is empty</h2>
                        <p class="empty-cart-description">
                            Looks like you haven't added any items to your cart yet.
                        </p>
                        <a href="products.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Cart Items -->
                <div class="cart-layout">
                    <div class="cart-items">
                        <div class="cart-header">
                            <h2>Cart Items (<?php echo count($cart_items); ?>)</h2>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="update_cart" value="1">
                            
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item">
                                    <div class="item-image">
                                        <img src="<?php echo getImageUrl($item['main_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    
                                    <div class="item-details">
                                        <h3 class="item-name">
                                            <a href="product-details.php?id=<?php echo $item['product_id']; ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                        </h3>
                                        <p class="item-artisan">by <?php echo htmlspecialchars($item['artisan_name']); ?></p>
                                        <p class="item-price"><?php echo formatPrice($item['price']); ?></p>
                                    </div>
                                    
                                    <div class="item-quantity">
                                        <label for="quantity_<?php echo $item['id']; ?>">Quantity:</label>
                                        <input type="number" 
                                               id="quantity_<?php echo $item['id']; ?>"
                                               name="quantities[<?php echo $item['id']; ?>]"
                                               value="<?php echo $item['quantity']; ?>"
                                               min="1" 
                                               max="<?php echo $item['stock_quantity']; ?>"
                                               class="quantity-input">
                                    </div>
                                    
                                    <div class="item-total">
                                        <span class="total-amount">
                                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="item-actions">
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="remove_item" value="1">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Remove this item from cart?')">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="cart-actions">
                                <button type="submit" class="btn btn-secondary">Update Cart</button>
                                <a href="products.php" class="btn btn-outline">Continue Shopping</a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3 class="summary-title">Order Summary</h3>
                            
                            <div class="summary-items">
                                <div class="summary-item">
                                    <span>Subtotal:</span>
                                    <span><?php echo formatPrice($cart_total); ?></span>
                                </div>
                                <div class="summary-item">
                                    <span>Shipping:</span>
                                    <span>Free</span>
                                </div>
                                <div class="summary-item">
                                    <span>Tax:</span>
                                    <span><?php echo formatPrice($cart_total * 0.18); ?></span>
                                </div>
                            </div>
                            
                            <div class="summary-total">
                                <span>Total:</span>
                                <span><?php echo formatPrice($cart_total * 1.18); ?></span>
                            </div>
                            
                            <a href="checkout.php" class="btn btn-primary btn-full">
                                Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<style>
.cart-main {
    min-height: 100vh;
    background: var(--bg-secondary);
}

.cart-content {
    padding: var(--spacing-2xl) 0;
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: var(--spacing-xl);
    margin-top: var(--spacing-xl);
}

.cart-items {
    background: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    box-shadow: 0 2px 8px var(--shadow-sm);
}

.cart-header h2 {
    margin-bottom: var(--spacing-lg);
    color: var(--text-primary);
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto auto;
    gap: var(--spacing-md);
    align-items: center;
    padding: var(--spacing-lg) 0;
    border-bottom: 1px solid var(--border-light);
}

.cart-item:last-child {
    border-bottom: none;
}

.item-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: var(--radius-md);
}

.item-name a {
    color: var(--text-primary);
    text-decoration: none;
    font-weight: 600;
}

.item-name a:hover {
    color: var(--primary);
}

.item-artisan {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-top: var(--spacing-xs);
}

.item-price {
    font-weight: 600;
    color: var(--primary);
}

.quantity-input {
    width: 80px;
    padding: var(--spacing-sm);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    text-align: center;
}

.cart-actions {
    margin-top: var(--spacing-xl);
    display: flex;
    gap: var(--spacing-md);
}

.cart-summary {
    position: sticky;
    top: var(--spacing-xl);
}

.summary-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    box-shadow: 0 2px 8px var(--shadow-sm);
}

.summary-title {
    margin-bottom: var(--spacing-lg);
    color: var(--text-primary);
}

.summary-items {
    margin-bottom: var(--spacing-lg);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-sm) 0;
    color: var(--text-secondary);
}

.summary-total {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-md) 0;
    border-top: 2px solid var(--border);
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--text-primary);
}

.empty-cart {
    text-align: center;
    padding: var(--spacing-3xl) 0;
}

.empty-cart-icon {
    font-size: 4rem;
    margin-bottom: var(--spacing-lg);
}

.empty-cart-title {
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
}

.empty-cart-description {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xl);
}

@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: 1fr;
        gap: var(--spacing-sm);
        text-align: center;
    }
    
    .item-image {
        justify-self: center;
    }
    
    .cart-actions {
        flex-direction: column;
    }
}
</style>

<?php include '../includes/footer.php'; ?> 