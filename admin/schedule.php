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
                $stmt = $pdo->prepare("INSERT INTO training_schedules (team, day_of_week, start_time, end_time, location, coach) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['team'],
                    $_POST['day_of_week'],
                    $_POST['start_time'],
                    $_POST['end_time'],
                    $_POST['location'],
                    $_POST['coach']
                ]);
                $message = 'Training schedule added successfully!';
            } elseif ($_POST['action'] == 'edit') {
                $stmt = $pdo->prepare("UPDATE training_schedules SET team = ?, day_of_week = ?, start_time = ?, end_time = ?, location = ?, coach = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['team'],
                    $_POST['day_of_week'],
                    $_POST['start_time'],
                    $_POST['end_time'],
                    $_POST['location'],
                    $_POST['coach'],
                    $_POST['schedule_id']
                ]);
                $message = 'Training schedule updated successfully!';
            } elseif ($_POST['action'] == 'delete' && isset($_POST['schedule_id'])) {
                $stmt = $pdo->prepare("DELETE FROM training_schedules WHERE id = ?");
                $stmt->execute([$_POST['schedule_id']]);
                $message = 'Training schedule deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all schedules
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM training_schedules ORDER BY team, FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time");
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get teams for dropdown
    $stmt = $pdo->query("SELECT DISTINCT team FROM players ORDER BY team");
    $teams = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = 'Error fetching schedules: ' . $e->getMessage();
}

// Get schedule to edit
$edit_schedule = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM training_schedules WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching schedule: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Training Schedule - Elite Football Academy</title>
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
            <li><a href="schedule.php" class="active">Schedule</a></li>
            <li><a href="gallery.php">Gallery</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li><a href="payments.php">Payments</a></li>
            <li><a href="news.php">News</a></li>
            <li><a href="media.php">Media</a></li>
            <li><a href="users.php">Users</a></li>
        </ul>
    </nav>

    <!-- Admin Content -->
    <div class="admin-content">
        <h2>Manage Training Schedule</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Add/Edit Schedule Form -->
        <div class="admin-card">
            <h3><?php echo $edit_schedule ? 'Edit Training Schedule' : 'Add New Training Schedule'; ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_schedule ? 'edit' : 'add'; ?>">
                <?php if ($edit_schedule): ?>
                    <input type="hidden" name="schedule_id" value="<?php echo $edit_schedule['id']; ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="team">Team</label>
                        <select id="team" name="team" required>
                            <option value="">Select Team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo htmlspecialchars($team); ?>" <?php echo $edit_schedule && $edit_schedule['team'] == $team ? 'selected' : ''; ?>><?php echo htmlspecialchars($team); ?></option>
                            <?php endforeach; ?>
                            <option value="U15 Team" <?php echo $edit_schedule && $edit_schedule['team'] == 'U15 Team' ? 'selected' : ''; ?>>U15 Team</option>
                            <option value="U16 Team" <?php echo $edit_schedule && $edit_schedule['team'] == 'U16 Team' ? 'selected' : ''; ?>>U16 Team</option>
                            <option value="U17 Team" <?php echo $edit_schedule && $edit_schedule['team'] == 'U17 Team' ? 'selected' : ''; ?>>U17 Team</option>
                            <option value="U18 Team" <?php echo $edit_schedule && $edit_schedule['team'] == 'U18 Team' ? 'selected' : ''; ?>>U18 Team</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="day_of_week">Day of Week</label>
                        <select id="day_of_week" name="day_of_week" required>
                            <option value="">Select Day</option>
                            <option value="Monday" <?php echo $edit_schedule && $edit_schedule['day_of_week'] == 'Monday' ? 'selected' : ''; ?>>Monday</option>
                            <option value="Tuesday" <?php echo $edit_schedule && $edit_schedule['day_of_week'] == 'Tuesday' ? 'selected' : ''; ?>>Tuesday</option>
                            <option value="Wednesday" <?php echo $edit_schedule && $edit_schedule['day_of_week'] == 'Wednesday' ? 'selected' : ''; ?>>Wednesday</option>
                            <option value="Thursday" <?php echo $edit_schedule && $edit_schedule['day_of_week'] == 'Thursday' ? 'selected' : ''; ?>>Thursday</option>
                            <option value="Friday" <?php echo $edit_schedule && $edit_schedule['day_of_week'] == 'Friday' ? 'selected' : ''; ?>>Friday</option>
                            <option value="Saturday" <?php echo $edit_schedule && $edit_schedule['day_of_week'] == 'Saturday' ? 'selected' : ''; ?>>Saturday</option>
                            <option value="Sunday" <?php echo $edit_schedule && $edit_schedule['day_of_week'] == 'Sunday' ? 'selected' : ''; ?>>Sunday</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time" value="<?php echo $edit_schedule ? $edit_schedule['start_time'] : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" value="<?php echo $edit_schedule ? $edit_schedule['end_time'] : ''; ?>" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" value="<?php echo $edit_schedule ? htmlspecialchars($edit_schedule['location']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="coach">Coach</label>
                        <input type="text" id="coach" name="coach" value="<?php echo $edit_schedule ? htmlspecialchars($edit_schedule['coach']) : ''; ?>" required>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn"><?php echo $edit_schedule ? 'Update Schedule' : 'Add Schedule'; ?></button>
                    <?php if ($edit_schedule): ?>
                        <a href="schedule.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Schedule List -->
        <div class="admin-card">
            <h3>Current Training Schedules</h3>
            <?php if (empty($schedules)): ?>
                <p>No training schedules found.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Team</th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Coach</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($schedule['team']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                                <td><?php echo date('g:i A', strtotime($schedule['start_time'])); ?> - <?php echo date('g:i A', strtotime($schedule['end_time'])); ?></td>
                                <td><?php echo htmlspecialchars($schedule['location']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['coach']); ?></td>
                                <td>
                                    <a href="schedule.php?edit=<?php echo $schedule['id']; ?>" class="btn btn-small">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                    </form>
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
