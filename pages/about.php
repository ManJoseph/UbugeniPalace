<?php
require_once '../config/config.php';

$page_title = getPageTitle('about');

// Include the header which contains the complete HTML structure
include '../includes/header.php';
?>

    <!-- Main Content -->
    <main class="about-main">
        <!-- Hero Section -->
        <section class="about-hero">
            <div class="container">
                <div class="about-hero-content">
                    <h1 class="about-hero-title">Our Story</h1>
                    <p class="about-hero-subtitle">Connecting Rwandan Artisans with the World</p>
                    <div class="about-hero-description">
                        <p>UbugeniPalace is more than just a marketplace. We are a bridge between traditional Rwandan craftsmanship and global appreciation, preserving cultural heritage while creating economic opportunities for local artisans.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Vision Section -->
        <section class="mission-vision-section">
            <div class="container">
                <div class="mission-vision-grid">
                    <div class="mission-card">
                        <div class="mission-icon">🎯</div>
                        <h2 class="mission-title">Our Mission</h2>
                        <p class="mission-description">
                            To empower Rwandan artisans by providing them with a digital platform to showcase their unique craftsmanship, connect with global customers, and build sustainable livelihoods while preserving our rich cultural heritage.
                        </p>
                    </div>
                    
                    <div class="vision-card">
                        <div class="vision-icon">🌟</div>
                        <h2 class="vision-title">Our Vision</h2>
                        <p class="vision-description">
                            To become the leading platform for authentic African craftsmanship, where every artisan's story is celebrated, every piece tells a tale, and every purchase supports sustainable community development.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Story Section -->
        <section class="story-section">
            <div class="container">
                <div class="story-content">
                    <div class="story-text">
                        <h2 class="story-title">The Journey Begins</h2>
                        <p class="story-paragraph">
                            In the heart of Rwanda, where tradition meets innovation, our story began with a simple observation: the incredible talent of local artisans was often hidden from the world. These skilled craftspeople, masters of pottery, basket weaving, jewelry making, and textile arts, were creating pieces of extraordinary beauty and cultural significance.
                        </p>
                        <p class="story-paragraph">
                            However, their reach was limited to local markets and occasional craft fairs. The global audience that would truly appreciate and value their work remained out of reach. This realization sparked the creation of UbugeniPalace – a digital bridge connecting Rwandan artisans with art lovers worldwide.
                        </p>
                        <p class="story-paragraph">
                            Our name, "UbugeniPalace" (Palace of Beauty), reflects our commitment to sharing the knowledge, skill, and beauty embedded in every handcrafted piece. We believe that every artisan has a story to tell, every piece has a history to share, and every purchase has the power to transform lives.
                        </p>
                    </div>
                    <div class="story-image">
                        <img src="../assets/images/backgrounds/landscape.jpg" alt="Rwandan Landscape" class="story-img">
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="values-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Our Core Values</h2>
                    <p class="section-description">The principles that guide everything we do</p>
                </div>
                
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">🤝</div>
                        <h3 class="value-title">Community First</h3>
                        <p class="value-description">
                            We prioritize the well-being and growth of our artisan community, ensuring fair compensation and sustainable partnerships.
                        </p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">🏺</div>
                        <h3 class="value-title">Cultural Preservation</h3>
                        <p class="value-description">
                            We honor and preserve traditional Rwandan craftsmanship, ensuring these skills are passed down to future generations.
                        </p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">✨</div>
                        <h3 class="value-title">Quality Excellence</h3>
                        <p class="value-description">
                            Every piece on our platform meets the highest standards of craftsmanship and authenticity.
                        </p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">🌍</div>
                        <h3 class="value-title">Global Connection</h3>
                        <p class="value-description">
                            We bridge cultural gaps, connecting artisans with customers who appreciate the story behind every piece.
                        </p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">💚</div>
                        <h3 class="value-title">Sustainability</h3>
                        <p class="value-description">
                            We promote environmentally conscious practices and support sustainable economic development.
                        </p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">🎨</div>
                        <h3 class="value-title">Innovation</h3>
                        <p class="value-description">
                            We embrace technology to preserve tradition, creating new opportunities for artisans in the digital age.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Impact Section -->
        <section class="impact-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Our Impact</h2>
                    <p class="section-description">Making a difference in communities across Rwanda</p>
                </div>
                
                <div class="impact-stats">
                    <div class="impact-stat">
                        <div class="stat-number"><?php echo $db->rowCount("SELECT COUNT(*) FROM users WHERE user_type = 'artisan' AND is_active = 1"); ?>+</div>
                        <div class="stat-label">Artisans Empowered</div>
                    </div>
                    
                    <div class="impact-stat">
                        <div class="stat-number"><?php echo $db->rowCount("SELECT COUNT(*) FROM products WHERE status = 'active'"); ?>+</div>
                        <div class="stat-label">Products Showcased</div>
                    </div>
                    
                    <div class="impact-stat">
                        <div class="stat-number"><?php echo count($categories); ?></div>
                        <div class="stat-label">Craft Categories</div>
                    </div>
                    
                    <div class="impact-stat">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Communities Supported</div>
                    </div>
                </div>
                
                <div class="impact-stories">
                    <div class="impact-story">
                        <div class="story-image">
                            <img src="../assets/images/artisans/artisan2.jpg" alt="Artisan Success Story" class="story-img">
                        </div>
                        <div class="story-content">
                            <h3 class="story-title">Marie's Journey</h3>
                            <p class="story-text">
                                "Through UbugeniPalace, I've been able to reach customers from around the world. My traditional basket weaving skills are now appreciated globally, and I can support my family while preserving our cultural heritage."
                            </p>
                            <div class="story-author">- Marie Uwimana, Basket Weaver</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Meet Our Team</h2>
                    <p class="section-description">The passionate individuals behind UbugeniPalace</p>
                </div>
                
                <div class="team-grid">
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../assets/images/Committee/ceo.jpg" alt="Jean Pierre Ndayisaba - Founder & CEO" class="member-img">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Jean Pierre Ndayisaba</h3>
                            <div class="member-role">Founder & CEO</div>
                            <p class="member-bio">
                                A passionate advocate for Rwandan culture and economic development, Jean Pierre leads our mission to connect artisans with global opportunities.
                            </p>
                        </div>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../assets/images/Committee/artisan director.jpg" alt="Sarah Mukamana - Head of Artisan Relations" class="member-img">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Sarah Mukamana</h3>
                            <div class="member-role">Head of Artisan Relations</div>
                            <p class="member-bio">
                                Sarah works directly with our artisan community, ensuring their voices are heard and their needs are met throughout their journey with us.
                            </p>
                        </div>
                    </div>
                    
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../assets/images/Committee/tec_director.jpg" alt="Emmanuel Niyonzima - Technology Director" class="member-img">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Emmanuel Niyonzima</h3>
                            <div class="member-role">Technology Director</div>
                            <p class="member-bio">
                                Emmanuel ensures our platform provides the best possible experience for both artisans and customers through innovative technology solutions.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="about-cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2 class="cta-title">Join Our Mission</h2>
                    <p class="cta-description">
                        Whether you're an artisan looking to showcase your work or a customer seeking authentic Rwandan craftsmanship, we invite you to be part of our story.
                    </p>
                    <div class="cta-buttons">
                        <a href="products.php" class="btn btn-primary">Explore Products</a>
                        <a href="contact.php" class="btn btn-outline">Get in Touch</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php include '../includes/footer.php'; ?>
