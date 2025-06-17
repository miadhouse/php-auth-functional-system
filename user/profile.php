<?php
/**
 * Edit User Profile
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash_message('error', 'You must be logged in to access your profile');
    redirect('../index.php');
}

// Get user profile
$user = get_user_profile();

// Handle form submission
$update_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $update_result = [
            'status' => false,
            'message' => 'Invalid security token. Please try again.'
        ];
    } else {
        // Validate form data
        $validation_rules = [
            'name' => [
                ['rule' => 'validate_required', 'field_name' => 'name'],
                ['rule' => 'validate_min_length', 'params' => [3], 'field_name' => 'name']
            ],
            'phone' => [
                ['rule' => 'validate_max_length', 'params' => [20], 'field_name' => 'phone']
            ],
            'address' => [
                ['rule' => 'validate_max_length', 'params' => [500], 'field_name' => 'address']
            ]
        ];
        
        $errors = validate_form($_POST, $validation_rules);
        
        if (empty($errors)) {
            // Update profile
            $update_data = [
                'name' => $_POST['name'],
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? ''
            ];
            
            $update_result = update_user_profile($update_data);
            
            if ($update_result['status']) {
                // Refresh user data
                $user = get_user_profile();
            }
        } else {
            $update_result = [
                'status' => false,
                'message' => reset($errors)
            ];
        }
    }
}

// Page title
$page_title = 'Edit Profile';

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
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-box-seam me-2"></i> My Orders
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action active">
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
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <?php if ($update_result !== null): ?>
                        <div class="alert alert-<?= $update_result['status'] ? 'success' : 'danger' ?>">
                            <?= h($update_result['message']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" id="profile-form">
                        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-3 text-center">
                                <div class="mb-3">
                                    <?php if (!empty($user['profile_image'])): ?>
                                        <img src="<?= h($user['profile_image']) ?>" alt="Profile" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                            <i class="bi bi-person-circle text-secondary" style="font-size: 5rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" disabled>
                                    <i class="bi bi-camera me-1"></i> Change Photo
                                </button>
                                <p class="text-muted small mt-2">Coming Soon</p>
                            </div>
                            
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" value="<?= h($user['email']) ?>" readonly>
                                    <div class="form-text text-muted">Your email cannot be changed.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= h($user['name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= h($user['phone'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?= h($user['address'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Account Created</label>
                                    <p class="form-control-plaintext"><?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../partials/footer.php';
?>