<?php
require_once '../config/config.php';

// Handle search form submission
$search_results = [];
$search_query = '';
$selected_category = '';
$min_price = '';
$max_price = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['search'])) {
    $search_query = sanitizeInput($_GET['search'] ?? $_POST['search'] ?? '');
    $selected_category = sanitizeInput($_GET['category'] ?? $_POST['category'] ?? '');
    $min_price = sanitizeInput($_GET['min_price'] ?? $_POST['min_price'] ?? '');
    $max_price = sanitizeInput($_GET['max_price'] ?? $_POST['max_price'] ?? '');
    
    // Build search query
    $sql = "
        SELECT p.*, c.name as category_name, u.full_name as artisan_name, a.rating as artisan_rating
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        JOIN artisans a ON p.artisan_id = a.id 
        JOIN users u ON a.user_id = u.id 
        WHERE p.status = 'active'
    ";
    
    $params = [];
    
    // Add search term
    if (!empty($search_query)) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR u.full_name LIKE ?)";
        $search_term = "%{$search_query}%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    // Add category filter
    if (!empty($selected_category)) {
        $sql .= " AND c.id = ?";
        $params[] = $selected_category;
    }
    
    // Add price range filter
    if (!empty($min_price)) {
        $sql .= " AND p.price >= ?";
        $params[] = $min_price;
    }
    
    if (!empty($max_price)) {
        $sql .= " AND p.price <= ?";
        $params[] = $max_price;
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $search_results = $db->fetchAll($sql, $params);
}

$page_title = 'Search Results - ' . SITE_NAME;
include '../includes/header.php';
?>

<main class="search-main">
    <!-- Page Header -->
    <section class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Search Products</h1>
            <p class="page-description">Find the perfect handcrafted items</p>
        </div>
    </section>

    <!-- Search Content -->
    <section class="search-content">
        <div class="container">
            <!-- Search Form -->
            <div class="search-form-section">
                <form method="GET" action="" class="search-form">
                    <div class="search-inputs">
                        <div class="search-field">
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search products, artisans..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>"
                                   class="search-input">
                        </div>
                        
                        <div class="search-field">
                            <select name="category" class="search-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $selected_category == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="search-field">
                            <input type="number" 
                                   name="min_price" 
                                   placeholder="Min Price" 
                                   value="<?php echo htmlspecialchars($min_price); ?>"
                                   class="price-input">
                        </div>
                        
                        <div class="search-field">
                            <input type="number" 
                                   name="max_price" 
                                   placeholder="Max Price" 
                                   value="<?php echo htmlspecialchars($max_price); ?>"
                                   class="price-input">
                        </div>
                        
                        <button type="submit" class="btn btn-primary search-btn">
                            Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Search Results -->
            <div class="search-results">
                <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])): ?>
                    <div class="results-header">
                        <h2>Search Results</h2>
                        <p class="results-count">
                            Found <?php echo count($search_results); ?> product(s)
                            <?php if (!empty($search_query)): ?>
                                for "<?php echo htmlspecialchars($search_query); ?>"
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if (empty($search_results)): ?>
                        <!-- No Results -->
                        <div class="no-results">
                            <div class="no-results-content">
                                <div class="no-results-icon">🔍</div>
                                <h3 class="no-results-title">No products found</h3>
                                <p class="no-results-description">
                                    Try adjusting your search terms or filters to find what you're looking for.
                                </p>
                                <a href="products.php" class="btn btn-primary">Browse All Products</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Results Grid -->
                        <div class="products-grid">
                            <?php foreach ($search_results as $product): ?>
                                <div class="product-card">
                                    <div class="product-image">
                                        <img src="<?php echo getImageUrl($product['main_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        
                                        <?php if ($product['is_featured']): ?>
                                            <span class="badge featured">Featured</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($product['discount_price']): ?>
                                            <span class="badge sale">Sale</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-info">
                                        <div class="product-category">
                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                        </div>
                                        
                                        <h3 class="product-name">
                                            <a href="product-details.php?id=<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h3>
                                        
                                        <p class="product-artisan">
                                            by <a href="artisan-profile.php?id=<?php echo $product['artisan_id']; ?>">
                                                <?php echo htmlspecialchars($product['artisan_name']); ?>
                                            </a>
                                            <span class="artisan-rating">
                                                <span class="stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span class="star <?php echo $i <= $product['artisan_rating'] ? 'filled' : ''; ?>">★</span>
                                                    <?php endfor; ?>
                                                </span>
                                            </span>
                                        </p>
                                        
                                        <div class="product-price">
                                            <?php if ($product['discount_price']): ?>
                                                <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                                                <span class="price-current"><?php echo formatPrice($product['discount_price']); ?></span>
                                            <?php else: ?>
                                                <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <form method="POST" action="cart-simple.php" style="display: inline;">
                                            <input type="hidden" name="add_to_cart" value="1">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
                                        </form>
                                        
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-outline btn-sm">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<style>
.search-main {
    min-height: 100vh;
    background: var(--bg-secondary);
}

