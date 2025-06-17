-- Complete Database Setup for Football Academy Website
-- Drop existing database and recreate to ensure clean setup

DROP DATABASE IF EXISTS football_academy;
CREATE DATABASE football_academy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE football_academy;

-- Create admin_users table first (referenced by other tables)
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
    full_name VARCHAR(100),
    phone VARCHAR(20),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    INDEX idx_username (username),
    INDEX idx_email (email),
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Create players table
CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age DATE,
    position VARCHAR(50),
    team VARCHAR(50),
    email VARCHAR(100),
    phone VARCHAR(20),
    parent_name VARCHAR(100),
    parent_email VARCHAR(100),
    parent_phone VARCHAR(20),
    address TEXT,
    medical_info TEXT,
    bio TEXT,
    profile_image VARCHAR(255),
    registration_date TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_team (team),
    INDEX idx_position (position),
    INDEX idx_status (status),
    INDEX idx_email (email)
);

-- Create contact_messages table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Create gallery table
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    category VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_created_at (created_at)
);

-- Create news table
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    image_path VARCHAR(255),
    author_id INT,
    published BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_published (published),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Create schedule table
CREATE TABLE schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(200),
    team VARCHAR(50),
    event_type ENUM('training', 'match', 'tournament', 'meeting') DEFAULT 'training',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_team (team),
    INDEX idx_event_type (event_type)
);

-- Create payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'card', 'bank_transfer', 'online') DEFAULT 'cash',
    description VARCHAR(200),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_player_id (player_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status),
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

-- Create admin_logs table
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Create subscribers table
CREATE TABLE subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100),
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    confirmation_token VARCHAR(64),
    confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Create settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    is_public BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public)
);

-- Create testimonials table
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    rating INT DEFAULT 5,
    is_featured BOOLEAN DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_featured (is_featured),
    INDEX idx_status (status)
);

-- Create events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(200),
    image_path VARCHAR(255),
    max_participants INT,
    registration_fee DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_status (status)
);


-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description, is_public) VALUES
('site_name', 'Elite Football Academy', 'Website name', 1),
('site_description', 'Professional football training for young athletes', 'Website description', 1),
('contact_email', 'info@elitefootballacademy.com', 'Contact email address', 1),
('contact_phone', '+1 (555) 123-4567', 'Contact phone number', 1),
('contact_address', '123 Football Drive, Sports City, SC 12345', 'Contact address', 1),
('registration_open', '1', 'Whether player registration is open', 0),
('social_facebook', 'https://facebook.com/elitefootballacademy', 'Facebook page URL', 1),
('social_twitter', 'https://twitter.com/elitefootball', 'Twitter profile URL', 1),
('social_instagram', 'https://instagram.com/elitefootballacademy', 'Instagram profile URL', 1);

-- Insert sample players
INSERT INTO players (name, age, position, team, email, phone, parent_name, parent_email, parent_phone, bio, status) VALUES
('John Smith', '2008-03-15', 'Forward', 'U15 Team', 'john.smith@email.com', '555-0101', 'Robert Smith', 'robert.smith@email.com', '555-0102', 'Talented young striker with excellent finishing ability.', 'active'),
('Michael Johnson', '2007-07-22', 'Midfielder', 'U16 Team', 'michael.johnson@email.com', '555-0201', 'Sarah Johnson', 'sarah.johnson@email.com', '555-0202', 'Creative midfielder with great vision and passing skills.', 'active'),
('David Wilson', '2006-11-08', 'Defender', 'U17 Team', 'david.wilson@email.com', '555-0301', 'Mark Wilson', 'mark.wilson@email.com', '555-0302', 'Solid defender with strong aerial ability.', 'active'),
('Alex Brown', '2005-05-12', 'Goalkeeper', 'U18 Team', 'alex.brown@email.com', '555-0401', 'Lisa Brown', 'lisa.brown@email.com', '555-0402', 'Reliable goalkeeper with excellent reflexes.', 'active');

-- Insert sample news
INSERT INTO news (title, content, excerpt, published, author_id) VALUES
('Season Opening Tournament', 'We are excited to announce our season opening tournament featuring all academy teams. This will be a great opportunity for players to showcase their skills and for families to see the progress made during training.', 'Season opening tournament announcement for all academy teams.', 1, 1),
('New Training Facilities', 'Elite Football Academy is proud to announce the opening of our new state-of-the-art training facilities. The new complex includes indoor training areas, fitness centers, and recovery rooms.', 'New training facilities now open with modern equipment.', 1, 1);

-- Insert sample gallery items
INSERT INTO gallery (title, description, image_path, category) VALUES
('Training Session', 'Players during intensive training session', 'assets/images/gallery/training1.jpg', 'training'),
('Match Day', 'Team celebrating victory', 'assets/images/gallery/match1.jpg', 'matches'),
('Team Photo', 'U16 Team group photo', 'assets/images/gallery/team1.jpg', 'teams'),
('Skills Training', 'Individual skills development', 'assets/images/gallery/skills1.jpg', 'training');

-- Insert sample schedule
INSERT INTO schedule (title, description, event_date, event_time, location, team, event_type) VALUES
('U15 Training', 'Regular training session for U15 team', CURDATE() + INTERVAL 1 DAY, '16:00:00', 'Main Field', 'U15 Team', 'training'),
('U16 vs Local Club', 'Friendly match against local club', CURDATE() + INTERVAL 3 DAY, '14:00:00', 'Stadium', 'U16 Team', 'match'),
('U17 Training', 'Tactical training session', CURDATE() + INTERVAL 2 DAY, '17:00:00', 'Training Ground', 'U17 Team', 'training'),
('Academy Tournament', 'Inter-academy tournament', CURDATE() + INTERVAL 7 DAY, '10:00:00', 'Sports Complex', 'All Teams', 'tournament');

-- Insert sample testimonials
INSERT INTO testimonials (name, role, content, rating, is_featured, status) VALUES
('John Smith Sr.', 'Parent of U15 Player', 'Elite Football Academy has transformed my son\'s game. The coaches are exceptional and the facilities are top-notch. Highly recommended!', 5, 1, 'active'),
('Michael Johnson', 'U17 Team Player', 'Since joining the academy, my confidence and skills have improved dramatically. The coaches push us to be our best every day.', 5, 1, 'active'),
('Sarah Williams', 'Parent of U13 Player', 'The holistic approach to player development at Elite Football Academy is what sets them apart. They focus on creating well-rounded athletes.', 5, 1, 'active');

-- Insert sample contact messages
INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES
('Jane Doe', 'jane.doe@email.com', '555-1234', 'Registration Inquiry', 'I would like to know more about registering my son for the U14 team.', 'new'),
('Tom Wilson', 'tom.wilson@email.com', '555-5678', 'Training Schedule', 'Could you please send me the training schedule for the U16 team?', 'new');

COMMIT;

SELECT 'Database setup completed successfully!' as status;
