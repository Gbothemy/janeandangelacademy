<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

try {
    $pdo = getConnection();

    // Get statistics
    $stats = [];

    // Total players
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM players");
    $stats['players'] = $stmt->fetch()['count'];

    // Total messages
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'");
    $stats['new_messages'] = $stmt->fetch()['count'];

    // Total payments this month
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments WHERE MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())");
    $stats['monthly_payments'] = $stmt->fetch()['count'];

    // Total gallery images
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery");
    $stats['gallery_images'] = $stmt->fetch()['count'];

    // Recent messages
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recent_messages = $stmt->fetchAll();

    // Recent registrations
    $stmt = $pdo->query("SELECT * FROM players WHERE registration_date IS NOT NULL ORDER BY registration_date DESC LIMIT 5");
    $recent_registrations = $stmt->fetchAll();

    // Recent admin activity
    $stmt = $pdo->query("SELECT l.*, u.username FROM admin_logs l JOIN admin_users u ON l.admin_id = u.id ORDER BY l.created_at DESC LIMIT 10");
    $recent_activity = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - Elite Football Academy</title>
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="players.php">Players</a></li>
                <li><a href="schedule.php">Schedule</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="payments.php">Payments</a></li>
                <li><a href="news.php">News</a></li>
                <li><a href="media.php">Media</a></li>
                <li><a href="users.php">Users</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="signup.php">Create Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Admin Content -->
        <div class="admin-content">
            <h2>Dashboard Overview</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $stats['players']; ?></h3>
                    <p>Total Players</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['new_messages']; ?></h3>
                    <p>New Messages</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['monthly_payments']; ?></h3>
                    <p>Monthly Payments</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['gallery_images']; ?></h3>
                    <p>Gallery Images</p>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Recent Messages -->
                <div class="admin-card">
                    <h3>Recent Contact Messages</h3>
                    <?php if (empty($recent_messages)): ?>
                        <p>No messages found.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_messages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($message['subject'], 0, 30)) . '...'; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($message['created_at'])); ?></td>
                                        <td>
                                            <span
                                                class="<?php echo $message['status'] == 'new' ? 'btn-danger' : 'btn-success'; ?> btn-small">
                                                <?php echo ucfirst($message['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="messages.php?view=<?php echo $message['id']; ?>"
                                                class="btn btn-small">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p style="margin-top: 1rem;"><a href="messages.php" class="btn">View All Messages</a></p>
                    <?php endif; ?>
                </div>

                <!-- Recent Registrations -->
                <div class="admin-card">
                    <h3>Recent Player Registrations</h3>
                    <?php if (empty($recent_registrations)): ?>
                        <p>No recent registrations found.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Team</th>
                                    <th>Position</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_registrations as $player): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($player['name']); ?></td>
                                        <td><?php echo htmlspecialchars($player['team']); ?></td>
                                        <td><?php echo htmlspecialchars($player['position']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($player['registration_date'])); ?></td>
                                        <td>
                                            <span
                                                class="<?php echo $player['status'] == 'active' ? 'btn-success' : ($player['status'] == 'pending' ? 'btn' : 'btn-danger'); ?> btn-small">
                                                <?php echo ucfirst($player['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="players.php?view=<?php echo $player['id']; ?>"
                                                class="btn btn-small">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p style="margin-top: 1rem;"><a href="players.php" class="btn">View All Players</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Admin Activity -->
            <div class="admin-card">
                <h3>Recent Admin Activity</h3>
                <?php if (empty($recent_activity)): ?>
                    <p>No recent activity found.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Date</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_activity as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['username']); ?></td>
                                    <td><?php echo htmlspecialchars(str_replace('_', ' ', $activity['action'])); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add this before the closing </body> tag -->
        <script src="../assets/js/main.js"></script>
        <script src="../assets/js/admin.js"></script>
    </body>
</html>