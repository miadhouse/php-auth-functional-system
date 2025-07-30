<?php
/**
 * Payment Receipt Management Functions
 * PHP 8.4 Pure Functional Script
 * Save as: includes/payment_receipts.php
 */

/**
 * Activate free plan subscription
 *
 * @param int $user_id User ID
 * @param int $plan_id Plan ID
 * @return array Status and message
 */
function activate_free_plan($user_id, $plan_id) {
    // Get plan details
    $plan = get_subscription_plan($plan_id);
    
    if (!$plan) {
        return [
            'status' => false,
            'message' => 'پلن انتخابی معتبر نیست'
        ];
    }
    
    // Check if it's really a free plan
    if ($plan['price_monthly'] > 0 || $plan['price_yearly'] > 0) {
        return [
            'status' => false,
            'message' => 'این پلن رایگان نیست'
        ];
    }
    
    // Check if user already has an active subscription
    $current_subscription = get_user_subscription($user_id);
    
    if ($current_subscription && $current_subscription['status'] === 'active') {
        // Cancel current subscription if not the same plan
        if ($current_subscription['plan_id'] != $plan_id) {
            cancel_subscription($current_subscription['id'], true);
        } else {
            return [
                'status' => false,
                'message' => 'شما در حال حاضر این پلن را دارید'
            ];
        }
    }
    
    // Create subscription (free plans don't expire)
    $current_period_start = date('Y-m-d H:i:s');
    $current_period_end = date('Y-m-d H:i:s', strtotime('+10 years')); // Very long period for free plan
    
    $result = db_execute(
        "INSERT INTO user_subscriptions (user_id, plan_id, status, billing_cycle, amount, current_period_start, current_period_end, created_at)
         VALUES (?, ?, 'active', 'monthly', 0.00, ?, ?, NOW())",
        [$user_id, $plan_id, $current_period_start, $current_period_end]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'خطا در فعال‌سازی پلن رایگان'
        ];
    }
    
    $subscription_id = db_last_insert_id();
    
    // Update user's current subscription
    db_execute(
        "UPDATE users SET current_subscription_id = ?, subscription_status = 'active' WHERE id = ?",
        [$subscription_id, $user_id]
    );
    
    // Log activity
    log_activity('free_plan_activated', "Free plan activated: {$plan['name']}", $user_id);
    
    return [
        'status' => true,
        'subscription_id' => $subscription_id,
        'message' => 'پلن رایگان با موفقیت فعال شد'
    ];
}

/**
 * Get bank account information for payments
 *
 * @return array Bank account information
 */
function get_bank_account_info() {
    $info = db_query_row("SELECT value FROM settings WHERE `key` = 'bank_account_info'");
    
    if ($info) {
        $decoded = json_decode($info['value'], true);
        return is_array($decoded) ? $decoded : [];
    }
    
    return [
        'bank_name' => 'بانک ملی ایران',
        'account_number' => '1234567890',
        'sheba' => 'IR123456789012345678901234',
        'account_holder' => 'شرکت نمونه'
    ];
}

/**
 * Get payment instructions
 *
 * @return string Payment instructions
 */
function get_payment_instructions() {
    $instructions = db_query_row("SELECT value FROM settings WHERE `key` = 'payment_instructions'");
    
    return $instructions['value'] ?? 'لطفاً مبلغ را به حساب زیر واریز کرده و فیش واریزی را آپلود کنید';
}

/**
 * Upload receipt file
 *
 * @param array $file File from $_FILES
 * @param int $user_id User ID
 * @return array Status and file path
 */
function upload_receipt_file($file, $user_id) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return [
            'status' => false,
            'message' => 'لطفاً فیش پرداخت را انتخاب کنید'
        ];
    }
    
    // Check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'status' => false,
            'message' => 'خطا در آپلود فایل'
        ];
    }
    
    // Get max file size from settings
    $max_size = db_query_row("SELECT value FROM settings WHERE `key` = 'max_receipt_size'");
    $max_size = $max_size ? (int)$max_size['value'] : 5242880; // 5MB default
    
    // Check file size
    if ($file['size'] > $max_size) {
        return [
            'status' => false,
            'message' => 'حجم فایل نباید بیشتر از ' . round($max_size / 1024 / 1024, 1) . ' مگابایت باشد'
        ];
    }
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime_type, $allowed_types)) {
        return [
            'status' => false,
            'message' => 'فرمت فایل مجاز نیست. فقط تصاویر و فایل PDF مجاز است'
        ];
    }
    
    // Create upload directory
    $upload_path = db_query_row("SELECT value FROM settings WHERE `key` = 'receipt_upload_path'");
    $upload_dir = __DIR__ . '/../' . ($upload_path['value'] ?? 'uploads/receipts/');
    
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return [
                'status' => false,
                'message' => 'خطا در ایجاد پوشه آپلود'
            ];
        }
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'receipt_' . $user_id . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'status' => true,
            'filename' => $filename,
            'filepath' => ($upload_path['value'] ?? 'uploads/receipts/') . $filename
        ];
    } else {
        return [
            'status' => false,
            'message' => 'خطا در ذخیره فایل'
        ];
    }
}

