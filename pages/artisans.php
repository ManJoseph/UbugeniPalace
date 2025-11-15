<?php
require_once '../config/config.php';

// Get filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$specialization = isset($_GET['specialization']) ? sanitizeInput($_GET['specialization']) : '';
$location = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'rating';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = ARTISANS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ["u.is_active = 1"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.full_name LIKE ? OR a.bio LIKE ? OR a.specialization LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($specialization)) {
    $where_conditions[] = "a.specialization LIKE ?";
    $params[] = "%{$specialization}%";
}

if (!empty($location)) {
    $where_conditions[] = "a.location LIKE ?";
    $params[] = "%{$location}%";
}

$where_clause = implode(' AND ', $where_conditions);

// Build sort clause
$sort_clause = match($sort) {
    'name' => 'ORDER BY u.full_name ASC',
    'newest' => 'ORDER BY a.created_at DESC',
    'products' => 'ORDER BY total_products DESC',
    default => 'ORDER BY a.rating DESC, a.total_reviews DESC'
};

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) as total
    FROM artisans a 
    JOIN users u ON a.user_id = u.id 
    WHERE {$where_clause}
";

$total_result = $db->fetchOne($count_query, $params);
$total_artisans = $total_result['total'];
$total_pages = ceil($total_artisans / $per_page);

// Get artisans
$artisans_query = "
    SELECT a.*, u.full_name, u.profile_image, u.email,
           COUNT(p.id) as total_products
    FROM artisans a 
    JOIN users u ON a.user_id = u.id 
    LEFT JOIN products p ON a.id = p.artisan_id AND p.status = 'active'
    WHERE {$where_clause}
    GROUP BY a.id
    {$sort_clause}
    LIMIT {$per_page} OFFSET {$offset}
";

$artisans = $db->fetchAll($artisans_query, $params);

