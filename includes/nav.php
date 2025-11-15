<?php
// Get cart count for logged in users
$cart_count = 0;
if (isLoggedIn()) {
    $cart_count = $db->rowCount("SELECT COUNT(*) FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
}

// Get wishlist count for logged in users
$wishlist_count = 0;
if (isLoggedIn()) {
    $wishlist_count = $db->rowCount("SELECT COUNT(*) FROM wishlist WHERE user_id = ?", [$_SESSION['user_id']]);
}
?>

<nav class="main-navigation" id="mainNav">
    <div class="nav-container">
        <!-- Logo Section -->
        <div class="nav-logo">
            <a href="<?php echo SITE_URL; ?>" class="logo-link">
                <div class="logo-circle">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" alt="<?php echo SITE_NAME; ?>" class="logo-img">
                </div>
                <div class="logo-text">
                    <span class="logo-main">UbugeniPalace</span>
                    <span class="logo-sub">Authentic Craftsmanship</span>
                </div>
            </a>
        </div>

        <!-- Main Navigation Menu -->
        <div class="nav-menu" id="navMenu">
            <ul class="nav-list">
                <?php foreach ($navigation_menu as $key => $item): ?>
                <li class="nav-item">
                    <a href="<?php echo $item['url']; ?>" class="nav-link" data-page="<?php echo $key; ?>">
                        <span class="nav-text-rw"><?php echo $item['title']; ?></span>
                        <span class="nav-text-en"><?php echo $item['title_en']; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <!-- Categories Dropdown -->
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="categoriesDropdown">
                        <span class="nav-text-rw">Amoko</span>
                        <span class="nav-text-en">Categories</span>
                        <span class="dropdown-arrow">▼</span>
                    </a>
                    <ul class="dropdown-menu" id="categoriesMenu">
                        <?php foreach ($categories as $category): ?>
                        <li class="dropdown-item">
                            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $category['id']; ?>" class="dropdown-link">
                                <div class="category-info">
                                    <span class="category-name-rw"><?php echo htmlspecialchars($category['name_kinyarwanda']); ?></span>
                                    <span class="category-name-en"><?php echo htmlspecialchars($category['name']); ?></span>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Search Bar -->
        <div class="nav-search">
            <form class="search-form" id="searchForm" action="<?php echo SITE_URL; ?>/pages/products.php" method="GET">
                <div class="search-input-group">
                    <input type="text" name="search" class="search-input" placeholder="Search products..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-button">
                        <img src="<?php echo SITE_URL; ?>/assets/images/icons/search.svg" alt="Search">
                    </button>
                </div>
                <div class="search-suggestions" id="searchSuggestions"></div>
            </form>
        </div>

        <!-- User Actions -->
        <div class="nav-actions">
            <!-- Wishlist -->
            <?php if (isLoggedIn()): ?>
            <a href="<?php echo SITE_URL; ?>/pages/wishlist.php" class="nav-action wishlist-link" title="Wishlist">
                <img src="<?php echo SITE_URL; ?>/assets/images/icons/heart.svg" alt="Wishlist">
                <span class="action-count wishlist-count" id="wishlistCount"><?php echo $wishlist_count; ?></span>
            </a>
            <?php endif; ?>

            <!-- Shopping Cart -->
            <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="nav-action cart-link" title="Shopping Cart">
                <img src="<?php echo SITE_URL; ?>/assets/images/icons/cart.svg" alt="Cart">
                <span class="action-count cart-count" id="cartCount"><?php echo $cart_count; ?></span>
            </a>

            <!-- User Account -->
            <?php if (isLoggedIn()): ?>
            <div class="nav-action user-menu dropdown">
                <button class="user-toggle dropdown-toggle" id="userMenuToggle">
                    <img src="<?php echo getImageUrl($current_user['profile_image']); ?>" alt="<?php echo htmlspecialchars($current_user['full_name']); ?>" class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    <span class="dropdown-arrow">▼</span>
                </button>
                <ul class="dropdown-menu user-dropdown" id="userDropdown">
                    <li class="dropdown-header">
                        <div class="user-info">
                            <img src="<?php echo getImageUrl($current_user['profile_image']); ?>" alt="Profile" class="user-avatar-small">
                            <div class="user-details">
                                <span class="user-full-name"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                                <span class="user-email"><?php echo htmlspecialchars($current_user['email']); ?></span>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li class="dropdown-item">
                        <a href="<?php echo SITE_URL; ?>/pages/dashboard.php" class="dropdown-link">
                            <span class="link-icon">👤</span> My Profile
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="<?php echo SITE_URL; ?>/pages/orders.php" class="dropdown-link">
                            <span class="link-icon">📦</span> My Orders
                        </a>
                    </li>
                    <?php if (isArtisan()): ?>
                    <li class="dropdown-item">
                        <a href="<?php echo SITE_URL; ?>/pages/artisan-dashboard.php" class="dropdown-link">
                            <span class="link-icon">🎨</span> Artisan Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                    <li class="dropdown-item">
                        <a href="<?php echo SITE_URL; ?>/admin/" class="dropdown-link">
                            <span class="link-icon">⚙️</span> Admin Panel
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="dropdown-divider"></li>
                    <li class="dropdown-item">
                        <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="dropdown-link logout">
                            <span class="link-icon">🚪</span> Logout
                        </a>
                    </li>
                </ul>
            </div>
            <?php else: ?>
            <div class="nav-action auth-links">
                <a href="<?php echo SITE_URL; ?>/pages/login.php" class="auth-link login-link">Login</a>
                <a href="<?php echo SITE_URL; ?>/pages/register.php" class="auth-link register-link btn btn-primary">Sign Up</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>

    <!-- Mobile Navigation Menu -->
    <div class="mobile-nav-menu" id="mobileNavMenu">
        <div class="mobile-nav-header">
            <div class="mobile-logo">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo-small.png" alt="<?php echo SITE_NAME; ?>">
                <span>UbugeniPalace</span>
            </div>
            <button class="mobile-menu-close" id="mobileMenuClose">✕</button>
        </div>
        
        <div class="mobile-nav-content">
            <!-- Mobile Search -->
            <div class="mobile-search">
                <form class="search-form" action="<?php echo SITE_URL; ?>/pages/products.php" method="GET">
                    <input type="text" name="search" placeholder="Search products..." class="mobile-search-input">
                    <button type="submit" class="mobile-search-btn">Search</button>
                </form>
            </div>

            <!-- Mobile Menu Items -->
            <ul class="mobile-nav-list">
                <?php foreach ($navigation_menu as $key => $item): ?>
                <li class="mobile-nav-item">
                    <a href="<?php echo $item['url']; ?>" class="mobile-nav-link">
                        <span class="mobile-nav-text"><?php echo $item['title_en']; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <!-- Mobile Categories -->
                <li class="mobile-nav-item mobile-categories">
                    <button class="mobile-nav-link categories-toggle">
                        <span class="mobile-nav-text">Categories</span>
                        <span class="toggle-arrow">▼</span>
                    </button>
                    <ul class="mobile-categories-list">
                        <?php foreach ($categories as $category): ?>
                        <li class="mobile-category-item">
                            <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $category['id']; ?>" class="mobile-category-link">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>

            <!-- Mobile User Section -->
            <div class="mobile-user-section">
                <?php if (isLoggedIn()): ?>
                <div class="mobile-user-info">
                    <img src="<?php echo getImageUrl($current_user['profile_image']); ?>" alt="Profile" class="mobile-user-avatar">
                    <span class="mobile-user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                </div>
                <ul class="mobile-user-menu">
                    <li><a href="<?php echo SITE_URL; ?>/pages/dashboard.php">My Profile</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/orders.php">My Orders</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/wishlist.php">Wishlist</a></li>
                    <?php if (isArtisan()): ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/artisan-dashboard.php">Artisan Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/logout.php" class="logout-link">Logout</a></li>
                </ul>
                <?php else: ?>
                <div class="mobile-auth-links">
                    <a href="<?php echo SITE_URL; ?>/pages/login.php" class="mobile-auth-link">Login</a>
                    <a href="<?php echo SITE_URL; ?>/pages/register.php" class="mobile-auth-link primary">Sign Up</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>