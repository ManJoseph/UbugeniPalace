<?php
require_once '../config/config.php';

// Redirect if not logged in or not an artisan
if (!isLoggedIn()) {
    redirectTo(SITE_URL . '/pages/login.php');
}

if (!isArtisan()) {
    redirectTo(SITE_URL . '/pages/dashboard.php');
}

$user = getCurrentUser();
$error = '';
$success = '';

// Get artisan record
$artisan = $db->fetchOne("SELECT * FROM artisans WHERE user_id = ?", [$_SESSION['user_id']]);
if (!$artisan) {
    // Try to create artisan profile if it doesn't exist
    $db->execute(
        "INSERT INTO artisans (user_id, specialization, location) VALUES (?, ?, ?)",
        [$_SESSION['user_id'], 'General Crafts', 'Rwanda']
    );
    $artisan = $db->fetchOne("SELECT * FROM artisans WHERE user_id = ?", [$_SESSION['user_id']]);
    
    if (!$artisan) {
        $error = 'Artisan profile not found. Please contact support.';
    }
}

// Handle product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $artisan) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $materials = sanitizeInput($_POST['materials']);
    $dimensions = sanitizeInput($_POST['dimensions']);
    
    // Validation
    if (empty($name) || empty($description) || $price <= 0 || $category_id <= 0) {
        $error = 'Please fill in all required fields with valid values.';
    } else {
        // Handle product images upload
        $image_paths = [];
        if (isset($_FILES['product_images']) && is_array($_FILES['product_images']['name'])) {
            for ($i = 0; $i < count($_FILES['product_images']['name']); $i++) {
                if ($_FILES['product_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['product_images']['name'][$i],
                        'type' => $_FILES['product_images']['type'][$i],
                        'tmp_name' => $_FILES['product_images']['tmp_name'][$i],
                        'error' => $_FILES['product_images']['error'][$i],
                        'size' => $_FILES['product_images']['size'][$i]
                    ];
                    
                    $upload_result = uploadImage($file, 'products-images');
                    if ($upload_result !== false) {
                        $image_paths[] = $upload_result;
                    }
                }
            }
        }
        
        if (empty($image_paths)) {
            $error = 'Please upload at least one product image.';
        } else {
            // Create product
            // Prepare gallery images as JSON
            $gallery_images = array_slice($image_paths, 1); // All images except the first one
            $gallery_images_json = !empty($gallery_images) ? json_encode($gallery_images) : null;
            
            $product_data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category_id' => $category_id,
                'artisan_id' => $artisan['id'],
                'stock_quantity' => $stock_quantity,
                'materials' => $materials,
                'dimensions' => $dimensions,
                'main_image' => $image_paths[0],
                'gallery_images' => $gallery_images_json,
                'status' => 'active'
            ];
            
            try {
                $result = $db->execute(
                    "INSERT INTO products (name, description, price, category_id, artisan_id, stock_quantity, materials, dimensions, main_image, gallery_images, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    array_values($product_data)
                );
                
                if ($result) {
                    // Set success message in session and redirect
                    showAlert('Product added successfully! Your product is now live and visible to customers.', 'success');
                    redirectTo(SITE_URL . '/pages/dashboard.php');
                } else {
                    $error = 'Failed to add product. Please try again.';
                }
            } catch (Exception $e) {
                error_log('Product creation error: ' . $e->getMessage());
                $error = 'Database error occurred. Please try again. Error: ' . $e->getMessage();
            }
        }
    }
}

// Get categories for dropdown
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

// If no categories exist, create some default ones
if (empty($categories)) {
    $default_categories = [
        ['name' => 'Pottery', 'name_kinyarwanda' => 'Ibikoresho by\'ubumba'],
        ['name' => 'Baskets', 'name_kinyarwanda' => 'Amasafuriya'],
        ['name' => 'Jewelry', 'name_kinyarwanda' => 'Ibishusho'],
        ['name' => 'Textiles', 'name_kinyarwanda' => 'Ibikoresho by\'ubudodo'],
        ['name' => 'Home Decor', 'name_kinyarwanda' => 'Ibikoresho by\'inzu'],
        ['name' => 'Paintings', 'name_kinyarwanda' => 'Amashusho']
    ];
    
    foreach ($default_categories as $cat) {
        $db->execute(
            "INSERT INTO categories (name, name_kinyarwanda) VALUES (?, ?)",
            [$cat['name'], $cat['name_kinyarwanda']]
        );
    }
    
    // Fetch categories again
    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
}

