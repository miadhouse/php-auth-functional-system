<?php
/**
 * Logout Script
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/config/config.php';

// Logout user
logout_user();

// Set flash message
set_flash_message('success', 'You have been successfully logged out');

// Redirect to home page
redirect('index.php');