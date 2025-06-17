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
                $name = $_POST['name'];
                $age = $_POST['age'];
                $position = $_POST['position'];
                $team = $_POST['team'];
                $bio = $_POST['bio'];
                $photoPath = null;

                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['photo']['tmp_name'];
                    $fileName = $_FILES['photo']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                    if (in_array($fileExtension, $allowedExtensions)) {
                        $newFileName = uniqid('player_', true) . '.' . $fileExtension;
                        $uploadPath = '../uploads/players/' . $newFileName;

                        if (!is_dir('../uploads/players')) {
                            mkdir('../uploads/players', 0775, true);
                        }

                        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                            $photoPath = 'uploads/players/' . $newFileName;
                        } else {
                            $error = 'Failed to move uploaded file.';
                        }
                    } else {
                        $error = 'Invalid photo format. Use JPG, PNG, or WEBP.';
                    }
                }

                if (!$error) {
                    $stmt = $pdo->prepare("INSERT INTO players (name, age, position, team, bio, photo) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $age, $position, $team, $bio, $photoPath]);
                    $message = 'Player added successfully!';
                }

            } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
                // Optional: delete the player's photo from storage
                $stmt = $pdo->prepare("SELECT photo FROM players WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $player = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($player && !empty($player['photo']) && file_exists('../' . $player['photo'])) {
                    unlink('../' . $player['photo']);
                }

                $stmt = $pdo->prepare("DELETE FROM players WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'Player deleted successfully!';
            }

        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all players
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM players ORDER BY created_at DESC");
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching players: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Players - Elite Football Academy</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
        <header class="admin-header">
            <div class="container">
                <h1>Elite Football Academy - Admin Panel</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?> |
                    <a href="logout.php" style="color: var(--primary-gold);">Logout</a>
                </p>
            </div>
        </header>

        <nav class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="players.php" class="active">Players</a></li>
                <li><a href="schedule.php">Schedule</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="payments.php">Payments</a></li>
                <li><a href="news.php">News</a></li>
            </ul>
        </nav>

        <div class="admin-content">
            <h2>Manage Players</h2>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="admin-card">
                <h3>Add New Player</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label for="name">Player Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" min="8" max="25" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label for="position">Position</label>
                            <select id="position" name="position" required>
                                <option value="">Select Position</option>
                                <option value="Goalkeeper">Goalkeeper</option>
                                <option value="Defender">Defender</option>
                                <option value="Midfielder">Midfielder</option>
                                <option value="Forward">Forward</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="team">Team</label>
                            <select id="team" name="team" required>
                                <option value="">Select Team</option>
                                <option value="U15 Team">U15 Team</option>
                                <option value="U16 Team">U16 Team</option>
                                <option value="U17 Team">U17 Team</option>
                                <option value="U18 Team">U18 Team</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bio">Player Bio</label>
                        <textarea id="bio" name="bio" rows="3"
                            placeholder="Brief description of the player's skills and achievements"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="photo">Player Photo</label>
                        <input type="file" id="photo" name="photo" accept="image/*">
                    </div>
                    <button type="submit" class="btn">Add Player</button>
                </form>
            </div>

            <div class="admin-card">
                <h3>Current Players</h3>
                <?php if (empty($players)): ?>
                    <p>No players found.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Position</th>
                                <th>Team</th>
                                <th>Bio</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($players as $player): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($player['photo'])): ?>
                                            <img src="../<?php echo htmlspecialchars($player['photo']); ?>" alt="Player Photo"
                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span>No photo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($player['name']); ?></td>
                                    <td><?php echo $player['age']; ?></td>
                                    <td><?php echo htmlspecialchars($player['position']); ?></td>
                                    <td><?php echo htmlspecialchars($player['team']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($player['bio'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($player['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this player?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $player['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <script src="../assets/js/main.js"></script>
        <script src="../assets/js/admin.js"></script>
    </body>
</html>