$page_title = getPageTitle('add-product');

// Include the header which contains the complete HTML structure
include '../includes/header.php';
?>

    <!-- Main Content -->
    <main class="add-product-main">
        <!-- Hero Section -->
        <section class="add-product-hero">
            <div class="container">
                <div class="add-product-hero-content">
                    <h1 class="add-product-hero-title">Add New Product</h1>
                    <p class="add-product-hero-subtitle">Showcase your craftsmanship to the world</p>
                    <div class="add-product-hero-description">
                        <p>Upload high-quality images and provide detailed information to help customers discover and appreciate your unique creations.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Add Product Form -->
        <section class="add-product-content">
            <div class="container">
                <div class="add-product-layout">
                    <div class="add-product-form-section">
                        <div class="form-header">
                            <h2 class="form-title">Product Information</h2>
                            <p class="form-description">Fill in the details below to create your product listing</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-error">
                                <span class="alert-icon">⚠️</span>
                                <span class="alert-message"><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <span class="alert-icon">✅</span>
                                <span class="alert-message"><?php echo htmlspecialchars($success); ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="add-product-form" id="addProductForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" id="name" name="name" class="form-input" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                    <div class="field-error" id="name_error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="category_id" class="form-label">Category *</label>
                                    <select id="category_id" name="category_id" class="form-input" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="field-error" id="category_id_error"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">Description *</label>
                                <textarea id="description" name="description" class="form-textarea" rows="4" 
                                          placeholder="Describe your product, its features, craftsmanship, and what makes it unique..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                <div class="field-error" id="description_error"></div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="price" class="form-label">Price (RWF) *</label>
                                    <input type="number" id="price" name="price" class="form-input" 
                                           value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                           min="0" step="100" required>
                                    <div class="field-error" id="price_error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                    <input type="number" id="stock_quantity" name="stock_quantity" class="form-input" 
                                           value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? '1'); ?>" 
                                           min="1" required>
                                    <div class="field-error" id="stock_quantity_error"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="materials" class="form-label">Materials Used</label>
                                    <input type="text" id="materials" name="materials" class="form-input" 
                                           value="<?php echo htmlspecialchars($_POST['materials'] ?? ''); ?>" 
                                           placeholder="e.g., Wood, Clay, Fabric, Metal">
                                    <div class="field-error" id="materials_error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="dimensions" class="form-label">Dimensions</label>
                                    <input type="text" id="dimensions" name="dimensions" class="form-input" 
                                           value="<?php echo htmlspecialchars($_POST['dimensions'] ?? ''); ?>" 
                                           placeholder="e.g., 20cm x 15cm x 10cm">
                                    <div class="field-error" id="dimensions_error"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="product_images" class="form-label">Product Images *</label>
                                <div class="image-upload-section">
                                    <div class="image-preview-grid" id="imagePreviewGrid">
                                        <div class="image-upload-placeholder">
                                            <span class="upload-icon">📷</span>
                                            <p>Click to upload images</p>
                                            <p class="upload-hint">First image will be the main product image</p>
                                        </div>
                                    </div>
                                    <input type="file" id="product_images" name="product_images[]" class="form-file" 
                                           accept="image/*" multiple style="display: none;">
                                    <label for="product_images" class="btn btn-outline">Choose Images</label>
                                    <p class="upload-help">Upload 1-5 images. First image will be the main product image. Max 5MB per image.</p>
                                </div>
                                <div class="field-error" id="product_images_error"></div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <span class="btn-text">Add Product</span>
                                    <span class="btn-loading" style="display: none;">Adding Product...</span>
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>

                    <!-- Product Tips -->
                    <div class="product-tips-section">
                        <div class="tips-header">
                            <h3 class="tips-title">Tips for Great Product Listings</h3>
                            <p class="tips-description">Make your products stand out with these helpful tips</p>
                        </div>
                        
                        <div class="tips-list">
                            <div class="tip-item">
                                <div class="tip-icon">📸</div>
                                <div class="tip-content">
                                    <h4>High-Quality Photos</h4>
                                    <p>Use good lighting and clear, detailed images that showcase your craftsmanship.</p>
                                </div>
                            </div>
                            
                            <div class="tip-item">
                                <div class="tip-icon">✍️</div>
                                <div class="tip-content">
                                    <h4>Detailed Descriptions</h4>
                                    <p>Tell the story behind your creation, materials used, and what makes it special.</p>
                                </div>
                            </div>
                            
                            <div class="tip-item">
                                <div class="tip-icon">💰</div>
                                <div class="tip-content">
                                    <h4>Fair Pricing</h4>
                                    <p>Price your work fairly, considering materials, time, and skill involved.</p>
                                </div>
                            </div>
                            
                            <div class="tip-item">
                                <div class="tip-icon">📏</div>
                                <div class="tip-content">
                                    <h4>Accurate Measurements</h4>
                                    <p>Provide precise dimensions so customers know exactly what they're getting.</p>
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
            const form = document.getElementById('addProductForm');
            const imageInput = document.getElementById('product_images');
            const imagePreviewGrid = document.getElementById('imagePreviewGrid');
            const submitBtn = document.getElementById('submitBtn');

            // Image preview functionality
            imageInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                
                if (files.length === 0) return;
                
                // Clear existing previews
                imagePreviewGrid.innerHTML = '';
                
                files.forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewDiv = document.createElement('div');
                            previewDiv.className = 'image-preview-item';
                            previewDiv.innerHTML = `
                                <img src="${e.target.result}" alt="Product preview ${index + 1}">
                                <div class="image-preview-overlay">
                                    <span class="image-number">${index + 1}</span>
                                    <button type="button" class="remove-image" onclick="removeImage(${index})">×</button>
                                </div>
                            `;
                            imagePreviewGrid.appendChild(previewDiv);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Basic validation
                const requiredFields = ['name', 'category_id', 'description', 'price', 'stock_quantity'];
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (!field.value.trim()) {
                        showFieldError(field, fieldId, 'This field is required');
                        isValid = false;
                    } else {
                        clearFieldError(field, fieldId);
                    }
                });
                
                // Price validation
                const priceField = document.getElementById('price');
                if (priceField.value && parseFloat(priceField.value) <= 0) {
                    showFieldError(priceField, 'price', 'Price must be greater than 0');
                    isValid = false;
                }
                
                // Stock validation
                const stockField = document.getElementById('stock_quantity');
                if (stockField.value && parseInt(stockField.value) < 1) {
                    showFieldError(stockField, 'stock_quantity', 'Stock quantity must be at least 1');
                    isValid = false;
                }
                
                // Image validation
                if (imageInput.files.length === 0) {
                    showFieldError(imageInput, 'product_images', 'Please upload at least one product image');
                    isValid = false;
                } else {
                    clearFieldError(imageInput, 'product_images');
                }
                
                if (!isValid) {
                    e.preventDefault();
                } else {
                    // Show loading state
                    submitBtn.querySelector('.btn-text').style.display = 'none';
                    submitBtn.querySelector('.btn-loading').style.display = 'inline';
                    submitBtn.disabled = true;
                }
            });

            function showFieldError(input, fieldId, message) {
                const errorElement = document.getElementById(fieldId + '_error');
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                }
                if (input) {
                    input.classList.add('error');
                }
            }

            function clearFieldError(input, fieldId) {
                const errorElement = document.getElementById(fieldId + '_error');
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                }
                if (input) {
                    input.classList.remove('error');
                }
            }
        });

        function removeImage(index) {
            const imageInput = document.getElementById('product_images');
            const dt = new DataTransfer();
            const files = Array.from(imageInput.files);
            
            files.forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            
            imageInput.files = dt.files;
            
            // Trigger change event to update preview
            const event = new Event('change');
            imageInput.dispatchEvent(event);
        }
    </script>

<?php include '../includes/footer.php'; ?> 