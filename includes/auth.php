<?php
/**
 * Authentication Functions
 * PHP 8.4 Pure Functional Script
 */

/**
 * Register a new user
 *
 * @param string $name User's name
 * @param string $email User's email
 * @param string $password User's password
 * @return array Status and message/user data
 */
function register_user($name, $email, $password) {
    // Check if email already exists
    $existing_user = db_query_row("SELECT id FROM users WHERE email = ?", [$email]);
    
    if ($existing_user) {
        return [
            'status' => false,
            'message' => 'Email is already registered'
        ];
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate verification token
    $verify_token = generate_token();
    
    // Insert user
    $result = db_execute(
        "INSERT INTO users (name, email, password, role_id, verify_token, created_at) 
         VALUES (?, ?, ?, ?, ?, NOW())",
        [$name, $email, $password_hash, ROLE_USER, $verify_token]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'Registration failed. Please try again.'
        ];
    }
    
    $user_id = db_last_insert_id();
    
    // Send verification email
    $verify_url = SITE_URL . '/verify.php?token=' . $verify_token;
    
    $subject = SITE_NAME . ' - Verify Your Email';
    $message = "Hello $name,\n\n"
             . "Thank you for registering at " . SITE_NAME . ". "
             . "Please verify your email by clicking the link below:\n\n"
             . $verify_url . "\n\n"
             . "This link will expire in 24 hours.\n\n"
             . "Regards,\n" . SITE_NAME . " Team";
    
    $email_sent = send_email($email, $subject, $message);
    
    return [
        'status' => true,
        'user_id' => $user_id,
        'email_sent' => $email_sent,
        'message' => 'Registration successful. Please check your email to verify your account.'
    ];
}

/**
 * Verify user email
 *
 * @param string $token Verification token
 * @return array Status and message
 */
function verify_user_email($token) {
    if (empty($token)) {
        return [
            'status' => false,
            'message' => 'Invalid verification token'
        ];
    }
    
    // Find user by token
    $user = db_query_row(
        "SELECT id, email, verified_at FROM users WHERE verify_token = ?", 
        [$token]
    );
    
    if (!$user) {
        return [
            'status' => false,
            'message' => 'Invalid verification token'
        ];
    }
    
    if ($user['verified_at'] !== null) {
        return [
            'status' => true,
            'message' => 'Email already verified. You can now login.'
        ];
    }
    
    // Update user as verified
    $result = db_execute(
        "UPDATE users SET verified_at = NOW(), verify_token = NULL WHERE id = ?",
        [$user['id']]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'Email verification failed. Please try again.'
        ];
    }
    
    log_activity('email_verified', 'Email verified successfully', $user['id']);
    
    return [
        'status' => true,
        'message' => 'Email verified successfully. You can now login.'
    ];
}

/**
 * Login user
 *
 * @param string $email User's email
 * @param string $password User's password
 * @param bool $remember Remember user
 * @return array Status and message/user data
 */
function login_user($email, $password, $remember = false) {
    // Find user by email
    $user = db_query_row(
        "SELECT id, name, email, password, role_id, verified_at, status 
         FROM users WHERE email = ?",
        [$email]
    );
    
    if (!$user) {
        return [
            'status' => false,
            'message' => 'Invalid email or password'
        ];
    }
    
    // Check if account is verified
    if ($user['verified_at'] === null) {
        return [
            'status' => false,
            'message' => 'Please verify your email before logging in'
        ];
    }
    
    // Check if account is active
    if ($user['status'] !== 'active') {
        return [
            'status' => false,
            'message' => 'Your account is not active. Please contact support.'
        ];
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        return [
            'status' => false,
            'message' => 'Invalid email or password'
        ];
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role_id'];
    
    // Update last login time
    db_execute(
        "UPDATE users SET last_login = NOW() WHERE id = ?",
        [$user['id']]
    );
    
    // Set remember me cookie if requested
    if ($remember) {
        $token = generate_token();
        $expires = time() + (86400 * 30); // 30 days
        
        // Store token in database
        db_execute(
            "INSERT INTO remember_tokens (user_id, token, expires_at) 
             VALUES (?, ?, FROM_UNIXTIME(?))",
            [$user['id'], $token, $expires]
        );
        
        // Set cookie
        setcookie('remember_token', $token, $expires, '/', '', true, true);
    }
    
    log_activity('login', 'User logged in', $user['id']);
    
    return [
        'status' => true,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_id' => $user['role_id']
        ],
        'message' => 'Login successful'
    ];
}

