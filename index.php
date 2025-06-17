<?php
require_once 'config/database.php';

$page_title = 'Home';

try {
    $pdo = getConnection();

    // Get featured players (limit 4)
    $stmt = $pdo->query("SELECT * FROM players WHERE status = 'active' ORDER BY created_at DESC LIMIT 4");
    $featured_players = $stmt->fetchAll();

    // Get latest news (limit 2)
    $stmt = $pdo->query("SELECT * FROM news WHERE published = 1 ORDER BY created_at DESC LIMIT 3");
    $latest_news = $stmt->fetchAll();

    // Get some gallery images for preview (limit 6)
    $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 3");
    $gallery_preview = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Jane & Angel Football Academy</h1>
        <p>United We Stand</p>
        <div class="hero-buttons">
            <a href="programs.php" class="btn">Explore Programs</a>
            <a href="contact.php" class="btn btn-secondary">Join Today</a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">About Our Academy</h2>
        <div class="about-grid">
            <div class="about-content">
                <h3>Excellence in Football Training</h3>
                <p>Jane & Angel football Academy has been at the forefront of youth football development.
                    Our comprehensive training programs are designed to nurture talent, build character, and create
                    future football stars.</p>
                <p>We believe in holistic development that combines technical skills, tactical understanding, physical
                    fitness, and mental strength. Our experienced coaches work with players of all skill levels to help
                    them reach their full potential.</p>
                <a href="about.php" class="btn">Learn More</a>
            </div>
            <div class="about-image">
                <img src="assets/images/team picture.jpeg" alt="Football training session">
            </div>
        </div>
    </div>
</section>

<!-- Latest News Section -->
<?php if (!empty($latest_news)): ?>
    <section class="section news-section">
        <div class="container">
            <h2 class="section-title">Latest News</h2>
            <div class="news-grid">
                <?php foreach ($latest_news as $news): ?>
                    <div class="news-card">
                        <?php if (!empty($news['image_path'])): ?>
                            <div class="news-image">
                                <img src="<?php echo htmlspecialchars($news['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($news['title']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="news-content">
                            <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                            <br>
                            <p><?php echo htmlspecialchars(substr($news['content'], 0, )); ?></p>
                            <br>
                            <div class="news-meta">
                                <span class="news-date"><?php echo date('M j, Y', strtotime($news['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="section-action">
                <br>
                <a href="news.php" class="btn" style="align-items: center;">View All News</a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Featured Players -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Featured Players</h2>
        <p class="section-subtitle">Meet some of our talented academy players who represent the future of football</p>
        <div class="players-grid">
            <?php if (!empty($featured_players)): ?>
                <?php foreach ($featured_players as $player): ?>
                    <div class="player-card">
                        <div class="player-image">
                            <?php if (!empty($player['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($player['profile_image']); ?>"
                                    alt="<?php echo htmlspecialchars($player['name']); ?>">
                            <?php else: ?>
                                <div class="player-placeholder">⚽</div>
                            <?php endif; ?>
                        </div>
                        <div class="player-info">
                            <h3><?php echo htmlspecialchars($player['name']); ?></h3>
                            <p><strong>Position:</strong> <?php echo htmlspecialchars($player['position']); ?></p>
                            <p><strong>Team:</strong> <?php echo htmlspecialchars($player['team']); ?></p>
                            <?php if (!empty($player['bio'])): ?>
                                <p><?php echo htmlspecialchars(substr($player['bio'], 0, 80)) . '...'; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No players found.</p>
            <?php endif; ?>
        </div>
        <br>
        <div class="section-action">
            <a href="players.php" class="btn">View All Players</a>
        </div>
    </div>
</section>

<!-- Gallery Preview -->
<?php if (!empty($gallery_preview)): ?>
    <section class="section gallery-preview">
        <div class="container">
            <h2 class="section-title">Gallery Preview</h2>
            <div class="gallery-grid">
                <?php foreach ($gallery_preview as $image): ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($image['title']); ?>">
                        <div class="gallery-overlay">
                            <h4><?php echo htmlspecialchars($image['title']); ?></h4>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="section-action">
                <a href="gallery.php" class="btn">View Full Gallery</a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Contact Section -->
<section class="section contact">
    <div class="container">
        <h2 class="section-title">Get In Touch</h2>
        <div class="contact-grid">
            <div class="contact-info">
                <h3>Contact Information</h3>
                <p><strong>Address:</strong>
                    <?php echo htmlspecialchars($settings['contact_address'] ?? '123 Football Drive, Sports City, SC 12345'); ?>
                </p>
                <p><strong>Phone:</strong>
                    <?php echo htmlspecialchars($settings['contact_phone'] ?? '(555) 123-4567'); ?></p>
                <p><strong>Email:</strong>
                    <?php echo htmlspecialchars($settings['contact_email'] ?? 'info@elitefootballacademy.com'); ?></p>
                <p><strong>Training Hours:</strong></p>
                <p>Monday - Friday: 4:00 PM - 8:00 PM</p>
                <p>Saturday: 9:00 AM - 5:00 PM</p>
                <p>Sunday: 10:00 AM - 4:00 PM</p>

                <div class="contact-map">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d-73.9857!3d40.7484!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQ0JzU0LjIiTiA3M8KwNTknMDguNSJX!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus"
                        width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
            <form class="contact-form" action="contact_process.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <button type="submit" class="btn">Send Message</button>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>