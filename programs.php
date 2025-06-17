<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs - Elite Football Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">Elite Football Academy</a>
            <ul class="nav-menu" id="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="programs.php" class="active">Programs</a></li>
                <li><a href="players.php">Players</a></li>
                <li><a href="schedule.php">Schedule</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" style="height: 60vh;">
        <div class="hero-content">
            <h1>Training Programs</h1>
            <p>Comprehensive development programs for every age and skill level</p>
        </div>
    </section>

    <!-- Programs Overview -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Our Training Programs</h2>
            <p class="section-subtitle">We offer structured programs designed to develop players at every stage of their football journey</p>
            
            <div class="programs-grid">
                <div class="program-card">
                    <h3>Little Kickers (Ages 5-7)</h3>
                    <p><strong>Duration:</strong> 1 hour sessions</p>
                    <p><strong>Focus:</strong> Fun introduction to football, basic motor skills, coordination, and social interaction through games and activities.</p>
                    <p><strong>Schedule:</strong> Saturdays 9:00 AM - 10:00 AM</p>
                    <p><strong>Monthly Fee:</strong> $80</p>
                    <a href="contact.php" class="btn">Enroll Now</a>
                </div>
                
                <div class="program-card">
                    <h3>Youth Development (Ages 8-12)</h3>
                    <p><strong>Duration:</strong> 1.5 hour sessions</p>
                    <p><strong>Focus:</strong> Foundation skills, ball control, passing, shooting, and introduction to team tactics.</p>
                    <p><strong>Schedule:</strong> Tuesdays & Thursdays 4:30 PM - 6:00 PM</p>
                    <p><strong>Monthly Fee:</strong> $120</p>
                    <a href="contact.php" class="btn">Enroll Now</a>
                </div>
                
                <div class="program-card">
                    <h3>Academy Teams U13-U15</h3>
                    <p><strong>Duration:</strong> 2 hour sessions + matches</p>
                    <p><strong>Focus:</strong> Advanced technical skills, tactical understanding, physical development, and competitive play.</p>
                    <p><strong>Schedule:</strong> Mon, Wed, Fri 5:00 PM - 7:00 PM + Weekend matches</p>
                    <p><strong>Monthly Fee:</strong> $180</p>
                    <a href="contact.php" class="btn">Enroll Now</a>
                </div>
                
                <div class="program-card">
                    <h3>Academy Teams U16-U18</h3>
                    <p><strong>Duration:</strong> 2.5 hour sessions + matches</p>
                    <p><strong>Focus:</strong> Elite level training, match preparation, leadership development, and pathway to professional football.</p>
                    <p><strong>Schedule:</strong> Mon, Wed, Fri 6:00 PM - 8:30 PM + Weekend matches</p>
                    <p><strong>Monthly Fee:</strong> $220</p>
                    <a href="contact.php" class="btn">Enroll Now</a>
                </div>
                
                <div class="program-card">
                    <h3>Elite Performance Program</h3>
                    <p><strong>Duration:</strong> 3 hour sessions + additional training</p>
                    <p><strong>Focus:</strong> Professional-level training, scout connections, college recruitment support, and career guidance.</p>
                    <p><strong>Schedule:</strong> Daily training + specialized sessions</p>
                    <p><strong>Monthly Fee:</strong> $350</p>
                    <a href="contact.php" class="btn">Apply Now</a>
                </div>
                
                <div class="program-card">
                    <h3>Goalkeeper Specialist</h3>
                    <p><strong>Duration:</strong> 1.5 hour sessions</p>
                    <p><strong>Focus:</strong> Specialized goalkeeper training, shot stopping, distribution, positioning, and mental preparation.</p>
                    <p><strong>Schedule:</strong> Saturdays 2:00 PM - 3:30 PM</p>
                    <p><strong>Monthly Fee:</strong> $100</p>
                    <a href="contact.php" class="btn">Enroll Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Training Methodology -->
    <section class="section programs">
        <div class="container">
            <h2 class="section-title">Our Training Methodology</h2>
            <div class="about-grid">
                <div class="about-content">
                    <h3>The Four Pillars of Development</h3>
                    <div style="margin: 2rem 0;">
                        <h4 style="color: var(--bronze-brown); margin-bottom: 0.5rem;">1. Technical Skills</h4>
                        <p>Ball control, passing, shooting, dribbling, and first touch development through progressive skill-building exercises.</p>
                        
                        <h4 style="color: var(--bronze-brown); margin-bottom: 0.5rem;">2. Tactical Understanding</h4>
                        <p>Game intelligence, positioning, decision-making, and understanding of different formations and playing styles.</p>
                        
                        <h4 style="color: var(--bronze-brown); margin-bottom: 0.5rem;">3. Physical Development</h4>
                        <p>Age-appropriate fitness training, injury prevention, strength building, and athletic performance enhancement.</p>
                        
                        <h4 style="color: var(--bronze-brown); margin-bottom: 0.5rem;">4. Mental Strength</h4>
                        <p>Confidence building, resilience, teamwork, leadership skills, and sports psychology principles.</p>
                    </div>
                </div>
                <div class="about-image">
                    <img src="/placeholder.svg?height=400&width=500" alt="Training methodology">
                </div>
            </div>
        </div>
    </section>

    <!-- What's Included -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">What's Included</h2>
            <div class="programs-grid">
                <div class="program-card">
                    <h3>Professional Coaching</h3>
                    <p>All our coaches are licensed professionals with extensive playing and coaching experience at high levels.</p>
                </div>
                <div class="program-card">
                    <h3>Performance Analysis</h3>
                    <p>Video analysis sessions to help players understand their strengths and areas for improvement.</p>
                </div>
                <div class="program-card">
                    <h3>Match Opportunities</h3>
                    <p>Regular competitive matches against other academies and clubs to test skills in real game situations.</p>
                </div>
                <div class="program-card">
                    <h3>Progress Tracking</h3>
                    <p>Regular assessments and progress reports to track development and set future goals.</p>
                </div>
                <div class="program-card">
                    <h3>Equipment & Kit</h3>
                    <p>Training equipment provided during sessions, plus official academy kit for team players.</p>
                </div>
                <div class="program-card">
                    <h3>Parent Communication</h3>
                    <p>Regular updates on player progress and opportunities for parent-coach discussions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Elite Football Academy. All rights reserved.</p>
            <div class="social-links">
                <a href="#">📘</a>
                <a href="#">📷</a>
                <a href="#">🐦</a>
                <a href="#">📺</a>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const navMenu = document.getElementById('nav-menu');
            navMenu.classList.toggle('active');
        }
    </script>
    <!-- Add this before the closing </body> tag -->
    <script src="assets/js/main.js"></script>
</body>
</html>
