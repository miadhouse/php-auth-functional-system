<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title ?? SITE_NAME) ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/custom.css" rel="stylesheet">
</head>

<body data-csrf-token="<?= get_csrf_token() ?>">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="<?= BASE_URL ?>/index.php">
                <i class="bi bi-layers me-2"></i><?= h(SITE_NAME) ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <!-- Logged in user navigation -->
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/user/') !== false ? 'active' : '' ?>" href="user/dashboard.php">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                        </li>
                        
                        <?php if (has_active_subscription()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="subscriptionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-star me-1"></i>My Subscription
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="subscriptionDropdown">
                                    <li><a class="dropdown-item" href="user/subscription.php">
                                        <i class="bi bi-gear me-2"></i>Manage Subscription
                                    </a></li>
                                    <li><a class="dropdown-item" href="user/billing.php">
                                        <i class="bi bi-credit-card me-2"></i>Billing History
                                    </a></li>
                                    <li><a class="dropdown-item" href="user/usage.php">
                                        <i class="bi bi-graph-up me-2"></i>Usage Statistics
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="subscription/plans.php">
                                        <i class="bi bi-arrow-up-circle me-2"></i>Upgrade Plan
                                    </a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link text-warning" href="subscription/plans.php">
                                    <i class="bi bi-star me-1"></i>Choose Plan
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="user/projects.php">
                                <i class="bi bi-folder me-1"></i>Projects
                            </a>
                        </li>
                        
                    <?php else: ?>
                        <!-- Guest navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="#pricing">
                                <i class="bi bi-tags me-1"></i>Pricing
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="features.php">
                                <i class="bi bi-list-check me-1"></i>Features
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="bi bi-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="bi bi-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <?php if (is_logged_in()): ?>
                        <!-- Notification Bell -->
                        <div class="dropdown me-3">
                            <button type="button" class="btn btn-outline-secondary position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                <!-- Notification badge (you can implement notification logic) -->
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                    3
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><a class="dropdown-item" href="#">
                                    <div class="d-flex">
                                        <i class="bi bi-info-circle text-info me-2 mt-1"></i>
                                        <div>
                                            <div class="fw-bold">Welcome!</div>
                                            <small class="text-muted">Thanks for joining our platform</small>
                                        </div>
                                    </div>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="user/notifications.php">View All</a></li>
                            </ul>
                        </div>

                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <span class="d-none d-md-inline"><?= h($_SESSION['user_name']) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><h6 class="dropdown-header">
                                    <?= h($_SESSION['user_name']) ?>
                                    <?php if (has_active_subscription()): ?>
                                        <?php $subscription = get_user_subscription(); ?>
                                        <div class="small text-muted">
                                            <span class="badge bg-<?= $subscription['plan_type'] === 'gold' ? 'warning' : 'primary' ?> text-dark">
                                                <?= h($subscription['plan_name']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </h6></li>
                                <li><hr class="dropdown-divider"></li>
                                
                                <?php if (is_admin()): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">
                                        <i class="bi bi-shield-check me-2"></i>Admin Dashboard
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                
                                <li><a class="dropdown-item" href="user/dashboard.php">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="user/profile.php">
                                    <i class="bi bi-person me-2"></i>Profile Settings
                                </a></li>
                                
                                <?php if (has_active_subscription()): ?>
                                    <li><a class="dropdown-item" href="user/subscription.php">
                                        <i class="bi bi-star me-2"></i>Subscription
                                    </a></li>
                                    <li><a class="dropdown-item" href="user/billing.php">
                                        <i class="bi bi-credit-card me-2"></i>Billing
                                    </a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item text-warning" href="subscription/plans.php">
                                        <i class="bi bi-star me-2"></i>Upgrade Account
                                    </a></li>
                                <?php endif; ?>
                                
                                <li><a class="dropdown-item" href="user/security.php">
                                    <i class="bi bi-shield-lock me-2"></i>Security
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Guest User Buttons -->
                        <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="bi bi-person-plus me-1"></i>Start Free Trial
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Subscription Status Banner (for logged in users) -->
    <?php if (is_logged_in()): ?>
        <?php $subscription = get_user_subscription(); ?>
        
        <?php if (!$subscription): ?>
            <!-- No active subscription -->
            <div class="bg-warning text-dark py-2">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No active subscription</strong> - Upgrade to unlock all features
                        </div>
                        <a href="subscription/plans.php" class="btn btn-dark btn-sm">
                            Choose Plan
                        </a>
                    </div>
                </div>
            </div>
        <?php elseif ($subscription['status'] === 'trial'): ?>
            <!-- Trial period -->
            <?php 
            $days_left = ceil((strtotime($subscription['trial_ends_at']) - time()) / 86400);
            ?>
            <div class="bg-info text-white py-2">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-gift me-2"></i>
                            <strong>Free Trial</strong> - <?= $days_left ?> days remaining
                        </div>
                        <a href="subscription/plans.php" class="btn btn-light btn-sm">
                            Subscribe Now
                        </a>
                    </div>
                </div>
            </div>
        <?php elseif ($subscription['status'] === 'cancelled'): ?>
            <!-- Cancelled subscription -->
            <?php 
            $days_left = ceil((strtotime($subscription['ends_at']) - time()) / 86400);
            ?>
            <div class="bg-danger text-white py-2">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <strong>Subscription Cancelled</strong> - Access ends in <?= $days_left ?> days
                        </div>
                        <a href="subscription/plans.php" class="btn btn-light btn-sm">
                            Reactivate
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php $flash_message = get_flash_message(); ?>
    <?php if ($flash_message): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= $flash_message['type'] ?> alert-dismissible fade show" role="alert">
                <?= h($flash_message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>