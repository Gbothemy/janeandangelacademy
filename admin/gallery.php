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
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    if (!in_array($_FILES['image']['type'], $allowed_types)) {
                        $error = 'Invalid file type. Allowed types: JPG, PNG, GIF';
                    } elseif ($_FILES['image']['size'] > $max_size) {
                        $error = 'File size exceeds the limit of 5MB';
                    } else {
                        // Create uploads directory if it doesn't exist
                        $upload_dir = '../uploads/gallery/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        // Generate unique filename
                        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $new_filename = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image_path, category) VALUES (?, ?, ?, ?)");
                            $stmt->execute([
                                $_POST['title'],
                                $_POST['description'],
                                'uploads/gallery/' . $new_filename,
                                $_POST['category']
                            ]);
                            $message = 'Gallery image added successfully!';
                        } else {
                            $error = 'Failed to upload image. Please try again.';
                        }
                    }
                } else {
                    $error = 'Please select an image to upload.';
                }
            } elseif ($_POST['action'] == 'edit') {
                $updates = [];
                $params = [];
                
                $updates[] = "title = ?";
                $params[] = $_POST['title'];
                
                $updates[] = "description = ?";
                $params[] = $_POST['description'];
                
                $updates[] = "category = ?";
                $params[] = $_POST['category'];
                
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
                        $upload_dir = '../uploads/gallery/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        // Generate unique filename
                        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $new_filename = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Get old image path to delete
                            $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
                            $stmt->execute([$_POST['gallery_id']]);
                            $old_image = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            // Delete old image if it exists
                            if ($old_image && file_exists('../' . $old_image['image_path'])) {
                                unlink('../' . $old_image['image_path']);
                            }
                            
                            $updates[] = "image_path = ?";
                            $params[] = 'uploads/gallery/' . $new_filename;
                        } else {
                            $error = 'Failed to upload image. Please try again.';
                        }
                    }
                }
                
                if (empty($error)) {
                    $params[] = $_POST['gallery_id'];
                    $stmt = $pdo->prepare("UPDATE gallery SET " . implode(", ", $updates) . " WHERE id = ?");
                    $stmt->execute($params);
                    $message = 'Gallery image updated successfully!';
                }
            } elseif ($_POST['action'] == 'delete' && isset($_POST['gallery_id'])) {
                // Get image path before deleting record
                $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
                $stmt->execute([$_POST['gallery_id']]);
                $image = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete from database
                $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
                $stmt->execute([$_POST['gallery_id']]);
                
                // Delete image file if it exists
                if ($image && file_exists('../' . $image['image_path'])) {
                    unlink('../' . $image['image_path']);
                }
                
                $message = 'Gallery image deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all gallery images
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM gallery ORDER BY category, created_at DESC");
    $gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching gallery images: ' . $e->getMessage();
}

// Get image to edit
$edit_image = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_image = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching image: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - Elite Football Academy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .gallery-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .gallery-admin-item {
            border: 1px solid var(--light-lavender);
            border-radius: 5px;
            overflow: hidden;
        }
        .gallery-admin-image {
            height: 180px;
            overflow: hidden;
        }
        .gallery-admin-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .gallery-admin-info {
            padding: 0.75rem;
        }
        .gallery-admin-info h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
        }
        .gallery-admin-info p {
            margin: 0 0 0.5rem 0;
            font-size: 0.9rem;
            color: var(--light-text);
        }
        .gallery-admin-actions {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
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
            <li><a href="gallery.php" class="active">Gallery</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li><a href="payments.php">Payments</a></li>
            <li><a href="news.php">News</a></li>
            <li><a href="media.php">Media</a></li>
            <li><a href="users.php">Users</a></li>
        </ul>
    </nav>

    <!-- Admin Content -->
    <div class="admin-content">
        <h2>Manage Gallery</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Add/Edit Gallery Image Form -->
        <div class="admin-card">
            <h3><?php echo $edit_image ? 'Edit Gallery Image' : 'Add New Gallery Image'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_image ? 'edit' : 'add'; ?>">
                <?php if ($edit_image): ?>
                    <input type="hidden" name="gallery_id" value="<?php echo $edit_image['id']; ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo $edit_image ? htmlspecialchars($edit_image['title']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="training" <?php echo $edit_image && $edit_image['category'] == 'training' ? 'selected' : ''; ?>>Training Sessions</option>
                            <option value="matches" <?php echo $edit_image && $edit_image['category'] == 'matches' ? 'selected' : ''; ?>>Matches & Tournaments</option>
                            <option value="events" <?php echo $edit_image && $edit_image['category'] == 'events' ? 'selected' : ''; ?>>Team Events</option>
                            <option value="facilities" <?php echo $edit_image && $edit_image['category'] == 'facilities' ? 'selected' : ''; ?>>Facilities</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?php echo $edit_image ? htmlspecialchars($edit_image['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Image <?php echo $edit_image ? '(Leave blank to keep current image)' : ''; ?></label>
                    <input type="file" id="image" name="image" accept="image/*" <?php echo $edit_image ? '' : 'required'; ?>>
                    <?php if ($edit_image): ?>
                        <div style="margin-top: 0.5rem;">
                            <strong>Current Image:</strong>
                            <img src="../<?php echo htmlspecialchars($edit_image['image_path']); ?>" alt="<?php echo htmlspecialchars($edit_image['title']); ?>" style="max-height: 100px; margin-top: 0.5rem; border-radius: 5px;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn"><?php echo $edit_image ? 'Update Image' : 'Add Image'; ?></button>
                    <?php if ($edit_image): ?>
                        <a href="gallery.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Gallery Images List -->
        <div class="admin-card">
            <h3>Gallery Images</h3>
            <?php if (empty($gallery_images)): ?>
                <p>No gallery images found.</p>
            <?php else: ?>
                <div class="gallery-admin-grid">
                    <?php foreach ($gallery_images as $image): ?>
                        <div class="gallery-admin-item">
                            <div class="gallery-admin-image">
                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>">
                            </div>
                            <div class="gallery-admin-info">
                                <h4><?php echo htmlspecialchars($image['title']); ?></h4>
                                <p><strong>Category:</strong> <?php echo ucfirst(htmlspecialchars($image['category'])); ?></p>
                                <p><strong>Added:</strong> <?php echo date('M j, Y', strtotime($image['created_at'])); ?></p>
                            </div>
                            <div class="gallery-admin-actions">
                                <a href="gallery.php?edit=<?php echo $image['id']; ?>" class="btn btn-small">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="gallery_id" value="<?php echo $image['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Add this before the closing </body> tag -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
