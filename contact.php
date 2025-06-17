<?php
require_once 'config/database.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validate input
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } else {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);

            $success_message = 'Thank you for your message. We will get back to you soon!';

            // Clear form data
            $name = $email = $phone = $subject = $message = '';
        } catch (PDOException $e) {
            $error_message = 'There was an error sending your message. Please try again.';
        }
    }
}

include 'includes/header.php';
?>



<!-- Contact Section -->
<section class="section contact" style="margin-top: 80px;">
    <div class="container">
        <h2 class="section-title">Contact Us</h2>
        <p class="section-subtitle">Get in touch with us for more information about our programs or to schedule a visit
        </p>

        <!-- Display success/error messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="contact-grid">
            <div class="contact-info">
                <h3>Contact Information</h3>
                <p><strong>Address:</strong> 123 Football Drive, Sports City, SC 12345</p>
                <p><strong>Phone:</strong> (555) 123-4567</p>
                <p><strong>Email:</strong> info@elitefootballacademy.com</p>
                <p><strong>Training Hours:</strong></p>
                <p>Monday - Friday: 4:00 PM - 8:00 PM</p>
                <p>Saturday: 9:00 AM - 5:00 PM</p>
                <p>Sunday: 10:00 AM - 4:00 PM</p>

                <h3 style="margin-top: 2rem;">Facilities</h3>
                <p>• 3 Full-size grass pitches</p>
                <p>• Indoor training facility</p>
                <p>• Fitness and conditioning center</p>
                <p>• Video analysis room</p>
                <p>• Player lounge and changing rooms</p>

            </div>
            <form class="contact-form" method="POST">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name"
                        value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email"
                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone"
                        value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select a subject</option>
                        <option value="Program Inquiry" <?php echo (isset($subject) && $subject == 'Program Inquiry') ? 'selected' : ''; ?>>Program Inquiry</option>
                        <option value="Registration" <?php echo (isset($subject) && $subject == 'Registration') ? 'selected' : ''; ?>>Registration</option>
                        <option value="Trial Session" <?php echo (isset($subject) && $subject == 'Trial Session') ? 'selected' : ''; ?>>Trial Session</option>
                        <option value="General Question" <?php echo (isset($subject) && $subject == 'General Question') ? 'selected' : ''; ?>>General Question</option>
                        <option value="Feedback" <?php echo (isset($subject) && $subject == 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                        <option value="Other" <?php echo (isset($subject) && $subject == 'Other') ? 'selected' : ''; ?>>
                            Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required
                        placeholder="Tell us about your interest in our programs, your child's age, experience level, or any questions you have..."><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                </div>
                <button type="submit" class="btn">Send Message</button>
            </form>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <div class="programs-grid">
            <div class="program-card">
                <h3>What age groups do you accept?</h3>
                <p>We accept players from ages 5 to 18, with programs specifically designed for different age groups and
                    skill levels.</p>
            </div>
            <div class="program-card">
                <h3>Do you offer trial sessions?</h3>
                <p>Yes! We offer free trial sessions for new players to experience our training methods and meet our
                    coaches.</p>
            </div>
            <div class="program-card">
                <h3>What equipment is needed?</h3>
                <p>Players need football boots, shin pads, and a water bottle. We provide all training equipment and
                    balls.</p>
            </div>
            <div class="program-card">
                <h3>How do I register my child?</h3>
                <p>Contact us to schedule a trial session, then complete our registration form and payment. We'll guide
                    you through the entire process.</p>
            </div>
            <div class="program-card">
                <h3>What are your payment options?</h3>
                <p>We accept monthly payments via bank transfer, credit card, or cash. Payment plans are available for
                    families who need them.</p>
            </div>
            <div class="program-card">
                <h3>Do you provide transportation?</h3>
                <p>Currently, we don't provide transportation. Parents are responsible for drop-off and pick-up at our
                    facility.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>