// Get specializations for filter
$specializations = $db->fetchAll("
    SELECT DISTINCT specialization 
    FROM artisans 
    WHERE specialization IS NOT NULL AND specialization != '' 
    ORDER BY specialization
");

// Get locations for filter
$locations = $db->fetchAll("
    SELECT DISTINCT location 
    FROM artisans 
    WHERE location IS NOT NULL AND location != '' 
    ORDER BY location
");

$page_title = getPageTitle('artisans');

// Include the header which contains the complete HTML structure
include '../includes/header.php';
?>

<!-- Page Header Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="kinyarwanda">Abanyabugeni bacu</span>
                    <span class="english">Meet Our Artisans</span>
                </h1>
                <p class="hero-description">
                    Discover the talented creators behind our beautiful handmade products. Each artisan brings their unique story, skills, and cultural heritage to every piece they create.
                </p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $total_artisans; ?></span>
                        <span class="stat-label">Artisans</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($specializations); ?></span>
                        <span class="stat-label">Specializations</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Handmade</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-slideshow">
                    <div class="slide active">
                        <img src="../assets/images/artisans/artisan1.jpg" alt="Rwandan Artisan at Work" class="hero-img">
                    </div>
                    <div class="slide">
                        <img src="../assets/images/artisans/artisan2.jpg" alt="Crafting Traditional Pottery" class="hero-img">
                    </div>
                    <div class="slide">
                        <img src="../assets/images/artisans/artisan3.jpg" alt="Weaving Beautiful Baskets" class="hero-img">
                    </div>
                </div>
                <div class="slide-indicators">
                    <span class="indicator active" onclick="currentSlide(1)"></span>
                    <span class="indicator" onclick="currentSlide(2)"></span>
                    <span class="indicator" onclick="currentSlide(3)"></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Artisans Section -->
<section class="featured-artisans-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Discover Our Talented Artisans</h2>
            <p class="section-description">
                Browse through our community of skilled craftspeople, each with their own unique story and expertise
            </p>
        </div>

        <div class="artisans-layout">
            <!-- Filters Sidebar -->
            <aside class="artisans-filters">
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
                               placeholder="Search artisans...">
                    </div>

                    <!-- Specialization -->
                    <div class="filter-group">
                        <label class="filter-label">Specialization</label>
                        <select name="specialization" class="filter-select">
                            <option value="">All Specializations</option>
                            <?php foreach ($specializations as $spec): ?>
                            <option value="<?php echo htmlspecialchars($spec['specialization']); ?>" 
                                    <?php echo $specialization === $spec['specialization'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec['specialization']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Location -->
                    <div class="filter-group">
                        <label class="filter-label">Location</label>
                        <select name="location" class="filter-select">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc['location']); ?>" 
                                    <?php echo $location === $loc['location'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc['location']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="filter-group">
                        <label class="filter-label">Sort By</label>
                        <select name="sort" class="filter-select">
                            <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="products" <?php echo $sort === 'products' ? 'selected' : ''; ?>>Most Products</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Apply Filters</button>
                </form>
            </aside>

            <!-- Artisans Content -->
            <div class="artisans-content">
                <!-- Results Header -->
                <div class="results-header">
                    <div class="results-info">
                        <span class="results-count"><?php echo $total_artisans; ?> artisans found</span>
                        <?php if (!empty($search) || !empty($specialization) || !empty($location)): ?>
                        <span class="active-filters">
                            <?php if (!empty($search)): ?>
                            <span class="filter-tag">Search: "<?php echo htmlspecialchars($search); ?>"</span>
                            <?php endif; ?>
                            <?php if (!empty($specialization)): ?>
                            <span class="filter-tag">Specialization: <?php echo htmlspecialchars($specialization); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($location)): ?>
                            <span class="filter-tag">Location: <?php echo htmlspecialchars($location); ?></span>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Artisans Grid -->
                <?php if (empty($artisans)): ?>
                <div class="no-artisans">
                    <div class="no-artisans-content">
                        <div class="no-artisans-icon">👥</div>
                        <h3 class="no-artisans-title">No artisans found</h3>
                        <p class="no-artisans-description">
                            Try adjusting your search criteria or browse all artisans
                        </p>
                        <a href="artisans.php" class="btn btn-outline">Browse All Artisans</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="artisans-grid">
                    <?php foreach ($artisans as $artisan): ?>
                    <article class="artisan-card" data-artisan="<?php echo $artisan['id']; ?>">
                        <div class="artisan-image">
                            <img src="<?php echo getImageUrl($artisan['profile_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($artisan['full_name']); ?>" 
                                 loading="lazy">
                            <div class="artisan-overlay">
                                <a href="artisan-profile.php?id=<?php echo $artisan['id']; ?>" class="view-profile">
                                    View Profile
                                </a>
                            </div>
                            <?php if ($artisan['is_featured']): ?>
                            <div class="artisan-badge">
                                <span class="badge featured">Featured</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="artisan-info">
                            <h3 class="artisan-name">
                                <a href="artisan-profile.php?id=<?php echo $artisan['id']; ?>">
                                    <?php echo htmlspecialchars($artisan['full_name']); ?>
                                </a>
                            </h3>
                            <div class="artisan-specialization"><?php echo htmlspecialchars($artisan['specialization']); ?></div>
                            <div class="artisan-location"><?php echo htmlspecialchars($artisan['location']); ?></div>
                            
                            <div class="artisan-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $artisan['rating'] ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-text">
                                    <?php echo number_format($artisan['rating'], 1); ?> 
                                    (<?php echo $artisan['total_reviews']; ?> reviews)
                                </span>
                            </div>
                            
                            <div class="artisan-stats">
                                <div class="stat">
                                    <span class="stat-value"><?php echo $artisan['total_products']; ?></span>
                                    <span class="stat-label">Products</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value"><?php echo $artisan['experience_years']; ?>+</span>
                                    <span class="stat-label">Years</span>
                                </div>
                            </div>
                            
                            <?php if ($artisan['bio']): ?>
                            <div class="artisan-bio">
                                <p><?php echo htmlspecialchars(substr($artisan['bio'], 0, 100)); ?>
                                <?php if (strlen($artisan['bio']) > 100): ?>...<?php endif; ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="artisan-actions">
                                <a href="artisan-profile.php?id=<?php echo $artisan['id']; ?>" class="btn btn-primary">
                                    View Profile
                                </a>
                                <a href="products.php?artisan=<?php echo $artisan['id']; ?>" class="btn btn-outline">
                                    View Products
                                </a>
                            </div>
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

<?php include '../includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form on filter change
        const filterForm = document.querySelector('.filters-form');
        const autoSubmitInputs = filterForm.querySelectorAll('select');
        
        autoSubmitInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });

        // Artisan card interactions
        const artisanCards = document.querySelectorAll('.artisan-card');
        artisanCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('a')) {
                    const artisanId = this.dataset.artisan;
                    window.location.href = `artisan-profile.php?id=${artisanId}`;
                }
            });
        });

        // Initialize slideshow
        initializeSlideshow();
    });

    function clearFilters() {
        window.location.href = 'artisans.php';
    }

    // Slideshow functionality
    let currentSlideIndex = 0;
    let slideInterval;
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const totalSlides = slides.length;

    function initializeSlideshow() {
        if (slides.length === 0) return;
        
        // Start auto-play immediately
        startAutoPlay();
    }

    function nextSlide() {
        // Remove active class from current slide and indicator
        slides[currentSlideIndex].classList.remove('active');
        indicators[currentSlideIndex].classList.remove('active');
        
        // Move to next slide
        currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
        
        // Add active class to new slide and indicator
        slides[currentSlideIndex].classList.add('active');
        indicators[currentSlideIndex].classList.add('active');
    }

    function currentSlide(index) {
        // Remove active class from current slide and indicator
        slides[currentSlideIndex].classList.remove('active');
        indicators[currentSlideIndex].classList.remove('active');
        
        // Set new slide index
        currentSlideIndex = index - 1;
        
        // Add active class to new slide and indicator
        slides[currentSlideIndex].classList.add('active');
        indicators[currentSlideIndex].classList.add('active');
        
        // Reset auto-play timer
        resetAutoPlay();
    }

    function startAutoPlay() {
        slideInterval = setInterval(() => {
            nextSlide();
        }, 4000); // Change slide every 4 seconds
    }

    function resetAutoPlay() {
        clearInterval(slideInterval);
        startAutoPlay();
    }

    // Global function for indicator clicks
    window.currentSlide = currentSlide;
</script>
