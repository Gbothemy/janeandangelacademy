-- Update Football Academy Database
USE football_academy;

-- Add media table for managing uploaded files
CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Add roles to admin_users table
ALTER TABLE admin_users 
ADD COLUMN IF NOT EXISTS role ENUM('admin', 'editor', 'viewer') DEFAULT 'editor' AFTER email;

-- Add profile fields to admin_users table
ALTER TABLE admin_users 
ADD COLUMN IF NOT EXISTS full_name VARCHAR(100) AFTER role,
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER full_name,
ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL AFTER phone;

-- Add status field to players table
ALTER TABLE players
ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'injured') DEFAULT 'active' AFTER bio;

-- Add more fields to payments table
ALTER TABLE payments
ADD COLUMN IF NOT EXISTS payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'other') AFTER payment_type,
ADD COLUMN IF NOT EXISTS receipt_number VARCHAR(50) AFTER payment_method;

-- Add settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    setting_group VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) VALUES
('site_name', 'Elite Football Academy', 'general'),
('site_email', 'info@elitefootballacademy.com', 'general'),
('site_phone', '(555) 123-4567', 'general'),
('site_address', '123 Football Drive, Sports City, SC 12345', 'general'),
('training_hours', 'Monday - Friday: 4:00 PM - 8:00 PM\nSaturday: 9:00 AM - 5:00 PM\nSunday: 10:00 AM - 4:00 PM', 'general');
