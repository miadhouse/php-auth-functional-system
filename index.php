<?php

/**
 * Landing Page
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/config/config.php';

// Check for auto-login with remember token
if (!is_logged_in()) {
    check_remember_token();
}

// Get featured products
$featured_products = db_query(
    "SELECT id, name, slug, description, price, sale_price, image 
     FROM products 
     WHERE status = 'active' AND featured = 1 
     ORDER BY created_at DESC 
     LIMIT 4"
);

// Get latest products
$latest_products = db_query(
    "SELECT id, name, slug, description, price, sale_price, image 
     FROM products 
     WHERE status = 'active' 
     ORDER BY created_at DESC 
     LIMIT 8"
);

// Get all categories
$categories = db_query(
    "SELECT id, name, slug, description, image 
     FROM categories 
     WHERE status = 'active' 
     ORDER BY name ASC"
);

// Page title
$page_title = SITE_NAME . ' - Home';

// Include header
require_once __DIR__ . '/partials/header.php';
?>

<!-- Hero Section -->
<div class="container-fluid bg-primary py-5 text-white" style="margin-top: -1.5rem;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Welcome to <?= h(SITE_NAME) ?></h1>
                <p class="lead">Your one-stop shop for all your needs. Quality products, fast shipping, and excellent customer service.</p>
                <div class="mt-4">
                    <a href="#featured-products" class="btn btn-light btn-lg me-2">Shop Now</a>
                    <?php if (!is_logged_in()): ?>
                        <button type="button" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">Sign In</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="assets/images/hero-image.jpg" alt="Shop Hero" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-truck text-primary" viewBox="0 0 16 16">
                            <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 1 1 .001 4A2 2 0 0 1 12 10zm-8 0a2 2 0 1 1 .001 4A2 2 0 0 1 4 10z" />
                        </svg>
                    </div>
                    <h4>Free Shipping</h4>
                    <p class="text-muted">Free shipping on all orders over $50.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-shield-check text-primary" viewBox="0 0 16 16">
                            <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z" />
                            <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z" />
                        </svg>
                    </div>
                    <h4>Secure Payments</h4>
                    <p class="text-muted">All transactions are secure and encrypted.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-counterclockwise text-primary" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z" />
                            <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z" />
                        </svg>
                    </div>
                    <h4>Easy Returns</h4>
                    <p class="text-muted">30-day easy return policy.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Products Section -->
<div id="featured-products" class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Featured Products</h2>
        <a href="#" class="btn btn-outline-primary">View All</a>
    </div>

    <div class="row g-4">
        <?php foreach ($featured_products as $product): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm product-card">
                    <img src="<?= h($product['image'] ?? 'assets/images/product-placeholder.jpg') ?>" class="card-img-top" alt="<?= h($product['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= h($product['name']) ?></h5>
                        <p class="card-text text-muted text-truncate"><?= h($product['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <?php if (isset($product['sale_price']) && $product['sale_price'] > 0): ?>
                                <div>
                                    <span class="text-decoration-line-through text-muted"><?= format_price($product['price']) ?></span>
                                    <span class="fw-bold text-danger ms-2"><?= format_price($product['sale_price']) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="fw-bold"><?= format_price($product['price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary add-to-cart-btn" data-id="<?= $product['id'] ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Categories Section -->
<div class="container-fluid bg-light py-5">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">Shop by Category</h2>

        <div class="row g-4 justify-content-center">
            <?php foreach ($categories as $category): ?>
                <div class="col-6 col-md-3">
                    <a href="#" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm">
                            <img src="<?= h($category['image'] ?? 'assets/images/category-placeholder.jpg') ?>" class="card-img-top" alt="<?= h($category['name']) ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title text-dark"><?= h($category['name']) ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Latest Products Section -->
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Latest Products</h2>
        <a href="#" class="btn btn-outline-primary">View All</a>
    </div>

    <div class="row g-4">
        <?php foreach ($latest_products as $product): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm product-card">
                    <img src="<?= h($product['image'] ?? 'assets/images/product-placeholder.jpg') ?>" class="card-img-top" alt="<?= h($product['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= h($product['name']) ?></h5>
                        <p class="card-text text-muted text-truncate"><?= h($product['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <?php if (isset($product['sale_price']) && $product['sale_price'] > 0): ?>
                                <div>
                                    <span class="text-decoration-line-through text-muted"><?= format_price($product['price']) ?></span>
                                    <span class="fw-bold text-danger ms-2"><?= format_price($product['sale_price']) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="fw-bold"><?= format_price($product['price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary add-to-cart-btn" data-id="<?= $product['id'] ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Newsletter Section -->
<div class="container-fluid bg-primary py-5 text-white">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h3 class="fw-bold mb-4">Subscribe to Our Newsletter</h3>
                <p class="mb-4">Get the latest updates, offers and special announcements.</p>
                <form class="d-flex justify-content-center">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control form-control-lg" placeholder="Your email address" aria-label="Email">
                        <button class="btn btn-light" type="button">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="container py-5">
    <h2 class="fw-bold text-center mb-5">What Our Customers Say</h2>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="card-text">"Great products and fast shipping! I'm very satisfied with my purchase and will definitely shop here again."</p>
                    <div class="d-flex align-items-center mt-3">
                        <div class="me-3">
                            <img src="assets/images/avatar1.jpg" alt="Customer" class="rounded-circle" width="50" height="50">
                        </div>
                        <div>
                            <h6 class="mb-0">John Smith</h6>
                            <small class="text-muted">Loyal Customer</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="card-text">"The customer service is exceptional. They went above and beyond to help me with my order. Highly recommended!"</p>
                    <div class="d-flex align-items-center mt-3">
                        <div class="me-3">
                            <img src="assets/images/avatar2.jpg" alt="Customer" class="rounded-circle" width="50" height="50">
                        </div>
                        <div>
                            <h6 class="mb-0">Sarah Johnson</h6>
                            <small class="text-muted">Happy Shopper</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                    </div>
                    <p class="card-text">"Quality products at reasonable prices. The delivery was prompt and everything arrived in perfect condition."</p>
                    <div class="d-flex align-items-center mt-3">
                        <div class="me-3">
                            <img src="assets/images/avatar3.jpg" alt="Customer" class="rounded-circle" width="50" height="50">
                        </div>
                        <div>
                            <h6 class="mb-0">Michael Brown</h6>
                            <small class="text-muted">Frequent Buyer</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add to Cart Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add to cart buttons
        const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;

                // AJAX request to add item to cart
                fetch('ajax/cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `action=add&product_id=${productId}&csrf_token=<?= get_csrf_token() ?>`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            // Update cart counter
                            const cartCounter = document.getElementById('cart-counter');
                            if (cartCounter) {
                                cartCounter.textContent = data.cart.total_quantity;
                            }

                            // Show success message
                            alert(data.message);
                        } else {
                            // Show error message
                            alert(data.message || 'Failed to add product to cart');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
            });
        });
    });
</script>

<?php
// Include footer
require_once __DIR__ . '/partials/footer.php';
?>