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
                $image_path = null;
                
                // Handle image upload if provided
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    if (!in_array($_FILES['image']['type'], $allowed_types)) {
                        $error = 'Invalid file type. Allowed types: JPG, PNG, GIF';
                    } elseif ($_FILES['image']['size'] > $max_size) {
                        $error = 'File size exceeds the limit of 5MB';
                    } else {
                        // Create uploads directory if it doesn't exist
                        $upload_dir = '../uploads/news/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        // Generate unique filename
                        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $new_filename = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image_path = 'uploads/news/' . $new_filename;
                        } else {
                            $error = 'Failed to upload image. Please try again.';
                        }
                    }
                }
                
                if (empty($error)) {
                    $published = isset($_POST['published']) ? 1 : 0;
                    
                    $stmt = $pdo->prepare("INSERT INTO news (title, content, image_path, published) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['content'],
                        $image_path,
                        $published
                    ]);
                    $message = 'News item added successfully!';
                }
            } elseif ($_POST['action'] == 'edit') {
                $updates = [];
                $params = [];
                
                $updates[] = "title = ?";
                $params[] = $_POST['title'];
                
                $updates[] = "content = ?";
                $params[] = $_POST['content'];
                
                $published = isset($_POST['published']) ? 1 : 0;
                $updates[] = "published = ?";
                $params[] = $published;
                
                // Handle image update if a new one is uploaded
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    if (!in_array($_FILES['image']['type'], $allowed_types)) {
                        $error = 'Invalid file type. Allowed types: JPG, PNG, GIF';
                    } elseif ($_FILES['image']['size'] > $max_size) {
                        $error = 'File size exceeds the limit of 5MB';
                    } else {
                        // Create uploads directory if it doesn't exist
                        $upload_dir = '../uploads/news/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        // Generate unique filename
                        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $new_filename = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Get old image path to delete
                            $stmt = $pdo->prepare("SELECT image_path FROM news WHERE id = ?");
                            $stmt->execute([$_POST['news_id']]);
                            $old_image = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            // Delete old image if it exists
                            if ($old_image && $old_image['image_path'] && file_exists('../' . $old_image['image_path'])) {
                                unlink('../' . $old_image['image_path']);
                            }
                            
                            $updates[] = "image_path = ?";
                            $params[] = 'uploads/news/' . $new_filename;
                        } else {
                            $error = 'Failed to upload image. Please try again.';
                        }
                    }
                }
                
                if (empty($error)) {
                    $params[] = $_POST['news_id'];
                    $stmt = $pdo->prepare("UPDATE news SET " . implode(", ", $updates) . " WHERE id = ?");
                    $stmt->execute($params);
                    $message = 'News item updated successfully!';
                }
            } elseif ($_POST['action'] == 'delete' && isset($_POST['news_id'])) {
                // Get image path before deleting record
                $stmt = $pdo->prepare("SELECT image_path FROM news WHERE id = ?");
                $stmt->execute([$_POST['news_id']]);
                $news = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete from database
                $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$_POST['news_id']]);
                
                // Delete image file if it exists
                if ($news && $news['image_path'] && file_exists('../' . $news['image_path'])) {
                    unlink('../' . $news['image_path']);
                }
                
                $message = 'News item deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all news items
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
    $news_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching news: ' . $e->getMessage();
}

// Get news item to edit
$edit_news = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_news = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching news item: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - Elite Football Academy</title>
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
            <li><a href="news.php" class="active">News</a></li>
            <li><a href="media.php">Media</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="signup.php">Create Admin</a></li>
        </ul>
    </nav>

    <!-- Admin Content -->
    <div class="admin-content">
        <h2>Manage News</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Add/Edit News Form -->
        <div class="admin-card">
            <h3><?php echo $edit_news ? 'Edit News Item' : 'Add New News Item'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_news ? 'edit' : 'add'; ?>">
                <?php if ($edit_news): ?>
                    <input type="hidden" name="news_id" value="<?php echo $edit_news['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo $edit_news ? htmlspecialchars($edit_news['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" rows="10" required><?php echo $edit_news ? htmlspecialchars($edit_news['content']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Featured Image <?php echo $edit_news ? '(Leave blank to keep current image)' : ''; ?></label>
                    <input type="file" id="image" name="image" accept="image/*" <?php echo $edit_news ? '' : ''; ?>>
                    <?php if ($edit_news && $edit_news['image_path']): ?>
                        <div style="margin-top: 0.5rem;">
                            <strong>Current Image:</strong>
                            <img src="../<?php echo htmlspecialchars($edit_news['image_path']); ?>" alt="<?php echo htmlspecialchars($edit_news['title']); ?>" style="max-height: 100px; margin-top: 0.5rem; border-radius: 5px;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div class="checkbox">
                        <input type="checkbox" id="published" name="published" <?php echo $edit_news && $edit_news['published'] ? 'checked' : ''; ?>>
                        <label for="published">Published</label>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn"><?php echo $edit_news ? 'Update News' : 'Add News'; ?></button>
                    <?php if ($edit_news): ?>
                        <a href="news.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- News List -->
        <div class="admin-card">
            <h3>News Items</h3>
            <?php if (empty($news_items)): ?>
                <p>No news items found.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($news_items as $news): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($news['title']); ?></td>
                                <td>
                                    <?php if ($news['image_path']): ?>
                                        <img src="../<?php echo htmlspecialchars($news['image_path']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" style="max-height: 50px; max-width: 100px; border-radius: 3px;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="<?php echo $news['published'] ? 'btn-success' : 'btn-secondary'; ?> btn-small">
                                        <?php echo $news['published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($news['created_at'])); ?></td>
                                <td>
                                    <a href="news.php?edit=<?php echo $news['id']; ?>" class="btn btn-small">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this news item?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="news_id" value="<?php echo $news['id']; ?>">
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
