<?php
/**
 * AJAX Cart Content Handler
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

// Get cart data
$cart = get_cart();
$cart_items = $cart['items'] ?? [];
$total_quantity = $cart['total_quantity'] ?? 0;
$total_price = $cart['total_price'] ?? 0;

// Generate HTML for cart items
$html = '';

if (empty($cart_items)) {
    // Empty cart
    $html .= '
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
        </div>
        <h5>Your cart is empty</h5>
        <p class="text-muted">Start shopping and add some products to your cart!</p>
        <a href="' . SITE_URL . '/products.php" class="btn btn-primary mt-3">Browse Products</a>
    </div>';
} else {
    // Cart items
    $html .= '<div class="cart-items mb-4">';
    
    foreach ($cart_items as $key => $item) {
        $item_total = $item['price'] * $item['quantity'];
        
        $html .= '
        <div class="card mb-3 cart-item">
            <div class="row g-0">
                <div class="col-4">
                    <img src="' . ($item['image'] ?? SITE_URL . '/assets/images/product-placeholder.jpg') . '" 
                         class="img-fluid rounded-start" alt="' . h($item['name']) . '" 
                         style="height: 100px; object-fit: cover;">
                </div>
                <div class="col-8">
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1">' . h($item['name']) . '</h6>
                        <p class="card-text mb-1">
                            <small class="text-muted">' . format_price($item['price']) . ' x ' . $item['quantity'] . '</small>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">' . format_price($item_total) . '</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-from-cart" data-key="' . $key . '">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    $html .= '</div>';
    
    // Cart summary
    $html .= '
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <span class="fw-bold">' . format_price($total_price) . '</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Shipping:</span>
                <span>Calculated at checkout</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-3">
                <span class="fs-5">Total:</span>
                <span class="fs-5 fw-bold">' . format_price($total_price) . '</span>
            </div>
            <div class="d-grid gap-2">
                <a href="' . SITE_URL . '/cart.php" class="btn btn-outline-primary">
                    View Cart
                </a>
                <a href="' . SITE_URL . '/checkout.php" class="btn btn-primary">
                    Checkout
                </a>
            </div>
        </div>
    </div>';
}

// Return JSON response
json_response([
    'status' => true,
    'html' => $html,
    'total_quantity' => $total_quantity,
    'total_price' => $total_price
]);