/**
 * Logout user
 *
 * @return void
 */
function logout_user() {
    // Clear remember token if exists
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        // Remove from database
        db_execute(
            "DELETE FROM remember_tokens WHERE token = ?",
            [$token]
        );
        
        // Clear cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
    
    // Log activity before clearing session
    if (isset($_SESSION['user_id'])) {
        log_activity('logout', 'User logged out', $_SESSION['user_id']);
    }
    
    // Clear session
    $_SESSION = [];
    
    // Destroy session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Check remember me token and auto-login
 *
 * @return bool True if user was logged in
 */
function check_remember_token() {
    if (!isset($_COOKIE['remember_token']) || is_logged_in()) {
        return false;
    }
    
    $token = $_COOKIE['remember_token'];
    
    // Find valid token
    $token_data = db_query_row(
        "SELECT rt.user_id, rt.expires_at, u.id, u.name, u.email, u.role_id, u.verified_at, u.status
         FROM remember_tokens rt
         JOIN users u ON rt.user_id = u.id
         WHERE rt.token = ? AND rt.expires_at > NOW()",
        [$token]
    );
    
    if (!$token_data) {
        // Invalid or expired token, clear cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        return false;
    }
    
    // Check if account is verified and active
    if ($token_data['verified_at'] === null || $token_data['status'] !== 'active') {
        return false;
    }
    
    // Set session variables
    $_SESSION['user_id'] = $token_data['id'];
    $_SESSION['user_name'] = $token_data['name'];
    $_SESSION['user_email'] = $token_data['email'];
    $_SESSION['user_role'] = $token_data['role_id'];
    
    // Update last login time
    db_execute(
        "UPDATE users SET last_login = NOW() WHERE id = ?",
        [$token_data['id']]
    );
    
    log_activity('auto_login', 'User auto-logged in with remember token', $token_data['id']);
    
    return true;
}

/**
 * Request password reset
 *
 * @param string $email User's email
 * @return array Status and message
 */
function request_password_reset($email) {
    // Find user by email
    $user = db_query_row(
        "SELECT id, name, email, verified_at, status FROM users WHERE email = ?",
        [$email]
    );
    
    if (!$user) {
        // Don't reveal that email doesn't exist
        return [
            'status' => true,
            'message' => 'If your email is registered, you will receive a password reset link'
        ];
    }
    
    // Check if account is verified
    if ($user['verified_at'] === null) {
        return [
            'status' => false,
            'message' => 'Please verify your email before resetting your password'
        ];
    }
    
    // Check if account is active
    if ($user['status'] !== 'active') {
        return [
            'status' => false,
            'message' => 'Your account is not active. Please contact support.'
        ];
    }
    
    // Generate reset token
    $token = generate_token();
    $expires = time() + 3600; // 1 hour
    
    // Store token in database
    $result = db_execute(
        "INSERT INTO password_resets (user_id, token, expires_at) 
         VALUES (?, ?, FROM_UNIXTIME(?))",
        [$user['id'], $token, $expires]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'Failed to generate reset token. Please try again.'
        ];
    }
    
    // Send reset email
    $reset_url = SITE_URL . '/reset-password.php?token=' . $token;
    
    $subject = SITE_NAME . ' - Password Reset';
    $message = "Hello {$user['name']},\n\n"
             . "You requested a password reset for your account at " . SITE_NAME . ". "
             . "Please click the link below to reset your password:\n\n"
             . $reset_url . "\n\n"
             . "This link will expire in 1 hour. If you did not request this reset, "
             . "please ignore this email.\n\n"
             . "Regards,\n" . SITE_NAME . " Team";
    
    $email_sent = send_email($user['email'], $subject, $message);
    
    log_activity('password_reset_request', 'Password reset requested', $user['id']);
    
    return [
        'status' => true,
        'email_sent' => $email_sent,
        'message' => 'If your email is registered, you will receive a password reset link'
    ];
}

/**
 * Verify password reset token
 *
 * @param string $token Reset token
 * @return array Status and user data
 */
