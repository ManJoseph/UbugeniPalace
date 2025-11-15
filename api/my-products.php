<?php
require_once '../config/config.php';

// Check if user is logged in and is an artisan
if (!isLoggedIn() || !isArtisan()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Get artisan profile
    $artisan = $db->fetchOne("SELECT * FROM artisans WHERE user_id = ?", [$_SESSION['user_id']]);
    
    if (!$artisan) {
        echo json_encode(['html' => '<div class="empty-state"><p>No artisan profile found.</p></div>']);
        exit;
    }
    
    // Get artisan's products
    $products = $db->fetchAll("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.artisan_id = ? AND p.status = 'active' 
        ORDER BY p.created_at DESC
    ", [$artisan['id']]);
    
    if (empty($products)) {
        $html = '
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h3 class="empty-title">No Products Yet</h3>
            <p class="empty-description">Start by adding your first product to showcase your craftsmanship.</p>
            <a href="../pages/add-product.php" class="btn btn-primary">Add Your First Product</a>
        </div>';
    } else {
        $html = '<div class="products-grid">';
        
        foreach ($products as $product) {
            $image_url = getImageUrl($product['main_image']);
            $price = formatPrice($product['price']);
            $created_date = formatDate($product['created_at']);
            
            $html .= '
            <div class="product-card">
                <div class="product-image">
                    <img src="' . $image_url . '" alt="' . htmlspecialchars($product['name']) . '">
                </div>
                <div class="product-info">
                    <div class="product-category">' . htmlspecialchars($product['category_name']) . '</div>
                    <h3 class="product-name">
                        <a href="../pages/product-details.php?id=' . $product['id'] . '">' . htmlspecialchars($product['name']) . '</a>
                    </h3>
                    <div class="product-price">
                        <span class="price-current">' . $price . '</span>
                    </div>
                    <div class="product-meta">
                        <span class="stock-info">Stock: ' . $product['stock_quantity'] . '</span>
                        <span class="created-date">Added: ' . $created_date . '</span>
                    </div>
                    <div class="product-actions">
                        <a href="../pages/edit-product.php?id=' . $product['id'] . '" class="btn btn-sm btn-outline">Edit</a>
                        <button class="btn btn-sm btn-danger" onclick="deleteProduct(' . $product['id'] . ')">Delete</button>
                    </div>
                </div>
            </div>';
        }
        
        $html .= '</div>';
    }
    
    echo json_encode(['html' => $html]);
    
} catch (Exception $e) {
    error_log('Error loading products: ' . $e->getMessage());
    echo json_encode(['html' => '<div class="error-state"><p>Failed to load products. Please try again.</p></div>']);
}
?> 