/**
 * Submit payment receipt
 *
 * @param int $user_id User ID
 * @param int $plan_id Plan ID
 * @param string $billing_cycle Billing cycle
 * @param array $receipt_data Receipt data
 * @return array Status and message
 */
function submit_payment_receipt($user_id, $plan_id, $billing_cycle, $receipt_data) {
    // Get plan details
    $plan = get_subscription_plan($plan_id);
    
    if (!$plan) {
        return [
            'status' => false,
            'message' => 'پلن انتخابی معتبر نیست'
        ];
    }
    
    // Calculate amount
    $amount = $billing_cycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
    
    if ($amount <= 0) {
        return [
            'status' => false,
            'message' => 'این پلن رایگان است و نیازی به پرداخت ندارد'
        ];
    }
    
    // Check if user already has a pending receipt for this plan
    $existing_receipt = db_query_row(
        "SELECT id FROM payment_receipts 
         WHERE user_id = ? AND plan_id = ? AND status = 'pending'",
        [$user_id, $plan_id]
    );
    
    if ($existing_receipt) {
        return [
            'status' => false,
            'message' => 'شما قبلاً برای این پلن فیش پرداخت ارسال کرده‌اید و در انتظار بررسی است'
        ];
    }
    
    // Insert receipt record
    $result = db_execute(
        "INSERT INTO payment_receipts (user_id, plan_id, billing_cycle, amount, receipt_image, bank_name, transaction_id, payment_date, description, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [
            $user_id,
            $plan_id,
            $billing_cycle,
            $amount,
            $receipt_data['receipt_image'],
            $receipt_data['bank_name'] ?? '',
            $receipt_data['transaction_id'] ?? '',
            $receipt_data['payment_date'],
            $receipt_data['description'] ?? ''
        ]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'خطا در ثبت فیش پرداخت'
        ];
    }
    
    $receipt_id = db_last_insert_id();
    
    // Create subscription request
    $request_result = db_execute(
        "INSERT INTO subscription_requests (user_id, plan_id, payment_receipt_id, billing_cycle, created_at)
         VALUES (?, ?, ?, ?, NOW())",
        [$user_id, $plan_id, $receipt_id, $billing_cycle]
    );
    
    if (!$request_result) {
        // Remove the receipt if request creation failed
        db_execute("DELETE FROM payment_receipts WHERE id = ?", [$receipt_id]);
        
        return [
            'status' => false,
            'message' => 'خطا در ثبت درخواست اشتراک'
        ];
    }
    
    // Log activity
    log_activity('payment_receipt_submitted', "Payment receipt submitted for plan: {$plan['name']}", $user_id);
    
    return [
        'status' => true,
        'receipt_id' => $receipt_id,
        'message' => 'فیش پرداخت با موفقیت ارسال شد و در انتظار بررسی ادمین است'
    ];
}

/**
 * Get user payment receipts
 *
 * @param int $user_id User ID
 * @param string $status Filter by status
 * @return array Payment receipts
 */
function get_user_payment_receipts($user_id, $status = null) {
    $query = "SELECT pr.*, sp.name as plan_name, sp.plan_type
              FROM payment_receipts pr
              JOIN subscription_plans sp ON pr.plan_id = sp.id
              WHERE pr.user_id = ?";
    
    $params = [$user_id];
    
    if ($status !== null) {
        $query .= " AND pr.status = ?";
        $params[] = $status;
    }
    
    $query .= " ORDER BY pr.created_at DESC";
    
    return db_query($query, $params);
}

/**
 * Get all payment receipts for admin
 *
 * @param string $status Filter by status
 * @param int $limit Limit number of results
 * @param int $offset Offset for pagination
 * @return array Payment receipts
 */
