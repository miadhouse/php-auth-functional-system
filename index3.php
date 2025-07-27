<?php
/**
 * Subscription Landing Page
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/subscription.php';

// Check for auto-login with remember token
if (!is_logged_in()) {
    check_remember_token();
}

// Get subscription plans for display
$subscription_plans = get_subscription_plans();

// Get plan comparison data
$plan_comparison = get_plan_comparison_data();

// Get testimonials from settings or database
$testimonials = [
    [
        'name' => 'Sarah Johnson',
        'company' => 'TechStart Inc.',
        'avatar' => 'assets/images/avatar1.jpg',
        'text' => 'This subscription service has transformed how we manage our projects. The Gold plan gives us everything we need.',
        'plan' => 'Gold Plan'
    ],
    [
        'name' => 'Mike Chen',
        'company' => 'Digital Solutions',
        'avatar' => 'assets/images/avatar2.jpg',
        'text' => 'Excellent value for money. The Silver plan is perfect for our growing team.',
        'plan' => 'Silver Plan'
    ],
    [
        'name' => 'Emily Rodriguez',
        'company' => 'Creative Agency',
        'avatar' => 'assets/images/avatar3.jpg',
        'text' => 'The Enterprise plan gives us the scalability and support we need for our large organization.',
        'plan' => 'Enterprise Plan'
    ]
];

// Page title
$page_title = SITE_NAME . ' - Choose Your Plan';

// Include header
require_once __DIR__ . '/partials/header.php';
?>

<!-- Hero Section -->
<div class="container-fluid bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin-top: -1.5rem;">
    <div class="container py-5 text-white">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Choose the Perfect Plan for Your Needs</h1>
                <p class="lead mb-4">Start with our free trial and upgrade anytime. All plans include our core features with varying limits and premium support options.</p>
                <div class="d-flex gap-3 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>14-day free trial</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>No credit card required</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Cancel anytime</span>
                    </div>
                </div>
                <?php if (!is_logged_in()): ?>
                    <button type="button" class="btn btn-light btn-lg me-3" data-bs-toggle="modal" data-bs-target="#registerModal">
                        Start Free Trial
                    </button>
                    <button type="button" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Sign In
                    </button>
                <?php else: ?>
                    <?php if (has_active_subscription()): ?>
                        <a href="user/dashboard.php" class="btn btn-light btn-lg me-3">
                            Go to Dashboard
                        </a>
                        <a href="user/subscription.php" class="btn btn-outline-light btn-lg">
                            Manage Subscription
                        </a>
                    <?php else: ?>
                        <a href="subscription/plans.php" class="btn btn-light btn-lg">
                            Choose Your Plan
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="col-lg-6 d-none d-lg-block text-center">
                <img src="assets/images/subscription-hero.svg" alt="Subscription Plans" class="img-fluid" style="max-height: 400px;">
            </div>
        </div>
    </div>
</div>

<!-- Pricing Plans Section -->
<div class="container py-5" id="pricing">
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3">Simple, Transparent Pricing</h2>
        <p class="lead text-muted">Choose the plan that best fits your needs. All plans include our core features.</p>
        
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

    <div class="row g-4">
        <?php foreach ($plan_comparison as $plan): ?>
            <div class="col-lg-<?= count($plan_comparison) > 4 ? '3' : (12 / count($plan_comparison)) ?> col-md-6">
                <div class="card h-100 border-0 shadow-sm <?= $plan['plan_type'] === 'gold' ? 'border-warning' : '' ?>" style="<?= $plan['plan_type'] === 'gold' ? 'border: 2px solid #ffc107 !important;' : '' ?>">
                    <?php if ($plan['plan_type'] === 'gold'): ?>
                        <div class="position-absolute top-0 start-50 translate-middle">
                            <span class="badge bg-warning text-dark px-3 py-2">Most Popular</span>
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
                        
                        <?php if ($plan['trial_days'] > 0): ?>
                            <p class="text-success small mb-3">
                                <i class="bi bi-gift-fill me-1"></i>
                                <?= $plan['trial_days'] ?>-day free trial
                            </p>
                        <?php endif; ?>
                        
                        <ul class="list-unstyled text-start mb-4">
                            <?php foreach ($plan['features'] as $feature): ?>
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
                        
                        <?php if (is_logged_in()): ?>
                            <?php 
                            $current_subscription = get_user_subscription();
                            $is_current_plan = $current_subscription && $current_subscription['plan_id'] == $plan['id'];
                            ?>
                            
                            <?php if ($is_current_plan): ?>
                                <button class="btn btn-outline-secondary w-100" disabled>
                                    Current Plan
                                </button>
                            <?php else: ?>
                                <a href="subscription/subscribe.php?plan=<?= $plan['slug'] ?>" 
                                   class="btn btn-<?= $plan['plan_type'] === 'gold' ? 'warning' : 'primary' ?> w-100">
                                    <?= $current_subscription ? 'Switch to ' . $plan['name'] : 'Start Free Trial' ?>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button type="button" 
                                    class="btn btn-<?= $plan['plan_type'] === 'gold' ? 'warning' : 'primary' ?> w-100"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#registerModal"
                                    data-plan="<?= $plan['slug'] ?>">
                                Start Free Trial
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-5">
        <p class="text-muted">Need a custom solution? <a href="contact.php">Contact our sales team</a></p>
    </div>
</div>

<!-- Features Comparison Table -->
<div class="container-fluid bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Feature Comparison</h2>
            <p class="lead text-muted">Compare all features across our subscription plans</p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover bg-white rounded shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Features</th>
                        <?php foreach ($plan_comparison as $plan): ?>
                            <th class="text-center"><?= h($plan['name']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Monthly Price</strong></td>
                        <?php foreach ($plan_comparison as $plan): ?>
                            <td class="text-center">$<?= number_format($plan['price_monthly'], 0) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Yearly Price</strong></td>
                        <?php foreach ($plan_comparison as $plan): ?>
                            <td class="text-center">$<?= number_format($plan['price_yearly'], 0) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Free Trial</strong></td>
                        <?php foreach ($plan_comparison as $plan): ?>
                            <td class="text-center">
                                <?= $plan['trial_days'] > 0 ? $plan['trial_days'] . ' days' : '—' ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Users</strong></td>
                        <?php foreach ($plan_comparison as $plan): ?>
                            <td class="text-center">
                                <?= $plan['max_users'] == -1 ? 'Unlimited' : $plan['max_users'] ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Storage</strong></td>
                        <?php foreach ($plan_comparison as $plan): ?>
                            <td class="text-center">
                                <?= $plan['max_storage_gb'] == -1 ? 'Unlimited' : $plan['max_storage_gb'] . 'GB' ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Projects</strong></td>
                        <?php foreach ($plan_comparison as $plan): ?>
                            <td class="text-center">
                                <?= $plan['max_projects'] == -1 ? 'Unlimited' : $plan['max_projects'] ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><strong>Priority Support</strong></td>
                        <?php foreach ($plan_comparison as $plan): ?>
                            <td class="text-center">
                                <?= $plan['priority_support'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '—' ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Benefits Section -->
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Why Choose Our Platform?</h2>
        <p class="lead text-muted">Built for teams of all sizes with features that scale with your needs</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="text-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-shield-check text-primary" style="font-size: 2rem;"></i>
                </div>
                <h4>Secure & Reliable</h4>
                <p class="text-muted">Enterprise-grade security with 99.9% uptime guarantee. Your data is safe with us.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                </div>
                <h4>Team Collaboration</h4>
                <p class="text-muted">Work together seamlessly with real-time collaboration tools and team management features.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center">
                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-graph-up text-info" style="font-size: 2rem;"></i>
                </div>
                <h4>Scalable Solution</h4>
                <p class="text-muted">Start small and grow. Our plans scale with your business needs and team size.</p>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="container-fluid bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">What Our Customers Say</h2>
            <p class="lead text-muted">Join thousands of satisfied customers who trust our platform</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3 text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <p class="card-text">"<?= h($testimonial['text']) ?>"</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="<?= h($testimonial['avatar']) ?>" alt="<?= h($testimonial['name']) ?>" class="rounded-circle me-3" width="50" height="50">
                                <div>
                                    <h6 class="mb-0"><?= h($testimonial['name']) ?></h6>
                                    <small class="text-muted"><?= h($testimonial['company']) ?></small>
                                    <div class="small text-primary"><?= h($testimonial['plan']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Frequently Asked Questions</h2>
        <p class="lead text-muted">Got questions? We have answers.</p>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq1">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                            Can I change my plan at any time?
                        </button>
                    </h2>
                    <div id="faqCollapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes! You can upgrade or downgrade your plan at any time. When you upgrade, you'll be charged the prorated difference immediately. When you downgrade, the change takes effect at your next billing cycle.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2">
                            What happens during the free trial?
                        </button>
                    </h2>
                    <div id="faqCollapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            During your free trial, you have full access to all features of your chosen plan. No credit card is required to start. If you don't subscribe before the trial ends, your account will be downgraded to our free tier.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq3">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3">
                            Can I cancel my subscription?
                        </button>
                    </h2>
                    <div id="faqCollapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, you can cancel your subscription at any time. When you cancel, you'll continue to have access to your plan features until the end of your current billing period. After that, your account will be downgraded to the free tier.
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
                            We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers for annual plans. All payments are processed securely through our payment partners.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq5">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5">
                            Do you offer refunds?
                        </button>
                    </h2>
                    <div id="faqCollapse5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We offer a 30-day money-back guarantee for annual plans. If you're not satisfied within the first 30 days, we'll provide a full refund. Monthly plans are not eligible for refunds but can be cancelled at any time.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="container-fluid bg-primary py-5 text-white">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-4">Ready to Get Started?</h2>
                <p class="lead mb-4">Join thousands of teams who are already using our platform to achieve their goals. Start your free trial today!</p>
                <?php if (!is_logged_in()): ?>
                    <button type="button" class="btn btn-light btn-lg me-3" data-bs-toggle="modal" data-bs-target="#registerModal">
                        Start Free Trial
                    </button>
                    <a href="contact.php" class="btn btn-outline-light btn-lg">Contact Sales</a>
                <?php else: ?>
                    <?php if (!has_active_subscription()): ?>
                        <a href="subscription/plans.php" class="btn btn-light btn-lg me-3">Choose Your Plan</a>
                    <?php endif; ?>
                    <a href="user/dashboard.php" class="btn btn-outline-light btn-lg">Go to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Billing Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const billingToggle = document.getElementById('billingToggle');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const yearlyPrices = document.querySelectorAll('.yearly-price');
    
    billingToggle.addEventListener('change', function() {
        if (this.checked) {
            // Show yearly prices
            monthlyPrices.forEach(price => price.classList.add('d-none'));
            yearlyPrices.forEach(price => price.classList.remove('d-none'));
        } else {
            // Show monthly prices
            monthlyPrices.forEach(price => price.classList.remove('d-none'));
            yearlyPrices.forEach(price => price.classList.add('d-none'));
        }
    });
    
    // Handle plan selection in registration modal
    const registerButtons = document.querySelectorAll('[data-plan]');
    registerButtons.forEach(button => {
        button.addEventListener('click', function() {
            const planSlug = this.dataset.plan;
            // Store selected plan in session storage for later use
            sessionStorage.setItem('selectedPlan', planSlug);
        });
    });
});
</script>

<?php
// Include footer
require_once __DIR__ . '/partials/footer.php';
?>