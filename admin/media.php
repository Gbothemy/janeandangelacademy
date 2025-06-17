<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'video/mp4'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['file']['type'], $allowed_types)) {
            $error = 'Invalid file type. Allowed types: JPG, PNG, GIF, PDF, MP4';
        } elseif ($_FILES['file']['size'] > $max_size) {
            $error = 'File size exceeds the limit of 5MB';
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                try {
                    $pdo = getConnection();
                    $stmt = $pdo->prepare("INSERT INTO media (file_name, file_path, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_FILES['file']['name'],
                        'uploads/' . $new_filename,
                        $_FILES['file']['type'],
                        $_FILES['file']['size'],
                        $_SESSION['admin_id']
                    ]);
                    $message = 'File uploaded successfully!';
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                    // Delete the uploaded file if database insertion fails
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            } else {
                $error = 'Failed to upload file. Please try again.';
            }
        }
    } else {
        $error = 'Please select a file to upload.';
    }
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['media_id'])) {
    try {
        $pdo = getConnection();
        
        // Get file path before deleting record
        $stmt = $pdo->prepare("SELECT file_path FROM media WHERE id = ?");
        $stmt->execute([$_POST['media_id']]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($file) {
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
            $stmt->execute([$_POST['media_id']]);
            
            // Delete file from server
            $file_path = '../' . $file['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            $message = 'File deleted successfully!';
        } else {
            $error = 'File not found.';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get all media files
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT m.*, u.username FROM media m LEFT JOIN admin_users u ON m.uploaded_by = u.id ORDER BY m.created_at DESC");
    $media_files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching media: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library - Elite Football Academy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .media-item {
            border: 1px solid var(--light-lavender);
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }
        .media-preview {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
        }
        .media-preview img {
            max-width: 100%;
            max-height: 100%;
        }
        .media-preview .file-icon {
            font-size: 3rem;
            color: var(--bronze-brown);
        }
        .media-info {
            padding: 0.5rem;
        }
        .media-info h4 {
            margin: 0;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .media-info p {
            margin: 0.25rem 0;
            font-size: 0.8rem;
            color: var(--light-text);
        }
        .media-actions {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem;
            background-color: #f9f9f9;
        }
    </style>
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
            <li><a href="media.php" class="active">Media</a></li>
            <li><a href="users.php">Users</a></li>
        </ul>
    </nav>

    <!-- Admin Content -->
    <div class="admin-content">
        <h2>Media Library</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="admin-card">
            <h3>Upload New File</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                <div class="form-group">
                    <label for="file">Select File</label>
                    <input type="file" id="file" name="file" required>
                    <small style="color: var(--light-text);">Max file size: 5MB. Allowed types: JPG, PNG, GIF, PDF, MP4</small>
                </div>
                <button type="submit" class="btn">Upload File</button>
            </form>
        </div>

        <!-- Media Library -->
        <div class="admin-card">
            <h3>Media Files</h3>
            <?php if (empty($media_files)): ?>
                <p>No media files found.</p>
            <?php else: ?>
                <div class="media-grid">
                    <?php foreach ($media_files as $file): ?>
                        <div class="media-item">
                            <div class="media-preview">
                                <?php if (strpos($file['file_type'], 'image/') === 0): ?>
                                    <img src="../<?php echo htmlspecialchars($file['file_path']); ?>" alt="<?php echo htmlspecialchars($file['file_name']); ?>">
                                <?php elseif ($file['file_type'] == 'application/pdf'): ?>
                                    <div class="file-icon">📄</div>
                                <?php elseif (strpos($file['file_type'], 'video/') === 0): ?>
                                    <div class="file-icon">🎬</div>
                                <?php else: ?>
                                    <div class="file-icon">📁</div>
                                <?php endif; ?>
                            </div>
                            <div class="media-info">
                                <h4 title="<?php echo htmlspecialchars($file['file_name']); ?>"><?php echo htmlspecialchars($file['file_name']); ?></h4>
                                <p><?php echo date('M j, Y', strtotime($file['created_at'])); ?></p>
                                <p><?php echo formatFileSize($file['file_size']); ?></p>
                            </div>
                            <div class="media-actions">
                                <a href="../<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" class="btn btn-small">View</a>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="media_id" value="<?php echo $file['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // Helper function to format file size
    function formatFileSize($bytes) {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    ?>
    <!-- Add this before the closing </body> tag -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
