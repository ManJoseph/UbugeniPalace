<?php
require_once '../config/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirectTo(SITE_URL . '/pages/login.php');
}

$user = getCurrentUser();
$user_type = $_SESSION['user_type'];

// Get user statistics
if ($user_type === 'customer') {
    // Customer stats
    $total_orders = $db->rowCount("SELECT COUNT(*) FROM orders WHERE user_id = ?", [$_SESSION['user_id']]);
    $total_spent = $db->fetchOne("SELECT SUM(total_amount) as total FROM orders WHERE user_id = ? AND status = 'completed'", [$_SESSION['user_id']]);
    $wishlist_count = $db->rowCount("SELECT COUNT(*) FROM wishlist WHERE user_id = ?", [$_SESSION['user_id']]);
    $recent_orders = $db->fetchAll("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ? 
        GROUP BY o.id
        ORDER BY o.created_at DESC 
        LIMIT 5
    ", [$_SESSION['user_id']]);

    // Load wishlist items for customer
    $wishlist_items = $db->fetchAll("
        SELECT w.id as wishlist_id, p.*
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ", [$_SESSION['user_id']]);

} elseif ($user_type === 'artisan') {
    // Get artisan profile first
    $artisan_profile = $db->fetchOne("SELECT * FROM artisans WHERE user_id = ?", [$_SESSION['user_id']]);
    
    if ($artisan_profile) {
        $artisan_id = $artisan_profile['id'];
        
        // Artisan stats using artisan_id
        $total_products = $db->rowCount("SELECT COUNT(*) FROM products WHERE artisan_id = ? AND status = 'active'", [$artisan_id]);
        $total_sales = $db->fetchOne("
            SELECT SUM(oi.price * oi.quantity) as total 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            JOIN products p ON oi.product_id = p.id 
            WHERE p.artisan_id = ? AND o.status = 'completed'
        ", [$artisan_id]);
        $total_orders = $db->rowCount("
            SELECT COUNT(DISTINCT o.id) 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            JOIN products p ON oi.product_id = p.id 
            WHERE p.artisan_id = ?
        ", [$artisan_id]);
        
        $recent_orders = $db->fetchAll("
            SELECT o.*, oi.product_id, p.name as product_name, u.full_name as customer_name
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            JOIN products p ON oi.product_id = p.id 
            JOIN users u ON o.user_id = u.id
            WHERE p.artisan_id = ? 
            ORDER BY o.created_at DESC 
            LIMIT 5
        ", [$artisan_id]);
        
        // Get artisan's products for display
        $artisan_products = $db->fetchAll("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.artisan_id = ? AND p.status = 'active' 
            ORDER BY p.created_at DESC 
            LIMIT 10
        ", [$artisan_id]);
    } else {
        // No artisan profile found
        $total_products = 0;
        $total_sales = ['total' => 0];
        $total_orders = 0;
        $recent_orders = [];
        $artisan_products = [];
    }
}

$page_title = getPageTitle('dashboard');

// Include the header which contains the complete HTML structure
include '../includes/header.php';
?>

    <!-- Main Content -->
    <main class="dashboard-main">
        <!-- Display Alert Messages -->
        <?php displayAlert(); ?>
        
        <!-- Dashboard Hero -->
        <section class="dashboard-hero">
            <div class="container">
                <div class="dashboard-hero-content">
                    <h1 class="dashboard-hero-title">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                    <p class="dashboard-hero-subtitle">Manage your account, track orders, and explore your dashboard</p>
                    <div class="dashboard-hero-stats">
                        <?php if ($user_type === 'customer'): ?>
                        <div class="hero-stat">
                            <span class="hero-stat-number"><?php echo $total_orders; ?></span>
                            <span class="hero-stat-label">Orders</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number"><?php echo formatPrice($total_spent['total'] ?? 0); ?></span>
                            <span class="hero-stat-label">Total Spent</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number"><?php echo $wishlist_count; ?></span>
                            <span class="hero-stat-label">Wishlist Items</span>
                        </div>
                        <?php else: ?>
                        <div class="hero-stat">
                            <span class="hero-stat-number"><?php echo $total_products; ?></span>
                            <span class="hero-stat-label">Active Products</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number"><?php echo formatPrice($total_sales['total'] ?? 0); ?></span>
                            <span class="hero-stat-label">Total Sales</span>
                        </div>
                        <div class="hero-stat">
                            <span class="hero-stat-number"><?php echo $total_orders; ?></span>
                            <span class="hero-stat-label">Orders Received</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dashboard Content -->
        <section class="dashboard-content">
            <div class="container">
                <div class="dashboard-layout">
                    <!-- Sidebar -->
                    <aside class="dashboard-sidebar">
                        <div class="user-profile-card">
                            <div class="profile-image">
                                <img src="<?php echo getImageUrl($user['profile_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($user['full_name']); ?>"
                                     onerror="this.src='../assets/images/icons/user.svg'">
                            </div>
                            <div class="profile-info">
                                <h3 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                <span class="profile-type"><?php echo ucfirst($user_type); ?></span>
                            </div>
                        </div>

                        <nav class="dashboard-nav">
                            <ul class="nav-list">
                                <li class="nav-item">
                                    <a href="#overview" class="nav-link active" data-tab="overview">
                                        <span class="nav-icon">📊</span>
                                        <span class="nav-text">Overview</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#profile" class="nav-link" data-tab="profile">
                                        <span class="nav-icon">👤</span>
                                        <span class="nav-text">Profile</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#orders" class="nav-link" data-tab="orders">
                                        <span class="nav-icon">📦</span>
                                        <span class="nav-text">Orders</span>
                                    </a>
                                </li>
                                <?php if ($user_type === 'artisan'): ?>
                                <li class="nav-item">
                                    <a href="#products" class="nav-link" data-tab="products">
                                        <span class="nav-icon">🎨</span>
                                        <span class="nav-text">My Products</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li class="nav-item">
                                    <a href="#wishlist" class="nav-link" data-tab="wishlist">
                                        <span class="nav-icon">❤️</span>
                                        <span class="nav-text">Wishlist</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#settings" class="nav-link" data-tab="settings">
                                        <span class="nav-icon">⚙️</span>
                                        <span class="nav-text">Settings</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </aside>

                    <!-- Main Content Area -->
                    <div class="dashboard-main-content">
                        <!-- Overview Tab -->
                        <div class="tab-content active" id="overview">
                            <div class="tab-header">
                                <h2 class="tab-title">Overview</h2>
                                <p class="tab-description">Your account summary and recent activity</p>
                            </div>

                            <!-- Quick Actions -->
                            <div class="quick-actions">
                                <h3 class="section-title">Quick Actions</h3>
                                <div class="actions-grid">
                                    <?php if ($user_type === 'customer'): ?>
                                    <a href="../pages/products.php" class="action-card">
                                        <div class="action-icon">🛍️</div>
                                        <div class="action-content">
                                            <h4>Start Shopping</h4>
                                            <p>Browse our collection of handmade products</p>
                                        </div>
                                    </a>
                                    <a href="#wishlist" class="action-card" onclick="switchTab('wishlist')">
                                        <div class="action-icon">❤️</div>
                                        <div class="action-content">
                                            <h4>View Wishlist</h4>
                                            <p>Check your saved items</p>
                                        </div>
                                    </a>
                                    <?php else: ?>
                                    <a href="add-product.php" class="action-card">
                                        <div class="action-icon">➕</div>
                                        <div class="action-content">
                                            <h4>Add Product</h4>
                                            <p>Create a new product listing</p>
                                        </div>
                                    </a>
                                    <a href="#products" class="action-card" onclick="switchTab('products')">
                                        <div class="action-icon">🎨</div>
                                        <div class="action-content">
                                            <h4>Manage Products</h4>
                                            <p>Edit your existing products</p>
                                        </div>
                                    </a>
                                    <?php endif; ?>
                                    <a href="#profile" class="action-card" onclick="switchTab('profile')">
                                        <div class="action-icon">👤</div>
                                        <div class="action-content">
                                            <h4>Edit Profile</h4>
                                            <p>Update your personal information</p>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <!-- Recent Activity -->
                            <div class="recent-activity">
                                <h3 class="section-title">Recent Activity</h3>
                                <?php if (!empty($recent_orders)): ?>
                                <div class="activity-list">
                                    <?php foreach ($recent_orders as $order): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">📦</div>
                                        <div class="activity-content">
                                            <h4 class="activity-title">
                                                Order #<?php echo $order['order_number']; ?>
                                            </h4>
                                            <p class="activity-description">
                                                <?php if ($user_type === 'customer'): ?>
                                                <?php echo $order['item_count']; ?> items • <?php echo formatPrice($order['total_amount']); ?>
                                                <?php else: ?>
                                                <?php echo htmlspecialchars($order['product_name']); ?> • <?php echo htmlspecialchars($order['customer_name']); ?>
                                                <?php endif; ?>
                                            </p>
                                            <span class="activity-date"><?php echo formatDate($order['created_at']); ?></span>
                                        </div>
                                        <div class="activity-status">
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-icon">📦</div>
                                    <h4 class="empty-title">No recent activity</h4>
                                    <p class="empty-description">
                                        <?php if ($user_type === 'customer'): ?>
                                        Start shopping to see your orders here!
                                        <?php else: ?>
                                        Add products to start receiving orders!
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($user_type === 'customer'): ?>
                                    <a href="../pages/products.php" class="btn btn-primary">Start Shopping</a>
                                    <?php else: ?>
                                    <a href="add-product.php" class="btn btn-primary">Add Your First Product</a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Profile Tab -->
                        <div class="tab-content" id="profile">
                            <div class="tab-header">
                                <h2 class="tab-title">Profile</h2>
                                <p class="tab-description">Manage your personal information</p>
                            </div>

                            <div class="profile-form">
                                <form method="POST" action="../api/profile.php" enctype="multipart/form-data" id="profileForm">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="full_name" class="form-label">Full Name</label>
                                            <input type="text" id="full_name" name="full_name" class="form-input" 
                                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" id="email" name="email" class="form-input" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-input" 
                                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea id="address" name="address" class="form-textarea" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                        </div>
                                    </div>

                                    <?php if ($user_type === 'artisan' && isset($artisan_profile)): ?>
                                    <div class="form-section">
                                        <h3 class="section-title">Artisan Profile</h3>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="specialization" class="form-label">Specialization</label>
                                                <input type="text" id="specialization" name="specialization" class="form-input" 
                                                       value="<?php echo htmlspecialchars($artisan_profile['specialization']); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="location" class="form-label">Location</label>
                                                <input type="text" id="location" name="location" class="form-input" 
                                                       value="<?php echo htmlspecialchars($artisan_profile['location']); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="bio" class="form-label">Bio</label>
                                            <textarea id="bio" name="bio" class="form-textarea" rows="4" 
                                                      placeholder="Tell customers about your craft, experience, and what makes your work unique..."><?php echo htmlspecialchars($artisan_profile['bio']); ?></textarea>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <div class="form-section">
                                        <h3 class="section-title">Profile Picture</h3>
                                        <div class="profile-image-upload">
                                            <div class="current-image">
                                                <img src="<?php echo getImageUrl($user['profile_image']); ?>" 
                                                     alt="Current profile picture"
                                                     onerror="this.src='../assets/images/icons/user.svg'">
                                            </div>
                                            <div class="upload-controls">
                                                <label for="profile_image" class="btn btn-outline">Choose New Image</label>
                                                <input type="file" id="profile_image" name="profile_image" class="form-file" 
                                                       accept="image/*" style="display: none;">
                                                <p class="upload-help">Recommended: Square image, 400x400px or larger</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                            <span class="btn-text">Save Changes</span>
                                            <span class="btn-loading" style="display: none;">Saving...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Orders Tab -->
                        <div class="tab-content" id="orders">
                            <div class="tab-header">
                                <h2 class="tab-title">Orders</h2>
                                <p class="tab-description">View your order history and track current orders</p>
                            </div>

                            <div class="orders-list">
                                <!-- Orders will be loaded here via AJAX -->
                                <div class="loading-state">
                                    <div class="loading-spinner"></div>
                                    <p>Loading orders...</p>
                                </div>
                            </div>
                        </div>

                        <?php if ($user_type === 'artisan'): ?>
                        <!-- Products Tab -->
                        <div class="tab-content" id="products">
                            <div class="tab-header">
                                <h2 class="tab-title">My Products</h2>
                                <p class="tab-description">Manage your product listings</p>
                                <a href="add-product.php" class="btn btn-primary">Add New Product</a>
                            </div>

                            <div class="products-list">
                                <?php if (!empty($artisan_products)): ?>
                                    <div class="products-grid">
                                        <?php foreach ($artisan_products as $product): ?>
                                            <div class="product-card">
                                                <div class="product-image">
                                                    <img src="<?php echo getImageUrl($product['main_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                         onerror="this.src='../assets/images/icons/user.svg'">
                                                </div>
                                                <div class="product-info">
                                                    <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                                    <h3 class="product-name">
                                                        <a href="product-details.php?id=<?php echo $product['id']; ?>">
                                                            <?php echo htmlspecialchars($product['name']); ?>
                                                        </a>
                                                    </h3>
                                                    <div class="product-price">
                                                        <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                                                    </div>
                                                    <div class="product-meta">
                                                        <span class="stock-info">Stock: <?php echo $product['stock_quantity']; ?></span>
                                                        <span class="created-date">Added: <?php echo formatDate($product['created_at']); ?></span>
                                                    </div>
                                                    <div class="product-actions">
                                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                                        <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <div class="empty-icon">📦</div>
                                        <h3 class="empty-title">No Products Yet</h3>
                                        <p class="empty-description">Start by adding your first product to showcase your craftsmanship.</p>
                                        <a href="add-product.php" class="btn btn-primary">Add Your First Product</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Wishlist Tab -->
                        <div class="tab-content" id="wishlist">
                            <div class="tab-header">
                                <h2 class="tab-title">Wishlist</h2>
                                <p class="tab-description">Your saved items</p>
                            </div>

                            <div class="wishlist-items">
                                <?php if ($user_type === 'customer'): ?>
                                    <?php if (!empty($wishlist_items)): ?>
                                        <div class="wishlist-grid">
                                            <?php foreach ($wishlist_items as $item): ?>
                                                <div class="wishlist-card">
                                                    <div class="wishlist-image">
                                                        <img src="<?php echo getImageUrl($item['image'] ?? $item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" onerror="this.src='../assets/images/icons/product.svg'">
                                                    </div>
                                                    <div class="wishlist-info">
                                                        <h4 class="wishlist-product-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                                                        <p class="wishlist-product-price"><?php echo formatPrice($item['price']); ?></p>
                                                        <a href="../pages/product-details.php?id=<?php echo $item['id']; ?>" class="btn btn-outline btn-sm">View</a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <div class="empty-icon">❤️</div>
                                            <h4 class="empty-title">No items in your wishlist</h4>
                                            <p class="empty-description">Browse products and add your favorites to your wishlist.</p>
                                            <a href="../pages/products.php" class="btn btn-primary">Browse Products</a>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <div class="empty-icon">❤️</div>
                                        <h4 class="empty-title">Wishlist not available</h4>
                                        <p class="empty-description">Only customers have wishlists.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Settings Tab -->
                        <div class="tab-content" id="settings">
                            <div class="tab-header">
                                <h2 class="tab-title">Settings</h2>
                                <p class="tab-description">Manage your account settings and preferences</p>
                            </div>

                            <div class="settings-sections">
                                <div class="settings-section">
                                    <h3 class="section-title">Change Password</h3>
                                    <form class="password-form" method="POST" action="../api/change-password.php" id="passwordForm">
                                        <div class="form-group">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" id="current_password" name="current_password" class="form-input" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" id="new_password" name="new_password" class="form-input" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="changePasswordBtn">
                                            <span class="btn-text">Change Password</span>
                                            <span class="btn-loading" style="display: none;">Updating...</span>
                                        </button>
                                    </form>
                                </div>

                                <div class="settings-section">
                                    <h3 class="section-title">Account Actions</h3>
                                    <div class="account-actions">
                                        <button class="btn btn-outline btn-danger" onclick="deleteAccount()">
                                            Delete Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab navigation
            const tabLinks = document.querySelectorAll('.dashboard-nav .nav-link');
            const tabContents = document.querySelectorAll('.tab-content');

            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetTab = this.dataset.tab;
                    switchTab(targetTab);
                });
            });

            // Load initial tab content
            loadTabContent('overview');

            // Profile form submission
            const profileForm = document.getElementById('profileForm');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitProfileForm(this);
                });
            }

            // Password form submission
            const passwordForm = document.getElementById('passwordForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitPasswordForm(this);
                });
            }

            // Profile image preview
            const profileImageInput = document.getElementById('profile_image');
            if (profileImageInput) {
                profileImageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.querySelector('.current-image img').src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });

        function switchTab(tab) {
            const tabLinks = document.querySelectorAll('.dashboard-nav .nav-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            // Update active states
            tabLinks.forEach(l => l.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
            document.getElementById(tab).classList.add('active');
            
            // Load tab content if needed
            loadTabContent(tab);
        }

        function loadTabContent(tab) {
            switch(tab) {
                case 'orders':
                    loadOrders();
                    break;
                case 'products':
                    loadProducts();
                    break;
                case 'wishlist':
                    loadWishlist();
                    break;
            }
        }

        function loadOrders() {
            const ordersList = document.querySelector('#orders .orders-list');
            
            fetch('../api/orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        ordersList.innerHTML = data.html;
                    } else {
                        ordersList.innerHTML = '<div class="error-state">Failed to load orders</div>';
                    }
                })
                .catch(error => {
                    ordersList.innerHTML = '<div class="error-state">Failed to load orders</div>';
                });
        }

        function loadProducts() {
            const productsList = document.querySelector('#products .products-list');
            
            fetch('../api/my-products.php')
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        productsList.innerHTML = data.html;
                    } else {
                        productsList.innerHTML = '<div class="error-state">Failed to load products</div>';
                    }
                })
                .catch(error => {
                    productsList.innerHTML = '<div class="error-state">Failed to load products</div>';
                });
        }

        function loadWishlist() {
            const wishlistItems = document.querySelector('#wishlist .wishlist-items');
            
            fetch('../api/wishlist-items.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        wishlistItems.innerHTML = data.html;
                    } else {
                        wishlistItems.innerHTML = '<div class="error-state">Failed to load wishlist</div>';
                    }
                })
                .catch(error => {
                    wishlistItems.innerHTML = '<div class="error-state">Failed to load wishlist</div>';
                });
        }

        function submitProfileForm(form) {
            const submitBtn = document.getElementById('saveProfileBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            submitBtn.disabled = true;

            const formData = new FormData(form);
            
            fetch('../api/profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Profile updated successfully!', 'success');
                } else {
                    showNotification(data.message || 'Failed to update profile', 'error');
                }
            })
            .catch(error => {
                showNotification('Failed to update profile', 'error');
            })
            .finally(() => {
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
            });
        }

        function submitPasswordForm(form) {
            const submitBtn = document.getElementById('changePasswordBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            submitBtn.disabled = true;

            const formData = new FormData(form);
            
            fetch('../api/change-password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Password changed successfully!', 'success');
                    form.reset();
                } else {
                    showNotification(data.message || 'Failed to change password', 'error');
                }
            })
            .catch(error => {
                showNotification('Failed to change password', 'error');
            })
            .finally(() => {
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
            });
        }

        function deleteAccount() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                fetch('../api/delete-account.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../index.php';
                    } else {
                        showNotification(data.message || 'Failed to delete account', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Failed to delete account', 'error');
                });
            }
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('product_id', productId);
                
                fetch('../api/delete-product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Product deleted successfully!', 'success');
                        // Reload products
                        loadProducts();
                    } else {
                        showNotification(data.message || 'Failed to delete product', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Failed to delete product', 'error');
                });
            }
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.remove()">×</button>
            `;
            
            // Add to page
            document.body.appendChild(notification);
            
            // Show notification
            setTimeout(() => notification.classList.add('show'), 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    </script>

<?php include '../includes/footer.php'; ?>
