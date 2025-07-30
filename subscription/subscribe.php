<?php
/**
 * Updated Subscription Subscribe Page with Payment Receipt
 * PHP 8.4 Pure Functional Script
 * Save as: subscription/subscribe.php
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/payment_receipts.php';

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
$is_free_plan = $amount <= 0;
$savings = $billing_cycle === 'yearly' ? ($plan['price_monthly'] * 12) - $plan['price_yearly'] : 0;

// Get bank account info and payment instructions
$bank_info = get_bank_account_info();
$payment_instructions = get_payment_instructions();

// Check if user can submit receipt
$can_submit = can_submit_receipt($_SESSION['user_id'], $plan['id']);

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
        
        if ($action === 'activate_free') {
            // Activate free plan
            $subscription_result = activate_free_plan($_SESSION['user_id'], $plan['id']);
            
            if ($subscription_result['status']) {
                set_flash_message('success', $subscription_result['message']);
                redirect('../user/subscription.php');
            }
        } elseif ($action === 'submit_receipt') {
            // Handle receipt upload
            if (!isset($_FILES['receipt_file'])) {
                $subscription_result = [
                    'status' => false,
                    'message' => 'لطفاً فیش پرداخت را انتخاب کنید'
                ];
            } else {
                // Upload receipt file
                $upload_result = upload_receipt_file($_FILES['receipt_file'], $_SESSION['user_id']);
                
                if ($upload_result['status']) {
                    // Submit payment receipt
                    $receipt_data = [
                        'receipt_image' => $upload_result['filepath'],
                        'bank_name' => $_POST['bank_name'] ?? '',
                        'transaction_id' => $_POST['transaction_id'] ?? '',
                        'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
                        'description' => $_POST['description'] ?? ''
                    ];
                    
                    $subscription_result = submit_payment_receipt(
                        $_SESSION['user_id'], 
                        $plan['id'], 
                        $billing_cycle, 
                        $receipt_data
                    );
                    
                    if ($subscription_result['status']) {
                        set_flash_message('success', $subscription_result['message']);
                        redirect('../user/subscription.php');
                    }
                } else {
                    $subscription_result = $upload_result;
                }
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
                                <?php if ($is_free_plan): ?>
                                    <div class="h2 mb-0 text-success">FREE</div>
                                    <div class="small">Forever</div>
                                <?php else: ?>
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
                                    <strong><?= $is_free_plan ? 'FREE' : format_price($amount) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Activation:</span>
                                    <strong><?= $is_free_plan ? 'Immediate' : 'After payment verification' ?></strong>
                                </div>
                            </div>
                            
                            <?php if ($is_changing_plan): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-arrow-up-circle me-2"></i>
                                    <strong>Plan Change</strong><br>
                                    You're changing from <strong><?= h($current_subscription['plan_name']) ?></strong> to <strong><?= h($plan['name']) ?></strong>.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($is_free_plan): ?>
                <!-- Free Plan Activation -->
                <div class="card shadow-sm">
                    <div class="card-body text-center py-4">
                        <div class="mb-4">
                            <i class="bi bi-gift text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h4>Activate Free Plan</h4>
                        <p class="text-muted mb-4">This plan is completely free and will be activated immediately.</p>
                        
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                            <input type="hidden" name="action" value="activate_free">
                            
                            <div class="form-check d-inline-block mb-4">
                                <input class="form-check-input" type="checkbox" id="terms_agreement" name="terms_agreement" required>
                                <label class="form-check-label" for="terms_agreement">
                                    I agree to the <a href="../terms.php" target="_blank">Terms of Service</a> and 
                                    <a href="../privacy.php" target="_blank">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Activate Free Plan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Payment Receipt Upload -->
                <?php if (!$can_submit['can_submit']): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= h($can_submit['message']) ?>
                    </div>
                    <div class="text-center">
                        <a href="plans.php" class="btn btn-outline-primary">Back to Plans</a>
                    </div>
                <?php else: ?>
                    <!-- Bank Account Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-bank me-2"></i>Payment Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <?= h($payment_instructions) ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold">Bank Account Details</h6>
                                    <table class="table table-bordered">
                                        <tr>
                                            <td><strong>Bank Name:</strong></td>
                                            <td><?= h($bank_info['bank_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Number:</strong></td>
                                            <td><?= h($bank_info['account_number']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>SHEBA:</strong></td>
                                            <td><?= h($bank_info['sheba']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Holder:</strong></td>
                                            <td><?= h($bank_info['account_holder']) ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold">Payment Amount</h6>
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary"><?= format_price($amount) ?></h3>
                                            <p class="mb-0"><?= ucfirst($billing_cycle) ?> subscription</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upload Receipt Form -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Upload Payment Receipt</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data" id="receiptForm">
                                <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                                <input type="hidden" name="action" value="submit_receipt">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="receipt_file" class="form-label">
                                                <i class="bi bi-file-earmark-image me-1"></i>
                                                Payment Receipt *
                                            </label>
                                            <input type="file" class="form-control" id="receipt_file" name="receipt_file" 
                                                   accept="image/*,.pdf" required>
                                            <div class="form-text">Supported formats: JPG, PNG, GIF, PDF (Max: 5MB)</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bank_name" class="form-label">Bank Name</label>
                                            <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                                   placeholder="e.g., Bank Melli Iran">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="transaction_id" class="form-label">Transaction ID</label>
                                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                                   placeholder="e.g., 123456789">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_date" class="form-label">Payment Date *</label>
                                            <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                                   value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Additional Notes</label>
                                            <textarea class="form-control" id="description" name="description" rows="3" 
                                                      placeholder="Any additional information about the payment..."></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Terms Agreement -->
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="terms_agreement" name="terms_agreement" required>
                                    <label class="form-check-label" for="terms_agreement">
                                        I agree to the <a href="../terms.php" target="_blank">Terms of Service</a> and 
                                        <a href="../privacy.php" target="_blank">Privacy Policy</a>
                                    </label>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                    <a href="plans.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        Back to Plans
                                    </a>
                                    
                                    <button type="submit" class="btn btn-<?= get_plan_type_color($plan['plan_type']) ?> btn-lg" id="submitBtn">
                                        <i class="bi bi-upload me-2"></i>
                                        Submit Payment Receipt
                                    </button>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Your subscription will be activated after admin verification of your payment receipt.
                                        This usually takes 1-2 business days.
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const receiptForm = document.getElementById('receiptForm');
    
    if (receiptForm) {
        receiptForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const termsAgreement = document.getElementById('terms_agreement');
            const receiptFile = document.getElementById('receipt_file');
            
            if (!termsAgreement.checked) {
                e.preventDefault();
                alert('Please agree to the Terms of Service and Privacy Policy to continue.');
                return false;
            }
            
            if (!receiptFile.files.length) {
                e.preventDefault();
                alert('Please select a payment receipt file.');
                return false;
            }
            
            // Check file size (5MB)
            if (receiptFile.files[0].size > 5242880) {
                e.preventDefault();
                alert('File size should not exceed 5MB.');
                return false;
            }
            
            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
        });
        
        // File preview
        const receiptFile = document.getElementById('receipt_file');
        if (receiptFile) {
            receiptFile.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    console.log(`Selected file: ${file.name} (${fileSize} MB)`);
                }
            });
        }
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?>