function get_all_payment_receipts($status = null, $limit = 20, $offset = 0) {
    $query = "SELECT pr.*, sp.name as plan_name, sp.plan_type, u.name as user_name, u.email as user_email
              FROM payment_receipts pr
              JOIN subscription_plans sp ON pr.plan_id = sp.id
              JOIN users u ON pr.user_id = u.id";
    
    $params = [];
    
    if ($status !== null) {
        $query .= " WHERE pr.status = ?";
        $params[] = $status;
    }
    
    $query .= " ORDER BY pr.created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    
    return db_query($query, $params);
}

/**
 * Review payment receipt (approve/reject)
 *
 * @param int $receipt_id Receipt ID
 * @param string $action 'approve' or 'reject'
 * @param string $admin_notes Admin notes
 * @param int $admin_id Admin user ID
 * @return array Status and message
 */
function review_payment_receipt($receipt_id, $action, $admin_notes = '', $admin_id = null) {
    if (!in_array($action, ['approve', 'reject'])) {
        return [
            'status' => false,
            'message' => 'عملیات نامعتبر'
        ];
    }
    
    if ($admin_id === null && is_logged_in()) {
        $admin_id = $_SESSION['user_id'];
    }
    
    // Get receipt details
    $receipt = db_query_row(
        "SELECT pr.*, sp.name as plan_name, u.name as user_name, u.email as user_email
         FROM payment_receipts pr
         JOIN subscription_plans sp ON pr.plan_id = sp.id
         JOIN users u ON pr.user_id = u.id
         WHERE pr.id = ?",
        [$receipt_id]
    );
    
    if (!$receipt) {
        return [
            'status' => false,
            'message' => 'فیش پرداخت یافت نشد'
        ];
    }
    
    if ($receipt['status'] !== 'pending') {
        return [
            'status' => false,
            'message' => 'این فیش پرداخت قبلاً بررسی شده است'
        ];
    }
    
    $new_status = $action === 'approve' ? 'approved' : 'rejected';
    
    // Update receipt status
    $result = db_execute(
        "UPDATE payment_receipts 
         SET status = ?, admin_notes = ?, reviewed_by = ?, reviewed_at = NOW()
         WHERE id = ?",
        [$new_status, $admin_notes, $admin_id, $receipt_id]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'خطا در به‌روزرسانی وضعیت فیش پرداخت'
        ];
    }
    
    // Update subscription request status
    db_execute(
        "UPDATE subscription_requests 
         SET status = ?, admin_notes = ?, processed_by = ?, processed_at = NOW()
         WHERE payment_receipt_id = ?",
        [$new_status, $admin_notes, $admin_id, $receipt_id]
    );
    
    // If approved, activate subscription
    if ($action === 'approve') {
        $activation_result = activate_subscription_from_receipt($receipt);
        
        if (!$activation_result['status']) {
            // Rollback the approval
            db_execute(
                "UPDATE payment_receipts SET status = 'pending', admin_notes = NULL, reviewed_by = NULL, reviewed_at = NULL WHERE id = ?",
                [$receipt_id]
            );
            
            return $activation_result;
        }
    }
    
    // Log activity
    log_activity('payment_receipt_reviewed', "Payment receipt {$action}d for user: {$receipt['user_name']}", $admin_id);
    
    return [
        'status' => true,
        'message' => $action === 'approve' ? 'فیش پرداخت تایید و اشتراک فعال شد' : 'فیش پرداخت رد شد'
    ];
}

/**
 * Activate subscription from approved receipt
 *
 * @param array $receipt Receipt data
 * @return array Status and message
 */