function verify_reset_token($token) {
    if (empty($token)) {
        return [
            'status' => false,
            'message' => 'Invalid reset token'
        ];
    }
    
    // Find valid token
    $token_data = db_query_row(
        "SELECT pr.user_id, pr.expires_at, u.id, u.email 
         FROM password_resets pr
         JOIN users u ON pr.user_id = u.id
         WHERE pr.token = ? AND pr.expires_at > NOW()
         ORDER BY pr.created_at DESC LIMIT 1",
        [$token]
    );
    
    if (!$token_data) {
        return [
            'status' => false,
            'message' => 'Invalid or expired reset token'
        ];
    }
    
    return [
        'status' => true,
        'user_id' => $token_data['id'],
        'email' => $token_data['email']
    ];
}

/**
 * Reset user password
 *
 * @param string $token Reset token
 * @param string $password New password
 * @return array Status and message
 */
function reset_password($token, $password) {
    // Verify token
    $token_data = verify_reset_token($token);
    
    if (!$token_data['status']) {
        return $token_data;
    }
    
    // Hash new password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password
    $result = db_execute(
        "UPDATE users SET password = ? WHERE id = ?",
        [$password_hash, $token_data['user_id']]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'Password reset failed. Please try again.'
        ];
    }
    
    // Invalidate all reset tokens for this user
    db_execute(
        "DELETE FROM password_resets WHERE user_id = ?",
        [$token_data['user_id']]
    );
    
    // Invalidate remember tokens
    db_execute(
        "DELETE FROM remember_tokens WHERE user_id = ?",
        [$token_data['user_id']]
    );
    
    log_activity('password_reset', 'Password reset successfully', $token_data['user_id']);
    
    return [
        'status' => true,
        'message' => 'Password reset successfully. You can now login with your new password.'
    ];
}

/**
 * Get user profile
 *
 * @param int $user_id User ID (current user if null)
 * @return array|false User data or false if not found
 */
function get_user_profile($user_id = null) {
    if ($user_id === null) {
        if (!is_logged_in()) {
            return false;
        }
        $user_id = $_SESSION['user_id'];
    }
    
    return db_query_row(
        "SELECT id, name, email, phone, address, profile_image, created_at, last_login 
         FROM users WHERE id = ?",
        [$user_id]
    );
}

/**
 * Update user profile
 *
 * @param array $data Profile data to update
 * @param int $user_id User ID (current user if null)
 * @return array Status and message
 */
function update_user_profile($data, $user_id = null) {
    if ($user_id === null) {
        if (!is_logged_in()) {
            return [
                'status' => false,
                'message' => 'You must be logged in to update your profile'
            ];
        }
        $user_id = $_SESSION['user_id'];
    }
    
    // Fields allowed to be updated
    $allowed_fields = ['name', 'phone', 'address'];
    $updates = [];
    $params = [];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        return [
            'status' => false,
            'message' => 'No fields to update'
        ];
    }
    
    // Add user_id to params
    $params[] = $user_id;
    
    $result = db_execute(
        "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?",
        $params
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'Profile update failed. Please try again.'
        ];
    }
    
    // Update session name if it was changed
    if (isset($data['name']) && $user_id == $_SESSION['user_id']) {
        $_SESSION['user_name'] = $data['name'];
    }
    
    log_activity('profile_update', 'Profile updated', $user_id);
    
    return [
        'status' => true,
        'message' => 'Profile updated successfully'
    ];
}

/**
 * Change user password
 *
 * @param string $current_password Current password
 * @param string $new_password New password
 * @param int $user_id User ID (current user if null)
 * @return array Status and message
 */
function change_user_password($current_password, $new_password, $user_id = null) {
    if ($user_id === null) {
        if (!is_logged_in()) {
            return [
                'status' => false,
                'message' => 'You must be logged in to change your password'
            ];
        }
        $user_id = $_SESSION['user_id'];
    }
    
    // Get current user data
    $user = db_query_row(
        "SELECT password FROM users WHERE id = ?",
        [$user_id]
    );
    
    if (!$user) {
        return [
            'status' => false,
            'message' => 'User not found'
        ];
    }
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        return [
            'status' => false,
            'message' => 'Current password is incorrect'
        ];
    }
    
    // Hash new password
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $result = db_execute(
        "UPDATE users SET password = ? WHERE id = ?",
        [$password_hash, $user_id]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'Password change failed. Please try again.'
        ];
    }
    
    // Invalidate remember tokens
    db_execute(
        "DELETE FROM remember_tokens WHERE user_id = ?",
        [$user_id]
    );
    
    log_activity('password_change', 'Password changed', $user_id);
    
    return [
        'status' => true,
        'message' => 'Password changed successfully'
    ];
}