<?php
/**
 * Email Verification Page
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/config/config.php';

// Get token from URL
$token = $_GET['token'] ?? '';

// Process verification
$result = verify_user_email($token);

// Page title
$page_title = 'Email Verification';

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
                    <?php if ($result['status']): ?>
                        <div class="alert alert-success">
                            <h5><i class="bi bi-check-circle-fill me-2"></i>Success!</h5>
                            <p class="mb-0"><?= h($result['message']) ?></p>
                        </div>
                        <p class="mt-4">You can now sign in to your account with your email and password.</p>
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                                Sign In
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>Error!</h5>
                            <p class="mb-0"><?= h($result['message']) ?></p>
                        </div>
                        <p class="mt-4">
                            If you're having trouble verifying your email, please try the following:
                        </p>
                        <ul>
                            <li>Check if you're using the correct verification link from your email</li>
                            <li>The verification link may have expired (valid for 24 hours)</li>
                            <li>You may have already verified your email</li>
                        </ul>
                        <p>
                            If you continue to have issues, please <a href="contact.php">contact support</a>.
                        </p>
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