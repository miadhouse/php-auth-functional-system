<?php
/**
 * Modals for authentication
 */
?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Sign In</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="login-alerts"></div>
                
                <form id="login-form">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label for="login-email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="login-email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="login-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="login-password" name="password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="login-remember" name="remember">
                        <label class="form-check-label" for="login-remember">Remember me</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="login-submit">Sign In</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="#" id="forgot-password-link">Forgot your password?</a>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <p class="mb-0">Don't have an account? <a href="#" id="register-link">Sign up</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Create an Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="register-alerts"></div>
                
                <form id="register-form">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label for="register-name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="register-name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="register-email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="register-email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="register-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="register-password" name="password" required>
                        <div class="form-text">Password must be at least 8 characters with uppercase, lowercase, and number.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="register-confirm-password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="register-confirm-password" name="confirm_password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="register-terms" name="terms" required>
                        <label class="form-check-label" for="register-terms">I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="register-submit">Sign Up</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <p class="mb-0">Already have an account? <a href="#" id="login-link">Sign in</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="forgot-password-alerts"></div>
                
                <form id="forgot-password-form">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                    
                    <p class="mb-3">Enter your email address and we'll send you a link to reset your password.</p>
                    
                    <div class="mb-3">
                        <label for="forgot-password-email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="forgot-password-email" name="email" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="forgot-password-submit">Send Reset Link</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <p class="mb-0">Remember your password? <a href="#" id="back-to-login-link">Back to login</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Toggle Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Login to Register
    document.getElementById('register-link').addEventListener('click', function(e) {
        e.preventDefault();
        var loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
        loginModal.hide();
        var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
        registerModal.show();
    });
    
    // Register to Login
    document.getElementById('login-link').addEventListener('click', function(e) {
        e.preventDefault();
        var registerModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
        registerModal.hide();
        var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
    });
    
    // Login to Forgot Password
    document.getElementById('forgot-password-link').addEventListener('click', function(e) {
        e.preventDefault();
        var loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
        loginModal.hide();
        var forgotPasswordModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
        forgotPasswordModal.show();
    });
    
    // Forgot Password to Login
    document.getElementById('back-to-login-link').addEventListener('click', function(e) {
        e.preventDefault();
        var forgotPasswordModal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
        forgotPasswordModal.hide();
        var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
    });
    
    // Form Submission Functions
    
    // Login Form
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        const remember = document.getElementById('login-remember').checked;
        const csrf_token = document.querySelector('#login-form input[name="csrf_token"]').value;
        
        const alertsContainer = document.getElementById('login-alerts');
        const submitButton = document.getElementById('login-submit');
        
        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...';
        
        // Clear previous alerts
        alertsContainer.innerHTML = '';
        
        // AJAX request
        fetch('ajax/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&remember=${remember ? 1 : 0}&csrf_token=${encodeURIComponent(csrf_token)}`
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = 'Sign In';
            
            if (data.status) {
                // Show success message
                alertsContainer.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                
                // Redirect after success
                setTimeout(() => {
                    window.location.href = data.redirect || 'index.php';
                }, 1000);
            } else {
                // Show error message
                alertsContainer.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = 'Sign In';
            
            // Show error message
            alertsContainer.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
            console.error('Error:', error);
        });
    });
    
    // Register Form
    document.getElementById('register-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('register-name').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;
        const confirm_password = document.getElementById('register-confirm-password').value;
        const terms = document.getElementById('register-terms').checked;
        const csrf_token = document.querySelector('#register-form input[name="csrf_token"]').value;
        
        const alertsContainer = document.getElementById('register-alerts');
        const submitButton = document.getElementById('register-submit');
        
        // Validate passwords match
        if (password !== confirm_password) {
            alertsContainer.innerHTML = '<div class="alert alert-danger">Passwords do not match</div>';
            return;
        }
        
        // Validate terms agreement
        if (!terms) {
            alertsContainer.innerHTML = '<div class="alert alert-danger">You must agree to the Terms of Service and Privacy Policy</div>';
            return;
        }
        
        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing up...';
        
        // Clear previous alerts
        alertsContainer.innerHTML = '';
        
        // AJAX request
        fetch('ajax/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=register&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&confirm_password=${encodeURIComponent(confirm_password)}&csrf_token=${encodeURIComponent(csrf_token)}`
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = 'Sign Up';
            
            if (data.status) {
                // Show success message
                alertsContainer.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                
                // Clear form
                document.getElementById('register-form').reset();
                
                // Switch to login modal after successful registration
                setTimeout(() => {
                    var registerModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                    registerModal.hide();
                    var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                    loginModal.show();
                    
                    // Show success message in login modal
                    document.getElementById('login-alerts').innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                }, 2000);
            } else {
                // Show error message
                alertsContainer.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = 'Sign Up';
            
            // Show error message
            alertsContainer.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
            console.error('Error:', error);
        });
    });
    
    // Forgot Password Form
    document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('forgot-password-email').value;
        const csrf_token = document.querySelector('#forgot-password-form input[name="csrf_token"]').value;
        
        const alertsContainer = document.getElementById('forgot-password-alerts');
        const submitButton = document.getElementById('forgot-password-submit');
        
        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
        
        // Clear previous alerts
        alertsContainer.innerHTML = '';
        
        // AJAX request
        fetch('ajax/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=forgot_password&email=${encodeURIComponent(email)}&csrf_token=${encodeURIComponent(csrf_token)}`
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = 'Send Reset Link';
            
            // Always show success (for security, we don't want to reveal if an email exists)
            alertsContainer.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            
            // Clear form
            document.getElementById('forgot-password-form').reset();
        })
        .catch(error => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = 'Send Reset Link';
            
            // Show error message
            alertsContainer.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
            console.error('Error:', error);
        });
    });
});
</script>