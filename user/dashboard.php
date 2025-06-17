<?php
/**
 * User Dashboard
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash_message('error', 'You must be logged in to access your dashboard');
    redirect('index.php');
}

// Get user profile
$user = get_user_profile();

// Get recent orders
$recent_orders = db_query(
    "SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at 
     FROM orders o 
     WHERE o.user_id = ? 
     ORDER BY o.created_at DESC 
     LIMIT 5",
    [$_SESSION['user_id']]
);

// Page title
$page_title = 'My Dashboard';

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
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
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
                    <a href="<?= BASE_URL ?>/logout.php" class="list-group-item list-group-item-action text-danger">
    <i class="bi bi-box-arrow-right me-2"></i> Logout
</a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Welcome Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?= h($user['profile_image']) ?>" alt="Profile" class="rounded-circle" width="80" height="80">
                            <?php else: ?>
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="bi bi-person-circle text-secondary" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="mb-1">Welcome, <?= h($user['name']) ?>!</h4>
                            <p class="text-muted mb-0">
                                Last login: <?= $user['last_login'] ? date('F j, Y, g:i a', strtotime($user['last_login'])) : 'Never' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Orders</h5>
                                    <?php 
                                    $order_count = db_query_row(
                                        "SELECT COUNT(*) as count FROM orders WHERE user_id = ?", 
                                        [$_SESSION['user_id']]
                                    );
                                    ?>
                                    <h2 class="mb-0"><?= $order_count['count'] ?? 0 ?></h2>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Wishlist</h5>
                                    <?php 
                                    $wishlist_count = db_query_row(
                                        "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?", 
                                        [$_SESSION['user_id']]
                                    );
                                    ?>
                                    <h2 class="mb-0"><?= $wishlist_count['count'] ?? 0 ?></h2>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-heart text-danger" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="wishlist.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Cart</h5>
                                    <h2 class="mb-0"><?= get_cart_count() ?></h2>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-cart text-success" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="../cart.php" class="btn btn-sm btn-outline-primary">View Cart</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <h5>No orders yet</h5>
                            <p class="text-muted">You haven't placed any orders yet.</p>
                            <a href="../products.php" class="btn btn-primary">Shop Now</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><?= h($order['order_number']) ?></td>
                                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= format_price($order['total_amount']) ?></td>
                                            <td>
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
                                            </td>
                                            <td>
                                                <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-end mt-3">
                            <a href="orders.php" class="btn btn-outline-primary">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?>