<?php
/**
 * Subscription Management Functions
 * PHP 8.4 Pure Functional Script
 */

/**
 * Get all subscription plans
 *
 * @return array List of subscription plans
 */
function get_subscription_plans() {
    return db_query(
        "SELECT * FROM subscription_plans WHERE status = 'active' ORDER BY sort_order ASC, price_monthly ASC"
    );
}

/**
 * Get subscription plan by ID
 *
 * @param int $plan_id Plan ID
 * @return array|false Plan data or false if not found
 */
function get_subscription_plan($plan_id) {
    return db_query_row(
        "SELECT * FROM subscription_plans WHERE id = ? AND status = 'active'",
        [$plan_id]
    );
}

/**
 * Get subscription plan by slug
 *
 * @param string $slug Plan slug
 * @return array|false Plan data or false if not found
 */
function get_subscription_plan_by_slug($slug) {
    return db_query_row(
        "SELECT * FROM subscription_plans WHERE slug = ? AND status = 'active'",
        [$slug]
    );
}

/**
 * Get user's current subscription
 *
 * @param int $user_id User ID (current user if null)
 * @return array|false Subscription data or false if no active subscription
 */
function get_user_subscription($user_id = null) {
    if ($user_id === null) {
        if (!is_logged_in()) {
            return false;
        }
        $user_id = $_SESSION['user_id'];
    }
    
    return db_query_row(
        "SELECT us.*, sp.name as plan_name, sp.features as plan_features, sp.plan_type
         FROM user_subscriptions us
         JOIN subscription_plans sp ON us.plan_id = sp.id
         WHERE us.user_id = ? 
         AND us.status IN ('active', 'trial')
         AND (us.ends_at IS NULL OR us.ends_at > NOW())
         ORDER BY us.created_at DESC 
         LIMIT 1",
        [$user_id]
    );
}

/**
 * Check if user has active subscription
 *
 * @param int $user_id User ID (current user if null)
 * @return bool True if user has active subscription
 */
function has_active_subscription($user_id = null) {
    return get_user_subscription($user_id) !== false;
}

/**
 * Check if user has specific subscription plan type
 *
 * @param string $plan_type Plan type (bronze, silver, gold, platinum, enterprise)
 * @param int $user_id User ID (current user if null)
 * @return bool True if user has the plan type or higher
 */
function has_subscription_plan($plan_type, $user_id = null) {
    $subscription = get_user_subscription($user_id);
    
    if (!$subscription) {
        return false;
    }
    
    $plan_hierarchy = [
        'bronze' => 1,
        'silver' => 2,
        'gold' => 3,
        'platinum' => 4,
        'enterprise' => 5
    ];
    
    $user_plan_level = $plan_hierarchy[$subscription['plan_type']] ?? 0;
    $required_plan_level = $plan_hierarchy[$plan_type] ?? 0;
    
    return $user_plan_level >= $required_plan_level;
}

/**
 * Check if user can access a feature
 *
 * @param string $feature Feature name
 * @param int $user_id User ID (current user if null)
 * @return bool True if user can access the feature
 */
function can_access_feature($feature, $user_id = null) {
    $subscription = get_user_subscription($user_id);
    
    if (!$subscription) {
        return false;
    }
    
    $features = json_decode($subscription['plan_features'], true);
    
    if (!is_array($features)) {
        return false;
    }
    
    return in_array($feature, $features);
}

/**
 * Create a new subscription
 *
 * @param int $user_id User ID
 * @param int $plan_id Plan ID
 * @param string $billing_cycle monthly or yearly
 * @param bool $start_trial Start with trial period
 * @return array Status and message
 */
