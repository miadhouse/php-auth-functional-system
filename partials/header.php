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
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php"><?= h(SITE_NAME) ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Categories
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                            <?php
                            $nav_categories = db_query("SELECT name, slug FROM categories WHERE status = 'active' ORDER BY name ASC");
                            foreach ($nav_categories as $category):
                            ?>
                                <li><a class="dropdown-item" href="category.php?slug=<?= urlencode($category['slug']) ?>"><?= h($category['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <!-- Search Form -->
                    <form class="d-flex me-2" method="get" action="search.php">
                        <input class="form-control me-2" type="search" name="q" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>

                    <!-- Cart Button -->
                    <div class="ms-2 me-3">
                        <button type="button" class="btn btn-outline-primary position-relative" id="cartButton">
                            <i class="bi bi-cart"></i>
                            <?php $cart_count = get_cart_count(); ?>
                            <span id="cart-counter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger<?= $cart_count > 0 ? '' : ' d-none' ?>">
                                <?= $cart_count ?>
                                <span class="visually-hidden">items in cart</span>
                            </span>
                        </button>
                    </div>


                    <?php if (is_logged_in()): ?>
                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                                <?= h($_SESSION['user_name']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if (is_admin()): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">Admin Dashboard</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="user/dashboard.php">My Account</a></li>
                                <li><a class="dropdown-item" href="user/orders.php">My Orders</a></li>
                                <li><a class="dropdown-item" href="user/profile.php">Edit Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Login/Register Buttons -->
                        <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Sign In
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
                            Sign Up
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

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