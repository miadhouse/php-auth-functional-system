<?php
/**
 * Admin Subscription Dashboard
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/subscription.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    set_flash_message('error', 'You do not have permission to access the admin area');
    redirect('../index.php');
}

// Get subscription analytics
$analytics = get_subscription_analytics();

// Get subscription statistics
$total_users = db_query_row("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
$subscribed_users = db_query_row("SELECT COUNT(*) as count FROM users WHERE subscription_status = 'active'")['count'] ?? 0;
$trial_users = db_query_row("SELECT COUNT(*) as count FROM user_subscriptions WHERE status = 'trial'")['count'] ?? 0;

// Monthly recurring revenue (MRR)
$mrr = db_query_row(
    "SELECT SUM(CASE WHEN billing_cycle = 'monthly' THEN amount ELSE amount/12 END) as mrr 
     FROM user_subscriptions 
     WHERE status IN ('active', 'trial')"
)['mrr'] ?? 0;

// Page title
$page_title = 'Subscription Dashboard';

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
                    <a href="subscriptions.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-star me-2"></i> Subscriptions
                    </a>
                    <a href="plans.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-layers me-2"></i> Subscription Plans
                    </a>
                    <a href="payments.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-credit-card me-2"></i> Payments
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-people me-2"></i> Users
                    </a>
                    <a href="analytics.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-graph-up me-2"></i> Analytics
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                    <a href="../index.php" class="list-group-item list-group-item-action text-primary">
                        <i class="bi bi-house me-2"></i> View Site
                    </a>
                    <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-10">
            <h2 class="mb-4">Subscription Dashboard</h2>
            
            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <?php if (empty($analytics['recent_subscriptions'])): ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No recent subscriptions found.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Plan</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($analytics['recent_subscriptions'], 0, 5) as $subscription): ?>
                                                <tr>
                                                    <td><?= h($subscription['user_name']) ?></td>
                                                    <td><?= h($subscription['plan_name']) ?></td>
                                                    <td>
                                                        <?php
                                                        $status_classes = [
                                                            'active' => 'bg-success',
                                                            'trial' => 'bg-info',
                                                            'cancelled' => 'bg-danger',
                                                            'expired' => 'bg-secondary'
                                                        ];
                                                        $status_class = $status_classes[$subscription['status']] ?? 'bg-secondary';
                                                        ?>
                                                        <span class="badge <?= $status_class ?>"><?= ucfirst($subscription['status']) ?></span>
                                                    </td>
                                                    <td><?= date('M j, Y', strtotime($subscription['created_at'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon & Quick Actions -->
            <div class="row">
                <!-- Expiring Soon -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Expiring Soon (Next 7 Days)</h6>
                            <span class="badge bg-warning"><?= count($analytics['expiring_soon']) ?> subscriptions</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($analytics['expiring_soon'])): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-calendar-check text-success" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No subscriptions expiring soon.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Plan</th>
                                                <th>Expires</th>
                                                <th>Days Left</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($analytics['expiring_soon'] as $subscription): ?>
                                                <?php 
                                                $days_left = ceil((strtotime($subscription['current_period_end']) - time()) / 86400);
                                                ?>
                                                <tr>
                                                    <td><?= h($subscription['user_name']) ?></td>
                                                    <td><?= h($subscription['plan_name']) ?></td>
                                                    <td><?= date('M j, Y', strtotime($subscription['current_period_end'])) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $days_left <= 3 ? 'danger' : 'warning' ?>">
                                                            <?= $days_left ?> days
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="subscription-detail.php?id=<?= $subscription['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
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
                
                <!-- Quick Actions -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="plans.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i> Add New Plan
                                </a>
                                <a href="subscriptions.php?status=trial" class="btn btn-outline-info">
                                    <i class="bi bi-gift me-2"></i> View Trial Users
                                </a>
                                <a href="payments.php?status=failed" class="btn btn-outline-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i> Failed Payments
                                </a>
                                <a href="analytics.php" class="btn btn-outline-success">
                                    <i class="bi bi-graph-up me-2"></i> Detailed Analytics
                                </a>
                                <a href="export.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-download me-2"></i> Export Data
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Health -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">System Health</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Payment Gateway</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Email Service</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Database</span>
                                <span class="badge bg-success">Healthy</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Backup Status</span>
                                <span class="badge bg-success">Current</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                        </div>
                        <div class="card-body">
                            <?php 
                            // Get recent activity logs
                            $recent_activity = db_query(
                                "SELECT al.*, u.name as user_name 
                                 FROM activity_logs al
                                 LEFT JOIN users u ON al.user_id = u.id
                                 WHERE al.action IN ('subscription_created', 'subscription_cancelled', 'subscription_changed', 'subscription_payment')
                                 ORDER BY al.created_at DESC 
                                 LIMIT 10"
                            );
                            ?>
                            
                            <?php if (empty($recent_activity)): ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No recent activity.</p>
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($recent_activity as $activity): ?>
                                        <div class="d-flex mb-3">
                                            <div class="flex-shrink-0">
                                                <?php
                                                $icon_class = match($activity['action']) {
                                                    'subscription_created' => 'bi-plus-circle text-success',
                                                    'subscription_cancelled' => 'bi-x-circle text-danger',
                                                    'subscription_changed' => 'bi-arrow-up-circle text-warning',
                                                    'subscription_payment' => 'bi-credit-card text-info',
                                                    default => 'bi-info-circle text-secondary'
                                                };
                                                ?>
                                                <i class="bi <?= $icon_class ?> me-3" style="font-size: 1.2rem;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <strong><?= h($activity['user_name'] ?? 'System') ?></strong>
                                                        <?= h($activity['detail']) ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= date('M j, g:i A', strtotime($activity['created_at'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="activity-logs.php" class="btn btn-outline-primary">View All Activity</a>
                                </div>
                            <?php endif; ?>
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
?>">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Active Subscriptions</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $analytics['total_active'] ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-star-fill fa-2x text-gray-300" style="font-size: 2rem;"></i>
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
                                        Monthly Revenue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= format_price($analytics['monthly_revenue']) ?></div>
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
                                        MRR</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= format_price($mrr) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-graph-up fa-2x text-gray-300" style="font-size: 2rem;"></i>
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
                                        Trial Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $trial_users ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-gift fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Total Users</h6>
                            <h2 class="card-text"><?= $total_users ?></h2>
                            <small class="text-muted">Registered users</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Conversion Rate</h6>
                            <h2 class="card-text"><?= $total_users > 0 ? round(($subscribed_users / $total_users) * 100, 1) : 0 ?>%</h2>
                            <small class="text-muted">Users to subscribers</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Yearly Revenue</h6>
                            <h2 class="card-text"><?= format_price($analytics['yearly_revenue']) ?></h2>
                            <small class="text-muted">This year total</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Subscriptions by Plan -->
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Subscriptions by Plan</h6>
                            <a href="plans.php" class="btn btn-sm btn-primary">Manage Plans</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($analytics['by_plan'])): ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No subscription data found.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Plan</th>
                                                <th>Type</th>
                                                <th class="text-end">Subscribers</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($analytics['by_plan'] as $plan): ?>
                                                <tr>
                                                    <td><?= h($plan['name']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $plan['plan_type'] === 'enterprise' ? 'dark' : $plan['plan_type'] ?>">
                                                            <?= ucfirst($plan['plan_type']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end"><?= $plan['count'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Subscriptions -->
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Subscriptions</h6>
                            <a href="subscriptions.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body