function create_subscription($user_id, $plan_id, $billing_cycle = 'monthly', $start_trial = true) {
    // Get plan details
    $plan = get_subscription_plan($plan_id);
    
    if (!$plan) {
        return [
            'status' => false,
            'message' => 'Invalid subscription plan'
        ];
    }
    
    // Check if user already has an active subscription
    if (has_active_subscription($user_id)) {
        return [
            'status' => false,
            'message' => 'User already has an active subscription'
        ];
    }
    
    // Calculate price and dates
    $amount = $billing_cycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
    $trial_days = $start_trial ? $plan['trial_days'] : 0;
    
    $trial_ends_at = null;
    $current_period_start = date('Y-m-d H:i:s');
    $current_period_end = null;
    $status = 'pending';
    
    if ($trial_days > 0) {
        $trial_ends_at = date('Y-m-d H:i:s', strtotime("+{$trial_days} days"));
        $current_period_end = $trial_ends_at;
        $status = 'trial';
    } else {
        if ($billing_cycle === 'yearly') {
            $current_period_end = date('Y-m-d H:i:s', strtotime('+1 year'));
        } else {
            $current_period_end = date('Y-m-d H:i:s', strtotime('+1 month'));
        }
        $status = 'active';
    }
    
    // Create subscription
    $result = db_execute(
        "INSERT INTO user_subscriptions (user_id, plan_id, status, billing_cycle, amount, trial_ends_at, current_period_start, current_period_end, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$user_id, $plan_id, $status, $billing_cycle, $amount, $trial_ends_at, $current_period_start, $current_period_end]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'Failed to create subscription'
        ];
    }
    
    $subscription_id = db_last_insert_id();
    
    // Update user's current subscription
    db_execute(
        "UPDATE users SET current_subscription_id = ?, subscription_status = ? WHERE id = ?",
        [$subscription_id, 'active', $user_id]
    );
    
    // Log activity
    log_activity('subscription_created', "Subscription created for plan: {$plan['name']}", $user_id);
    
    return [
        'status' => true,
        'subscription_id' => $subscription_id,
        'message' => 'Subscription created successfully'
    ];
}

/**
 * Cancel user subscription
 *
 * @param int $subscription_id Subscription ID
 * @param bool $immediate Cancel immediately or at period end
 * @return array Status and message
 */
function cancel_subscription($subscription_id, $immediate = false) {
    // Get subscription details
    $subscription = db_query_row(
        "SELECT * FROM user_subscriptions WHERE id = ?",
        [$subscription_id]
    );
    
    if (!$subscription) {
        return [
            'status' => false,
            'message' => 'Subscription not found'
        ];
    }
    
    // Create payment record
    $result = db_execute(
        "INSERT INTO subscription_payments (subscription_id, user_id, amount, payment_method, transaction_id, status, payment_date, created_at)
         VALUES (?, ?, ?, ?, ?, 'completed', NOW(), NOW())",
        [$subscription_id, $subscription['user_id'], $amount, $payment_method, $transaction_id]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'Failed to record payment'
        ];
    }
    
    // Update subscription period
    $billing_cycle = $subscription['billing_cycle'];
    $new_period_start = $subscription['current_period_end'];
    
    if ($billing_cycle === 'yearly') {
        $new_period_end = date('Y-m-d H:i:s', strtotime($new_period_start . ' +1 year'));
    } else {
        $new_period_end = date('Y-m-d H:i:s', strtotime($new_period_start . ' +1 month'));
    }
    
    // Update subscription
    db_execute(
        "UPDATE user_subscriptions SET 
         status = 'active',
         current_period_start = ?,
         current_period_end = ?,
         trial_ends_at = NULL,
         updated_at = NOW()
         WHERE id = ?",
        [$new_period_start, $new_period_end, $subscription_id]
    );
    
    // Update user status
    db_execute(
        "UPDATE users SET subscription_status = 'active' WHERE id = ?",
        [$subscription['user_id']]
    );
    
    // Log activity
    log_activity('subscription_payment', "Payment processed: $amount", $subscription['user_id']);
    
    return [
        'status' => true,
        'message' => 'Payment processed successfully'
    ];
}

/**
 * Get subscription payment history
 *
 * @param int $user_id User ID
 * @param int $limit Number of records to fetch
 * @return array Payment history
 */
function get_subscription_payment_history($user_id, $limit = 10) {
    return db_query(
        "SELECT sp.*, us.billing_cycle, pl.name as plan_name
         FROM subscription_payments sp
         JOIN user_subscriptions us ON sp.subscription_id = us.id
         JOIN subscription_plans pl ON us.plan_id = pl.id
         WHERE sp.user_id = ?
         ORDER BY sp.created_at DESC
         LIMIT ?",
        [$user_id, $limit]
    );
}

/**
 * Check for expired subscriptions and update status
 *
 * @return int Number of expired subscriptions processed
 */