.search-content {
    padding: var(--spacing-2xl) 0;
}

.search-form-section {
    background: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
    box-shadow: 0 2px 8px var(--shadow-sm);
}

.search-form {
    width: 100%;
}

.search-inputs {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: var(--spacing-md);
    align-items: end;
}

.search-field {
    display: flex;
    flex-direction: column;
}

.search-input,
.search-select,
.price-input {
    padding: var(--spacing-md);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    font-size: 1rem;
    transition: border-color var(--transition-fast);
}

.search-input:focus,
.search-select:focus,
.price-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.search-btn {
    padding: var(--spacing-md) var(--spacing-lg);
    white-space: nowrap;
}

.results-header {
    margin-bottom: var(--spacing-xl);
}

.results-header h2 {
    margin-bottom: var(--spacing-sm);
    color: var(--text-primary);
}

.results-count {
    color: var(--text-secondary);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-lg);
}

.product-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 2px 8px var(--shadow-sm);
    transition: transform var(--transition-normal), box-shadow var(--transition-normal);
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px var(--shadow-md);
}

.product-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-normal);
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.badge {
    position: absolute;
    top: var(--spacing-sm);
    left: var(--spacing-sm);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-white);
}

.badge.featured {
    background: var(--primary);
}

.badge.sale {
    background: var(--accent-error);
}

.product-info {
    padding: var(--spacing-lg);
}

.product-category {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: var(--spacing-xs);
}

.product-name {
    margin-bottom: var(--spacing-sm);
}

.product-name a {
    color: var(--text-primary);
    text-decoration: none;
    font-weight: 600;
}

.product-name a:hover {
    color: var(--primary);
}

.product-artisan {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: var(--spacing-sm);
}

.product-artisan a {
    color: var(--primary);
    text-decoration: none;
}

.product-artisan a:hover {
    text-decoration: underline;
}

.artisan-rating {
    margin-left: var(--spacing-sm);
}

.stars {
    color: var(--accent-warning);
}

.star.filled {
    color: var(--accent-warning);
}

.product-price {
    margin-bottom: var(--spacing-md);
}

.price-original {
    text-decoration: line-through;
    color: var(--text-light);
    margin-right: var(--spacing-sm);
}

.price-current {
    font-weight: 600;
    color: var(--primary);
    font-size: 1.1rem;
}

.product-actions {
    padding: 0 var(--spacing-lg) var(--spacing-lg);
    display: flex;
    gap: var(--spacing-sm);
}

.no-results {
    text-align: center;
    padding: var(--spacing-3xl) 0;
}

.no-results-icon {
    font-size: 4rem;
    margin-bottom: var(--spacing-lg);
}

.no-results-title {
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
}

.no-results-description {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xl);
}

@media (max-width: 768px) {
    .search-inputs {
        grid-template-columns: 1fr;
        gap: var(--spacing-sm);
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    
    .product-actions {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?> 