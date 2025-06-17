<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();

    // Get all players grouped by team
    $stmt = $pdo->query("SELECT * FROM players ORDER BY team, name");
    $all_players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group players by team
    $players_by_team = [];
    foreach ($all_players as $player) {
        $players_by_team[$player['team']][] = $player;
    }

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

include 'includes/header.php';
?>


<!-- Hero Section -->
<section class="hero" style="height: 60vh;">
    <div class="hero-content">
        <h1>Our Players</h1>
        <p>Meet the talented athletes who make up our academy teams</p>
    </div>
</section>

<!-- Players Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Academy Players</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($players_by_team)): ?>
            <?php foreach ($players_by_team as $team => $players): ?>
                <div style="margin-bottom: 4rem;">
                    <h3 style="color: var(--bronze-brown); font-size: 2rem; margin-bottom: 2rem; text-align: center;">
                        <?php echo htmlspecialchars($team); ?>
                    </h3>

                    <div class="players-grid">
                        <?php foreach ($players as $player): ?>
                            <div class="player-card">
                                <div class="player-image">
                                    <?php if ($player['photo']): ?>
                                        <img src="<?php echo htmlspecialchars($player['photo']); ?>"
                                            alt="<?php echo htmlspecialchars($player['name']); ?>"
                                            style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        ⚽
                                    <?php endif; ?>
                                </div>
                                <div class="player-info">
                                    <h3><?php echo htmlspecialchars($player['name']); ?></h3>
                                    <p><strong>Age:</strong> <?php echo $player['age']; ?></p>
                                    <p><strong>Position:</strong> <?php echo htmlspecialchars($player['position']); ?></p>
                                    <?php if ($player['bio']): ?>
                                        <p><?php echo htmlspecialchars($player['bio']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="section-subtitle">
                <p>No players found. Check back soon as we update our roster!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Player Stats -->
<section class="section programs">
    <div class="container">
        <h2 class="section-title">Player Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo count($all_players); ?></h3>
                <p>Total Players</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($players_by_team); ?></h3>
                <p>Active Teams</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count(array_filter($all_players, function ($p) {
                    return $p['position'] == 'Forward';
                })); ?>
                </h3>
                <p>Forwards</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count(array_filter($all_players, function ($p) {
                    return $p['position'] == 'Midfielder';
                })); ?>
                </h3>
                <p>Midfielders</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count(array_filter($all_players, function ($p) {
                    return $p['position'] == 'Defender';
                })); ?>
                </h3>
                <p>Defenders</p>
            </div>
        </div>
    </div>
</section>

<!-- Join Our Academy -->
<section class="section">
    <div class="container">
        <div class="about-grid">
            <div class="about-content">
                <h2>Join Our Academy</h2>
                <p>Are you ready to take your football skills to the next level? Jane & Angel Football Academyis
                    always
                    looking for dedicated young players who are passionate about the game.</p>

                <h3>What We Look For:</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">⚽ Passion for football</li>
                    <li style="margin-bottom: 0.5rem;">🏃 Commitment to training</li>
                    <li style="margin-bottom: 0.5rem;">🤝 Team player attitude</li>
                    <li style="margin-bottom: 0.5rem;">📚 Good academic standing</li>
                    <li style="margin-bottom: 0.5rem;">💪 Willingness to learn and improve</li>
                </ul>

                <p>We welcome players of all skill levels and provide pathways for development from beginner to elite
                    level.</p>

                <a href="contact.php" class="btn">Apply Now</a>
            </div>
            <div class="about-image">
                <img src="/placeholder.svg?height=400&width=500" alt="Join our academy">
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>