function process_expired_subscriptions() {
    // Get expired subscriptions
    $expired_subscriptions = db_query(
        "SELECT id, user_id FROM user_subscriptions 
         WHERE status IN ('active', 'trial') 
         AND current_period_end < NOW()
         AND (ends_at IS NULL OR ends_at < NOW())"
    );
    
    $processed = 0;
    
    foreach ($expired_subscriptions as $subscription) {
        // Update subscription status
        db_execute(
            "UPDATE user_subscriptions SET status = 'expired' WHERE id = ?",
            [$subscription['id']]
        );
        
        // Update user status
        db_execute(
            "UPDATE users SET subscription_status = 'expired', current_subscription_id = NULL WHERE id = ?",
            [$subscription['user_id']]
        );
        
        // Log activity
        log_activity('subscription_expired', 'Subscription expired', $subscription['user_id']);
        
        $processed++;
    }
    
    return $processed;
}

/**
 * Get subscription analytics for admin
 *
 * @return array Analytics data
 */
function get_subscription_analytics() {
    $analytics = [];
    
    // Total active subscriptions
    $analytics['total_active'] = db_query_row(
        "SELECT COUNT(*) as count FROM user_subscriptions WHERE status IN ('active', 'trial')"
    )['count'] ?? 0;
    
    // Total revenue this month
    $analytics['monthly_revenue'] = db_query_row(
        "SELECT SUM(amount) as total FROM subscription_payments 
         WHERE status = 'completed' 
         AND DATE_FORMAT(payment_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')"
    )['total'] ?? 0;
    
    // Total revenue this year
    $analytics['yearly_revenue'] = db_query_row(
        "SELECT SUM(amount) as total FROM subscription_payments 
         WHERE status = 'completed' 
         AND YEAR(payment_date) = YEAR(NOW())"
    )['total'] ?? 0;
    
    // Subscriptions by plan
    $analytics['by_plan'] = db_query(
        "SELECT sp.name, sp.plan_type, COUNT(us.id) as count
         FROM subscription_plans sp
         LEFT JOIN user_subscriptions us ON sp.id = us.plan_id AND us.status IN ('active', 'trial')
         WHERE sp.status = 'active'
         GROUP BY sp.id
         ORDER BY sp.sort_order"
    );
    
    // Recent subscriptions
    $analytics['recent_subscriptions'] = db_query(
        "SELECT us.*, u.name as user_name, sp.name as plan_name
         FROM user_subscriptions us
         JOIN users u ON us.user_id = u.id
         JOIN subscription_plans sp ON us.plan_id = sp.id
         ORDER BY us.created_at DESC
         LIMIT 10"
    );
    
    // Expiring soon (next 7 days)
    $analytics['expiring_soon'] = db_query(
        "SELECT us.*, u.name as user_name, sp.name as plan_name
         FROM user_subscriptions us
         JOIN users u ON us.user_id = u.id
         JOIN subscription_plans sp ON us.plan_id = sp.id
         WHERE us.status IN ('active', 'trial')
         AND us.current_period_end BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
         ORDER BY us.current_period_end ASC"
    );
    
    return $analytics;
}

/**
 * Send subscription renewal reminder
 *
 * @param int $user_id User ID
 * @param array $subscription Subscription data
 * @return bool True if email sent successfully
 */
function send_subscription_renewal_reminder($user_id, $subscription) {
    $user = get_user_profile($user_id);
    
    if (!$user) {
        return false;
    }
    
    $days_until_renewal = ceil((strtotime($subscription['current_period_end']) - time()) / 86400);
    
    $subject = SITE_NAME . ' - Subscription Renewal Reminder';
    $message = "Hello {$user['name']},\n\n"
             . "This is a reminder that your {$subscription['plan_name']} subscription will renew in {$days_until_renewal} days.\n\n"
             . "Renewal Date: " . date('F j, Y', strtotime($subscription['current_period_end'])) . "\n"
             . "Amount: " . format_price($subscription['amount']) . "\n\n"
             . "If you need to make any changes to your subscription, please visit your account settings.\n\n"
             . "Regards,\n" . SITE_NAME . " Team";
    
    return send_email($user['email'], $subject, $message);
}

/**
 * Get subscription plan comparison data
 *
 * @return array Formatted plan comparison data
 */
function get_plan_comparison_data() {
    $plans = get_subscription_plans();
    $comparison = [];
    
    foreach ($plans as $plan) {
        $features = json_decode($plan['features'], true) ?? [];
        
        $comparison[] = [
            'id' => $plan['id'],
            'name' => $plan['name'],
            'slug' => $plan['slug'],
            'description' => $plan['description'],
            'plan_type' => $plan['plan_type'],
            'price_monthly' => $plan['price_monthly'],
            'price_yearly' => $plan['price_yearly'],
            'trial_days' => $plan['trial_days'],
            'features' => $features,
            'max_users' => $plan['max_users'],
            'max_storage_gb' => $plan['max_storage_gb'],
            'max_projects' => $plan['max_projects'],
            'priority_support' => $plan['priority_support']
        ];
    }
    
    return $comparison;
}

/**
 * Check subscription limits for specific actions
 *
 * @param string $action Action to check (users, storage, projects, etc.)
 * @param int $user_id User ID
 * @return array Status and limit information
 */
function check_subscription_limits($action, $user_id = null) {
    if ($user_id === null) {
        if (!is_logged_in()) {
            return [
                'allowed' => false,
                'message' => 'You must be logged in'
            ];
        }
        $user_id = $_SESSION['user_id'];
    }
    
    $subscription = get_user_subscription($user_id);
    
    if (!$subscription) {
        return [
            'allowed' => false,
            'message' => 'No active subscription found'
        ];
    }
    
    $plan = get_subscription_plan($subscription['plan_id']);
    
    switch ($action) {
        case 'add_user':
            if ($plan['max_users'] == -1) {
                return ['allowed' => true, 'message' => 'Unlimited users allowed'];
            }
            
            $current_users = db_query_row(
                "SELECT COUNT(*) as count FROM users WHERE id != ?",
                [$user_id]
            )['count'] ?? 0;
            
            if ($current_users >= $plan['max_users']) {
                return [
                    'allowed' => false,
                    'message' => "User limit reached ({$plan['max_users']} users max)"
                ];
            }
            break;
            
        case 'add_project':
            if ($plan['max_projects'] == -1) {
                return ['allowed' => true, 'message' => 'Unlimited projects allowed'];
            }
            
            // You would implement project counting logic here
            // For now, returning allowed
            break;
            
        case 'upload_file':
            if ($plan['max_storage_gb'] == -1) {
                return ['allowed' => true, 'message' => 'Unlimited storage allowed'];
            }
            
            // You would implement storage usage calculation here
            // For now, returning allowed
            break;
    }
    
    return ['allowed' => true, 'message' => 'Action allowed'];
}];
    }
    
    if ($subscription['status'] === 'cancelled') {
        return [
            'status' => false,
            'message' => 'Subscription is already cancelled'
        ];
    }
    
    $ends_at = $immediate ? date('Y-m-d H:i:s') : $subscription['current_period_end'];
    
    // Update subscription
    $result = db_execute(
        "UPDATE user_subscriptions SET status = 'cancelled', cancelled_at = NOW(), ends_at = ? WHERE id = ?",
        [$ends_at, $subscription_id]
    );
    
    if (!$result) {
        return [
            'status' => false,
            'message' => 'Failed to cancel subscription'
        ];
    }
    
    // If immediate cancellation, update user status
    if ($immediate) {
        db_execute(
            "UPDATE users SET subscription_status = 'cancelled' WHERE id = ?",
            [$subscription['user_id']]
        );
    }
    
    // Log activity
    log_activity('subscription_cancelled', "Subscription cancelled" . ($immediate ? ' immediately' : ' at period end'), $subscription['user_id']);
    
    return [
        'status' => true,
        'message' => 'Subscription cancelled successfully'
    ];
}

