-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 18, 2025 at 09:04 AM
-- Server version: 9.1.0
-- PHP Version: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farsi`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `CreateDailyBackup`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateDailyBackup` ()   BEGIN
    DECLARE backup_date VARCHAR(20);
    SET backup_date = DATE_FORMAT(NOW(), '%Y%m%d_%H%i%s');
    
    -- اینجا می‌توانید کد backup را اضافه کنید
    INSERT INTO activity_logs (action, detail, created_at) 
    VALUES ('system_backup', CONCAT('Daily backup created: ', backup_date), NOW());
END$$

DROP PROCEDURE IF EXISTS `GetUserOrderStats`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserOrderStats` (IN `p_user_id` INT)   BEGIN
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(total_amount) as total_spent,
        AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE user_id = p_user_id;
END$$

DROP PROCEDURE IF EXISTS `UpdateProductStock`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateProductStock` (IN `p_product_id` INT, IN `p_quantity` INT, IN `p_operation` ENUM('increase','decrease'))   BEGIN
    DECLARE current_stock INT DEFAULT 0;
    
    SELECT stock INTO current_stock FROM products WHERE id = p_product_id;
    
    IF p_operation = 'increase' THEN
        UPDATE products 
        SET stock = stock + p_quantity,
            updated_at = NOW()
        WHERE id = p_product_id;
    ELSEIF p_operation = 'decrease' THEN
        IF current_stock >= p_quantity THEN
            UPDATE products 
            SET stock = stock - p_quantity,
                status = CASE WHEN (stock - p_quantity) <= 0 THEN 'out_of_stock' ELSE status END,
                updated_at = NOW()
            WHERE id = p_product_id;
        END IF;
    END IF;
END$$

