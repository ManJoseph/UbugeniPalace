<?php
require_once 'config/config.php';

// Get featured products and artisans
$featured_products = $db->fetchAll("
    SELECT p.*, c.name as category_name, u.full_name as artisan_name, a.rating as artisan_rating
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN artisans a ON p.artisan_id = a.id 
    JOIN users u ON a.user_id = u.id 
    WHERE p.is_featured = 1 AND p.status = 'active' 
    ORDER BY p.created_at DESC 
    LIMIT 8
");

$featured_artisans = $db->fetchAll("
    SELECT a.*, u.full_name, u.profile_image, u.email,
           COUNT(p.id) as total_products
    FROM artisans a 
    JOIN users u ON a.user_id = u.id 
    LEFT JOIN products p ON a.id = p.artisan_id AND p.status = 'active'
    WHERE a.is_featured = 1 AND u.is_active = 1
    GROUP BY a.id
    ORDER BY a.rating DESC, a.total_reviews DESC
    LIMIT 6
");

$page_title = getPageTitle('home');

// Include the header which contains the complete HTML structure
include 'includes/header.php';
?>

    <!-- Main Content -->
    <main class="homepage-main">
        <!-- Hero Section -->
        <section class="hero-section" id="home-hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        <span class="kinyarwanda">UbugeniPalace</span>
                        <span class="english">Discover Authentic Rwandan Craftsmanship</span>
                    </h1>
                    <p class="hero-description">
                        Explore unique handcrafted treasures made by talented Rwandan artisans. 
                        From traditional agaseke baskets to contemporary art, discover the beauty 
                        of authentic craftsmanship.
                    </p>
                    <div class="hero-buttons">
                        <a href="pages/products" class="btn btn-primary">
                            Explore Products
                        </a>
                        <a href="pages/artisans" class="btn btn-secondary">
                            Meet Artisans
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/images/heroes/hero-main.jpg" alt="Rwandan Artisan at Work" class="hero-img">
                </div>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $db->rowCount("SELECT COUNT(*) FROM users WHERE user_type = 'artisan' AND is_active = 1"); ?>+</span>
                    <span class="stat-label">Talented Artisans</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $db->rowCount("SELECT COUNT(*) FROM products WHERE status = 'active'"); ?>+</span>
                    <span class="stat-label">Unique Products</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($categories); ?></span>
                    <span class="stat-label">Craft Categories</span>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories-section">
            <div class="container">
                <header class="section-header">
                    <h2 class="section-title">Shop by Category</h2>
                    <p class="section-description">Explore our diverse collection of traditional and contemporary crafts</p>
                </header>
                
                <div class="categories-grid">
                    <?php 
                    // Define category Cloudinary URLs
                    $category_images = [
                        'Pottery' => 'https://res.cloudinary.com/dncja0ipr/image/upload/v1772635998/ubugenipalace/categories/pottery.jpg',
                        'Baskets' => 'https://res.cloudinary.com/dncja0ipr/image/upload/v1772635999/ubugenipalace/categories/baskets.jpg', 
                        'Jewelry' => 'https://res.cloudinary.com/dncja0ipr/image/upload/v1772636000/ubugenipalace/categories/jewelry.jpg',
                        'Textiles' => 'https://res.cloudinary.com/dncja0ipr/image/upload/v1772636001/ubugenipalace/categories/textiles.jpg',
                        'Home Decor' => 'https://res.cloudinary.com/dncja0ipr/image/upload/v1772636002/ubugenipalace/categories/home_decor.jpg',
                        'Paintings' => 'https://res.cloudinary.com/dncja0ipr/image/upload/v1772636003/ubugenipalace/categories/paintings.jpg'
                    ];
                    
                    foreach ($categories as $category): 
                        // Get the representative image for this category
                        $category_image = isset($category_images[$category['name']]) 
                            ? $category_images[$category['name']] 
                            : 'products/pottery/pot1.jpeg'; // fallback local image
                    ?>
                    <article class="category-card" data-category="<?php echo $category['id']; ?>">
                        <div class="category-image">
                            <img src="<?php echo getImageUrl($category_image); ?>" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                 loading="lazy">
                            <div class="category-overlay">
                                <div class="category-info">
                                    <h3 class="category-name">
                                        <span class="kinyarwanda"><?php echo htmlspecialchars($category['name_kinyarwanda']); ?></span>
                                        <span class="english"><?php echo htmlspecialchars($category['name']); ?></span>
                                    </h3>
                                    <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                                    <a href="pages/products?category=<?php echo $category['id']; ?>" class="category-link">
                                        Shop Now <span class="arrow">→</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="featured-products-section">
            <div class="container">
                <header class="section-header">
                    <h2 class="section-title">Featured Products</h2>
                    <p class="section-description">Handpicked treasures from our most talented artisans</p>
                </header>
                
                <div class="products-grid">
                    <?php foreach ($featured_products as $product): ?>
                    <article class="product-card" data-product="<?php echo $product['id']; ?>">
                        <div class="product-image">
                            <img src="<?php echo getImageUrl($product['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 loading="lazy">
                            <div class="product-badges">
                                <span class="badge featured">Featured</span>
                                <?php if ($product['discount_price']): ?>
                                <span class="badge sale">Sale</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="pages/product-details?id=<?php echo $product['id']; ?>" class="btn-icon view">
                                    <span>View Details</span>
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <h3 class="product-name">
                                <a href="pages/product-details?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <div class="product-artisan">
                                by <a href="pages/artisan-profile?id=<?php echo $product['artisan_id']; ?>">
                                    <?php echo htmlspecialchars($product['artisan_name']); ?>
                                </a>
                                <div class="artisan-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= $product['artisan_rating'] ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            </div>
                            <div class="product-price">
                                <?php if ($product['discount_price']): ?>
                                    <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                                    <span class="price-current"><?php echo formatPrice($product['discount_price']); ?></span>
                                <?php else: ?>
                                    <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary add-to-cart" data-product="<?php echo $product['id']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                
                <div class="section-footer">
                    <a href="pages/products" class="btn btn-outline">View All Products</a>
                </div>
            </div>
        </section>

        <!-- Featured Artisans Section -->
        <section class="featured-artisans-section">
            <div class="container">
                <header class="section-header">
                    <h2 class="section-title">Meet Our Artisans</h2>
                    <p class="section-description">Discover the talented creators behind these beautiful crafts</p>
                </header>
                
                <div class="artisans-grid">
                    <?php foreach ($featured_artisans as $artisan): ?>
                    <article class="artisan-card" data-artisan="<?php echo $artisan['id']; ?>">
                        <div class="artisan-image">
                            <img src="<?php echo getImageUrl($artisan['profile_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($artisan['full_name']); ?>" 
                                 loading="lazy">
                            <div class="artisan-overlay">
                                <a href="pages/artisan-profile?id=<?php echo $artisan['id']; ?>" class="view-profile">
                                    View Profile
                                </a>
                            </div>
                        </div>
                        <div class="artisan-info">
                            <h3 class="artisan-name">
                                <a href="pages/artisan-profile?id=<?php echo $artisan['id']; ?>">
                                    <?php echo htmlspecialchars($artisan['full_name']); ?>
                                </a>
                            </h3>
                            <div class="artisan-specialization"><?php echo htmlspecialchars($artisan['specialization']); ?></div>
                            <div class="artisan-location"><?php echo htmlspecialchars($artisan['location']); ?></div>
                            <div class="artisan-stats">
                                <div class="stat">
                                    <span class="stat-value"><?php echo $artisan['total_products']; ?></span>
                                    <span class="stat-label">Products</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value"><?php echo number_format($artisan['rating'], 1); ?></span>
                                    <span class="stat-label">Rating</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value"><?php echo $artisan['experience_years']; ?>+</span>
                                    <span class="stat-label">Years</span>
                                </div>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                
                <div class="section-footer">
                    <a href="pages/artisans" class="btn btn-outline">Meet All Artisans</a>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="about-preview-section">
            <div class="container">
                <div class="about-content">
                    <div class="about-text">
                        <header class="section-header">
                            <h2 class="section-title">Our Story</h2>
                        </header>
                        <p class="about-description">
                            UbugeniPalace celebrates the rich tradition of Rwandan craftsmanship while 
                            empowering local artisans to share their talents with the world. We bridge the gap 
                            between traditional techniques and modern markets, ensuring that these beautiful 
                            art forms continue to thrive.
                        </p>
                        <div class="about-features">
                            <div class="feature">
                                <div class="feature-icon">🎨</div>
                                <div class="feature-text">
                                    <h4>Authentic Craftsmanship</h4>
                                    <p>Every piece tells a story of skill, tradition, and passion</p>
                                </div>
                            </div>
                            <div class="feature">
                                <div class="feature-icon">🤝</div>
                                <div class="feature-text">
                                    <h4>Direct from Artisans</h4>
                                    <p>Support creators directly and learn their inspiring stories</p>
                                </div>
                            </div>
                            <div class="feature">
                                <div class="feature-icon">🌍</div>
                                <div class="feature-text">
                                    <h4>Global Reach</h4>
                                    <p>Bringing Rwandan artistry to customers worldwide</p>
                                </div>
                            </div>
                        </div>
                        <a href="pages/about" class="btn btn-primary">Learn More About Us</a>
                    </div>
                    <div class="about-image">
                        <img src="assets/images/heroes/hero-artisans.jpg" alt="Rwandan Artisans" loading="lazy">
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section">
            <div class="container">
                <div class="newsletter-content">
                    <div class="newsletter-text">
                        <h2 class="newsletter-title">Stay Connected</h2>
                        <p class="newsletter-description">
                            Get updates on new products, featured artisans, and special offers
                        </p>
                    </div>
                    <form class="newsletter-form" id="newsletterForm">
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Enter your email" required>
                            <button type="submit" class="btn btn-primary">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer Section -->
    <footer class="main-footer">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <!-- JavaScript Files -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/cart.js"></script>
    <script src="assets/js/animations.js"></script>
    
    <script>
        // Homepage specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize homepage animations
            initializeHomepage();
            
            // Newsletter form submission
            const newsletterForm = document.getElementById('newsletterForm');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = this.querySelector('input[type="email"]').value;
                    
                    // Add newsletter subscription logic here
                    showNotification('Thank you for subscribing!', 'success');
                    this.reset();
                });
            }
            
            // Category card hover effects
            const categoryCards = document.querySelectorAll('.category-card');
            categoryCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('hover');
                });
                
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('hover');
                });
            });
            
            // Product card interactions
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                const wishlistBtn = card.querySelector('.wishlist');
                if (wishlistBtn) {
                    wishlistBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const productId = this.dataset.product;
                        toggleWishlist(productId);
                    });
                }
            });
            
            // Artisan card interactions
            const artisanCards = document.querySelectorAll('.artisan-card');
            artisanCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    if (!e.target.closest('a')) {
                        const artisanId = this.dataset.artisan;
                        window.location.href = `pages/artisan-profile?id=${artisanId}`;
                    }
                });
            });
        });
        
        function initializeHomepage() {
            // Animate hero elements
            const heroElements = document.querySelectorAll('.hero-title, .hero-description, .hero-buttons');
            heroElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.8s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 200);
            });
            
            // Animate stats on scroll
            const statsSection = document.querySelector('.hero-stats');
            if (statsSection) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            animateStats();
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                observer.observe(statsSection);
            }
        }
        
        function animateStats() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                let currentValue = 0;
                const increment = finalValue / 30;
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    stat.textContent = Math.floor(currentValue) + '+';
                }, 50);
            });
        }
    </script>
</body>
</html>