/**
 * Upgrade/Downgrade subscription
 *
 * @param int $subscription_id Current subscription ID
 * @param int $new_plan_id New plan ID
 * @param string $billing_cycle Billing cycle
 * @return array Status and message
 */
function change_subscription_plan($subscription_id, $new_plan_id, $billing_cycle = null) {
    // Get current subscription
    $current_subscription = db_query_row(
        "SELECT us.*, sp.name as current_plan_name 
         FROM user_subscriptions us
         JOIN subscription_plans sp ON us.plan_id = sp.id
         WHERE us.id = ?",
        [$subscription_id]
    );
    
    if (!$current_subscription) {
        return [
            'status' => false,
            'message' => 'Current subscription not found'
        ];
    }
    
    // Get new plan
    $new_plan = get_subscription_plan($new_plan_id);
    
    if (!$new_plan) {
        return [
            'status' => false,
            'message' => 'New plan not found'
        ];
    }
    
    if ($billing_cycle === null) {
        $billing_cycle = $current_subscription['billing_cycle'];
    }
    
    // Calculate new amount
    $new_amount = $billing_cycle === 'yearly' ? $new_plan['price_yearly'] : $new_plan['price_monthly'];
    
    // Cancel current subscription at period end
    cancel_subscription($subscription_id, false);
    
    // Create new subscription
    $result = create_subscription($current_subscription['user_id'], $new_plan_id, $billing_cycle, false);
    
    if (!$result['status']) {
        return $result;
    }
    
    // Log activity
    log_activity('subscription_changed', "Subscription changed from {$current_subscription['current_plan_name']} to {$new_plan['name']}", $current_subscription['user_id']);
    
    return [
        'status' => true,
        'message' => 'Subscription plan changed successfully'
    ];
}

