<?php
if (!isset($settings)) {
    $settings = getSiteSettings();
}
?>
<!-- Footer -->
<footer class="site-footer dark-mode">
    <div class="container">
        <div class="footer-grid">
            <!-- About -->
            <div class="footer-col">
                <h3>About Us</h3>
                <p>Jane & Angel Football Academy is dedicated to developing young football talent through professional
                    training
                    and competitive opportunities.</p>
                <ul class="contact-list">
                    <li><i class="icon bounce">📍</i>
                        <?php echo htmlspecialchars($settings['contact_address'] ?? '123 Sports Avenue, City, Country'); ?>
                    </li>
                    <li><i class="icon bounce">📞</i>
                        <?php echo htmlspecialchars($settings['contact_phone'] ?? '+1 (555) 123-4567'); ?></li>
                    <li><i class="icon bounce">✉️</i>
                        <?php echo htmlspecialchars($settings['contact_email'] ?? 'info@elitefootballacademy.com'); ?>
                    </li>
                </ul>
            </div>

            <!-- Links -->
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="players.php">Players</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <?php if (!empty($settings['registration_open'])): ?>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Social & Newsletter -->
            <div class="footer-col">
                <h3>Connect With Us</h3>
                <div class="social-icons">
                    <?php if (!empty($settings['social_facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_facebook']); ?>" target="_blank"
                            aria-label="Facebook"><i class="icon bounce">📘</i></a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_twitter']); ?>" target="_blank"
                            aria-label="Twitter"><i class="icon bounce">🐦</i></a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_instagram']); ?>" target="_blank"
                            aria-label="Instagram"><i class="icon bounce">📸</i></a>
                    <?php endif; ?>
                </div>

                <div class="newsletter">
                    <h4>Subscribe to Our Newsletter</h4>
                    <form action="subscribe.php" method="post" class="newsletter-form">
                        <input type="email" name="email" placeholder="Your email address" required>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?>
                <?php echo htmlspecialchars($settings['site_name'] ?? 'Jane & Angel Football Academy'); ?>. All rights
                reserved.
            </p>
            <nav class="footer-nav">
                <a href="privacy-policy.php">Privacy Policy</a>
                <a href="terms-of-service.php">Terms of Service</a>
                <a href="admin/login.php">Admin</a>
            </nav>
        </div>
    </div>
</footer>

<!-- Scroll to top -->
<button class="scroll-to-top" aria-label="Scroll to top">↑</button>

<script src="assets/js/main.js"></script>
</body>
</html>