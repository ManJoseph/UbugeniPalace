<footer class="main-footer">
<div class="footer-content">
    <div class="container">
        <div class="footer-grid">
            <!-- Company Info -->
            <div class="footer-section company-info">
                <div class="footer-logo">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" alt="<?php echo SITE_NAME; ?>" class="footer-logo-img">
                    <div class="footer-logo-text">
                        <h3 class="footer-logo-title">UbugeniPalace</h3>
                        <p class="footer-logo-subtitle">U-Pal</p>
                    </div>
                </div>
                <p class="company-description">
                    Connecting talented Rwandan artisans with art lovers worldwide. 
                    Discover unique, handcrafted treasures that tell stories of tradition, 
                    skill, and creativity.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link facebook" title="Follow us on Facebook">
                        <img src="<?php echo SITE_URL; ?>/assets/images/icons/facebook.svg" alt="Facebook" class="social-icon">
                    </a>
                    <a href="#" class="social-link whatsapp" title="Contact us on WhatsApp">
                        <img src="<?php echo SITE_URL; ?>/assets/images/icons/whatsapp.svg" alt="WhatsApp" class="social-icon">
                    </a>
                    <a href="#" class="social-link twitter" title="Follow us on Twitter">
                        <img src="<?php echo SITE_URL; ?>/assets/images/icons/twitter.svg" alt="Twitter" class="social-icon">
                    </a>
                    <a href="#" class="social-link youtube" title="Subscribe to our YouTube">
                        <img src="<?php echo SITE_URL; ?>/assets/images/icons/youtube.svg" alt="YouTube" class="social-icon">
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-section quick-links">
                <h4 class="footer-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/products.php">All Products</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/artisans.php">Meet Artisans</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/about.php">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/contact.php">Contact Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/faq.php">FAQ</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/shipping.php">Shipping Info</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="footer-section categories-links">
                <h4 class="footer-title">Shop Categories</h4>
                <ul class="footer-links">
                    <?php foreach ($categories as $category): ?>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                            <span class="category-rw">(<?php echo htmlspecialchars($category['name_kinyarwanda']); ?>)</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="footer-section customer-service">
                <h4 class="footer-title">Customer Service</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/pages/help.php">Help Center</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/returns.php">Returns & Exchanges</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/privacy.php">Privacy Policy</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/terms.php">Terms of Service</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/support.php">Customer Support</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-section contact-info">
                <h4 class="footer-title">Get in Touch</h4>
                <div class="contact-details">
                    <div class="contact-item">
                        <span class="contact-icon">📍</span>
                        <div class="contact-text">
                            <strong>Address:</strong><br>
                            KG 123 St, Kigali<br>
                            Rwanda, East Africa
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">📞</span>
                        <div class="contact-text">
                            <strong>Phone:</strong><br>
                                <a href="tel:+250788123456">+250 788 123 456</a>
                            </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">✉️</span>
                        <div class="contact-text">
                            <strong>Email:</strong><br>
                                <a href="mailto:info@ubugenipalace.rw">info@ubugenipalace.rw</a>
                            </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">🕐</span>
                        <div class="contact-text">
                            <strong>Business Hours:</strong><br>
                            Mon - Fri: 8:00 AM - 6:00 PM<br>
                                Sat: Closed <br>
                                Sun: 9:00 AM - 4:00 PM
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer Bottom -->
<div class="footer-bottom">
    <div class="container">
        <div class="footer-bottom-content">
            <div class="footer-copyright">
                <p>&copy; <?php echo date('Y'); ?> UbugeniPalace. All rights reserved.</p>
                <p class="footer-tagline">Empowering Rwandan Artisans • Celebrating Cultural Heritage</p>
            </div>
            
            <div class="footer-certifications">
                <div class="certification-item">
                    <span class="cert-icon">🛡️</span>
                    <span class="cert-text">Secure Payments</span>
                </div>
                <div class="certification-item">
                    <span class="cert-icon">🚚</span>
                    <span class="cert-text">Fast Delivery</span>
                </div>
                <div class="certification-item">
                    <span class="cert-icon">✅</span>
                    <span class="cert-text">Authentic Products</span>
                </div>
                <div class="certification-item">
                    <span class="cert-icon">💯</span>
                    <span class="cert-text">Quality Guaranteed</span>
                </div>
            </div>

            <div class="footer-language">
                <button class="language-toggle" id="languageToggle">
                    <span class="lang-icon">🌐</span>
                    <span class="lang-text">English</span>
                    <span class="lang-arrow">▼</span>
                </button>
                <ul class="language-menu" id="languageMenu">
                    <li><a href="#" data-lang="en" class="lang-option active">English</a></li>
                    <li><a href="#" data-lang="rw" class="lang-option">Kinyarwanda</a></li>
                    <li><a href="#" data-lang="fr" class="lang-option">Français</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" title="Back to top">
    <span class="back-to-top-icon">↑</span>
</button>