<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['message_id']]);
        $message = 'Message status updated successfully!';
    } catch (PDOException $e) {
        $error = 'Error updating message: ' . $e->getMessage();
    }
}

// Get all messages
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching messages: ' . $e->getMessage();
}

// Get specific message if viewing
$viewing_message = null;
if (isset($_GET['view'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$_GET['view']]);
        $viewing_message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Mark as read if it was new
        if ($viewing_message && $viewing_message['status'] == 'new') {
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
            $stmt->execute([$_GET['view']]);
            $viewing_message['status'] = 'read';
        }
    } catch (PDOException $e) {
        $error = 'Error fetching message: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - Elite Football Academy</title>
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
            <li><a href="messages.php" class="active">Messages</a></li>
            <li><a href="payments.php">Payments</a></li>
            <li><a href="news.php">News</a></li>
        </ul>
    </nav>

    <!-- Admin Content -->
    <div class="admin-content">
        <h2>Contact Messages</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($viewing_message): ?>
            <!-- Message Detail View -->
            <div class="admin-card">
                <h3>Message Details</h3>
                <p><a href="messages.php" class="btn btn-small">← Back to Messages</a></p>
                
                <div style="margin: 1rem 0;">
                    <strong>From:</strong> <?php echo htmlspecialchars($viewing_message['name']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($viewing_message['email']); ?><br>
                    <strong>Phone:</strong> <?php echo htmlspecialchars($viewing_message['phone']); ?><br>
                    <strong>Subject:</strong> <?php echo htmlspecialchars($viewing_message['subject']); ?><br>
                    <strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($viewing_message['created_at'])); ?><br>
                    <strong>Status:</strong> 
                    <span class="<?php echo $viewing_message['status'] == 'new' ? 'btn-danger' : ($viewing_message['status'] == 'read' ? 'btn' : 'btn-success'); ?> btn-small">
                        <?php echo ucfirst($viewing_message['status']); ?>
                    </span>
                </div>
                
                <div style="background: #f9f9f9; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                    <strong>Message:</strong><br>
                    <?php echo nl2br(htmlspecialchars($viewing_message['message'])); ?>
                </div>
                
                <form method="POST" style="margin-top: 1rem;">
                    <input type="hidden" name="message_id" value="<?php echo $viewing_message['id']; ?>">
                    <div class="form-group">
                        <label for="status">Update Status:</label>
                        <select name="status" id="status">
                            <option value="new" <?php echo $viewing_message['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="read" <?php echo $viewing_message['status'] == 'read' ? 'selected' : ''; ?>>Read</option>
                            <option value="replied" <?php echo $viewing_message['status'] == 'replied' ? 'selected' : ''; ?>>Replied</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn">Update Status</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Messages List -->
            <div class="admin-card">
                <h3>All Messages</h3>
                <?php if (empty($messages)): ?>
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
                            <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo htmlspecialchars(substr($msg['subject'], 0, 40)) . (strlen($msg['subject']) > 40 ? '...' : ''); ?></td>
                                <td><?php echo date('M j, Y', strtotime($msg['created_at'])); ?></td>
                                <td>
                                    <span class="<?php echo $msg['status'] == 'new' ? 'btn-danger' : ($msg['status'] == 'read' ? 'btn' : 'btn-success'); ?> btn-small">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="messages.php?view=<?php echo $msg['id']; ?>" class="btn btn-small">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- Add this before the closing </body> tag -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
