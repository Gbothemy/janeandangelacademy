-- Insert sample data
USE football_academy;

-- Insert admin user (password: admin123)
INSERT INTO admin_users (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@footballacademy.com');

-- Insert sample players
INSERT INTO players (name, age, position, team, bio) VALUES 
('Marcus Johnson', 16, 'Forward', 'U17 Team', 'Talented striker with excellent finishing skills and pace.'),
('David Rodriguez', 15, 'Midfielder', 'U16 Team', 'Creative midfielder with great vision and passing ability.'),
('Alex Thompson', 17, 'Defender', 'U18 Team', 'Solid defender with strong aerial ability and leadership qualities.'),
('James Wilson', 14, 'Goalkeeper', 'U15 Team', 'Promising young goalkeeper with quick reflexes.');

-- Insert sample training schedules
INSERT INTO training_schedules (team, day_of_week, start_time, end_time, location, coach) VALUES 
('U15 Team', 'Monday', '16:00:00', '18:00:00', 'Main Field', 'Coach Martinez'),
('U15 Team', 'Wednesday', '16:00:00', '18:00:00', 'Main Field', 'Coach Martinez'),
('U16 Team', 'Tuesday', '16:30:00', '18:30:00', 'Field 2', 'Coach Johnson'),
('U16 Team', 'Thursday', '16:30:00', '18:30:00', 'Field 2', 'Coach Johnson'),
('U17 Team', 'Monday', '17:00:00', '19:00:00', 'Field 3', 'Coach Williams'),
('U17 Team', 'Friday', '17:00:00', '19:00:00', 'Field 3', 'Coach Williams'),
('U18 Team', 'Tuesday', '18:00:00', '20:00:00', 'Main Field', 'Coach Davis'),
('U18 Team', 'Saturday', '10:00:00', '12:00:00', 'Main Field', 'Coach Davis');

-- Insert sample news
INSERT INTO news (title, content, published) VALUES 
('Welcome to Our New Season!', 'We are excited to announce the start of our new football season. Registration is now open for all age groups.', TRUE),
('Tournament Victory!', 'Congratulations to our U17 team for winning the Regional Championship! Great teamwork and dedication.', TRUE);
