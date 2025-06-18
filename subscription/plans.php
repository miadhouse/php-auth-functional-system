<?php
/**
 * Subscription Plans Page
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/subscription.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash_message('error', 'You must be logged in to view subscription plans');
    redirect('../index.php');
}

// Get current subscription
$current_subscription = get_user_subscription();

// Get all plans
$plans = get_subscription_plans();

// Page title
$page_title = 'Subscription Plans';

// Include header
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-5">
    <?php if ($current_subscription): ?>
        <!-- Current Subscription Info -->
        <div class="alert alert-info mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="alert-heading mb-1">Current Plan: <?= h($current_subscription['plan_name']) ?></h5>
                    <p class="mb-0">
                        Status: <span class="badge bg-<?= $current_subscription['status'] === 'active' ? 'success' : 'warning' ?>"><?= ucfirst($current_subscription['status']) ?></span>
                        <?php if ($current_subscription['status'] === 'trial'): ?>
                            | Trial ends: <?= date('F j, Y', strtotime($current_subscription['trial_ends_at'])) ?>
                        <?php else: ?>
                            | Renews: <?= date('F j, Y', strtotime($current_subscription['current_period_end'])) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <a href="../user/subscription.php" class="btn btn-outline-primary">Manage Subscription</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mb-5">
        <h1 class="fw-bold">Choose Your Plan</h1>
        <p class="lead text-muted">
            <?= $current_subscription ? 'Upgrade or change your current plan' : 'Select the perfect plan for your needs' ?>
        </p>
        
        <!-- Billing Toggle -->
        <div class="d-flex justify-content-center align-items-center mb-4">
            <span class="me-3">Monthly</span>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="billingToggle">
                <label class="form-check-label" for="billingToggle"></label>
            </div>
            <span class="ms-3">Yearly <span class="badge bg-success">Save 20%</span></span>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <?php foreach ($plans as $plan): ?>
            <?php 
            $is_current_plan = $current_subscription && $current_subscription['plan_id'] == $plan['id'];
            $features = json_decode($plan['features'], true) ?? [];
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm <?= $plan['plan_type'] === 'gold' ? 'border-warning' : '' ?> <?= $is_current_plan ? 'border-success' : '' ?>" 
                     style="<?= $plan['plan_type'] === 'gold' ? 'border: 2px solid #ffc107 !important;' : '' ?> <?= $is_current_plan ? 'border: 2px solid #198754 !important;' : '' ?>">
                    
                    <?php if ($plan['plan_type'] === 'gold' && !$is_current_plan): ?>
                        <div class="position-absolute top-0 start-50 translate-middle">
                            <span class="badge bg-warning text-dark px-3 py-2">Most Popular</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($is_current_plan): ?>
                        <div class="position-absolute top-0 start-50 translate-middle">
                            <span class="badge bg-success px-3 py-2">Current Plan</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-header bg-white border-0 text-center pt-4">
                        <h3 class="fw-bold text-<?= $plan['plan_type'] === 'enterprise' ? 'dark' : $plan['plan_type'] ?>"><?= h($plan['name']) ?></h3>
                        <p class="text-muted"><?= h($plan['description']) ?></p>
                    </div>
                    
                    <div class="card-body text-center">
                        <div class="pricing-display mb-4">
                            <div class="monthly-price">
                                <span class="h2 fw-bold">$<?= number_format($plan['price_monthly'], 0) ?></span>
                                <span class="text-muted">/month</span>
                            </div>
                            <div class="yearly-price d-none">
                                <span class="h2 fw-bold">$<?= number_format($plan['price_yearly'], 0) ?></span>
                                <span class="text-muted">/year</span>
                                <div class="small text-success">
                                    Save $<?= number_format(($plan['price_monthly'] * 12) - $plan['price_yearly'], 0) ?> per year
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($plan['trial_days'] > 0 && !$current_subscription): ?>
                            <p class="text-success small mb-3">
                                <i class="bi bi-gift-fill me-1"></i>
                                <?= $plan['trial_days'] ?>-day free trial
                            </p>
                        <?php endif; ?>
                        
                        <ul class="list-unstyled text-start mb-4">
                            <?php foreach ($features as $feature): ?>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <?= h($feature) ?>
                                </li>
                            <?php endforeach; ?>
                            
                            <?php if ($plan['max_users'] !== null): ?>
                                <li class="mb-2">
                                    <i class="bi bi-people-fill text-primary me-2"></i>
                                    <?= $plan['max_users'] == -1 ? 'Unlimited' : $plan['max_users'] ?> Users
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($plan['max_storage_gb'] !== null): ?>
                                <li class="mb-2">
                                    <i class="bi bi-hdd-fill text-info me-2"></i>
                                    <?= $plan['max_storage_gb'] == -1 ? 'Unlimited' : $plan['max_storage_gb'] . 'GB' ?> Storage
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($plan['max_projects'] !== null): ?>
                                <li class="mb-2">
                                    <i class="bi bi-folder-fill text-warning me-2"></i>
                                    <?= $plan['max_projects'] == -1 ? 'Unlimited' : $plan['max_projects'] ?> Projects
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($plan['priority_support']): ?>
                                <li class="mb-2">
                                    <i class="bi bi-headset text-danger me-2"></i>
                                    Priority Support
                                </li>
                            <?php endif; ?>
                        </ul>
                        
                        <?php if ($is_current_plan): ?>
                            <button class="btn btn-outline-success w-100" disabled>
                                <i class="bi bi-check-circle me-1"></i>
                                Current Plan
                            </button>
                        <?php else: ?>
                            <form method="get" action="subscribe.php" class="subscribe-form">
                                <input type="hidden" name="plan" value="<?= h($plan['slug']) ?>">
                                <input type="hidden" name="billing" value="monthly" class="billing-input">
                                <button type="submit" class="btn btn-<?= $plan['plan_type'] === 'gold' ? 'warning' : 'primary' ?> w-100">
                                    <?php if ($current_subscription): ?>
                                        Switch to <?= h($plan['name']) ?>
                                    <?php else: ?>
                                        Start Free Trial
                                    <?php endif; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-5">
        <p class="text-muted">
            Need help choosing? <a href="../contact.php">Contact our team</a> or 
            <a href="../user/dashboard.php">go back to dashboard</a>
        </p>
    </div>
</div>

<!-- Billing Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const billingToggle = document.getElementById('billingToggle');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const yearlyPrices = document.querySelectorAll('.yearly-price');
    const billingInputs = document.querySelectorAll('.billing-input');
    
    billingToggle.addEventListener('change', function() {
        const isYearly = this.checked;
        
        if (isYearly) {
            // Show yearly prices
            monthlyPrices.forEach(price => price.classList.add('d-none'));
            yearlyPrices.forEach(price => price.classList.remove('d-none'));
            billingInputs.forEach(input => input.value = 'yearly');
        } else {
            // Show monthly prices
            monthlyPrices.forEach(price => price.classList.remove('d-none'));
            yearlyPrices.forEach(price => price.classList.add('d-none'));
            billingInputs.forEach(input => input.value = 'monthly');
        }
    });
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?>