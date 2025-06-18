<?php
/**
 * User Subscription Management
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/subscription.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash_message('error', 'You must be logged in to manage your subscription');
    redirect('../index.php');
}

// Get current subscription
$subscription = get_user_subscription();

// Handle form submissions
$action_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $action_result = [
            'status' => false,
            'message' => 'Invalid security token. Please try again.'
        ];
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'cancel_subscription':
                if ($subscription) {
                    $immediate = isset($_POST['immediate']) && $_POST['immediate'] === '1';
                    $action_result = cancel_subscription($subscription['id'], $immediate);
                    
                    if ($action_result['status']) {
                        // Refresh subscription data
                        $subscription = get_user_subscription();
                    }
                }
                break;
                
            case 'reactivate_subscription':
                // For reactivation, redirect to plans page
                redirect('../subscription/plans.php');
                break;
        }
    }
}

// Get payment history
$payment_history = [];
if ($subscription) {
    $payment_history = get_subscription_payment_history($_SESSION['user_id'], 5);
}

// Get usage statistics
$usage_stats = [];
if ($subscription) {
    $usage_stats = get_subscription_usage($_SESSION['user_id']);
}

// Page title
$page_title = 'Manage Subscription';
$page = 'subscription';
// Include header
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
     <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <?php if ($action_result !== null): ?>
                <div class="alert alert-<?= $action_result['status'] ? 'success' : 'danger' ?>">
                    <?= h($action_result['message']) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($subscription): ?>
                <!-- Current Subscription -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Current Subscription</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="text-<?= $subscription['plan_type'] === 'enterprise' ? 'dark' : $subscription['plan_type'] ?>">
                                    <?= h($subscription['plan_name']) ?>
                                </h4>
                                <p class="text-muted mb-3"><?= ucfirst($subscription['billing_cycle']) ?> billing</p>
                                
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <strong>Status:</strong>
                                        <span class="badge bg-<?= $subscription['status'] === 'active' ? 'success' : ($subscription['status'] === 'trial' ? 'info' : 'warning') ?> ms-2">
                                            <?= ucfirst($subscription['status']) ?>
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <strong>Amount:</strong>
                                        <?= format_price($subscription['amount']) ?>/<?= $subscription['billing_cycle'] === 'yearly' ? 'year' : 'month' ?>
                                    </div>
                                    
                                    <?php if ($subscription['status'] === 'trial'): ?>
                                        <div class="col-sm-6 mb-3">
                                            <strong>Trial Ends:</strong>
                                            <?= date('F j, Y', strtotime($subscription['trial_ends_at'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="col-sm-6 mb-3">
                                            <strong>
                                                <?= $subscription['status'] === 'cancelled' ? 'Ends:' : 'Renews:' ?>
                                            </strong>
                                            <?= date('F j, Y', strtotime($subscription['current_period_end'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="col-sm-6 mb-3">
                                        <strong>Started:</strong>
                                        <?= date('F j, Y', strtotime($subscription['created_at'])) ?>
                                    </div>
                                </div>
                                
                                <?php if ($subscription['status'] === 'cancelled' && $subscription['cancelled_at']): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Subscription cancelled on <?= date('F j, Y', strtotime($subscription['cancelled_at'])) ?>.
                                        You'll continue to have access until <?= date('F j, Y', strtotime($subscription['ends_at'])) ?>.
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 text-md-end">
                                <?php if ($subscription['status'] === 'cancelled'): ?>
                                    <a href="../subscription/plans.php" class="btn btn-success mb-2">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        Reactivate
                                    </a>
                                <?php else: ?>
                                    <a href="../subscription/plans.php" class="btn btn-primary mb-2">
                                        <i class="bi bi-arrow-up-circle me-1"></i>
                                        Change Plan
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($subscription['status'] !== 'cancelled'): ?>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Cancel Subscription
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Subscription Features -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Plan Features</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $features = json_decode($subscription['plan_features'], true) ?? [];
                        $plan = get_subscription_plan($subscription['plan_id']);
                        ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Included Features</h6>
                                <ul class="list-unstyled">
                                    <?php foreach ($features as $feature): ?>
                                        <li class="mb-2">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <?= h($feature) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Usage Limits</h6>
                                <ul class="list-unstyled">
                                    <?php if ($plan['max_users'] !== null): ?>
                                        <li class="mb-2">
                                            <i class="bi bi-people me-2 text-primary"></i>
                                            <strong>Users:</strong> <?= $plan['max_users'] == -1 ? 'Unlimited' : $plan['max_users'] ?>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php if ($plan['max_storage_gb'] !== null): ?>
                                        <li class="mb-2">
                                            <i class="bi bi-hdd me-2 text-info"></i>
                                            <strong>Storage:</strong> <?= $plan['max_storage_gb'] == -1 ? 'Unlimited' : $plan['max_storage_gb'] . 'GB' ?>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php if ($plan['max_projects'] !== null): ?>
                                        <li class="mb-2">
                                            <i class="bi bi-folder me-2 text-warning"></i>
                                            <strong>Projects:</strong> <?= $plan['max_projects'] == -1 ? 'Unlimited' : $plan['max_projects'] ?>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <li class="mb-2">
                                        <i class="bi bi-headset me-2 text-danger"></i>
                                        <strong>Support:</strong> <?= $plan['priority_support'] ? 'Priority' : 'Standard' ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Payments -->
                <?php if (!empty($payment_history)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Payments</h5>
                            <a href="billing.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Plan</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($payment_history, 0, 3) as $payment): ?>
                                            <tr>
                                                <td><?= date('M j, Y', strtotime($payment['payment_date'])) ?></td>
                                                <td><?= format_price($payment['amount']) ?></td>
                                                <td><?= h($payment['plan_name']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $payment['status'] === 'completed' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($payment['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- No Active Subscription -->
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-star text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h3>No Active Subscription</h3>
                        <p class="text-muted mb-4">You don't have an active subscription. Choose a plan to unlock all features and start using our platform.</p>
                        <a href="../subscription/plans.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-star me-2"></i>
                            Choose a Plan
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Cancel Subscription Modal -->
<?php if ($subscription && $subscription['status'] !== 'cancelled'): ?>
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>We're sorry to see you go! Are you sure you want to cancel your subscription?</p>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>What happens when you cancel:</strong>
                    <ul class="mb-0 mt-2">
                        <li>You'll continue to have access until <?= date('F j, Y', strtotime($subscription['current_period_end'])) ?></li>
                        <li>No future charges will be made</li>
                        <li>You can reactivate anytime before your access expires</li>
                    </ul>
                </div>
                
                <form method="post" id="cancelForm">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                    <input type="hidden" name="action" value="cancel_subscription">
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="immediateCancel" name="immediate" value="1">
                        <label class="form-check-label" for="immediateCancel">
                            Cancel immediately (lose access right away)
                        </label>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Subscription</button>
                        <button type="submit" class="btn btn-danger">Cancel Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?>