<?php
// user/index.php
declare(strict_types=1);

// Include configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Include functions
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash_message('error', 'Please log in to access your dashboard');
    redirect(url());
}

// Check permission
if (!has_permission('view_user_dashboard')) {
    set_flash_message('error', 'You do not have permission to access this page');
    redirect(url());
}

// Get user data
$user = get_logged_in_user();

// Get recent orders
function get_recent_orders(int $user_id, int $limit = 5): array {
    $db = connect_db();
    if (!$db) {
        return [];
    }
    
    $stmt = $db->prepare("
        SELECT o.id, o.total, o.status, o.created_at, COUNT(oi.id) as item_count 
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    
    return $stmt->fetchAll();
}

// Page title
$page_title = 'User Dashboard - ' . APP_NAME;

// Get recent orders
$recent_orders = get_recent_orders($user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .dashboard-card {
            transition: transform 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/../partials/header.php'; ?>
    
    <!-- User Dashboard -->
    <div class="container py-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">User Dashboard</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="<?= url('user/index.php') ?>" class="list-group-item list-group-item-action active">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                        <a href="<?= url('user/profile.php') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-person me-2"></i> My Profile
                        </a>
                        <a href="<?= url('user/orders.php') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-bag me-2"></i> My Orders
                        </a>
                        <a href="<?= url('shop.php') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-shop me-2"></i> Go to Shop
                        </a>
                        <a href="#" id="logout-link" class="list-group-item list-group-item-action text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Welcome, <?= h($user['username']) ?>!</h2>
                </div>
                
                <!-- Dashboard Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card h-100 bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">My Orders</h5>
                                        <h2 class="mb-0"><?= count($recent_orders) ?></h2>
                                    </div>
                                    <i class="bi bi-bag fs-1"></i>
                                </div>
                                <p class="card-text mt-3">View your order history</p>
                                <a href="<?= url('user/orders.php') ?>" class="btn btn-outline-light btn-sm">View All</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card h-100 bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Profile</h5>
                                        <h2 class="mb-0"><i class="bi bi-person-check"></i></h2>
                                    </div>
                                    <i class="bi bi-person-circle fs-1"></i>
                                </div>
                                <p class="card-text mt-3">Update your profile information</p>
                                <a href="<?= url('user/profile.php') ?>" class="btn btn-outline-light btn-sm">Edit Profile</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card dashboard-card h-100 bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Shop Now</h5>
                                        <h2 class="mb-0"><i class="bi bi-cart-plus"></i></h2>
                                    </div>
                                    <i class="bi bi-shop fs-1"></i>
                                </div>
                                <p class="card-text mt-3">Browse our latest products</p>
                                <a href="<?= url('shop.php') ?>" class="btn btn-outline-light btn-sm">Go to Shop</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_orders)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?= $order['id'] ?></td>
                                                <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                                <td><?= $order['item_count'] ?></td>
                                                <td>$<?= number_format($order['total'], 2) ?></td>
                                                <td>
                                                    <?php 
                                                    $status_class = match($order['status']) {
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                    ?>
                                                    <span class="badge bg-<?= $status_class ?>">
                                                        <?= ucfirst($order['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?= url("user/order-details.php?id={$order['id']}") ?>" class="btn btn-sm btn-outline-primary">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-3">
                                <a href="<?= url('user/orders.php') ?>" class="btn btn-primary">
                                    View All Orders
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                You don't have any orders yet.
                            </div>
                            <a href="<?= url('shop.php') ?>" class="btn btn-primary">
                                Start Shopping
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
    
    <!-- Logout Form -->
    <form id="logout-form" action="<?= url('handlers/logout.php') ?>" method="POST" class="d-none">
        <?= csrf_field() ?>
    </form>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= url('assets/js/main.js') ?>"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle logout
            document.getElementById('logout-link').addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('logout-form').submit();
            });
        });
    </script>
</body>
</html>