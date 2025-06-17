-- Create admin_logs table for tracking admin actions
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add created_by column to admin_users table if it doesn't exist
ALTER TABLE `admin_users` 
ADD COLUMN IF NOT EXISTS `created_by` int(11) DEFAULT NULL AFTER `phone`,
ADD COLUMN IF NOT EXISTS `last_login` datetime DEFAULT NULL AFTER `created_by`,
ADD COLUMN IF NOT EXISTS `role` varchar(20) NOT NULL DEFAULT 'editor' AFTER `last_login`;

-- Create payments table if it doesn't exist
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `player_id` (`player_id`),
  KEY `payment_date` (`payment_date`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create media table if it doesn't exist
CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `file_type` (`file_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create settings table if it doesn't exist
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `setting_group` (`setting_group`),
  KEY `is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings if they don't exist
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `is_public`) VALUES
('site_name', 'Elite Football Academy', 'general', 1),
('site_description', 'Professional football training for young athletes', 'general', 1),
('contact_email', 'info@elitefootballacademy.com', 'contact', 1),
('contact_phone', '+1 (555) 123-4567', 'contact', 1),
('contact_address', '123 Sports Avenue, City, Country', 'contact', 1),
('social_facebook', 'https://facebook.com/elitefootballacademy', 'social', 1),
('social_twitter', 'https://twitter.com/elitefootball', 'social', 1),
('social_instagram', 'https://instagram.com/elitefootballacademy', 'social', 1),
('registration_open', '1', 'registration', 1),
('payment_methods', 'cash,credit_card,bank_transfer,paypal', 'payment', 0),
('smtp_host', '', 'email', 0),
('smtp_port', '587', 'email', 0),
('smtp_username', '', 'email', 0),
('smtp_password', '', 'email', 0),
('smtp_encryption', 'tls', 'email', 0);
