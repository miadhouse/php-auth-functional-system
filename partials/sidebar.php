   <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">My Account</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo ($page == 'Dashboard') ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="subscription.php" class="list-group-item list-group-item-action <?php echo ($page == 'subscription') ? 'active' : ''; ?>">
                        <i class="bi bi-star me-2"></i> Subscription
                    </a>
                    <a href="billing.php" class="list-group-item list-group-item-action <?php echo ($page == 'billing') ? 'active' : ''; ?>">
                        <i class="bi bi-credit-card me-2"></i> Billing History
                    </a>
                    <a href="usage.php" class="list-group-item list-group-item-action <?php echo ($page == 'usage') ? 'active' : ''; ?>">
                        <i class="bi bi-graph-up me-2"></i> Usage Statistics
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action <?php echo ($page == 'profile') ? 'active' : ''; ?>">
                        <i class="bi bi-person me-2"></i> Profile Settings
                    </a>
                    <a href="security.php" class="list-group-item list-group-item-action <?php echo ($page == 'security') ? 'active' : ''; ?>">
                        <i class="bi bi-shield-lock me-2"></i> Security
                    </a>
                    <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                    </a>
                </div>
            </div>
        </div>