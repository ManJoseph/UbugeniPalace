<?php
require_once '../config/config.php';

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($min_price > 0) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
}

if ($max_price > 0) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(' AND ', $where_conditions);

// Build sort clause
$sort_clause = match($sort) {
    'price_low' => 'ORDER BY p.price ASC',
    'price_high' => 'ORDER BY p.price DESC',
    'name' => 'ORDER BY p.name ASC',
    'popular' => 'ORDER BY p.views_count DESC',
    default => 'ORDER BY p.created_at DESC'
};

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) as total
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN artisans a ON p.artisan_id = a.id 
    JOIN users u ON a.user_id = u.id 
    WHERE {$where_clause}
";

$total_result = $db->fetchOne($count_query, $params);
$total_products = $total_result['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
$products_query = "
    SELECT p.*, c.name as category_name, u.full_name as artisan_name, a.rating as artisan_rating
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN artisans a ON p.artisan_id = a.id 
    JOIN users u ON a.user_id = u.id 
    WHERE {$where_clause}
    {$sort_clause}
    LIMIT {$per_page} OFFSET {$offset}
";

$products = $db->fetchAll($products_query, $params);

// Get categories for filter
$categories = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");

// Get price range for filter
$price_range = $db->fetchOne("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE status = 'active'");

$page_title = getPageTitle('products');

// Include the header which contains the complete HTML structure
include '../includes/header.php';
?>

    <!-- Main Content -->
    <main class="products-main">
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <div class="page-header-content">
                    <h1 class="page-title">Discover Products</h1>
                    <p class="page-description">Explore unique handmade treasures from talented Rwandan artisans</p>
                    <div class="page-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $total_products; ?></span>
                            <span class="stat-label">Products</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($categories); ?></span>
                            <span class="stat-label">Categories</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="products-section">
            <div class="container">
                <div class="products-layout">
                    <!-- Filters Sidebar -->
                    <aside class="products-filters">
                        <div class="filters-header">
                            <h3 class="filters-title">Filters</h3>
                            <button class="clear-filters" onclick="clearFilters()">Clear All</button>
                        </div>

                        <form class="filters-form" method="GET" action="">
                            <!-- Search -->
                            <div class="filter-group">
                                <label class="filter-label">Search</label>
                                <input type="text" name="search" class="filter-input" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search products...">
                            </div>

                            <!-- Categories -->
                            <div class="filter-group">
                                <label class="filter-label">Categories</label>
                                <div class="filter-options">
                                    <label class="filter-option">
                                        <input type="radio" name="category" value="0" 
                                               <?php echo $category_id === 0 ? 'checked' : ''; ?>>
                                        <span class="option-text">All Categories</span>
                                    </label>
                                    <?php foreach ($categories as $category): ?>
                                    <label class="filter-option">
                                        <input type="radio" name="category" value="<?php echo $category['id']; ?>" 
                                               <?php echo $category_id === $category['id'] ? 'checked' : ''; ?>>
                                        <span class="option-text"><?php echo htmlspecialchars($category['name']); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="filter-group">
                                <label class="filter-label">Price Range</label>
                                <div class="price-range">
                                    <input type="number" name="min_price" class="price-input" 
                                           value="<?php echo $min_price; ?>" 
                                           placeholder="Min" min="0">
                                    <span class="price-separator">-</span>
                                    <input type="number" name="max_price" class="price-input" 
                                           value="<?php echo $max_price; ?>" 
                                           placeholder="Max" min="0">
                                </div>
                            </div>

                            <!-- Sort -->
                            <div class="filter-group">
                                <label class="filter-label">Sort By</label>
                                <select name="sort" class="filter-select">
                                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                                    <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-full">Apply Filters</button>
                        </form>
                    </aside>

                    <!-- Products Grid -->
                    <div class="products-content">
                        <!-- Results Header -->
                        <div class="results-header">
                            <div class="results-info">
                                <span class="results-count"><?php echo $total_products; ?> products found</span>
                                <?php if (!empty($search) || $category_id > 0): ?>
                                <span class="active-filters">
                                    <?php if (!empty($search)): ?>
                                    <span class="filter-tag">Search: "<?php echo htmlspecialchars($search); ?>"</span>
                                    <?php endif; ?>
                                    <?php if ($category_id > 0): ?>
                                    <span class="filter-tag">Category: <?php echo htmlspecialchars($categories[array_search($category_id, array_column($categories, 'id'))]['name'] ?? ''); ?></span>
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="view-options">
                                <button class="view-toggle active" data-view="grid" title="Grid View">
                                    <span class="view-icon">⊞</span>
                                </button>
                                <button class="view-toggle" data-view="list" title="List View">
                                    <span class="view-icon">☰</span>
                                </button>
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <?php if (empty($products)): ?>
                        <div class="no-products">
                            <div class="no-products-content">
                                <div class="no-products-icon">🔍</div>
                                <h3 class="no-products-title">No products found</h3>
                                <p class="no-products-description">
                                    Try adjusting your search criteria or browse all categories
                                </p>
                                <a href="products.php" class="btn btn-outline">Browse All Products</a>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="products-grid" id="productsGrid">
                            <?php foreach ($products as $product): ?>
                            <article class="product-card" data-product="<?php echo $product['id']; ?>">
                                <div class="product-image">
                                    <img src="<?php echo getImageUrl($product['main_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         loading="lazy">
                                    <div class="product-badges">
                                        <?php if ($product['is_featured']): ?>
                                        <span class="badge featured">Featured</span>
                                        <?php endif; ?>
                                        <?php if ($product['discount_price']): ?>
                                        <span class="badge sale">Sale</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_custom_order']): ?>
                                        <span class="badge custom">Custom</span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- <div class="product-actions">
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn-icon view">
                                            <span>View Details</span>
                                        </a>
                                    </div> -->
                                </div>
                                <div class="product-info">
                                    <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                    <h3 class="product-name">
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h3>
                                    <div class="product-artisan">
                                        by <a href="artisan-profile.php?id=<?php echo $product['artisan_id']; ?>">
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

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="pagination-link prev">
                                ← Previous
                            </a>
                            <?php endif; ?>

                            <div class="pagination-numbers">
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                                <?php endfor; ?>
                            </div>

                            <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="pagination-link next">
                                Next →
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php include '../includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // View toggle functionality
        const viewToggles = document.querySelectorAll('.view-toggle');
        const productsGrid = document.getElementById('productsGrid');

        viewToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const view = this.dataset.view;
                
                // Update active state
                viewToggles.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Update grid class
                productsGrid.className = `products-grid products-${view}`;
            });
        });

        // Auto-submit form on filter change
        const filterForm = document.querySelector('.filters-form');
        const autoSubmitInputs = filterForm.querySelectorAll('select, input[type="radio"]');
        
        autoSubmitInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });

        // Product card interactions
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
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

    function clearFilters() {
        window.location.href = 'products.php';
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
