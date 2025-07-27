<?php
// auth_modals.php - Authentication Modals to be included in the landing page
session_start();
require_once 'config.php';

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $token_hash = hash('sha256', $token);
    
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE remember_token = ?");
        $stmt->execute([$token_hash]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
        }
    } catch (PDOException $e) {
        // Silent error
    }
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">ورود</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">ایمیل</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">رمز عبور</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                        <label class="form-check-label" for="rememberMe">مرا به خاطر بسپار</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">ورود</button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="#" id="forgotPasswordLink">رمز عبور را فراموش کرده‌اید؟</a>
                    </div>
                    
                    <div class="text-center mt-3">
                        آیا حساب کاربری ندارید؟ <a href="#" id="showRegisterModal">ثبت نام</a>
                    </div>
                    
                    <div id="loginMessage" class="mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">ثبت نام</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="registerName" class="form-label">نام و نام خانوادگی</label>
                        <input type="text" class="form-control" id="registerName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">ایمیل</label>
                        <input type="email" class="form-control" id="registerEmail" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">رمز عبور</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" required minlength="8">
                        <div class="form-text">رمز عبور باید حداقل 8 کاراکتر باشد.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="registerConfirmPassword" class="form-label">تکرار رمز عبور</label>
                        <input type="password" class="form-control" id="registerConfirmPassword" name="confirm_password" required minlength="8">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">ثبت نام</button>
                    </div>
                    
                    <div class="text-center mt-3">
                        قبلاً ثبت نام کرده‌اید؟ <a href="#" id="showLoginModal">ورود</a>
                    </div>
                    
                    <div id="registerMessage" class="mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">بازیابی رمز عبور</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="forgotPasswordForm">
                    <input type="hidden" name="action" value="forgot_password">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="forgotEmail" class="form-label">ایمیل</label>
                        <input type="email" class="form-control" id="forgotEmail" name="email" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">ارسال لینک بازیابی</button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="#" id="backToLoginModal">بازگشت به ورود</a>
                    </div>
                    
                    <div id="forgotPasswordMessage" class="mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show login modal
    $('.login-button').on('click', function(e) {
        e.preventDefault();
        $('#loginModal').modal('show');
    });
    
    // Show register modal from login modal
    $('#showRegisterModal').on('click', function(e) {
        e.preventDefault();
        $('#loginModal').modal('hide');
        $('#registerModal').modal('show');
    });
    
    // Show login modal from register modal
    $('#showLoginModal').on('click', function(e) {
        e.preventDefault();
        $('#registerModal').modal('hide');
        $('#loginModal').modal('show');
    });
    
    // Show forgot password modal
    $('#forgotPasswordLink').on('click', function(e) {
        e.preventDefault();
        $('#loginModal').modal('hide');
        $('#forgotPasswordModal').modal('show');
    });
    
    // Back to login from forgot password
    $('#backToLoginModal').on('click', function(e) {
        e.preventDefault();
        $('#forgotPasswordModal').modal('hide');
        $('#loginModal').modal('show');
    });
    
    // Login form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'auth_controller.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#loginMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 2000);
                } else {
                    $('#loginMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#loginMessage').html('<div class="alert alert-danger">خطا در ارتباط با سرور. لطفا دوباره تلاش کنید.</div>');
            }
        });
    });
    
    // Register form submission
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        // Check if passwords match
        if ($('#registerPassword').val() !== $('#registerConfirmPassword').val()) {
            $('#registerMessage').html('<div class="alert alert-danger">رمز عبور و تکرار آن مطابقت ندارند</div>');
            return;
        }
        
        $.ajax({
            url: 'auth_controller.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#registerMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    setTimeout(function() {
                        $('#registerModal').modal('hide');
                        $('#loginModal').modal('show');
                    }, 3000);
                } else {
                    $('#registerMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#registerMessage').html('<div class="alert alert-danger">خطا در ارتباط با سرور. لطفا دوباره تلاش کنید.</div>');
            }
        });
    });
    
    // Forgot password form submission
    $('#forgotPasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'auth_controller.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#forgotPasswordMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                } else {
                    $('#forgotPasswordMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#forgotPasswordMessage').html('<div class="alert alert-danger">خطا در ارتباط با سرور. لطفا دوباره تلاش کنید.</div>');
            }
        });
    });
});
</script>