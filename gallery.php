<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();

    // Get all gallery images
    $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
    $gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group images by category
    $images_by_category = [];
    foreach ($gallery_images as $image) {
        $images_by_category[$image['category']][] = $image;
    }

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="height: 60vh;">
    <div class="hero-content">
        <h1>Photo Gallery</h1>
        <p>Capturing moments of excellence, teamwork, and achievement</p>
    </div>
</section>

<!-- Gallery Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Academy Gallery</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($images_by_category)): ?>
            <?php foreach ($images_by_category as $category => $images): ?>
                <div style="margin-bottom: 4rem;">
                    <h3 style="color: var(--bronze-brown); font-size: 2rem; margin-bottom: 2rem; text-align: center;">
                        <?php echo htmlspecialchars(ucfirst($category)); ?>
                    </h3>

                    <div class="gallery-grid">
                        <?php foreach ($images as $image): ?>
                            <div class="gallery-item" data-category="training">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($image['title']); ?>"
                                    onclick="openModal('<?php echo htmlspecialchars($image['image_path']); ?>', '<?php echo htmlspecialchars($image['title']); ?>', '<?php echo htmlspecialchars($image['description']); ?>')">
                                <div class="gallery-overlay">
                                    <h4><?php echo htmlspecialchars($image['title']); ?></h4>
                                    <?php if ($image['description']): ?>
                                        <p><?php echo htmlspecialchars(substr($image['description'], 0, 50)) . '...'; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="section-subtitle">
                <p>No images available at the moment. Check back soon for updates!</p>
            </div>

            <!-- Placeholder gallery for demo -->
            <div class="gallery-grid">
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <div class="gallery-item">
                        <img src="/placeholder.svg?height=300&width=400" alt="Academy Photo <?php echo $i; ?>">
                        <div class="gallery-overlay">
                            <h4>Academy Photo <?php echo $i; ?></h4>
                            <p>Training session highlights</p>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Image Modal -->
<div id="imageModal" class="modal"
    style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9);">
    <div class="modal-content"
        style="position: relative; margin: auto; padding: 20px; width: 90%; max-width: 800px; top: 50%; transform: translateY(-50%);">
        <span class="close" onclick="closeModal()"
            style="position: absolute; top: 10px; right: 25px; color: white; font-size: 35px; font-weight: bold; cursor: pointer;">&times;</span>
        <img id="modalImage" style="width: 100%; height: auto; border-radius: 10px;">
        <div style="color: white; text-align: center; margin-top: 1rem;">
            <h3 id="modalTitle"></h3>
            <p id="modalDescription"></p>
        </div>
    </div>
</div>

<!-- Categories Info -->
<section class="section programs">
    <div class="container">
        <h2 class="section-title">Gallery Categories</h2>
        <div class="programs-grid">
            <div class="program-card">
                <h3>Training Sessions</h3>
                <p>Behind-the-scenes photos from our daily training sessions, showcasing player development and
                    coaching excellence.</p>
            </div>
            <div class="program-card">
                <h3>Matches & Tournaments</h3>
                <p>Action shots from competitive matches and tournament victories, capturing the excitement of
                    game day.</p>
            </div>
            <div class="program-card">
                <h3>Team Events</h3>
                <p>Special events, team bonding activities, award ceremonies, and celebrations of our academy
                    community.</p>
            </div>
            <div class="program-card">
                <h3>Facilities</h3>
                <p>Our state-of-the-art training facilities, pitches, and equipment that provide the perfect
                    environment for development.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>