--
-- Functions
--
DROP FUNCTION IF EXISTS `CalculateShippingCost`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateShippingCost` (`p_total_amount` DECIMAL(10,2)) RETURNS DECIMAL(10,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE shipping_cost DECIMAL(10,2) DEFAULT 0.00;
    DECLARE free_shipping_threshold DECIMAL(10,2) DEFAULT 50.00;
    DECLARE base_shipping_cost DECIMAL(10,2) DEFAULT 5.00;
    
    -- دریافت تنظیمات از جدول settings
    SELECT CAST(value AS DECIMAL(10,2)) INTO free_shipping_threshold 
    FROM settings WHERE `key` = 'free_shipping_threshold' LIMIT 1;
    
    SELECT CAST(value AS DECIMAL(10,2)) INTO base_shipping_cost 
    FROM settings WHERE `key` = 'shipping_cost' LIMIT 1;
    
    IF p_total_amount >= free_shipping_threshold THEN
        SET shipping_cost = 0.00;
    ELSE
        SET shipping_cost = base_shipping_cost;
    END IF;
    
    RETURN shipping_cost;
END$$

DROP FUNCTION IF EXISTS `GenerateOrderNumber`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `GenerateOrderNumber` () RETURNS VARCHAR(50) CHARSET utf8mb4 DETERMINISTIC READS SQL DATA BEGIN
    DECLARE order_count INT DEFAULT 0;
    DECLARE order_number VARCHAR(50);
    
    SELECT COUNT(*) INTO order_count FROM orders;
    SET order_number = CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(order_count + 1, 6, '0'));
    
    RETURN order_number;
END$$

DROP FUNCTION IF EXISTS `GetProductAverageRating`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `GetProductAverageRating` (`p_product_id` INT) RETURNS DECIMAL(3,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE avg_rating DECIMAL(3,2) DEFAULT 0.00;
    
    SELECT COALESCE(AVG(rating), 0.00) 
    INTO avg_rating
    FROM product_reviews 
    WHERE product_id = p_product_id AND status = 'approved';
    
    RETURN avg_rating;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'User logged in', '::1', NULL, '2025-06-15 14:03:08'),
(2, 1, 'logout', 'User logged out', '::1', NULL, '2025-06-15 14:03:49'),
(3, 2, 'login', 'User logged in', '::1', NULL, '2025-06-15 14:04:03'),
(4, 2, 'profile_update', 'Profile updated', '::1', NULL, '2025-06-15 14:04:24'),
(5, 2, 'logout', 'User logged out', '::1', NULL, '2025-06-15 14:05:11'),
(6, 7, 'email_verified', 'Email verified successfully', '::1', NULL, '2025-06-15 17:40:05'),
(7, 7, 'login', 'User logged in', '::1', NULL, '2025-06-15 17:40:08'),
(8, 7, 'logout', 'User logged out', '::1', NULL, '2025-06-17 18:40:21'),
(9, 7, 'login', 'User logged in', NULL, NULL, '2025-06-17 19:29:10'),
(10, 7, 'login', 'User logged in', '::1', NULL, '2025-06-17 19:29:10');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('published','draft','private') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `meta_title`, `meta_description`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'About Us', 'about', '<h2>About Our Company</h2><p>We are a leading e-commerce platform...</p>', NULL, NULL, 'published', 0, '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(2, 'Contact Us', 'contact', '<h2>Get in Touch</h2><p>We would love to hear from you...</p>', NULL, NULL, 'published', 0, '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(3, 'Privacy Policy', 'privacy', '<h2>Privacy Policy</h2><p>Your privacy is important to us...</p>', NULL, NULL, 'published', 0, '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(4, 'Terms of Service', 'terms', '<h2>Terms of Service</h2><p>By using our service, you agree to...</p>', NULL, NULL, 'published', 0, '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(5, 'Refund Policy', 'refund', '<h2>Refund Policy</h2><p>We offer refunds under certain conditions...</p>', NULL, NULL, 'published', 0, '2025-06-15 14:01:45', '2025-06-15 14:01:45');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(2, 7, '142fd46b32e14810b3efc3db87d4a044', '2025-07-17 19:29:10', '2025-06-17 19:29:10');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'admin', 'Administrator with full access', '2025-06-15 14:01:44'),
(2, 'user', 'Regular customer user', '2025-06-15 14:01:44');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` enum('string','integer','boolean','json','text') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `group` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `group` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `group`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'PHP Shop', 'string', 'general', 'Website name', '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(2, 'site_description', 'Your one-stop shop for all your needs', 'text', 'general', 'Website description', '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(3, 'site_email', 'info@example.com', 'string', 'general', 'Website contact email', '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(4, 'currency', 'USD', 'string', 'general', 'Default currency', '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(5, 'tax_rate', '0.08', 'string', 'general', 'Default tax rate', '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(6, 'shipping_cost', '5.00', 'string', 'shipping', 'Default shipping cost', '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(7, 'free_shipping_threshold', '50.00', 'string', 'shipping', 'Free shipping minimum amount', '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(8, 'items_per_page', '20', 'integer', 'general', 'Items per page for pagination', '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(9, 'allow_guest_checkout', '1', 'boolean', 'checkout', 'Allow guest checkout', '2025-06-15 14:01:45', '2025-06-15 14:01:45'),
(10, 'require_email_verification', '1', 'boolean', 'users', 'Require email verification for new users', '2025-06-15 14:01:45', '2025-06-15 14:01:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` int NOT NULL DEFAULT '2',
  `status` enum('active','inactive','banned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `verify_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `current_subscription_id` int DEFAULT NULL,
  `subscription_status` enum('none','active','expired','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `status` (`status`),
  KEY `verify_token` (`verify_token`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `profile_image`, `role_id`, `status`, `verify_token`, `verified_at`, `last_login`, `created_at`, `updated_at`, `current_subscription_id`, `subscription_status`) VALUES
(1, 'Administrator', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, 'active', NULL, '2025-06-15 14:01:44', '2025-06-15 14:03:08', '2025-06-15 14:01:44', '2025-06-15 14:03:08', NULL, 'none'),
(2, 'qweqweqwe', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', '', NULL, 2, 'active', NULL, '2025-06-15 14:01:46', '2025-06-15 14:04:02', '2025-06-15 14:01:46', '2025-06-15 14:04:24', NULL, 'none'),
(7, 'miad house', 'miadaleali@gmail.com', '$2y$12$kAUDkBvcIFbYRAtLo1C2l.KaCe4D9CWjk5WOU5nYHClvOjPuVy8DG', NULL, NULL, NULL, 2, 'active', NULL, '2025-06-15 17:40:05', '2025-06-17 19:29:10', '2025-06-15 17:39:51', '2025-06-17 19:29:10', NULL, 'none'),
(8, 'miadactive@gmail.com', 'miadactive@gmail.com', '$2y$12$/c2evH9T4didBND6/DzSleWeXl7ROS5x4xGp.zxvgT.rGOcyIktOW', NULL, NULL, NULL, 2, 'active', 'fc46e94f6ec5625378628cb6a5d4d52a', NULL, NULL, '2025-06-17 18:54:50', '2025-06-17 18:54:50', NULL, 'none');

--
-- Triggers `users`
--
DROP TRIGGER IF EXISTS `log_user_activity`;
DELIMITER $$
CREATE TRIGGER `log_user_activity` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.last_login != OLD.last_login THEN
        INSERT INTO activity_logs (user_id, action, detail, created_at)
        VALUES (NEW.id, 'login', 'User logged in', NOW());
    END IF;
END
$$
DELIMITER ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
