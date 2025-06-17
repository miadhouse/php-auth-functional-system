<?php
/**
 * User Orders Page
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash_message('error', 'You must be logged in to view your orders');
    redirect('../index.php');
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "SELECT id, order_number, total_amount, status, payment_status, created_at 
          FROM orders 
          WHERE user_id = ?";

$params = [$user_id];

// Add status filter if provided
if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

// Add order by and limit
$query .= " ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $per_page;

// Get orders
$orders = db_query($query, $params);

// Get total orders for pagination
$count_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
$count_params = [$user_id];

if (!empty($status_filter)) {
    $count_query .= " AND status = ?";
    $count_params[] = $status_filter;
}

$total_orders = db_query_row($count_query, $count_params)['count'] ?? 0;
$total_pages = ceil($total_orders / $per_page);

// Page title
$page_title = 'My Orders';

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
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Orders</h5>
                    
                    <!-- Filter Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= !empty($status_filter) ? 'Status: ' . ucfirst($status_filter) : 'Filter by Status' ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                            <li><a class="dropdown-item <?= empty($status_filter) ? 'active' : '' ?>" href="orders.php">All Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?= $status_filter === 'pending' ? 'active' : '' ?>" href="orders.php?status=pending">Pending</a></li>
                            <li><a class="dropdown-item <?= $status_filter === 'processing' ? 'active' : '' ?>" href="orders.php?status=processing">Processing</a></li>
                            <li><a class="dropdown-item <?= $status_filter === 'completed' ? 'active' : '' ?>" href="orders.php?status=completed">Completed</a></li>
                            <li><a class="dropdown-item <?= $status_filter === 'cancelled' ? 'active' : '' ?>" href="orders.php?status=cancelled">Cancelled</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-box2 text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <h5>No orders found</h5>
                            <?php if (!empty($status_filter)): ?>
                                <p class="text-muted">You don't have any <?= $status_filter ?> orders.</p>
                                <a href="orders.php" class="btn btn-outline-primary mt-2">View All Orders</a>
                            <?php else: ?>
                                <p class="text-muted">You haven't placed any orders yet.</p>
                                <a href="../products.php" class="btn btn-primary mt-2">Start Shopping</a>
                            <?php endif; ?>
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
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><a href="order-detail.php?id=<?= $order['id'] ?>"><?= h($order['order_number']) ?></a></td>
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
                                            </td>
                                            <td>
                                                <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php
                                    $status_param = !empty($status_filter) ? "&status=$status_filter" : "";
                                    
                                    // Previous button
                                    if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="orders.php?page=<?= $page - 1 . $status_param ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <!-- Page numbers -->
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="orders.php?page=<?= $i . $status_param ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <!-- Next button -->
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="orders.php?page=<?= $page + 1 . $status_param ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
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