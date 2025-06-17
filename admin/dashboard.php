<?php
/**
 * Admin Dashboard
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    set_flash_message('error', 'You do not have permission to access the admin area');
    redirect('../index.php');
}

// Get statistics
$total_users = db_query_row("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
$total_products = db_query_row("SELECT COUNT(*) as count FROM products")['count'] ?? 0;
$total_orders = db_query_row("SELECT COUNT(*) as count FROM orders")['count'] ?? 0;
$total_sales = db_query_row("SELECT SUM(total_amount) as total FROM orders WHERE status IN ('completed', 'processing')")['total'] ?? 0;

// Get recent orders
$recent_orders = db_query(
    "SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at, u.name as user_name  
     FROM orders o 
     JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC 
     LIMIT 5"
);

// Get low stock products
$low_stock_products = db_query(
    "SELECT id, name, stock, price 
     FROM products 
     WHERE stock <= 10 AND status = 'active' 
     ORDER BY stock ASC 
     LIMIT 5"
);

// Page title
$page_title = 'Admin Dashboard';

// Include header
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 mb-4">
            <!-- Admin Sidebar -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Admin Panel</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="products.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-box me-2"></i> Products
                    </a>
                    <a href="categories.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-tags me-2"></i> Categories
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-bag me-2"></i> Orders
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-people me-2"></i> Users
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                    <a href="../index.php" class="list-group-item list-group-item-action text-primary">
                        <i class="bi bi-shop me-2"></i> View Shop
                    </a>
                    <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-10">
            <h2 class="mb-4">Admin Dashboard</h2>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_users ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people-fill fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Sales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= format_price($total_sales) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-currency-dollar fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_orders ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-bag-fill fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Total Products</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_products ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-box-fill fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Recent Orders -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                            <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_orders)): ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No orders found.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Customer</th>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_orders as $order): ?>
                                                <tr>
                                                    <td><a href="order-detail.php?id=<?= $order['id'] ?>"><?= h($order['order_number']) ?></a></td>
                                                    <td><?= h($order['user_name']) ?></td>
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
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Low Stock Products</h6>
                            <a href="products.php?filter=low_stock" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($low_stock_products)): ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No low stock products found.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Stock</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($low_stock_products as $product): ?>
                                                <tr>
                                                    <td><a href="product-edit.php?id=<?= $product['id'] ?>"><?= h($product['name']) ?></a></td>
                                                    <td><span class="<?= $product['stock'] <= 5 ? 'text-danger fw-bold' : 'text-warning' ?>"><?= $product['stock'] ?></span></td>
                                                    <td><?= format_price($product['price']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="product-add.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i> Add New Product
                                </a>
                                <a href="category-add.php" class="btn btn-outline-primary">
                                    <i class="bi bi-folder-plus me-2"></i> Add New Category
                                </a>
                                <a href="orders.php?status=pending" class="btn btn-outline-warning">
                                    <i class="bi bi-clock me-2"></i> View Pending Orders
                                </a>
                            </div>
                        </div>
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