<?php
/**
 * Shopping Cart Functions
 * PHP 8.4 Pure Functional Script
 */

/**
 * Initialize cart
 *
 * @return array Cart data
 */
function initialize_cart() {
    // Check for existing cart in session
    if (isset($_SESSION['cart'])) {
        return $_SESSION['cart'];
    }
    
    // Check for existing cart in cookie
    if (isset($_COOKIE[CART_COOKIE_NAME])) {
        $cart_data = json_decode($_COOKIE[CART_COOKIE_NAME], true);
        if (is_array($cart_data)) {
            $_SESSION['cart'] = $cart_data;
            return $cart_data;
        }
    }
    
    // Create new empty cart
    $cart = [
        'items' => [],
        'total_quantity' => 0,
        'total_price' => 0.00,
        'created_at' => time(),
        'updated_at' => time()
    ];
    
    $_SESSION['cart'] = $cart;
    return $cart;
}

/**
 * Save cart
 *
 * @param array $cart Cart data
 * @return void
 */
function save_cart($cart) {
    // Update timestamp
    $cart['updated_at'] = time();
    
    // Save to session
    $_SESSION['cart'] = $cart;
    
    // Save to cookie
    setcookie(
        CART_COOKIE_NAME,
        json_encode($cart),
        time() + CART_COOKIE_EXPIRE,
        '/',
        '',
        true,
        true
    );
    
    // If user is logged in, save to database
    if (is_logged_in()) {
        $user_id = $_SESSION['user_id'];
        
        // Check if user has a cart in DB
        $existing_cart = db_query_row(
            "SELECT id FROM carts WHERE user_id = ? AND status = 'active'",
            [$user_id]
        );
        
        $cart_data = json_encode($cart);
        
        if ($existing_cart) {
            // Update existing cart
            db_execute(
                "UPDATE carts SET cart_data = ?, updated_at = NOW() WHERE id = ?",
                [$cart_data, $existing_cart['id']]
            );
        } else {
            // Create new cart
            db_execute(
                "INSERT INTO carts (user_id, cart_data, created_at, updated_at, status) 
                 VALUES (?, ?, NOW(), NOW(), 'active')",
                [$user_id, $cart_data]
            );
        }
    }
}

/**
 * Calculate cart totals
 *
 * @param array $cart Cart data
 * @return array Updated cart with totals
 */
function calculate_cart_totals($cart) {
    $total_quantity = 0;
    $total_price = 0.00;
    
    foreach ($cart['items'] as $item) {
        $total_quantity += $item['quantity'];
        $total_price += $item['price'] * $item['quantity'];
    }
    
    $cart['total_quantity'] = $total_quantity;
    $cart['total_price'] = $total_price;
    
    return $cart;
}

/**
 * Add item to cart
 *
 * @param int $product_id Product ID
 * @param int $quantity Quantity to add
 * @param array $options Product options
 * @return array Updated cart
 */
function add_to_cart($product_id, $quantity = 1, $options = []) {
    // Get product data
    $product = db_query_row(
        "SELECT id, name, price, image, stock FROM products WHERE id = ? AND status = 'active'",
        [$product_id]
    );
    
    if (!$product) {
        return [
            'status' => false,
            'message' => 'Product not found'
        ];
    }
    
    // Check stock
    if ($product['stock'] < $quantity) {
        return [
            'status' => false,
            'message' => 'Not enough stock available'
        ];
    }
    
    // Initialize cart
    $cart = initialize_cart();
    
    // Create unique item key based on product ID and options
    $item_key = $product_id;
    if (!empty($options)) {
        $item_key .= '_' . md5(json_encode($options));
    }
    
    // Check if item already exists in cart
    if (isset($cart['items'][$item_key])) {
        // Update quantity
        $cart['items'][$item_key]['quantity'] += $quantity;
    } else {
        // Add new item
        $cart['items'][$item_key] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'quantity' => $quantity,
            'image' => $product['image'],
            'options' => $options
        ];
    }
    
    // Recalculate totals
    $cart = calculate_cart_totals($cart);
    
    // Save cart
    save_cart($cart);
    
    return [
        'status' => true,
        'message' => 'Product added to cart',
        'cart' => $cart
    ];
}

/**
 * Update cart item quantity
 *
 * @param string $item_key Item key
 * @param int $quantity New quantity
 * @return array Updated cart
 */
function update_cart_item($item_key, $quantity) {
    // Initialize cart
    $cart = initialize_cart();
    
    // Check if item exists
    if (!isset($cart['items'][$item_key])) {
        return [
            'status' => false,
            'message' => 'Item not found in cart'
        ];
    }
    
    // Get product ID
    $product_id = $cart['items'][$item_key]['id'];
    
    // Check stock
    $product = db_query_row(
        "SELECT stock FROM products WHERE id = ?",
        [$product_id]
    );
    
    if (!$product || $product['stock'] < $quantity) {
        return [
            'status' => false,
            'message' => 'Not enough stock available'
        ];
    }
    
    if ($quantity <= 0) {
        // Remove item
        unset($cart['items'][$item_key]);
    } else {
        // Update quantity
        $cart['items'][$item_key]['quantity'] = $quantity;
    }
    
    // Recalculate totals
    $cart = calculate_cart_totals($cart);
    
    // Save cart
    save_cart($cart);
    
    return [
        'status' => true,
        'message' => 'Cart updated',
        'cart' => $cart
    ];
}

/**
 * Remove item from cart
 *
 * @param string $item_key Item key
 * @return array Updated cart
 */
