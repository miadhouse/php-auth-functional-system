<?php
/**
 * Main Configuration
 * PHP 8.4 Pure Functional Script
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
define('SITE_URL', 'http://localhost/farsi-fahr-functional'); // Updated with correct path
define('BASE_URL', '/farsi-fahr-functional'); // Base URL path without domain
define('SITE_NAME', 'PHP Shop');
define('SITE_EMAIL', 'noreply@example.com');

// Email configuration for Gmail
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'persian.techfact@gmail.com');
define('MAIL_PASSWORD', 'mnwp skvr anly yjwl'); // Use app password for Gmail
define('MAIL_ENCRYPTION', 'tls');

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hour

// User roles
define('ROLE_ADMIN', 1);
define('ROLE_USER', 2);

// Cart settings
define('CART_COOKIE_NAME', 'shop_cart');
define('CART_COOKIE_EXPIRE', 86400 * 30); // 30 days

// Include required files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../includes/cart.php';
require_once __DIR__ . '/../includes/subscription.php'; // Add this line

// Initialize CSRF token
if (!isset($_SESSION[CSRF_TOKEN_NAME]) || 
    (isset($_SESSION[CSRF_TOKEN_NAME . '_time']) && 
     time() - $_SESSION[CSRF_TOKEN_NAME . '_time'] > CSRF_TOKEN_EXPIRE)) {
    
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
}