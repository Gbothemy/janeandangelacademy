<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();
    
    // Get all training schedules grouped by team
    $stmt = $pdo->query("SELECT * FROM training_schedules ORDER BY team, FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time");
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group schedules by team
    $schedules_by_team = [];
    foreach ($schedules as $schedule) {
        $schedules_by_team[$schedule['team']][] = $schedule;
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Schedule - Elite Football Academy</title>
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
                <li><a href="programs.php">Programs</a></li>
                <li><a href="players.php">Players</a></li>
                <li><a href="schedule.php" class="active">Schedule</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" style="height: 60vh;">
        <div class="hero-content">
            <h1>Training Schedule</h1>
            <p>Stay up to date with all training sessions and team activities</p>
        </div>
    </section>

    <!-- Schedule Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Weekly Training Schedule</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($schedules_by_team)): ?>
                <?php foreach ($schedules_by_team as $team => $team_schedules): ?>
                    <div class="admin-card" style="margin-bottom: 2rem;">
                        <h3 style="color: var(--bronze-brown); margin-bottom: 1rem;"><?php echo htmlspecialchars($team); ?></h3>
                        
                        <div class="schedule-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                            <?php foreach ($team_schedules as $schedule): ?>
                                <div class="schedule-card" style="background: var(--light-lavender); padding: 1.5rem; border-radius: 8px;">
                                    <h4 style="color: var(--bronze-brown); margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($schedule['day_of_week']); ?>
                                    </h4>
                                    <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($schedule['start_time'])); ?> - <?php echo date('g:i A', strtotime($schedule['end_time'])); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($schedule['location']); ?></p>
                                    <p><strong>Coach:</strong> <?php echo htmlspecialchars($schedule['coach']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="section-subtitle">
                    <p>No training schedules available at the moment. Please check back later or contact us for more information.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Important Information -->
    <section class="section programs">
        <div class="container">
            <h2 class="section-title">Important Information</h2>
            <div class="programs-grid">
                <div class="program-card">
                    <h3>Arrival Time</h3>
                    <p>Please arrive 15 minutes before your scheduled training session to allow time for warm-up and preparation.</p>
                </div>
                <div class="program-card">
                    <h3>What to Bring</h3>
                    <p>Football boots, shin pads, water bottle, and appropriate training kit. All other equipment will be provided.</p>
                </div>
                <div class="program-card">
                    <h3>Weather Policy</h3>
                    <p>Training continues in light rain. Sessions may be moved indoors or cancelled in severe weather conditions.</p>
                </div>
                <div class="program-card">
                    <h3>Absence Policy</h3>
                    <p>Please notify your coach at least 2 hours in advance if your child cannot attend a training session.</p>
                </div>
                <div class="program-card">
                    <h3>Parent Guidelines</h3>
                    <p>Parents are welcome to watch training sessions. Please maintain a positive and supportive environment.</p>
                </div>
                <div class="program-card">
                    <h3>Contact Information</h3>
                    <p>For schedule changes or questions, contact us at (555) 123-4567 or info@elitefootballacademy.com</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Weekly Overview -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Weekly Overview</h2>
            <div class="admin-card">
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                                <th>Sunday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Create a time-based schedule view
                            $time_slots = [];
                            foreach ($schedules as $schedule) {
                                $start_time = $schedule['start_time'];
                                $time_slots[$start_time][$schedule['day_of_week']] = $schedule;
                            }
                            ksort($time_slots);
                            
                            foreach ($time_slots as $time => $day_schedules):
                            ?>
                            <tr>
                                <td><strong><?php echo date('g:i A', strtotime($time)); ?></strong></td>
                                <?php
                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                foreach ($days as $day):
                                ?>
                                <td>
                                    <?php if (isset($day_schedules[$day])): ?>
                                        <div style="font-size: 0.9rem;">
                                            <strong><?php echo htmlspecialchars($day_schedules[$day]['team']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($day_schedules[$day]['location']); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
