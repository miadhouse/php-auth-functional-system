<?php
// auth_controller.php - Main Authentication Controller

// Start session
session_start();
require_once 'config.php';

// Create tables if they don't exist
create_tables();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Verify CSRF token for all actions
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        echo json_encode(['status' => 'error', 'message' => 'نشست منقضی شده است. لطفا صفحه را رفرش کنید.']);
        exit;
    }
    
    switch ($action) {
        case 'register':
            register();
            break;
        case 'login':
            login();
            break;
        case 'forgot_password':
            forgot_password();
            break;
        case 'reset_password':
            reset_password();
            break;
        case 'logout':
            logout();
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'درخواست نامعتبر']);
    }
}

// Register Function
function register() {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        echo json_encode(['status' => 'error', 'message' => 'لطفا تمام فیلدها را پر کنید']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'آدرس ایمیل نامعتبر است']);
        exit;
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'رمز عبور و تکرار آن مطابقت ندارند']);
        exit;
    }
    
    if (strlen($password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'رمز عبور باید حداقل 8 کاراکتر باشد']);
        exit;
    }
    
    try {
        $pdo = db_connect();
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'این ایمیل قبلا ثبت شده است']);
            exit;
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_token) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password_hash, $verification_token]);
        
        // Send verification email
        $verification_link = SITE_URL . "/verify.php?token=" . $verification_token . "&email=" . urlencode($email);
        $subject = "تایید ثبت نام در " . SITE_NAME;
        $message = "
        <html>
        <body dir='rtl'>
            <h2>به " . SITE_NAME . " خوش آمدید!</h2>
            <p>برای تایید حساب کاربری خود، لطفا روی لینک زیر کلیک کنید:</p>
            <p><a href='" . $verification_link . "'>تایید حساب کاربری</a></p>
            <p>اگر شما در سایت ما ثبت نام نکرده‌اید، لطفا این ایمیل را نادیده بگیرید.</p>
        </body>
        </html>
        ";
        
        if (send_email($email, $subject, $message)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'ثبت نام با موفقیت انجام شد. لطفا ایمیل خود را برای تایید حساب کاربری بررسی کنید.'
            ]);
        } else {
            echo json_encode([
                'status' => 'warning', 
                'message' => 'ثبت نام انجام شد اما ارسال ایمیل تایید با مشکل مواجه شد. لطفا با پشتیبانی تماس بگیرید.'
            ]);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'خطا در ثبت نام. لطفا دوباره تلاش کنید.']);
    }
}

// Login Function
function login() {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? (bool)$_POST['remember'] : false;
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'لطفا ایمیل و رمز عبور را وارد کنید']);
        exit;
    }
    
    try {
        $pdo = db_connect();
        
        // Get user
        $stmt = $pdo->prepare("SELECT id, name, email, password, email_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'ایمیل یا رمز عبور اشتباه است']);
            exit;
        }
        
        if (!$user['email_verified']) {
            echo json_encode(['status' => 'error', 'message' => 'لطفا ابتدا حساب کاربری خود را از طریق ایمیل تایید کنید']);
            exit;
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token_hash, $user['id']]);
            
            setcookie('remember_token', $token, $expiry, '/', '', true, true);
        }
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'ورود موفقیت‌آمیز. در حال انتقال به پنل کاربری...',
            'redirect' => 'dashboard.php'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'خطا در ورود. لطفا دوباره تلاش کنید.']);
    }
}

// Forgot Password Function
function forgot_password() {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'لطفا ایمیل خود را وارد کنید']);
        exit;
    }
    
    try {
        $pdo = db_connect();
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() === 0) {
            // To prevent email enumeration, we'll show a success message even if email doesn't exist
            echo json_encode([
                'status' => 'success', 
                'message' => 'اگر این ایمیل در سیستم ما ثبت شده باشد، یک لینک بازیابی برای شما ارسال خواهد شد'
            ]);
            exit;
        }
        
        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // Token expires in 1 hour
        
        // Update user with reset token
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([$reset_token, $expires, $email]);
        
        // Send reset email
        $reset_link = SITE_URL . "/reset-password.php?token=" . $reset_token . "&email=" . urlencode($email);
        $subject = "بازیابی رمز عبور در " . SITE_NAME;
        $message = "
        <html>
        <body dir='rtl'>
            <h2>بازیابی رمز عبور</h2>
            <p>شما درخواست بازیابی رمز عبور کرده‌اید. برای تنظیم رمز عبور جدید، روی لینک زیر کلیک کنید:</p>
            <p><a href='" . $reset_link . "'>بازیابی رمز عبور</a></p>
            <p>اگر شما این درخواست را نداده‌اید، لطفا این ایمیل را نادیده بگیرید.</p>
            <p>این لینک تا 1 ساعت اعتبار دارد.</p>
        </body>
        </html>
        ";
        
        if (send_email($email, $subject, $message)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'لینک بازیابی رمز عبور به ایمیل شما ارسال شد. لطفا ایمیل خود را بررسی کنید.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'ارسال ایمیل بازیابی با مشکل مواجه شد. لطفا با پشتیبانی تماس بگیرید.'
            ]);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'خطا در پردازش درخواست. لطفا دوباره تلاش کنید.']);
    }
}

// Reset Password Function
function reset_password() {
    $token = $_POST['token'] ?? '';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($token) || empty($email) || empty($password) || empty($confirm_password)) {
        echo json_encode(['status' => 'error', 'message' => 'لطفا تمام فیلدها را پر کنید']);
        exit;
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'رمز عبور و تکرار آن مطابقت ندارند']);
        exit;
    }
    
    if (strlen($password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'رمز عبور باید حداقل 8 کاراکتر باشد']);
        exit;
    }
    
    try {
        $pdo = db_connect();
        
        // Check token validity
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$email, $token]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['status' => 'error', 'message' => 'لینک بازیابی نامعتبر است یا منقضی شده است']);
            exit;
        }
        
        // Update password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?");
        $stmt->execute([$password_hash, $email]);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'رمز عبور با موفقیت تغییر کرد. اکنون می‌توانید وارد شوید.',
            'redirect' => 'index.php'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'خطا در بازیابی رمز عبور. لطفا دوباره تلاش کنید.']);
    }
}

// Logout Function
function logout() {
    // Clear session
    $_SESSION = [];
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
    
    // Destroy session
    session_destroy();
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'خروج موفقیت‌آمیز',
        'redirect' => 'index.php'
    ]);
}
?>