<?php
require_once '../config/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get cart items
$cart_items = $db->fetchAll("
    SELECT c.*, p.name, p.price, p.discount_price, p.main_image, p.stock_quantity,
           u.full_name as artisan_name
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    JOIN artisans a ON p.artisan_id = a.id 
    JOIN users u ON a.user_id = u.id 
    WHERE c.user_id = ? AND p.status = 'active'
    ORDER BY c.added_at DESC
", [$_SESSION['user_id']]);

// Calculate totals
$subtotal = 0;
$total_items = 0;

foreach ($cart_items as $item) {
    $price = $item['discount_price'] ?: $item['price'];
    $subtotal += $price * $item['quantity'];
    $total_items += $item['quantity'];
}

$shipping_fee = 0; // Free shipping for now
$tax_rate = 0.18; // 18% VAT
$tax_amount = $subtotal * $tax_rate;
$total = $subtotal + $shipping_fee + $tax_amount;

$page_title = getPageTitle('cart');
?>
<!DOCTYPE html>
<html lang="rw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="Review your shopping cart and proceed to checkout.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo/favicon.png">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
</head>
<body>
    <!-- Header Section -->
    <header class="main-header">
        <?php include '../includes/nav.php'; ?>
    </header>

    <!-- Main Content -->
    <main class="cart-main">
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <div class="page-header-content">
                    <h1 class="page-title">Shopping Cart</h1>
                    <p class="page-description">Review your items and proceed to checkout</p>
                </div>
            </div>
        </section>

        <!-- Cart Section -->
        <section class="cart-section">
            <div class="container">
                <?php if (empty($cart_items)): ?>
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
                <div class="cart-layout">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <div class="cart-header">
                            <h2 class="cart-title">Cart Items (<?php echo $total_items; ?>)</h2>
                            <button class="clear-cart-btn" onclick="clearCart()">Clear Cart</button>
                        </div>

                        <div class="cart-items-list">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-product="<?php echo $item['product_id']; ?>">
                                <div class="item-image">
                                    <img src="<?php echo getImageUrl('products/' . $item['main_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                
                                <div class="item-details">
                                    <h3 class="item-name">
                                        <a href="product-details.php?id=<?php echo $item['product_id']; ?>">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </h3>
                                    <div class="item-artisan">by <?php echo htmlspecialchars($item['artisan_name']); ?></div>
                                    
                                    <div class="item-price">
                                        <?php if ($item['discount_price']): ?>
                                        <span class="price-original"><?php echo formatPrice($item['price']); ?></span>
                                        <span class="price-current"><?php echo formatPrice($item['discount_price']); ?></span>
                                        <?php else: ?>
                                        <span class="price-current"><?php echo formatPrice($item['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="item-quantity">
                                    <div class="quantity-controls">
                                        <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                        <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock_quantity']; ?>"
                                               onchange="updateQuantity(<?php echo $item['product_id']; ?>, 0, this.value)">
                                        <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                                    </div>
                                    <div class="stock-info">
                                        <?php echo $item['stock_quantity']; ?> in stock
                                    </div>
                                </div>
                                
                                <div class="item-total">
                                    <?php 
                                    $item_price = $item['discount_price'] ?: $item['price'];
                                    $item_total = $item_price * $item['quantity'];
                                    ?>
                                    <span class="total-amount"><?php echo formatPrice($item_total); ?></span>
                                </div>
                                
                                <div class="item-actions">
                                    <button class="remove-item-btn" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                        <span class="remove-icon">×</span>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3 class="summary-title">Order Summary</h3>
                            
                            <div class="summary-items">
                                <div class="summary-item">
                                    <span class="item-label">Subtotal (<?php echo $total_items; ?> items)</span>
                                    <span class="item-value"><?php echo formatPrice($subtotal); ?></span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="item-label">Shipping</span>
                                    <span class="item-value"><?php echo $shipping_fee > 0 ? formatPrice($shipping_fee) : 'Free'; ?></span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="item-label">Tax (18% VAT)</span>
                                    <span class="item-value"><?php echo formatPrice($tax_amount); ?></span>
                                </div>
                            </div>
                            
                            <div class="summary-total">
                                <span class="total-label">Total</span>
                                <span class="total-value"><?php echo formatPrice($total); ?></span>
                            </div>
                            
                            <div class="summary-actions">
                                <a href="checkout.php" class="btn btn-primary btn-full">Proceed to Checkout</a>
                                <a href="products.php" class="btn btn-outline btn-full">Continue Shopping</a>
                            </div>
                            
                            <div class="payment-methods">
                                <h4 class="payment-title">Accepted Payment Methods</h4>
                                <div class="payment-icons">
                                    <span class="payment-icon">💳</span>
                                    <span class="payment-icon">🏦</span>
                                    <span class="payment-icon">📱</span>
                                    <span class="payment-icon">💰</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer Section -->
    <footer class="main-footer">
        <?php include '../includes/footer.php'; ?>
    </footer>

    <!-- JavaScript Files -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/cart.js"></script>
    <script src="../assets/js/animations.js"></script>
    
    <script>
        function updateQuantity(productId, delta, newValue = null) {
            let quantity;
            
            if (newValue !== null) {
                quantity = parseInt(newValue);
            } else {
                const quantityInput = document.querySelector(`[data-product="${productId}"] .quantity-input`);
                const currentValue = parseInt(quantityInput.value);
                quantity = Math.max(1, currentValue + delta);
            }
            
            fetch('../api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh to update totals
                } else {
                    showNotification(data.message || 'Failed to update quantity', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to update quantity', 'error');
            });
        }

        function removeFromCart(productId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                fetch('../api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount(data.cart_count);
                        location.reload(); // Refresh to update totals
                    } else {
                        showNotification(data.message || 'Failed to remove item', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Failed to remove item', 'error');
                });
            }
        }

        function clearCart() {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                fetch('../api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'clear'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount(data.cart_count);
                        location.reload();
                    } else {
                        showNotification(data.message || 'Failed to clear cart', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Failed to clear cart', 'error');
                });
            }
        }

        function updateCartCount(count) {
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = count;
            }
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>