/**
 * Get subscription usage for a user
 *
 * @param int $user_id User ID
 * @param string $metric_name Metric name (optional)
 * @return array Usage data
 */
function get_subscription_usage($user_id, $metric_name = null) {
    $query = "SELECT * FROM subscription_usage WHERE user_id = ?";
    $params = [$user_id];
    
    if ($metric_name !== null) {
        $query .= " AND metric_name = ?";
        $params[] = $metric_name;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    return db_query($query, $params);
}

/**
 * Update subscription usage
 *
 * @param int $user_id User ID
 * @param string $metric_name Metric name
 * @param int $usage_amount Usage amount to add
 * @return array Status and message
 */
function update_subscription_usage($user_id, $metric_name, $usage_amount = 1) {
    $subscription = get_user_subscription($user_id);
    
    if (!$subscription) {
        return [
            'status' => false,
            'message' => 'No active subscription found'
        ];
    }
    
    // Get current period dates
    $period_start = date('Y-m-01 00:00:00'); // Start of current month
    $period_end = date('Y-m-t 23:59:59'); // End of current month
    
    // Check if usage record exists for current period
    $existing_usage = db_query_row(
        "SELECT * FROM subscription_usage WHERE user_id = ? AND subscription_id = ? AND metric_name = ? AND period_start = ?",
        [$user_id, $subscription['id'], $metric_name, $period_start]
    );
    
    if ($existing_usage) {
        // Update existing usage
        $new_usage = $existing_usage['current_usage'] + $usage_amount;
        
        $result = db_execute(
            "UPDATE subscription_usage SET current_usage = ?, updated_at = NOW() WHERE id = ?",
            [$new_usage, $existing_usage['id']]
        );
    } else {
        // Create new usage record
        $result = db_execute(
            "INSERT INTO subscription_usage (user_id, subscription_id, metric_name, current_usage, period_start, period_end, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$user_id, $subscription['id'], $metric_name, $usage_amount, $period_start, $period_end]
        );
    }
    
    return [
        'status' => (bool)$result,
        'message' => $result ? 'Usage updated successfully' : 'Failed to update usage'
    ];
}

/**
 * Check if user has reached usage limit
 *
 * @param int $user_id User ID
 * @param string $metric_name Metric name
 * @param int $limit_value Limit to check against
 * @return bool True if limit reached
 */
function has_reached_usage_limit($user_id, $metric_name, $limit_value) {
    if ($limit_value === -1) {
        return false; // Unlimited
    }
    
    $period_start = date('Y-m-01 00:00:00');
    
    $usage = db_query_row(
        "SELECT current_usage FROM subscription_usage WHERE user_id = ? AND metric_name = ? AND period_start = ?",
        [$user_id, $metric_name, $period_start]
    );
    
    $current_usage = $usage['current_usage'] ?? 0;
    
    return $current_usage >= $limit_value;
}

/**
 * Process subscription payment
 *
 * @param int $subscription_id Subscription ID
 * @param float $amount Payment amount
 * @param string $payment_method Payment method
 * @param string $transaction_id Transaction ID
 * @return array Status and message
 */
function process_subscription_payment($subscription_id, $amount, $payment_method, $transaction_id) {
    $subscription = db_query_row(
        "SELECT * FROM user_subscriptions WHERE id = ?",
        [$subscription_id]
    );
    
    if (!$subscription) {
        return [
            'status' => false,
            'message' => 'Subscription not found'