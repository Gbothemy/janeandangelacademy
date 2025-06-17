<?php
require_once 'config/database.php';

$page_title = 'Player Registration';
$message = '';
$error = '';

// Check if registration is open
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'registration_open'");
    $registration_open = $stmt->fetchColumn();
    
    if ($registration_open != '1') {
        $error = 'Registration is currently closed. Please check back later or contact us for more information.';
    }
} catch (PDOException $e) {
    $error = 'System error. Please try again later.';
}

// Get available teams
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT DISTINCT team FROM players ORDER BY team");
    $teams = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $teams = ['U15 Team', 'U16 Team', 'U17 Team', 'U18 Team'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    // Validate input
    $name = trim($_POST['name']);
    $dob = $_POST['dob'];
    $position = trim($_POST['position']);
    $team = trim($_POST['team']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $parent_name = trim($_POST['parent_name']);
    $parent_email = trim($_POST['parent_email']);
    $parent_phone = trim($_POST['parent_phone']);
    $address = trim($_POST['address']);
    $medical_info = trim($_POST['medical_info']);
    
    if (empty($name) || empty($dob) || empty($position) || empty($team) || empty($email) || empty($parent_name) || empty($parent_email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!filter_var($parent_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid parent email address.';
    } else {
        // Handle profile image upload
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                $error = 'Invalid file type. Allowed types: JPG, PNG, GIF';
            } elseif ($_FILES['profile_image']['size'] > $max_size) {
                $error = 'File size exceeds the limit of 5MB';
            } else {
                // Create uploads directory if it doesn't exist
                $upload_dir = 'uploads/players/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    $profile_image = $upload_path;
                } else {
                    $error = 'Failed to upload image. Please try again.';
                }
            }
        }
        
        if (empty($error)) {
            try {
                $pdo = getConnection();
                
                // Check if player with same email already exists
                $stmt = $pdo->prepare("SELECT id FROM players WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->rowCount() > 0) {
                    $error = 'A player with this email address is already registered.';
                } else {
                    // Insert new player
                    $stmt = $pdo->prepare("INSERT INTO players (name, dob, position, team, email, phone, parent_name, parent_email, parent_phone, address, medical_info, profile_image, registration_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')");
                    $stmt->execute([
                        $name,
                        $dob,
                        $position,
                        $team,
                        $email,
                        $phone,
                        $parent_name,
                        $parent_email,
                        $parent_phone,
                        $address,
                        $medical_info,
                        $profile_image
                    ]);
                    
                    $player_id = $pdo->lastInsertId();
                    
                    // Send notification email to admin
                    // This would typically use a proper email library like PHPMailer
                    $admin_email = 'admin@elitefootballacademy.com';
                    $subject = 'New Player Registration';
                    $email_message = "A new player has registered:\n\n";
                    $email_message .= "Name: $name\n";
                    $email_message .= "Team: $team\n";
                    $email_message .= "Position: $position\n";
                    $email_message .= "Email: $email\n";
                    $email_message .= "Parent: $parent_name\n";
                    $email_message .= "Parent Email: $parent_email\n\n";
                    $email_message .= "Please log in to the admin panel to review this registration.";
                    
                    // Uncomment to send email when email functionality is set up
                    // mail($admin_email, $subject, $email_message);
                    
                    $message = 'Registration submitted successfully! We will contact you soon to confirm your registration.';
                    
                    // Clear form data on success
                    $name = $dob = $position = $team = $email = $phone = $parent_name = $parent_email = $parent_phone = $address = $medical_info = '';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include 'includes/header.php';
?>

<!-- Page Banner -->
<div class="page-banner">
    <div class="container">
        <h1>Player Registration</h1>
        <p>Join Elite Football Academy and take your game to the next level</p>
    </div>
</div>

<!-- Registration Form -->
<section class="registration-section">
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($registration_open == '1'): ?>
            <div class="registration-intro">
                <h2>Register for the 2023-2024 Season</h2>
                <p>Please complete the form below to register as a player with Elite Football Academy. All fields marked with an asterisk (*) are required.</p>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="registration-form" id="registrationForm">
                <div class="form-section">
                    <h3>Player Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="dob">Date of Birth <span class="required">*</span></label>
                            <input type="date" id="dob" name="dob" value="<?php echo isset($dob) ? $dob : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="position">Position <span class="required">*</span></label>
                            <select id="position" name="position" required>
                                <option value="">Select Position</option>
                                <option value="Goalkeeper" <?php echo isset($position) && $position == 'Goalkeeper' ? 'selected' : ''; ?>>Goalkeeper</option>
                                <option value="Defender" <?php echo isset($position) && $position == 'Defender' ? 'selected' : ''; ?>>Defender</option>
                                <option value="Midfielder" <?php echo isset($position) && $position == 'Midfielder' ? 'selected' : ''; ?>>Midfielder</option>
                                <option value="Forward" <?php echo isset($position) && $position == 'Forward' ? 'selected' : ''; ?>>Forward</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="team">Team <span class="required">*</span></label>
                            <select id="team" name="team" required>
                                <option value="">Select Team</option>
                                <?php foreach ($teams as $team_option): ?>
                                    <option value="<?php echo htmlspecialchars($team_option); ?>" <?php echo isset($team) && $team == $team_option ? 'selected' : ''; ?>><?php echo htmlspecialchars($team_option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_image">Profile Image</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*">
                        <small class="form-text text-muted">Max file size: 5MB. Allowed types: JPG, PNG, GIF</small>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Parent/Guardian Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="parent_name">Parent/Guardian Name <span class="required">*</span></label>
                            <input type="text" id="parent_name" name="parent_name" value="<?php echo isset($parent_name) ? htmlspecialchars($parent_name) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="parent_email">Parent/Guardian Email <span class="required">*</span></label>
                            <input type="email" id="parent_email" name="parent_email" value="<?php echo isset($parent_email) ? htmlspecialchars($parent_email) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="parent_phone">Parent/Guardian Phone <span class="required">*</span></label>
                        <input type="tel" id="parent_phone" name="parent_phone" value="<?php echo isset($parent_phone) ? htmlspecialchars($parent_phone) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Medical Information</h3>
                    
                    <div class="form-group">
                        <label for="medical_info">Medical Information (allergies, conditions, etc.)</label>
                        <textarea id="medical_info" name="medical_info" rows="4"><?php echo isset($medical_info) ? htmlspecialchars($medical_info) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Terms and Conditions</h3>
                    
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <a href="terms-of-service.php" target="_blank">Terms and Conditions</a> and <a href="privacy-policy.php" target="_blank">Privacy Policy</a> <span class="required">*</span></label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="medical_consent" name="medical_consent" required>
                            <label for="medical_consent">I give consent for emergency medical treatment if needed <span class="required">*</span></label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Submit Registration</button>
                </div>
            </form>
        <?php else: ?>
            <div class="registration-closed">
                <h2>Registration Currently Closed</h2>
                <p>Registration for the current season is closed. Please check back later or contact us for more information about upcoming registration periods.</p>
                <a href="contact.php" class="btn">Contact Us</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Registration Benefits -->
<section class="benefits-section">
    <div class="container">
        <h2>Benefits of Joining Elite Football Academy</h2>
        
        <div class="benefits-grid">
            <div class="benefit-card">
                <div class="benefit-icon">🏆</div>
                <h3>Professional Coaching</h3>
                <p>Train with UEFA-licensed coaches with experience at professional clubs.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">🥅</div>
                <h3>State-of-the-Art Facilities</h3>
                <p>Access to premium training facilities, including full-size pitches and indoor training areas.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">🏃</div>
                <h3>Performance Analysis</h3>
                <p>Regular performance assessments and personalized development plans.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">🌍</div>
                <h3>Competitive Matches</h3>
                <p>Regular matches against quality opposition, including tournaments and showcases.</p>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Show validation messages
                const invalidInputs = form.querySelectorAll(':invalid');
                invalidInputs.forEach(input => {
                    input.classList.add('error');
                });
            }
            
            form.classList.add('was-validated');
        });
        
        // Clear validation styling when input changes
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.remove('error');
                    this.classList.add('success');
                } else {
                    this.classList.remove('success');
                }
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
