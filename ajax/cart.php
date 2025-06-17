<?php
/**
 * AJAX Cart Handler
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

// Check CSRF token for state-changing actions
if (in_array($action, ['add', 'update', 'remove', 'clear']) && !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    json_response([
        'status' => false,
        'message' => 'Invalid security token. Please refresh the page and try again.'
    ], 403);
}

switch ($action) {
    case 'add':
        handle_add_to_cart();
        break;
        
    case 'update':
        handle_update_cart();
        break;
        
    case 'remove':
        handle_remove_from_cart();
        break;
        
    case 'clear':
        handle_clear_cart();
        break;
        
    case 'get':
        handle_get_cart();
        break;
        
    default:
        json_response([
            'status' => false,
            'message' => 'Invalid action'
        ], 400);
}

/**
 * Handle add to cart
 */
function handle_add_to_cart() {
    // Validate product ID
    if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        json_response([
            'status' => false,
            'message' => 'Invalid product'
        ]);
    }
    
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $options = isset($_POST['options']) && is_array($_POST['options']) ? $_POST['options'] : [];
    
    // Ensure quantity is positive
    if ($quantity <= 0) {
        $quantity = 1;
    }
    
    // Add to cart
    $result = add_to_cart($product_id, $quantity, $options);
    
    json_response($result);
}

/**
 * Handle update cart
 */
function handle_update_cart() {
    // Validate item key and quantity
    if (!isset($_POST['item_key']) || empty($_POST['item_key'])) {
        json_response([
            'status' => false,
            'message' => 'Invalid item'
        ]);
    }
    
    if (!isset($_POST['quantity']) || !is_numeric($_POST['quantity'])) {
        json_response([
            'status' => false,
            'message' => 'Invalid quantity'
        ]);
    }
    
    $item_key = $_POST['item_key'];
    $quantity = (int)$_POST['quantity'];
    
    // Update cart
    $result = update_cart_item($item_key, $quantity);
    
    json_response($result);
}

/**
 * Handle remove from cart
 */
function handle_remove_from_cart() {
    // Validate item key
    if (!isset($_POST['item_key']) || empty($_POST['item_key'])) {
        json_response([
            'status' => false,
            'message' => 'Invalid item'
        ]);
    }
    
    $item_key = $_POST['item_key'];
    
    // Remove from cart
    $result = remove_from_cart($item_key);
    
    json_response($result);
}

/**
 * Handle clear cart
 */
function handle_clear_cart() {
    // Clear cart
    $result = clear_cart();
    
    json_response($result);
}

/**
 * Handle get cart
 */
function handle_get_cart() {
    // Get cart
    $cart = get_cart();
    
    json_response([
        'status' => true,
        'cart' => $cart
    ]);
}