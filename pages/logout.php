<?php
require_once '../config/config.php';

// Clear all session data
session_start();
session_destroy();

// Clear any cookies
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to home page
redirectTo(SITE_URL);
?> 