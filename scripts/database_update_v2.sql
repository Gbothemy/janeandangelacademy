-- Update Football Academy Database - Version 2
USE football_academy;

-- Add missing columns to players table
ALTER TABLE players 
ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL AFTER bio,
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL AFTER email,
ADD COLUMN IF NOT EXISTS dob DATE NULL AFTER phone,
ADD COLUMN IF NOT EXISTS parent_name VARCHAR(100) NULL AFTER dob,
ADD COLUMN IF NOT EXISTS parent_email VARCHAR(100) NULL AFTER parent_name,
ADD COLUMN IF NOT EXISTS parent_phone VARCHAR(20) NULL AFTER parent_email,
ADD COLUMN IF NOT EXISTS address TEXT NULL AFTER parent_phone,
ADD COLUMN IF NOT EXISTS medical_info TEXT NULL AFTER address,
ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) NULL AFTER medical_info,
ADD COLUMN IF NOT EXISTS registration_date DATETIME NULL AFTER profile_image;

-- Update payments table structure
ALTER TABLE payments
ADD COLUMN IF NOT EXISTS player_id INT NULL AFTER id,
ADD COLUMN IF NOT EXISTS description VARCHAR(255) NULL AFTER payment_type,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL AFTER description,
ADD CONSTRAINT fk_payments_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL;

-- Create testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    rating TINYINT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    location VARCHAR(255) NULL,
    is_public BOOLEAN DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Create subscribers table for newsletter
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NULL,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    confirmation_token VARCHAR(64) NULL,
    confirmed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample testimonials
INSERT INTO testimonials (name, role, content, is_featured, rating) VALUES
('John Smith', 'Parent of U15 Player', 'Elite Football Academy has transformed my son\'s game. The coaches are exceptional and the facilities are top-notch. Highly recommended!', TRUE, 5),
('Michael Johnson', 'U17 Team Player', 'Since joining the academy, my confidence and skills have improved dramatically. The coaches push us to be our best every day.', TRUE, 5),
('Sarah Williams', 'Parent of U13 Player', 'The holistic approach to player development at Elite Football Academy is what sets them apart. They focus on creating well-rounded athletes.', TRUE, 4);

-- Insert sample events
INSERT INTO events (title, description, start_date, end_date, location, is_public) VALUES
('Summer Training Camp', 'Intensive training camp for all academy players. Focus on technical skills and tactical understanding.', '2023-07-10 09:00:00', '2023-07-14 16:00:00', 'Main Academy Grounds', TRUE),
('U17 Tournament', 'Regional tournament featuring top academies from the area. Great opportunity for players to showcase their talents.', '2023-08-05 10:00:00', '2023-08-06 18:00:00', 'City Sports Complex', TRUE),
('Parent Information Session', 'Information session for parents of new and prospective players. Learn about our programs, philosophy, and pathways.', '2023-06-25 19:00:00', '2023-06-25 21:00:00', 'Academy Meeting Room', TRUE);
