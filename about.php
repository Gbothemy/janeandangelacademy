<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();

    // Get some statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM players");
    $total_players = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query("SELECT COUNT(DISTINCT team) as count FROM players");
    $total_teams = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>About Us - Jane & Angel Football Academy</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>

        <!-- Hero Section -->
        <section class="hero" style="height: 60vh;">
            <div class="hero-content">
                <h1>About Jane & Angel Football Academy</h1>
                <p>Building champions on and off the field since 2024</p>
            </div>
        </section>

        <!-- About Content -->
        <section class="section">
            <div class="container">
                <div class="about-grid">
                    <div class="about-content">
                        <h2>Our Story</h2>
                        <p>Jane & Angel Football Academy was founded as a subsidiary of Jane & Angel football Nig
                            Ltd on
                            28th May2024 with a simple mission: to provide young
                            players
                            with the highest quality football training in a supportive, professional environment. What
                            started as a small local club has grown into one of the region's most respected youth
                            football academies.</p>

                        <p>Our founders, former professional players and experienced coaches, recognized the need for a
                            comprehensive development program that goes beyond just technical skills. We focus on
                            developing the whole player - technically, tactically, physically, and mentally.</p>

                        <h3>Our Philosophy</h3>
                        <p>We believe that every player has unique potential. Our role is to identify that potential and
                            provide the tools, training, and support needed to help each player reach their goals,
                            whether that's playing at the highest professional level or simply enjoying the beautiful
                            game.</p>
                    </div>
                    <div class="about-image">
                        <img src="/placeholder.svg?height=500&width=600" alt="Academy facilities">
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="section programs">
            <div class="container">
                <h2 class="section-title">Academy Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $total_players; ?></h3>
                        <p>Active Players</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $total_teams; ?></h3>
                        <p>Teams</p>
                    </div>
                    <div class="stat-card">
                        <h3>2+</h3>
                        <p>Years Experience</p>
                    </div>
                    <div class="stat-card">
                        <h3>0</h3>
                        <p>Trophies Won</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Vision -->
        <section class="section">
            <div class="container">
                <div class="programs-grid">
                    <div class="program-card">
                        <h3>Our Mission</h3>
                        <p>To develop young football players through professional coaching, state-of-the-art facilities,
                            and a commitment to excellence both on and off the field. We strive to create an environment
                            where players can reach their full potential while developing life skills that will serve
                            them beyond football.</p>
                    </div>
                    <div class="program-card">
                        <h3>Our Vision</h3>
                        <p>To be the leading youth football academy in the region, recognized for producing skilled,
                            confident, and well-rounded individuals who excel in football and in life. We aim to be the
                            pathway of choice for young players aspiring to reach the highest levels of the game.</p>
                    </div>
                    <div class="program-card">
                        <h3>Our Values</h3>
                        <p>Excellence, Integrity, Teamwork, Respect, and Dedication. These core values guide everything
                            we do, from our training methods to how we interact with players, parents, and the
                            community. We believe that success in football comes from strong character and unwavering
                            commitment.</p>
                    </div>
                </div>
            </div>
        </section>

        <?php include 'includes/footer.php'; ?>