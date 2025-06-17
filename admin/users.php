<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            $pdo = getConnection();

            if ($_POST['action'] == 'add') {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
                $stmt->execute([$_POST['username']]);
                if ($stmt->rowCount() > 0) {
                    $error = 'Username already exists. Please choose a different username.';
                } else {
                    // Hash the password
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email, role, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['username'],
                        $hashed_password,
                        $_POST['email'],
                        $_POST['role'],
                        $_POST['full_name'],
                        $_POST['phone']
                    ]);
                    $message = 'User added successfully!';
                }
            } elseif ($_POST['action'] == 'edit') {
                $updates = [];
                $params = [];

                if (!empty($_POST['email'])) {
                    $updates[] = "email = ?";
                    $params[] = $_POST['email'];
                }

                if (!empty($_POST['role'])) {
                    $updates[] = "role = ?";
                    $params[] = $_POST['role'];
                }

                if (!empty($_POST['full_name'])) {
                    $updates[] = "full_name = ?";
                    $params[] = $_POST['full_name'];
                }

                if (!empty($_POST['phone'])) {
                    $updates[] = "phone = ?";
                    $params[] = $_POST['phone'];
                }

                if (!empty($_POST['password'])) {
                    $updates[] = "password = ?";
                    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }

                if (!empty($updates)) {
                    $params[] = $_POST['user_id'];
                    $stmt = $pdo->prepare("UPDATE admin_users SET " . implode(", ", $updates) . " WHERE id = ?");
                    $stmt->execute($params);
                    $message = 'User updated successfully!';
                }
            } elseif ($_POST['action'] == 'delete' && isset($_POST['user_id'])) {
                // Don't allow deletion of the current user
                if ($_POST['user_id'] == $_SESSION['admin_id']) {
                    $error = 'You cannot delete your own account.';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
                    $stmt->execute([$_POST['user_id']]);
                    $message = 'User deleted successfully!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all users
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM admin_users ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching users: ' . $e->getMessage();
}

// Get user to edit
$edit_user = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching user: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Users - Elite Football Academy</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="container">
                <h1>Elite Football Academy - Admin Panel</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?> | <a href="logout.php"
                        style="color: var(--primary-gold);">Logout</a></p>
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
                <li><a href="users.php" class="active">Users</a></li>
            </ul>
        </nav>

        <!-- Admin Content -->
        <div class="admin-content">
            <h2>Manage Users</h2>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Add/Edit User Form -->
            <div class="admin-card">
                <h3><?php echo $edit_user ? 'Edit User' : 'Add New User'; ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $edit_user ? 'edit' : 'add'; ?>">
                    <?php if ($edit_user): ?>
                        <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username"
                                value="<?php echo $edit_user ? htmlspecialchars($edit_user['username']) : ''; ?>" <?php echo $edit_user ? 'readonly' : 'required'; ?>>
                            <?php if ($edit_user): ?>
                                <small style="color: var(--light-text);">Username cannot be changed</small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name"
                                value="<?php echo $edit_user && isset($edit_user['full_name']) ? htmlspecialchars($edit_user['full_name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone"
                                value="<?php echo $edit_user && isset($edit_user['phone']) ? htmlspecialchars($edit_user['phone']) : ''; ?>">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label for="password">Password
                                <?php echo $edit_user ? '(Leave blank to keep current)' : ''; ?></label>
                            <input type="password" id="password" name="password" <?php echo $edit_user ? '' : 'required'; ?>>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin" <?php echo $edit_user && $edit_user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="editor" <?php echo $edit_user && $edit_user['role'] == 'editor' ? 'selected' : ''; ?>>Editor</option>
                                <option value="viewer" <?php echo $edit_user && $edit_user['role'] == 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit"
                            class="btn"><?php echo $edit_user ? 'Update User' : 'Add User'; ?></button>
                        <?php if ($edit_user): ?>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Users List -->
            <div class="admin-card">
                <h3>Current Users</h3>
                <?php if (empty($users)): ?>
                    <p>No users found.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Full Name</th>
                                    <th>Last Login</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span
                                                class="<?php echo $user['role'] == 'admin' ? 'btn-success' : ($user['role'] == 'editor' ? 'btn' : ''); ?> btn-small">
                                                <?php echo ucfirst($user['role'] ?? 'editor'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : '-'; ?>
                                        </td>
                                        <td><?php echo isset($user['last_login']) ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <a href="users.php?edit=<?php echo $user['id']; ?>" class="btn btn-small">Edit</a>
                                            <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                <form method="POST" style="display: inline;"
                                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Add this before the closing </body> tag -->
        <script src="../assets/js/main.js"></script>
        <script src="../assets/js/admin.js"></script>
    </body>
</html>