function remove_from_cart($item_key) {
    // Initialize cart
    $cart = initialize_cart();
    
    // Check if item exists
    if (!isset($cart['items'][$item_key])) {
        return [
            'status' => false,
            'message' => 'Item not found in cart'
        ];
    }
    
    // Remove item
    unset($cart['items'][$item_key]);
    
    // Recalculate totals
    $cart = calculate_cart_totals($cart);
    
    // Save cart
    save_cart($cart);
    
    return [
        'status' => true,
        'message' => 'Item removed from cart',
        'cart' => $cart
    ];
}

/**
 * Clear cart
 *
 * @return array Empty cart
 */
function clear_cart() {
    // Create empty cart
    $cart = [
        'items' => [],
        'total_quantity' => 0,
        'total_price' => 0.00,
        'created_at' => time(),
        'updated_at' => time()
    ];
    
    // Save empty cart
    save_cart($cart);
    
    return [
        'status' => true,
        'message' => 'Cart cleared',
        'cart' => $cart
    ];
}

/**
 * Get cart data
 *
 * @return array Cart data
 */
function get_cart() {
    return initialize_cart();
}

/**
 * Get cart item count
 *
 * @return int Number of items in cart
 */
function get_cart_count() {
    $cart = initialize_cart();
    // Check if total_quantity exists, if not calculate it
    if (!isset($cart['total_quantity'])) {
        $total_quantity = 0;
        
        // Make sure items array exists
        if (isset($cart['items']) && is_array($cart['items'])) {
            foreach ($cart['items'] as $item) {
                $total_quantity += isset($item['quantity']) ? (int)$item['quantity'] : 0;
            }
        }
        
        $cart['total_quantity'] = $total_quantity;
        
        // Save updated cart with total_quantity
        save_cart($cart);
    }
    
    return $cart['total_quantity'] ?? 0;
}

/**
 * Merge guest cart with user cart
 *
 * @param int $user_id User ID
 * @return array Merged cart
 */
function merge_carts($user_id) {
    // Get session cart
    $session_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : ['items' => []];
    
    // Get user cart from database
    $db_cart_row = db_query_row(
        "SELECT cart_data FROM carts WHERE user_id = ? AND status = 'active' ORDER BY updated_at DESC LIMIT 1",
        [$user_id]
    );
    
    if (!$db_cart_row) {
        // No db cart, just use session cart
        return $session_cart;
    }
    
    $db_cart = json_decode($db_cart_row['cart_data'], true);
    
    if (empty($session_cart['items'])) {
        // No session cart items, just use db cart
        return $db_cart;
    }
    
    if (empty($db_cart['items'])) {
        // No db cart items, just use session cart
        return $session_cart;
    }
    
    // Merge carts
    foreach ($session_cart['items'] as $key => $item) {
        if (isset($db_cart['items'][$key])) {
            // Item exists in both carts, use the higher quantity
            $db_cart['items'][$key]['quantity'] += $item['quantity'];
        } else {
            // Item only in session cart, add to db cart
            $db_cart['items'][$key] = $item;
        }
    }
    
    // Recalculate totals
    $db_cart = calculate_cart_totals($db_cart);
    
    // Save merged cart
    save_cart($db_cart);
    
    return $db_cart;
}

/**
 * Sync cart with database
 * 
 * @return array Updated cart
 */
function sync_cart_with_database() {
    if (!is_logged_in()) {
        return initialize_cart();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get user's latest cart from database
    $db_cart_row = db_query_row(
        "SELECT cart_data FROM carts WHERE user_id = ? AND status = 'active' ORDER BY updated_at DESC LIMIT 1",
        [$user_id]
    );
    
    if (!$db_cart_row) {
        // No cart in database, use session cart
        $cart = initialize_cart();
        save_cart($cart);
        return $cart;
    }
    
    // Use database cart
    $db_cart = json_decode($db_cart_row['cart_data'], true);
    
    // Update session
    $_SESSION['cart'] = $db_cart;
    
    return $db_cart;
}

/**
 * Validate cart items (check stock, availability, etc.)
 * 
 * @return array Validation results
 */
function validate_cart() {
    $cart = initialize_cart();
    $results = [
        'valid' => true,
        'messages' => [],
        'invalid_items' => []
    ];
    
    if (empty($cart['items'])) {
        $results['valid'] = false;
        $results['messages'][] = 'Cart is empty';
        return $results;
    }
    
    foreach ($cart['items'] as $key => $item) {
        // Check if product exists and is active
        $product = db_query_row(
            "SELECT id, name, price, stock, status FROM products WHERE id = ?",
            [$item['id']]
        );
        
        if (!$product) {
            // Product not found
            $results['valid'] = false;
            $results['messages'][] = "Product '{$item['name']}' is no longer available";
            $results['invalid_items'][] = $key;
            continue;
        }
        
        if ($product['status'] !== 'active') {
            // Product not active
            $results['valid'] = false;
            $results['messages'][] = "Product '{$item['name']}' is no longer available";
            $results['invalid_items'][] = $key;
            continue;
        }
        
        if ($product['stock'] < $item['quantity']) {
            // Not enough stock
            $results['valid'] = false;
            $results['messages'][] = "Only {$product['stock']} of '{$item['name']}' available";
            $results['invalid_items'][] = $key;
            continue;
        }
        
        // Check if price has changed
        if (abs($product['price'] - $item['price']) > 0.01) {
            // Price has changed, update item
            $cart['items'][$key]['price'] = (float)$product['price'];
            $cart['items'][$key]['price_changed'] = true;
            $results['messages'][] = "Price for '{$item['name']}' has been updated";
        }
    }
    
    // If any prices changed, recalculate totals and save cart
    if (in_array('price_changed', array_column($cart['items'], 'price_changed'))) {
        $cart = calculate_cart_totals($cart);
        save_cart($cart);
    }
    
    return $results;
}