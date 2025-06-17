<?php
require_once '../config/database.php';

// Check if admin is logged in and has admin role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if the current user has admin privileges
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT role FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $current_user = $stmt->fetch();
    
    if (!$current_user || $current_user['role'] !== 'admin') {
        $_SESSION['error_message'] = "You don't have permission to access this page.";
        header('Location: dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$message = '';
$error = '';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid form submission.';
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $_POST['role'];
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        
        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all required fields.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->rowCount() > 0) {
                    $error = 'Username already exists. Please choose a different username.';
                } else {
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->rowCount() > 0) {
                        $error = 'Email already exists. Please use a different email address.';
                    } else {
                        // Hash the password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new admin user
                        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email, role, full_name, phone, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $username,
                            $hashed_password,
                            $email,
                            $role,
                            $full_name,
                            $phone,
                            $_SESSION['admin_id']
                        ]);
                        
                        $message = 'Admin user created successfully!';
                        
                        // Log the action
                        $new_user_id = $pdo->lastInsertId();
                        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, ?, ?)");
                        $stmt->execute([
                            $_SESSION['admin_id'],
                            'create_user',
                            json_encode(['user_id' => $new_user_id, 'username' => $username, 'role' => $role])
                        ]);
                        
                        // Clear form data on success
                        $username = $email = $full_name = $phone = '';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Regenerate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - Elite Football Academy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <h1>Elite Football Academy - Admin Panel</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="logout.php" style="color: var(--primary-gold);">Logout</a></p>
        </div>
    </header>

    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="players.php">Players</a></li>
            <li><a href="schedule.php">Schedule</a></li>
            <li><a href="gallery.php">Gallery</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li><a href="payments.php">Payments</a></li>
            <li><a href="news.php">News</a></li>
            <li><a href="media.php">Media</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="signup.php" class="active">Create Admin</a></li>
        </ul>
    </nav>

    <!-- Admin Content -->
    <div class="admin-content">
        <h2>Create New Admin User</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <h3>Admin User Registration</h3>
            <p class="text-muted">Create a new administrator account for the football academy website.</p>
            
            <form method="POST" id="signupForm" class="needs-validation">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                        <div class="invalid-feedback">Please enter a username.</div>
                        <small class="form-text text-muted">Username must be unique and will be used for login.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" required>
                        <div class="invalid-feedback">Password must be at least 8 characters.</div>
                        <div class="password-strength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Role <span class="required">*</span></label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin (Full Access)</option>
                            <option value="editor">Editor (Limited Access)</option>
                            <option value="viewer">Viewer (Read-only)</option>
                        </select>
                        <div class="invalid-feedback">Please select a role.</div>
                        <small class="form-text text-muted">
                            <strong>Admin:</strong> Full access to all features<br>
                            <strong>Editor:</strong> Can edit content but not manage users<br>
                            <strong>Viewer:</strong> Can only view information
                        </small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I confirm that this user requires admin access to the system <span class="required">*</span></label>
                        <div class="invalid-feedback">You must confirm this statement.</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Create Admin User</button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('signupForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordStrength = document.querySelector('.password-strength');
        
        // Password strength indicator
        password.addEventListener('input', function() {
            const value = password.value;
            let strength = 0;
            let message = '';
            
            if (value.length >= 8) strength += 1;
            if (value.match(/[a-z]+/)) strength += 1;
            if (value.match(/[A-Z]+/)) strength += 1;
            if (value.match(/[0-9]+/)) strength += 1;
            if (value.match(/[^a-zA-Z0-9]+/)) strength += 1;
            
            switch (strength) {
                case 0:
                case 1:
                    message = '<span style="color: #ff4d4d;">Very Weak</span>';
                    break;
                case 2:
                    message = '<span style="color: #ffa64d;">Weak</span>';
                    break;
                case 3:
                    message = '<span style="color: #ffff4d;">Medium</span>';
                    break;
                case 4:
                    message = '<span style="color: #4dff4d;">Strong</span>';
                    break;
                case 5:
                    message = '<span style="color: #4d4dff;">Very Strong</span>';
                    break;
            }
            
            passwordStrength.innerHTML = 'Password Strength: ' + message;
        });
        
        // Password match validation
        confirmPassword.addEventListener('input', function() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
        
        // Form validation
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Show validation messages
                const invalidInputs = form.querySelectorAll(':invalid');
                invalidInputs.forEach(input => {
                    const feedback = input.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.style.display = 'block';
                    }
                });
            }
            
            form.classList.add('was-validated');
        });
        
        // Clear validation messages when input changes
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.style.display = '';
                }
            });
        });
        
        // Username availability check
        const username = document.getElementById('username');
        let usernameTimer;
        
        username.addEventListener('input', function() {
            clearTimeout(usernameTimer);
            
            if (username.value.length < 3) return;
            
            usernameTimer = setTimeout(function() {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'check_username.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.exists) {
                                username.setCustomValidity('Username already exists');
                                const feedback = username.nextElementSibling;
                                if (feedback && feedback.classList.contains('invalid-feedback')) {
                                    feedback.textContent = 'This username is already taken.';
                                    feedback.style.display = 'block';
                                }
                            } else {
                                username.setCustomValidity('');
                            }
                        } catch (e) {
                            console.error('Error parsing JSON response:', e);
                        }
                    }
                };
                xhr.send('username=' + encodeURIComponent(username.value));
            }, 500);
        });
    });
    </script>
</body>
</html>
