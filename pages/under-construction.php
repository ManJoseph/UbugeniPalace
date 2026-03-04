<?php
require_once '../config/config.php';

$page_title = 'Feature Under Design | ' . SITE_NAME;

// Include the header which contains the complete HTML structure
include '../includes/header.php';
?>

<style>
    .under-construction-main {
        padding: 100px 20px;
        text-align: center;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .uc-content {
        max-width: 600px;
        margin: 0 auto;
        padding: 40px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }
    .uc-icon {
        font-size: 64px;
        margin-bottom: 24px;
        display: block;
    }
    .uc-title {
        color: #1e293b;
        font-size: 2.5rem;
        margin-bottom: 16px;
        font-family: 'Poppins', sans-serif;
    }
    .uc-message {
        color: #64748b;
        font-size: 1.25rem;
        line-height: 1.6;
        margin-bottom: 32px;
    }
    .uc-contact {
        display: inline-block;
        padding: 12px 24px;
        background-color: #2563eb;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .uc-contact:hover {
        background-color: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }
    .uc-back {
        display: block;
        margin-top: 20px;
        color: #64748b;
        text-decoration: none;
        font-size: 0.95rem;
    }
    .uc-back:hover {
        color: #2563eb;
        text-decoration: underline;
    }
</style>

<main class="under-construction-main">
    <div class="container">
        <div class="uc-content">
            <span class="uc-icon">🎨</span>
            <h1 class="uc-title">Feature Under Design</h1>
            <p class="uc-message">
                We're currently crafting this experience for you. 
                Our team of artisans and developers are working hard to bring this feature to life.
            </p>
            <p class="uc-message" style="font-weight: 500; color: #334155;">
                Contact development team through:<br>
                <a href="mailto:josephmanizabayo7@gmail.com" class="uc-contact">josephmanizabayo7@gmail.com</a>
            </p>
            <a href="<?php echo SITE_URL; ?>" class="uc-back">← Back to Home</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
