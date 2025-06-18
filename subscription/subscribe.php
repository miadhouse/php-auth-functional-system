<?php
/**
 * Subscription Subscribe Page
 * PHP 8.4 Pure Functional Script
 * Save as: subscription/subscribe.php
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/subscription.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash_message('error', 'You must be logged in to subscribe to a plan');
    redirect('../index.php');
}

// Get plan from URL parameter
$plan_slug = $_GET['plan'] ?? '';
$billing_cycle = $_GET['billing'] ?? 'monthly';

if (empty($plan_slug)) {
    set_flash_message('error', 'Please select a subscription plan');
    redirect('plans.php');
}

// Get plan details
$plan = get_subscription_plan_by_slug($plan_slug);

if (!$plan) {
    set_flash_message('error', 'Invalid subscription plan selected');
    redirect('plans.php');
}

// Check if user already has an active subscription
$current_subscription = get_user_subscription();
$is_changing_plan = false;

if ($current_subscription) {
    if ($current_subscription['plan_id'] == $plan['id']) {
        set_flash_message('info', 'You are already subscribed to this plan');
        redirect('../user/subscription.php');
    }
    $is_changing_plan = true;
}

// Calculate pricing
$amount = $billing_cycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
$trial_days = !$is_changing_plan ? $plan['trial_days'] : 0;
$savings = $billing_cycle === 'yearly' ? ($plan['price_monthly'] * 12) - $plan['price_yearly'] : 0;

// Handle form submission
$subscription_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $subscription_result = [
            'status' => false,
            'message' => 'Invalid security token. Please try again.'
        ];
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'subscribe') {
            if ($is_changing_plan) {
                // Change existing subscription
                $subscription_result = change_subscription_plan(
                    $current_subscription['id'], 
                    $plan['id'], 
                    $billing_cycle
                );
            } else {
                // Create new subscription
                $subscription_result = create_subscription(
                    $_SESSION['user_id'], 
                    $plan['id'], 
                    $billing_cycle, 
                    $trial_days > 0
                );
            }
            
            if ($subscription_result['status']) {
                // Redirect to success page or dashboard
                set_flash_message('success', $subscription_result['message']);
                redirect('../user/subscription.php');
            }
        }
    }
}

// Parse plan features
$features = json_decode($plan['features'], true) ?? [];

// Page title
$page_title = 'Subscribe to ' . $plan['name'];

// Include header
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="plans.php">Plans</a></li>
                    <li class="breadcrumb-item active"><?= h($plan['name']) ?></li>
                </ol>
            </nav>
            
            <?php if ($subscription_result && !$subscription_result['status']): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= h($subscription_result['message']) ?>
                </div>
            <?php endif; ?>
            
            <!-- Plan Details Card -->
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-header bg-<?= get_plan_type_color($plan['plan_type']) ?> text-white py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1"><?= h($plan['name']) ?></h2>
                            <p class="mb-0 opacity-75"><?= h($plan['description']) ?></p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="pricing-display">
                                <div class="h2 mb-0">
                                    <?= format_price($amount) ?>
                                </div>
                                <div class="small">
                                    per <?= $billing_cycle === 'yearly' ? 'year' : 'month' ?>
                                </div>
                                <?php if ($billing_cycle === 'yearly' && $savings > 0): ?>
                                    <div class="badge bg-light text-dark mt-2">
                                        Save <?= format_price($savings) ?> yearly
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="row">
                        <!-- Plan Features -->
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3">What's Included</h5>
                            <ul class="list-unstyled">
                                <?php foreach ($features as $feature): ?>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <?= h($feature) ?>
                                    </li>
                                <?php endforeach; ?>
                                
                                <?php if ($plan['max_users'] !== null): ?>
                                    <li class="mb-2">
                                        <i class="bi bi-people-fill text-primary me-2"></i>
                                        <strong>Users:</strong> <?= $plan['max_users'] == -1 ? 'Unlimited' : number_format($plan['max_users']) ?>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($plan['max_storage_gb'] !== null): ?>
                                    <li class="mb-2">
                                        <i class="bi bi-hdd-fill text-info me-2"></i>
                                        <strong>Storage:</strong> <?= $plan['max_storage_gb'] == -1 ? 'Unlimited' : $plan['max_storage_gb'] . 'GB' ?>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($plan['max_projects'] !== null): ?>
                                    <li class="mb-2">
                                        <i class="bi bi-folder-fill text-warning me-2"></i>
                                        <strong>Projects:</strong> <?= $plan['max_projects'] == -1 ? 'Unlimited' : number_format($plan['max_projects']) ?>
                                    </li>
                                <?php endif; ?>
                                
                                <li class="mb-2">
                                    <i class="bi bi-headset text-danger me-2"></i>
                                    <strong>Support:</strong> <?= $plan['priority_support'] ? 'Priority Support' : 'Standard Support' ?>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Subscription Details -->
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3">Subscription Details</h5>
                            
                            <div class="mb-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Plan:</span>
                                    <strong><?= h($plan['name']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Billing:</span>
                                    <strong><?= ucfirst($billing_cycle) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Amount:</span>
                                    <strong><?= format_price($amount) ?></strong>
                                </div>
                                
                                <?php if ($trial_days > 0): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Free Trial:</span>
                                        <strong class="text-success"><?= $trial_days ?> days</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>First Charge:</span>
                                        <strong><?= date('M j, Y', strtotime("+{$trial_days} days")) ?></strong>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex justify-content-between">
                                        <span>First Charge:</span>
                                        <strong>Today</strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($trial_days > 0): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-gift me-2"></i>
                                    <strong>Free Trial Included!</strong><br>
                                    Try <?= h($plan['name']) ?> free for <?= $trial_days ?> days. 
                                    Cancel anytime during your trial at no charge.
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($is_changing_plan): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-arrow-up-circle me-2"></i>
                                    <strong>Plan Change</strong><br>
                                    You're changing from <strong><?= h($current_subscription['plan_name']) ?></strong> to <strong><?= h($plan['name']) ?></strong>.
                                    The change will take effect immediately.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Billing Cycle Selection -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Choose Billing Cycle</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card <?= $billing_cycle === 'monthly' ? 'border-primary' : 'border-light' ?> h-100">
                                <div class="card-body text-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="billing_cycle" id="monthly" value="monthly" <?= $billing_cycle === 'monthly' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold" for="monthly">
                                            Monthly Billing
                                        </label>
                                    </div>
                                    <div class="h4 mt-2"><?= format_price($plan['price_monthly']) ?></div>
                                    <div class="text-muted">per month</div>
                                    <div class="small text-muted mt-2">Billed monthly</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card <?= $billing_cycle === 'yearly' ? 'border-primary' : 'border-light' ?> h-100 position-relative">
                                <?php if ($savings > 0): ?>
                                    <div class="position-absolute top-0 start-50 translate-middle">
                                        <span class="badge bg-success px-3 py-2">Save <?= format_price($savings) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body text-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="billing_cycle" id="yearly" value="yearly" <?= $billing_cycle === 'yearly' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold" for="yearly">
                                            Yearly Billing
                                        </label>
                                    </div>
                                    <div class="h4 mt-2"><?= format_price($plan['price_yearly']) ?></div>
                                    <div class="text-muted">per year</div>
                                    <div class="small text-success mt-2">
                                        <?= $savings > 0 ? 'Save ' . round((($plan['price_monthly'] * 12 - $plan['price_yearly']) / ($plan['price_monthly'] * 12)) * 100) . '%' : 'Best Value' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Terms and Subscribe -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="post" id="subscribeForm">
                        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                        <input type="hidden" name="action" value="subscribe">
                        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                        <input type="hidden" name="billing_cycle" value="<?= $billing_cycle ?>" id="selected_billing_cycle">
                        
                        <!-- Terms Agreement -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms_agreement" name="terms_agreement" required>
                            <label class="form-check-label" for="terms_agreement">
                                I agree to the <a href="../terms.php" target="_blank">Terms of Service</a> and 
                                <a href="../privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <!-- Subscribe Button -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="plans.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Back to Plans
                            </a>
                            
                            <button type="submit" class="btn btn-<?= get_plan_type_color($plan['plan_type']) ?> btn-lg" id="subscribeBtn">
                                <?php if ($trial_days > 0 && !$is_changing_plan): ?>
                                    <i class="bi bi-gift me-2"></i>
                                    Start Free Trial
                                <?php elseif ($is_changing_plan): ?>
                                    <i class="bi bi-arrow-up-circle me-2"></i>
                                    Change Plan
                                <?php else: ?>
                                    <i class="bi bi-credit-card me-2"></i>
                                    Subscribe Now
                                <?php endif; ?>
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <?php if ($trial_days > 0 && !$is_changing_plan): ?>
                                    Your trial starts immediately. No payment required until <?= date('M j, Y', strtotime("+{$trial_days} days")) ?>.
                                <?php else: ?>
                                    You can cancel your subscription at any time from your account settings.
                                <?php endif; ?>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- FAQ Section -->
            <div class="mt-5">
                <h4 class="fw-bold mb-4">Frequently Asked Questions</h4>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                                What happens during my free trial?
                            </button>
                        </h2>
                        <div id="faqCollapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                During your free trial, you have full access to all features of your chosen plan. 
                                No credit card is required, and you can cancel anytime before the trial ends without any charges.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2">
                                Can I change my plan later?
                            </button>
                        </h2>
                        <div id="faqCollapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! You can upgrade or downgrade your plan at any time from your account settings. 
                                When you upgrade, changes take effect immediately. When you downgrade, changes take effect at your next billing cycle.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3">
                                How can I cancel my subscription?
                            </button>
                        </h2>
                        <div id="faqCollapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can cancel your subscription anytime from your account settings. 
                                When you cancel, you'll continue to have access until the end of your current billing period.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faqCollapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers for annual subscriptions. 
                                All payments are processed securely through our payment partners.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for billing cycle selection -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyRadio = document.getElementById('monthly');
    const yearlyRadio = document.getElementById('yearly');
    const billingCycleInput = document.getElementById('selected_billing_cycle');
    const subscribeForm = document.getElementById('subscribeForm');
    
    // Handle billing cycle change
    function updateBillingCycle() {
        const selectedCycle = monthlyRadio.checked ? 'monthly' : 'yearly';
        billingCycleInput.value = selectedCycle;
        
        // Update URL to reflect billing cycle
        const url = new URL(window.location);
        url.searchParams.set('billing', selectedCycle);
        window.history.replaceState({}, '', url);
        
        // Reload page to update pricing
        window.location.href = url.toString();
    }
    
    monthlyRadio.addEventListener('change', updateBillingCycle);
    yearlyRadio.addEventListener('change', updateBillingCycle);
    
    // Handle form submission
    subscribeForm.addEventListener('submit', function(e) {
        const subscribeBtn = document.getElementById('subscribeBtn');
        const termsAgreement = document.getElementById('terms_agreement');
        
        if (!termsAgreement.checked) {
            e.preventDefault();
            alert('Please agree to the Terms of Service and Privacy Policy to continue.');
            return false;
        }
        
        // Disable button and show loading state
        subscribeBtn.disabled = true;
        subscribeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
    });
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?>