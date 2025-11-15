<?php
require_once '../config/config.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    redirectTo(SITE_URL . '/pages/products.php');
}

// Get product details
$product = $db->fetchOne("
    SELECT p.*, c.name as category_name, c.name_kinyarwanda as category_name_kinyarwanda,
           u.full_name as artisan_name, u.profile_image as artisan_image,
           a.rating as artisan_rating, a.total_reviews as artisan_total_reviews,
           a.bio as artisan_bio, a.specialization, a.location
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN artisans a ON p.artisan_id = a.id 
    JOIN users u ON a.user_id = u.id 
    WHERE p.id = ? AND p.status = 'active'
", [$product_id]);

if (!$product) {
    redirectTo(SITE_URL . '/pages/products.php');
}

// Increment view count
$db->execute("UPDATE products SET views_count = views_count + 1 WHERE id = ?", [$product_id]);

// Get product reviews
$reviews = $db->fetchAll("
    SELECT r.*, u.full_name, u.profile_image
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 10
", [$product_id]);

// Get related products
$related_products = $db->fetchAll("
    SELECT p.*, c.name as category_name, u.full_name as artisan_name
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN artisans a ON p.artisan_id = a.id 
    JOIN users u ON a.user_id = u.id 
    WHERE p.status = 'active' 
    AND p.id != ? 
    AND (p.category_id = ? OR p.artisan_id = ?)
    ORDER BY p.created_at DESC 
    LIMIT 4
", [$product_id, $product['category_id'], $product['artisan_id']]);

// Parse gallery images
$gallery_images = [];
if ($product['gallery_images']) {
    $gallery_images = json_decode($product['gallery_images'], true) ?: [];
}

// Add main image to gallery if not already included
if (!in_array($product['main_image'], $gallery_images)) {
    array_unshift($gallery_images, $product['main_image']);
}

$page_title = $product['name'] . ' - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="rw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 160)); ?>">
    
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
    <main class="product-details-main">
        <!-- Breadcrumb -->
        <section class="breadcrumb-section">
            <div class="container">
                <nav class="breadcrumb">
                    <a href="<?php echo SITE_URL; ?>" class="breadcrumb-item">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="products.php" class="breadcrumb-item">Products</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="products.php?category=<?php echo $product['category_id']; ?>" class="breadcrumb-item">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></span>
                </nav>
            </div>
        </section>

        <!-- Product Details Section -->
        <section class="product-details-section">
            <div class="container">
                <div class="product-details-layout">
                    <!-- Product Images -->
                    <div class="product-images">
                        <div class="main-image-container">
                            <img src="<?php echo getImageUrl($product['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="main-image" id="mainImage">
                            <div class="image-zoom-overlay" id="zoomOverlay"></div>
                        </div>
                        
                        <?php if (count($gallery_images) > 1): ?>
                        <div class="image-thumbnails">
                            <?php foreach ($gallery_images as $index => $image): ?>
                            <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 data-image="<?php echo getImageUrl($image); ?>">
                                <img src="<?php echo getImageUrl($image); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Info -->
                    <div class="product-info">
                        <div class="product-header">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                            <?php if ($product['name_kinyarwanda']): ?>
                            <h2 class="product-title-kinyarwanda"><?php echo htmlspecialchars($product['name_kinyarwanda']); ?></h2>
                            <?php endif; ?>
                        </div>

                        <div class="product-artisan">
                            <div class="artisan-info">
                                <img src="<?php echo getImageUrl($product['artisan_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['artisan_name']); ?>" 
                                     class="artisan-avatar">
                                <div class="artisan-details">
                                    <h3 class="artisan-name">
                                        <a href="artisan-profile.php?id=<?php echo $product['artisan_id']; ?>">
                                            <?php echo htmlspecialchars($product['artisan_name']); ?>
                                        </a>
                                    </h3>
                                    <div class="artisan-rating">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $product['artisan_rating'] ? 'filled' : ''; ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-text">
                                            <?php echo number_format($product['artisan_rating'], 1); ?> 
                                            (<?php echo $product['artisan_total_reviews']; ?> reviews)
                                        </span>
                                    </div>
                                    <div class="artisan-specialization"><?php echo htmlspecialchars($product['specialization']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="product-price-section">
                            <?php if ($product['discount_price']): ?>
                            <div class="price-info">
                                <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                                <span class="price-current"><?php echo formatPrice($product['discount_price']); ?></span>
                                <span class="discount-badge">
                                    <?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>% OFF
                                </span>
                            </div>
                            <?php else: ?>
                            <div class="price-info">
                                <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="product-description">
                            <h3 class="description-title">Description</h3>
                            <div class="description-content">
                                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            </div>
                        </div>

                        <?php if ($product['materials'] || $product['dimensions'] || $product['weight']): ?>
                        <div class="product-specifications">
                            <h3 class="specifications-title">Specifications</h3>
                            <div class="specifications-grid">
                                <?php if ($product['materials']): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Materials:</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['materials']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['dimensions']): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Dimensions:</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['dimensions']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['weight']): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Weight:</span>
                                    <span class="spec-value"><?php echo $product['weight']; ?> kg</span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product['colors']): ?>
                                <div class="spec-item">
                                    <span class="spec-label">Colors:</span>
                                    <span class="spec-value"><?php echo htmlspecialchars($product['colors']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="product-actions">
                            <div class="quantity-selector">
                                <label for="quantity" class="quantity-label">Quantity:</label>
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                           max="<?php echo $product['stock_quantity']; ?>" class="quantity-input">
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                                </div>
                                <span class="stock-info">
                                    <?php echo $product['stock_quantity']; ?> in stock
                                </span>
                            </div>

                            <div class="action-buttons">
                                <button class="btn btn-primary btn-large add-to-cart-btn" 
                                        data-product="<?php echo $product['id']; ?>">
                                    <span class="btn-icon">🛒</span>
                                    Add to Cart
                                </button>
                                
                                <button class="btn btn-outline btn-large wishlist-btn" 
                                        data-product="<?php echo $product['id']; ?>">
                                    <span class="btn-icon">❤️</span>
                                    Add to Wishlist
                                </button>
                            </div>

                            <?php if ($product['is_custom_order']): ?>
                            <div class="custom-order-info">
                                <p class="custom-order-text">
                                    💡 This item is available for custom orders. 
                                    <a href="contact.php?subject=custom_order&product=<?php echo $product['id']; ?>" class="custom-order-link">
                                        Contact the artisan for custom requests
                                    </a>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reviews Section -->
        <?php if (!empty($reviews)): ?>
        <section class="reviews-section">
            <div class="container">
                <div class="reviews-header">
                    <h2 class="reviews-title">Customer Reviews</h2>
                    <div class="reviews-summary">
                        <div class="average-rating">
                            <?php 
                            $avg_rating = array_sum(array_column($reviews, 'rating')) / count($reviews);
                            ?>
                            <span class="rating-number"><?php echo number_format($avg_rating, 1); ?></span>
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $avg_rating ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="total-reviews"><?php echo count($reviews); ?> reviews</span>
                        </div>
                    </div>
                </div>

                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <img src="<?php echo getImageUrl($review['profile_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($review['full_name']); ?>" 
                                     class="reviewer-avatar">
                                <div class="reviewer-details">
                                    <h4 class="reviewer-name"><?php echo htmlspecialchars($review['full_name']); ?></h4>
                                    <div class="review-rating">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="review-date"><?php echo formatDate($review['created_at']); ?></div>
                        </div>
                        <div class="review-content">
                            <p class="review-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <section class="related-products-section">
            <div class="container">
                <header class="section-header">
                    <h2 class="section-title">You Might Also Like</h2>
                    <p class="section-description">Discover more unique pieces from this artisan and category</p>
                </header>
                
                <div class="products-grid">
                    <?php foreach ($related_products as $related_product): ?>
                    <article class="product-card" data-product="<?php echo $related_product['id']; ?>">
                        <div class="product-image">
                            <img src="<?php echo getImageUrl('products/' . $related_product['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($related_product['name']); ?>" 
                                 loading="lazy">
                            <div class="product-badges">
                                <?php if ($related_product['is_featured']): ?>
                                <span class="badge featured">Featured</span>
                                <?php endif; ?>
                                <?php if ($related_product['discount_price']): ?>
                                <span class="badge sale">Sale</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="product-details.php?id=<?php echo $related_product['id']; ?>" class="btn-icon view">
                                    <span>View Details</span>
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($related_product['category_name']); ?></div>
                            <h3 class="product-name">
                                <a href="product-details.php?id=<?php echo $related_product['id']; ?>">
                                    <?php echo htmlspecialchars($related_product['name']); ?>
                                </a>
                            </h3>
                            <div class="product-artisan">
                                by <a href="artisan-profile.php?id=<?php echo $related_product['artisan_id']; ?>">
                                    <?php echo htmlspecialchars($related_product['artisan_name']); ?>
                                </a>
                            </div>
                            <div class="product-price">
                                <?php if ($related_product['discount_price']): ?>
                                <span class="price-original"><?php echo formatPrice($related_product['price']); ?></span>
                                <span class="price-current"><?php echo formatPrice($related_product['discount_price']); ?></span>
                                <?php else: ?>
                                <span class="price-current"><?php echo formatPrice($related_product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary add-to-cart" data-product="<?php echo $related_product['id']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Image gallery functionality
            const mainImage = document.getElementById('mainImage');
            const thumbnails = document.querySelectorAll('.thumbnail-item');
            
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    const imageSrc = this.dataset.image;
                    mainImage.src = imageSrc;
                    
                    // Update active thumbnail
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Image zoom functionality
            const mainImageContainer = document.querySelector('.main-image-container');
            const zoomOverlay = document.getElementById('zoomOverlay');
            
            if (mainImageContainer && zoomOverlay) {
                mainImageContainer.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const xPercent = (x / rect.width) * 100;
                    const yPercent = (y / rect.height) * 100;
                    
                    mainImage.style.transformOrigin = `${xPercent}% ${yPercent}%`;
                    mainImage.style.transform = 'scale(1.5)';
                    zoomOverlay.style.display = 'block';
                });
                
                mainImageContainer.addEventListener('mouseleave', function() {
                    mainImage.style.transform = 'scale(1)';
                    zoomOverlay.style.display = 'none';
                });
            }

            // Product actions
            const addToCartBtn = document.querySelector('.add-to-cart-btn');
            const wishlistBtn = document.querySelector('.wishlist-btn');
            
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function() {
                    const productId = this.dataset.product;
                    const quantity = parseInt(document.getElementById('quantity').value);
                    addToCart(productId, quantity);
                });
            }
            
            if (wishlistBtn) {
                wishlistBtn.addEventListener('click', function() {
                    const productId = this.dataset.product;
                    toggleWishlist(productId);
                });
            }

            // Related products interactions
            const relatedProductCards = document.querySelectorAll('.related-products-section .product-card');
            relatedProductCards.forEach(card => {
                const wishlistBtn = card.querySelector('.wishlist');
                if (wishlistBtn) {
                    wishlistBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const productId = this.dataset.product;
                        toggleWishlist(productId);
                    });
                }

                const addToCartBtn = card.querySelector('.add-to-cart');
                if (addToCartBtn) {
                    addToCartBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const productId = this.dataset.product;
                        addToCart(productId, 1);
                    });
                }
            });
        });

        function changeQuantity(delta) {
            const quantityInput = document.getElementById('quantity');
            const currentValue = parseInt(quantityInput.value);
            const newValue = Math.max(1, currentValue + delta);
            const maxValue = parseInt(quantityInput.getAttribute('max'));
            
            if (newValue <= maxValue) {
                quantityInput.value = newValue;
            }
        }

        function addToCart(productId, quantity) {
            if (!<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
                window.location.href = 'login.php';
                return;
            }

            fetch('../api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                    showNotification('Product added to cart!', 'success');
                } else {
                    showNotification(data.message || 'Failed to add to cart', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to add to cart', 'error');
            });
        }

        function toggleWishlist(productId) {
            if (!<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
                window.location.href = 'login.php';
                return;
            }

            fetch('../api/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'toggle',
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const wishlistBtn = document.querySelector(`[data-product="${productId}"] .wishlist, .wishlist-btn[data-product="${productId}"]`);
                    if (data.in_wishlist) {
                        wishlistBtn.classList.add('active');
                        showNotification('Added to wishlist!', 'success');
                    } else {
                        wishlistBtn.classList.remove('active');
                        showNotification('Removed from wishlist', 'info');
                    }
                    updateWishlistCount(data.count);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function updateCartCount(count) {
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = count;
            }
        }

        function updateWishlistCount(count) {
            const wishlistCount = document.getElementById('wishlistCount');
            if (wishlistCount) {
                wishlistCount.textContent = count;
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