function activate_subscription_from_receipt($receipt) {
    $user_id = $receipt['user_id'];
    $plan_id = $receipt['plan_id'];
    $billing_cycle = $receipt['billing_cycle'];
    
    // Check if user already has an active subscription
    $current_subscription = get_user_subscription($user_id);
    
    if ($current_subscription && $current_subscription['status'] === 'active') {
        // Cancel current subscription
        cancel_subscription($current_subscription['id'], true);
    }
    
    // Calculate subscription period
    $current_period_start = date('Y-m-d H:i:s');
    if ($billing_cycle === 'yearly') {
        $current_period_end = date('Y-m-d H:i:s', strtotime('+1 year'));
    } else {
        $current_period_end = date('Y-m-d H:i:s', strtotime('+1 month'));
    }
    
    // Create subscription
    $result = db_execute(
        "INSERT INTO user_subscriptions (user_id, plan_id, status, billing_cycle, amount, current_period_start, current_period_end, created_at)
         VALUES (?, ?, 'active', ?, ?, ?, ?, NOW())",
        [$user_id, $plan_id, $billing_cycle, $receipt['amount'], $current_period_start, $current_period_end]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'خطا در فعال‌سازی اشتراک'
        ];
    }
    
    $subscription_id = db_last_insert_id();
    
    // Update user's current subscription
    db_execute(
        "UPDATE users SET current_subscription_id = ?, subscription_status = 'active' WHERE id = ?",
        [$subscription_id, $user_id]
    );
    
    // Create payment record
    db_execute(
        "INSERT INTO subscription_payments (subscription_id, user_id, amount, payment_method, transaction_id, status, payment_date, created_at)
         VALUES (?, ?, ?, 'bank_transfer', ?, 'completed', ?, NOW())",
        [$subscription_id, $user_id, $receipt['amount'], $receipt['transaction_id'], $receipt['payment_date']]
    );
    
    // Log activity
    log_activity('subscription_activated', "Subscription activated from payment receipt", $user_id);
    
    return [
        'status' => true,
        'subscription_id' => $subscription_id,
        'message' => 'اشتراک با موفقیت فعال شد'
    ];
}

/**
 * Get receipt statistics for admin
 *
 * @return array Statistics
 */
function get_receipt_statistics() {
    $stats = [];
    
    // Total receipts
    $stats['total'] = db_query_row("SELECT COUNT(*) as count FROM payment_receipts")['count'] ?? 0;
    
    // Pending receipts
    $stats['pending'] = db_query_row("SELECT COUNT(*) as count FROM payment_receipts WHERE status = 'pending'")['count'] ?? 0;
    
    // Approved receipts
    $stats['approved'] = db_query_row("SELECT COUNT(*) as count FROM payment_receipts WHERE status = 'approved'")['count'] ?? 0;
    
    // Rejected receipts
    $stats['rejected'] = db_query_row("SELECT COUNT(*) as count FROM payment_receipts WHERE status = 'rejected'")['count'] ?? 0;
    
    // Total amount approved
    $stats['total_amount'] = db_query_row("SELECT SUM(amount) as total FROM payment_receipts WHERE status = 'approved'")['total'] ?? 0;
    
    // Recent receipts
    $stats['recent'] = db_query(
        "SELECT pr.*, sp.name as plan_name, u.name as user_name
         FROM payment_receipts pr
         JOIN subscription_plans sp ON pr.plan_id = sp.id
         JOIN users u ON pr.user_id = u.id
         ORDER BY pr.created_at DESC
         LIMIT 5"
    );
    
    return $stats;
}

/**
 * Check if user can submit receipt for plan
 *
 * @param int $user_id User ID
 * @param int $plan_id Plan ID
 * @return array Status and message
 */
function can_submit_receipt($user_id, $plan_id) {
    // Get plan details
    $plan = get_subscription_plan($plan_id);
    
    if (!$plan) {
        return [
            'can_submit' => false,
            'message' => 'پلن انتخابی معتبر نیست'
        ];
    }
    
    // Check if plan is free
    if ($plan['price_monthly'] <= 0 && $plan['price_yearly'] <= 0) {
        return [
            'can_submit' => false,
            'message' => 'این پلن رایگان است و نیازی به پرداخت ندارد'
        ];
    }
    
    // Check if user already has pending receipt
    $pending_receipt = db_query_row(
        "SELECT id FROM payment_receipts WHERE user_id = ? AND plan_id = ? AND status = 'pending'",
        [$user_id, $plan_id]
    );
    
    if ($pending_receipt) {
        return [
            'can_submit' => false,
            'message' => 'شما قبلاً برای این پلن فیش پرداخت ارسال کرده‌اید'
        ];
    }
    
    // Check if user already has this plan active
    $current_subscription = get_user_subscription($user_id);
    
    if ($current_subscription && $current_subscription['plan_id'] == $plan_id && $current_subscription['status'] === 'active') {
        return [
            'can_submit' => false,
            'message' => 'شما در حال حاضر این پلن را دارید'
        ];
    }
    
    return [
        'can_submit' => true,
        'message' => 'می‌توانید فیش پرداخت ارسال کنید'
    ];
}