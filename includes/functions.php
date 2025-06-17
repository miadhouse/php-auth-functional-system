<?php
/**
 * Core Functions
 * PHP 8.4 Pure Functional Script
 */

/**
 * Get the CSRF token
 *
 * @return string Current CSRF token
 */
function get_csrf_token() {
    return $_SESSION[CSRF_TOKEN_NAME] ?? '';
}

/**
 * Verify CSRF token
 *
 * @param string $token Token to verify
 * @return bool True if token is valid
 */
function verify_csrf_token($token) {
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Redirect to a specific URL
 *
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Set flash message
 *
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 * @return void
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
        'time' => time()
    ];
}

/**
 * Get and clear flash message
 *
 * @return array|null Flash message or null if no message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        // Only return if less than 5 minutes old
        if (time() - $message['time'] < 300) {
            return $message;
        }
    }
    
    return null;
}

/**
 * Generate a random token
 *
 * @param int $length Token length
 * @return string Random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if user is logged in
 *
 * @return bool True if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has a specific role
 *
 * @param int $role_id Role ID to check
 * @return bool True if user has role
 */
function has_role($role_id) {
    if (!is_logged_in()) {
        return false;
    }
    
    return ($_SESSION['user_role'] ?? 0) == $role_id;
}

/**
 * Check if user is admin
 *
 * @return bool True if user is admin
 */
function is_admin() {
    return has_role(ROLE_ADMIN);
}

/**
 * Sanitize output for HTML display
 *
 * @param string $string String to sanitize
 * @return string Sanitized string
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format price for display
 *
 * @param float $price Price to format
 * @return string Formatted price
 */
function format_price($price) {
    return '$' . number_format($price, 2);
}

/**
 * Get current page URL
 *
 * @return string Current URL
 */
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if current request is AJAX
 *
 * @return bool True if request is AJAX
 */
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON response
 *
 * @param mixed $data Data to encode
 * @param int $status HTTP status code
 * @return void
 */
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Log activity
 *
 * @param string $action Action performed
 * @param string $detail Activity details
 * @param int $user_id User ID (current user if null)
 * @return bool True on success
 */
function log_activity($action, $detail, $user_id = null) {
    if ($user_id === null && is_logged_in()) {
        $user_id = $_SESSION['user_id'];
    }
    
    return db_execute(
        "INSERT INTO activity_logs (user_id, action, detail, ip_address, created_at) 
         VALUES (?, ?, ?, ?, NOW())",
        [$user_id, $action, $detail, $_SERVER['REMOTE_ADDR']]
    );
}