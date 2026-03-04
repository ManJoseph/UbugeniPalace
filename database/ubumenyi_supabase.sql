-- UbugeniPalace Database Structure for PostgreSQL (Supabase)

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type VARCHAR(20) CHECK (user_type IN ('customer', 'artisan', 'admin')) DEFAULT 'customer',
    profile_image VARCHAR(255) DEFAULT 'default-avatar.jpg',
    address TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Artisans detailed profiles
CREATE TABLE artisans (
    id SERIAL PRIMARY KEY,
    user_id INT UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    bio TEXT,
    specialization VARCHAR(100),
    experience_years INT DEFAULT 0,
    location VARCHAR(100),
    workshop_address TEXT,
    social_media JSONB,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    cover_image VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Product categories
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    name_kinyarwanda VARCHAR(50),
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    artisan_id INT NOT NULL REFERENCES artisans(id) ON DELETE CASCADE,
    category_id INT NOT NULL REFERENCES categories(id),
    name VARCHAR(100) NOT NULL,
    name_kinyarwanda VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2) DEFAULT NULL,
    stock_quantity INT DEFAULT 1,
    materials VARCHAR(255),
    dimensions VARCHAR(100),
    weight DECIMAL(5,2),
    colors VARCHAR(255),
    main_image VARCHAR(255) NOT NULL,
    gallery_images JSONB,
    is_featured BOOLEAN DEFAULT FALSE,
    is_custom_order BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) CHECK (status IN ('active', 'inactive', 'sold')) DEFAULT 'active',
    views_count INT DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Shopping cart
CREATE TABLE cart (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    quantity INT DEFAULT 1,
    custom_notes TEXT,
    added_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_user_product UNIQUE (user_id, product_id)
);

-- Orders
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id),
    order_number VARCHAR(20) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_fee DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(20) CHECK (status IN ('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled')) DEFAULT 'pending',
    payment_status VARCHAR(20) CHECK (payment_status IN ('pending', 'paid', 'failed', 'refunded')) DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address JSONB NOT NULL,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Order items
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES products(id),
    artisan_id INT NOT NULL REFERENCES artisans(id),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    custom_notes TEXT
);

-- Product reviews
CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    order_id INT REFERENCES orders(id),
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    review_images JSONB,
    is_verified BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_user_product_review UNIQUE (user_id, product_id, order_id)
);

-- Wishlist
CREATE TABLE wishlist (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    added_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_user_product_wishlist UNIQUE (user_id, product_id)
);

-- Contact messages
CREATE TABLE contact_messages (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    replied_at TIMESTAMP WITH TIME ZONE NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Site settings
CREATE TABLE site_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    description TEXT,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Password Reset Requests Table
CREATE TABLE password_reset_requests (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    request_token VARCHAR(64) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    status VARCHAR(20) CHECK (status IN ('pending', 'approved', 'rejected', 'completed')) DEFAULT 'pending',
    admin_notes TEXT,
    new_password VARCHAR(255),
    processed_by INT REFERENCES users(id) ON DELETE SET NULL,
    processed_at TIMESTAMP WITH TIME ZONE NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Admin Notifications Table
CREATE TABLE admin_notifications (
    id SERIAL PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSONB,
    is_read BOOLEAN DEFAULT FALSE,
    read_by INT REFERENCES users(id) ON DELETE SET NULL,
    read_at TIMESTAMP WITH TIME ZONE NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO categories (name, name_kinyarwanda, description, image) VALUES
('Pottery', 'Ibicunga', 'Handcrafted clay pottery and ceramics', 'pottery-category.jpg'),
('Baskets', 'Agaseke', 'Traditional and modern woven baskets', 'baskets-category.jpg'),
('Jewelry', 'Imitako', 'Handmade jewelry and accessories', 'jewelry-category.jpg'),
('Textiles', 'Imyenda', 'Woven fabrics and textile art', 'textiles-category.jpg'),
('Home Decor', 'Imitako y''inzu', 'Decorative items for home', 'home-category.jpg'),
('Paintings', 'Amashusho', 'Original artwork and paintings', 'paints-category.jpg');

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, user_type) VALUES
('admin', 'admin@ubumenyi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Insert sample site settings
INSERT INTO site_settings (setting_key, setting_value, description) VALUES
('site_name', 'Ubumenyi bw''Ubugeni', 'Website name'),
('site_tagline', 'Discover Authentic Rwandan Craftsmanship', 'Website tagline'),
('contact_email', 'info@ubumenyi.rw', 'Contact email'),
('contact_phone', '+250 788 123 456', 'Contact phone number'),
('shipping_fee', '5000', 'Default shipping fee in RWF'),
('tax_rate', '18', 'Tax rate percentage');

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_artisan ON products(artisan_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_reviews_product ON reviews(product_id);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_email ON password_reset_requests(email);
CREATE INDEX idx_token ON password_reset_requests(request_token);
CREATE INDEX idx_status ON password_reset_requests(status);
CREATE INDEX idx_expires ON password_reset_requests(expires_at);
CREATE INDEX idx_notif_type ON admin_notifications(type);
CREATE INDEX idx_notif_user_id ON admin_notifications(user_id);
CREATE INDEX idx_notif_is_read ON admin_notifications(is_read);
CREATE INDEX idx_notif_created_at ON admin_notifications(created_at);
