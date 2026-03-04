<?php
// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_title = getPageTitle($current_page);

// Get current user if logged in
$current_user = null;
if (isLoggedIn()) {
    $current_user = getCurrentUser();
}

// Get navigation menu and categories
$navigation_menu = getNavigationMenu();
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="rw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="Discover authentic Rwandan craftsmanship. Shop handmade pottery, baskets, jewelry, textiles, and art directly from local artisans.">
    <meta name="keywords" content="Rwanda, artisan, crafts, handmade, pottery, baskets, agaseke, jewelry, textiles">
    <meta name="author" content="UbugeniPalace">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="Discover authentic Rwandan craftsmanship. Shop handmade pottery, baskets, jewelry, textiles, and art directly from local artisans.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/logo/logo.png">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $page_title; ?>">
    <meta name="twitter:description" content="Discover authentic Rwandan craftsmanship. Shop handmade pottery, baskets, jewelry, textiles, and art directly from local artisans.">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/assets/images/logo/logo.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/logo/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/assets/images/logo/logo.png">
    
    <!-- Preconnect to external domains for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/animations.css">
    
    <!-- Additional Meta Tags -->
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#2563eb">
    <meta name="msapplication-TileColor" content="#2563eb">
    
    <!-- Structured Data for SEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "UbugeniPalace",
        "description": "Discover authentic Rwandan craftsmanship. Shop handmade pottery, baskets, jewelry, textiles, and art directly from local artisans.",
        "url": "<?php echo SITE_URL; ?>",
        "logo": "<?php echo SITE_URL; ?>/assets/images/logo/logo.png",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+250788123456",
            "contactType": "customer service",
            "email": "info@ubugenipalace.rw"
        },
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Kigali",
            "addressCountry": "RW"
        }
    }
    </script>
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Header Section -->
    <header class="header">
        <div class="header-left">
            <div class="logo-container">
                <div class="logo-circle">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" alt="Logo" class="logo" />
                </div>
            </div>
            <div class="branding">
                <h1 class="site-name">UbugeniPalace</h1>
                <span class="tagline">U-Pal</span>
            </div>
        </div>

        <nav class="nav">
            <a href="<?php echo SITE_URL; ?>" class="nav-link">Home</a>
            <a href="<?php echo SITE_URL; ?>/pages/artisans" class="nav-link">Artisans</a>
            <a href="<?php echo SITE_URL; ?>/pages/products" class="nav-link">Products</a>
            <a href="<?php echo SITE_URL; ?>/pages/about" class="nav-link">About</a>
            <!-- <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="nav-link">Contact</a> -->
        </nav>

        <div class="header-right">
            <div class="search-box">
                <input type="text" placeholder="Search products..." />
                <button>🔍</button>
            </div>
            <?php if (isLoggedIn() && $current_user): ?>
                <div class="user-menu">
                    <span>Welcome, <?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    <a href="<?php echo SITE_URL; ?>/pages/dashboard">Dashboard</a>
                    <a href="<?php echo SITE_URL; ?>/pages/logout">Logout</a>
                </div>
            <?php else: ?>
                <button class="login-btn" onclick="openLoginModal()">Login</button>
                <a href="<?php echo SITE_URL; ?>/pages/register" class="signup-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Main Content Wrapper -->
    <main id="main-content" class="main-content">
        <!-- Display any alerts/messages -->
        <?php displayAlert(); ?>
        
        <!-- Login Modal -->
        <?php include 'login-modal.php'; ?>
