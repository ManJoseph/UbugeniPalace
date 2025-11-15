<?php
require_once 'config/config.php';
$page_title = 'Test Login Modal';
include 'includes/header.php';
?>

<div style="padding: 100px 20px; text-align: center;">
    <h1>Login Modal Test</h1>
    <p>Click the login button in the header to test the popup modal.</p>
    
    <div style="margin: 40px 0;">
        <button onclick="openLoginModal()" class="btn btn-primary" style="font-size: 18px; padding: 15px 30px;">
            Test Login Modal
        </button>
    </div>
    
    <p>This page tests the login modal functionality. The modal should:</p>
    <ul style="text-align: left; max-width: 600px; margin: 20px auto;">
        <li>Open when clicking the login button</li>
        <li>Have a beautiful gradient header with logo</li>
        <li>Include email and password fields with icons</li>
        <li>Have a password toggle button</li>
        <li>Include "Remember me" and "Forgot password" options</li>
        <li>Show social login buttons</li>
        <li>Have a link to registration</li>
        <li>Close when clicking outside or pressing Escape</li>
        <li>Be fully responsive on mobile devices</li>
    </ul>
</div>

<?php include 'includes/footer.php'; ?> 