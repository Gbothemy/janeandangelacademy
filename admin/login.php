<?php
require_once '../config/database.php';

// Check if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
        } else {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("SELECT id, username, password, role, full_name FROM admin_users WHERE username = ? AND status = 'active'");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password'])) {
                    // Successful login
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['admin_full_name'] = $admin['full_name'];

                    // Update last login time
                    $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$admin['id']]);

                    // Log successful login
                    logAdminAction('login', ['success' => true]);

                    redirect('dashboard.php');
                } else {
                    $error = 'Invalid username or password';

                    // Log failed login attempt
                    if ($admin) {
                        logAdminAction('login_failed', [
                            'username' => $username,
                            'reason' => 'invalid_password'
                        ]);
                    }
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = 'Database error occurred. Please try again.';
            }
        }
    }
}

// Regenerate CSRF token
$_SESSION['csrf_token'] = generateToken();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - Elite Football Academy</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <style>
            .login-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 20px;
            }

            .login-form {
                background: white;
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                width: 100%;
                max-width: 400px;
            }

            .login-form h2 {
                text-align: center;
                margin-bottom: 2rem;
                color: #333;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: bold;
                color: #555;
            }

            .form-group input {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 1rem;
            }

            .form-group input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
            }

            .btn {
                width: 100%;
                padding: 0.75rem;
                background: #667eea;
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 1rem;
                cursor: pointer;
                transition: background 0.3s;
            }

            .btn:hover {
                background: #5a6fd8;
            }

            .btn:disabled {
                background: #ccc;
                cursor: not-allowed;
            }

            .alert {
                padding: 0.75rem;
                margin-bottom: 1rem;
                border-radius: 5px;
            }

            .alert-error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }

            .login-footer {
                text-align: center;
                margin-top: 1rem;
            }

            .login-footer a {
                color: #667eea;
                text-decoration: none;
            }

            .login-footer a:hover {
                text-decoration: underline;
            }

            .spinner {
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #333;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <form class="login-form" method="POST">
                <h2>Admin Login</h2>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn">Login</button>

                <div class="login-footer">
                    <p><a href="../index.php">← Back to Website</a></p>
                    <p><small>Default: admin / admin123</small></p>
                </div>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.querySelector('.login-form');

                form.addEventListener('submit', function (event) {
                    const submitBtn = form.querySelector('button[type="submit"]');

                    if (form.checkValidity()) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner"></span> Logging in...';
                    }
                });
            });
        </script>
    </body>
</html>