<?php
/**
 * Reset Password Page
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/config/config.php';

// Get token from URL
$token = $_GET['token'] ?? '';

// Check if token is valid
$token_data = verify_reset_token($token);
$token_valid = $token_data['status'] ?? false;

// Handle form submission
$reset_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $reset_result = [
            'status' => false,
            'message' => 'Invalid security token. Please try again.'
        ];
    } else {
        // Validate password
        $validation_rules = [
            'password' => [
                ['rule' => 'validate_required', 'field_name' => 'password'],
                ['rule' => 'validate_password']
            ],
            'confirm_password' => [
                ['rule' => 'validate_required', 'field_name' => 'confirm password']
            ]
        ];
        
        $errors = validate_form($_POST, $validation_rules);
        
        // Check if passwords match
        if ($_POST['password'] !== $_POST['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (empty($errors)) {
            // Reset password
            $reset_result = reset_password($token, $_POST['password']);
        } else {
            $reset_result = [
                'status' => false,
                'message' => reset($errors)
            ];
        }
    }
}

// Page title
$page_title = 'Reset Password';

// Include header
require_once __DIR__ . '/partials/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="m-0"><?= h($page_title) ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($reset_result !== null): ?>
                        <?php if ($reset_result['status']): ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-check-circle-fill me-2"></i>Success!</h5>
                                <p class="mb-0"><?= h($reset_result['message']) ?></p>
                            </div>
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    Sign In
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>Error!</h5>
                                <p class="mb-0"><?= h($reset_result['message']) ?></p>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($token_valid): ?>
                        <form method="post" id="reset-password-form">
                            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                            <input type="hidden" name="token" value="<?= h($token) ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Password must be at least 8 characters with uppercase, lowercase, and number.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm-password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Reset Password</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>Invalid or Expired Token</h5>
                            <p class="mb-0">The password reset link is invalid or has expired.</p>
                        </div>
                        <p class="mt-4">
                            Please request a new password reset link:
                        </p>
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                                Request Password Reset
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/partials/footer.php';
?>