</main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3"><?= h(SITE_NAME) ?></h5>
                    <p>Your one-stop shop for all your needs. Quality products, fast shipping, and excellent customer service.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white text-decoration-none mb-2 d-inline-block">Home</a></li>
                        <li><a href="products.php" class="text-white text-decoration-none mb-2 d-inline-block">Products</a></li>
                        <li><a href="about.php" class="text-white text-decoration-none mb-2 d-inline-block">About Us</a></li>
                        <li><a href="contact.php" class="text-white text-decoration-none mb-2 d-inline-block">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-4">
                    <h5 class="fw-bold mb-3">Categories</h5>
                    <ul class="list-unstyled">
                        <?php
                        $footer_categories = db_query("SELECT name, slug FROM categories WHERE status = 'active' ORDER BY name ASC LIMIT 5");
                        foreach ($footer_categories as $category):
                        ?>
                            <li><a href="category.php?slug=<?= urlencode($category['slug']) ?>" class="text-white text-decoration-none mb-2 d-inline-block"><?= h($category['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-4">
                    <h5 class="fw-bold mb-3">Contact Us</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-geo-alt-fill me-2"></i> 123 Main Street, City, Country</li>
                        <li class="mb-2"><i class="bi bi-telephone-fill me-2"></i> +1 234 567 890</li>
                        <li class="mb-2"><i class="bi bi-envelope-fill me-2"></i> info@example.com</li>
                        <li class="mb-2"><i class="bi bi-clock-fill me-2"></i> Monday-Friday: 9am-5pm</li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-md-0">&copy; <?= date('Y') ?> <?= h(SITE_NAME) ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy.php" class="text-white text-decoration-none me-3">Privacy Policy</a>
                    <a href="terms.php" class="text-white text-decoration-none me-3">Terms of Service</a>
                    <a href="refund.php" class="text-white text-decoration-none">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>
    <!-- Cart Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="cartOffcanvasLabel">Your Cart</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="cart-loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading your cart...</p>
        </div>
        <div id="cart-content" class="d-none">
            <!-- Cart content will be loaded here via AJAX -->
        </div>
    </div>
</div>
    <?php require_once __DIR__ . '/modals.php'; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/main2.js"></script>
</body>
</html>