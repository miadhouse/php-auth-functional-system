<?php
/**
 * Order Detail Page
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash_message('error', 'You must be logged in to view order details');
    redirect('../index.php');
}

// Get order ID
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    set_flash_message('error', 'Invalid order ID');
    redirect('orders.php');
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Get order details
$order = db_query_row(
    "SELECT o.*, u.name as user_name, u.email as user_email
     FROM orders o
     JOIN users u ON o.user_id = u.id
     WHERE o.id = ? AND o.user_id = ?",
    [$order_id, $user_id]
);

// Check if order exists and belongs to the user
if (!$order) {
    set_flash_message('error', 'Order not found or does not belong to you');
    redirect('orders.php');
}

// Get order items
$order_items = db_query(
    "SELECT oi.*, p.name as product_name, p.image as product_image
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?
     ORDER BY oi.id ASC",
    [$order_id]
);

// Calculate order summary
$subtotal = 0;
foreach ($order_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Assume shipping, tax, etc. are stored in the order record
$shipping = $order['shipping_cost'] ?? 0;
$tax = $order['tax_amount'] ?? 0;
$discount = $order['discount_amount'] ?? 0;

// Page title
$page_title = 'Order Details - ' . $order['order_number'];

// Include header
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">My Account</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-box-seam me-2"></i> My Orders
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-person me-2"></i> Edit Profile
                    </a>
                    <a href="addresses.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-geo-alt me-2"></i> Addresses
                    </a>
                    <a href="wishlist.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-heart me-2"></i> Wishlist
                    </a>
                    <a href="change-password.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield-lock me-2"></i> Change Password
                    </a>
                    <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Order Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order #<?= h($order['order_number']) ?></h5>
                    <a href="orders.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Order Information</h6>
                            <p class="mb-1"><strong>Order Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                            <p class="mb-1">
                                <strong>Order Status:</strong>
                                <?php
                                $status_classes = [
                                    'pending' => 'bg-warning text-dark',
                                    'processing' => 'bg-info text-dark',
                                    'completed' => 'bg-success text-white',
                                    'cancelled' => 'bg-danger text-white',
                                    'refunded' => 'bg-secondary text-white'
                                ];
                                $status_class = $status_classes[$order['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $status_class ?>"><?= ucfirst($order['status']) ?></span>
                            </p>
                            <p class="mb-1">
                                <strong>Payment Status:</strong>
                                <?php
                                $payment_classes = [
                                    'pending' => 'bg-warning text-dark',
                                    'paid' => 'bg-success text-white',
                                    'failed' => 'bg-danger text-white',
                                    'refunded' => 'bg-info text-dark'
                                ];
                                $payment_class = $payment_classes[$order['payment_status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $payment_class ?>"><?= ucfirst($order['payment_status']) ?></span>
                            </p>
                            <p class="mb-1"><strong>Payment Method:</strong> <?= h($order['payment_method'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> <?= h($order['user_name']) ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= h($order['user_email']) ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Shipping Address</h6>
                            <?php if (!empty($order['shipping_address'])): ?>
                                <address>
                                    <?= nl2br(h($order['shipping_address'])) ?>
                                </address>
                            <?php else: ?>
                                <p class="text-muted">No shipping address provided</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Billing Address</h6>
                            <?php if (!empty($order['billing_address'])): ?>
                                <address>
                                    <?= nl2br(h($order['billing_address'])) ?>
                                </address>
                            <?php else: ?>
                                <p class="text-muted">Same as shipping address</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <img src="<?= h($item['product_image'] ?? '../assets/images/product-placeholder.jpg') ?>" 
                                                         alt="<?= h($item['product_name']) ?>" 
                                                         class="img-thumbnail" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?= h($item['product_name']) ?></h6>
                                                    <?php 
                                                    if (!empty($item['options'])) {
                                                        $options = json_decode($item['options'], true);
                                                        if (is_array($options)) {
                                                            echo '<small class="text-muted">';
                                                            foreach ($options as $key => $value) {
                                                                echo h(ucfirst($key)) . ': ' . h($value) . '<br>';
                                                            }
                                                            echo '</small>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= format_price($item['price']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="text-end"><?= format_price($item['price'] * $item['quantity']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><?= format_price($subtotal) ?></td>
                                </tr>
                                <?php if ($shipping > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td class="text-end"><?= format_price($shipping) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($tax > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                                    <td class="text-end"><?= format_price($tax) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($discount > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                                    <td class="text-end">-<?= format_price($discount) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                    <td class="text-end fw-bold"><?= format_price($order['total_amount']) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <?php if (!empty($order['notes'])): ?>
                    <div class="mt-4">
                        <h6 class="fw-bold">Order Notes</h6>
                        <div class="card">
                            <div class="card-body bg-light">
                                <?= nl2br(h($order['notes'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="orders.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Orders
                        </a>
                        
                        <?php if ($order['status'] === 'completed'): ?>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print Order
                        </button>
                        <?php elseif ($order['status'] === 'pending'): ?>
                        <form method="post" action="cancel-order.php">
                            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="bi bi-x-circle me-1"></i> Cancel Order
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?>