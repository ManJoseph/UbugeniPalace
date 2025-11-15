<?php
require_once '../config/config.php';

$page_title = getPageTitle('contact');

// Include the header which contains the complete HTML structure
include '../includes/header.php';

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Here you would typically save to database or send email
        // For now, we'll just show a success message
        $success_message = 'Thank you for your message! We will get back to you soon.';
        
        // Clear form data after successful submission
        $name = $email = $subject = $message = '';
    }
}
?>

    <!-- Main Content -->
    <main class="contact-main">
        <!-- Hero Section -->
        <section class="contact-hero">
            <div class="container">
                <div class="contact-hero-content">
                    <h1 class="contact-hero-title">Get in Touch</h1>
                    <p class="contact-hero-subtitle">We'd love to hear from you</p>
                    <div class="contact-hero-description">
                        <p>Have questions about our platform, need support, or want to collaborate? We're here to help you connect with Rwandan artisans and discover authentic craftsmanship.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Content -->
        <section class="contact-content">
            <div class="container">
                <div class="contact-layout">
                    <!-- Contact Form -->
                    <div class="contact-form-section">
                        <div class="form-header">
                            <h2 class="form-title">Send us a Message</h2>
                            <p class="form-description">Fill out the form below and we'll get back to you as soon as possible.</p>
                        </div>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-error">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form class="contact-form" method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" id="subject" name="subject" class="form-input" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message" class="form-label">Message *</label>
                                <textarea id="message" name="message" class="form-textarea" rows="6" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-full">Send Message</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="contact-info-section">
                        <div class="contact-info-header">
                            <h2 class="contact-info-title">Contact Information</h2>
                            <p class="contact-info-description">Reach out to us through any of these channels.</p>
                        </div>
                        
                        <div class="contact-methods">
                            <div class="contact-method">
                                <div class="method-icon">📍</div>
                                <div class="method-content">
                                    <h3 class="method-title">Visit Our Office</h3>
                                    <p class="method-details">
                                        Kigali, Rwanda<br>
                                        KG 123 Street, Nyarugenge District<br>
                                        Kigali City, Rwanda
                                    </p>
                                </div>
                            </div>
                            
                            <div class="contact-method">
                                <div class="method-icon">📧</div>
                                <div class="method-content">
                                    <h3 class="method-title">Email Us</h3>
                                    <p class="method-details">
                                        <a href="mailto:info@ubugenipalace.rw">info@ubugenipalace.rw</a><br>
                                        <a href="mailto:support@ubugenipalace.rw">support@ubugenipalace.rw</a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="contact-method">
                                <div class="method-icon">📞</div>
                                <div class="method-content">
                                    <h3 class="method-title">Call Us</h3>
                                    <p class="method-details">
                                        <a href="tel:+250788123456">+250 788 123 456</a><br>
                                        <a href="tel:+250789123456">+250 789 123 456</a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="contact-method">
                                <div class="method-icon">🕒</div>
                                <div class="method-content">
                                    <h3 class="method-title">Business Hours</h3>
                                    <p class="method-details">
                                        Monday - Friday: 8:00 AM - 6:00 PM<br>
                                        Saturday: Closed <br>
                                        Sunday: 9:00 AM - 4:00 PM
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="social-links-section">
                            <h3 class="social-title">Follow Us</h3>
                            <div class="social-links">
                                <a href="#" class="social-link" title="Facebook">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/icons/facebook.svg" alt="Facebook" class="social-icon">
                                    <span class="social-name">Facebook</span>
                                </a>
                                <a href="#" class="social-link" title="WhatsApp">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/icons/whatsapp.svg" alt="WhatsApp" class="social-icon">
                                    <span class="social-name">WhatsApp</span>
                                </a>
                                <a href="#" class="social-link" title="Twitter">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/icons/twitter.svg" alt="Twitter" class="social-icon">
                                    <span class="social-name">Twitter</span>
                                </a>
                                <a href="#" class="social-link" title="YouTube">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/icons/youtube.svg" alt="YouTube" class="social-icon">
                                    <span class="social-name">YouTube</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Frequently Asked Questions</h2>
                    <p class="section-description">Find quick answers to common questions</p>
                </div>
                
                <div class="faq-grid">
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How can I become an artisan on your platform?</h3>
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>To join as an artisan, simply register for an account and select "Artisan" as your user type. You'll need to provide information about your craft, upload photos of your work, and complete your profile. Our team will review your application and get back to you within 2-3 business days.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>What payment methods do you accept?</h3>
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>We accept various payment methods including credit/debit cards, mobile money (M-Pesa, Airtel Money), and bank transfers. All payments are processed securely through our trusted payment partners.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How do you ensure product quality?</h3>
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>We work closely with our artisan community to maintain high standards. Each artisan is carefully vetted, and we regularly review product quality. We also have a customer review system that helps maintain quality standards.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>What is your shipping policy?</h3>
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>We offer both local and international shipping. Local delivery within Rwanda typically takes 2-3 business days, while international shipping can take 7-14 days depending on the destination. Shipping costs are calculated based on weight and destination.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Can I request custom orders?</h3>
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes! Many of our artisans accept custom orders. You can contact them directly through their profile pages or reach out to us, and we'll connect you with the right artisan for your custom piece.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How can I support local artisans?</h3>
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>The best way to support local artisans is to purchase their products! You can also follow them on social media, leave positive reviews, and share their work with friends and family. Every purchase directly supports their livelihood and helps preserve traditional crafts.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section class="map-section">
            <div class="container">
                <div class="map-content">
                    <h2 class="map-title">Find Our Office</h2>
                    <p class="map-description">Visit us in Kigali to see our work in person</p>
                    <div class="map-placeholder">
                        <div class="map-image">
                            <img src="../assets/images/backgrounds/kigali.jpg" alt="Office Location" class="map-img">
                        </div>
                        <div class="map-overlay">
                            <div class="map-info">
                                <h3>UbugeniPalace</h3>
                                <p>KG 123 Street, Nyarugenge District<br>Kigali City, Rwanda</p>
                                <a href="#" class="btn btn-outline">Get Directions</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        // FAQ Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');
                const toggle = item.querySelector('.faq-toggle');
                
                question.addEventListener('click', function() {
                    const isActive = item.classList.contains('active');
                    
                    // Close all other FAQ items
                    faqItems.forEach(otherItem => {
                        otherItem.classList.remove('active');
                        otherItem.querySelector('.faq-answer').style.maxHeight = '0';
                        otherItem.querySelector('.faq-toggle').textContent = '+';
                    });
                    
                    // Toggle current item
                    if (!isActive) {
                        item.classList.add('active');
                        answer.style.maxHeight = answer.scrollHeight + 'px';
                        toggle.textContent = '−';
                    }
                });
            });
        });
    </script>

<?php include '../includes/footer.php'; ?>
