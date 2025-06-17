<?php
/**
 * AJAX Authentication Handler
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Check if AJAX request
if (!is_ajax_request()) {
    http_response_code(403);
    echo json_encode([
        'status' => false,
        'message' => 'Forbidden: Direct access not allowed'
    ]);
    exit;
}

// Get action
$action = $_POST['action'] ?? '';

// Check CSRF token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response([
        'status' => false,
        'message' => 'Invalid security token. Please refresh the page and try again.'
    ], 403);
}

switch ($action) {
    case 'login':
        handle_login();
        break;
        
    case 'register':
        handle_register();
        break;
        
    case 'forgot_password':
        handle_forgot_password();
        break;
        
    default:
        json_response([
            'status' => false,
            'message' => 'Invalid action'
        ], 400);
}

/**
 * Handle login
 */
function handle_login() {
    // Validate required fields
    $validation_rules = [
        'email' => [
            ['rule' => 'validate_required', 'field_name' => 'email'],
            ['rule' => 'validate_email']
        ],
        'password' => [
            ['rule' => 'validate_required', 'field_name' => 'password']
        ]
    ];
    
    $errors = validate_form($_POST, $validation_rules);
    
    if (!empty($errors)) {
        json_response([
            'status' => false,
            'message' => reset($errors)
        ]);
    }
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) && $_POST['remember'] == 1;
    
    // Attempt login
    $result = login_user($email, $password, $remember);
    
    if ($result['status']) {
        // Login successful
        json_response([
            'status' => true,
            'message' => $result['message'],
            'redirect' => is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php'
        ]);
    } else {
        // Login failed
        json_response([
            'status' => false,
            'message' => $result['message']
        ]);
    }
}

/**
 * Handle registration
 */
function handle_register() {
    // Validate required fields
    $validation_rules = [
        'name' => [
            ['rule' => 'validate_required', 'field_name' => 'name'],
            ['rule' => 'validate_min_length', 'params' => [3], 'field_name' => 'name']
        ],
        'email' => [
            ['rule' => 'validate_required', 'field_name' => 'email'],
            ['rule' => 'validate_email']
        ],
        'password' => [
            ['rule' => 'validate_required', 'field_name' => 'password'],
            ['rule' => 'validate_password']
        ],
        'confirm_password' => [
            ['rule' => 'validate_required', 'field_name' => 'confirm password']
        ]
    ];
    
    $errors = validate_form($_POST, $validation_rules);
    
    // Check if passwords match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (!empty($errors)) {
        json_response([
            'status' => false,
            'message' => reset($errors)
        ]);
    }
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Attempt registration
    $result = register_user($name, $email, $password);
    
    json_response([
        'status' => $result['status'],
        'message' => $result['message']
    ]);
}

/**
 * Handle forgot password
 */
function handle_forgot_password() {
    // Validate required fields
    $validation_rules = [
        'email' => [
            ['rule' => 'validate_required', 'field_name' => 'email'],
            ['rule' => 'validate_email']
        ]
    ];
    
    $errors = validate_form($_POST, $validation_rules);
    
    if (!empty($errors)) {
        json_response([
            'status' => false,
            'message' => reset($errors)
        ]);
    }
    
    $email = $_POST['email'];
    
    // Request password reset
    $result = request_password_reset($email);
    
    // Always return success for security reasons
    json_response([
        'status' => true,
        'message' => 'If your email is registered, you will receive a password reset link shortly.'
    ]);
}