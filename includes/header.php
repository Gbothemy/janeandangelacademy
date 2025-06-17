<?php
// Get site settings
function getSiteSettings()
{
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE is_public = 1");
        $stmt->execute();
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        // Return default settings if database query fails
        return [
            'site_name' => 'Jane & Angel Football Academy',
            'site_description' => 'Professional football training for young athletes',
            'contact_email' => 'info@elitefootballacademy.com',
            'contact_phone' => '+1 (555) 123-4567',
            'social_facebook' => 'https://facebook.com/elitefootballacademy',
            'social_twitter' => 'https://twitter.com/elitefootball',
            'social_instagram' => 'https://instagram.com/elitefootballacademy'
        ];
    }
}

$settings = getSiteSettings();
$current_page = basename($_SERVER['PHP_SELF']);

// Page metadata fallback
$page_title = $page_title ?? '';
$site_name = htmlspecialchars($settings['site_name'] ?? 'Jane & Angel Football Academy');
$site_desc = htmlspecialchars($settings['site_description'] ?? 'Professional football training for young athletes');
$og_image = $og_image ?? 'assets/images/og-image.jpg';
$canonical_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $page_title ? htmlspecialchars($page_title) . ' - ' : '' ?><?= $site_name ?></title>
        <meta name="description" content="<?= $site_desc ?>">
        <meta name="robots" content="index, follow">
        <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

        <!-- Open Graph for social sharing -->
        <meta property="og:title"
            content="<?= $page_title ? htmlspecialchars($page_title) . ' - ' : '' ?><?= $site_name ?>">
        <meta property="og:description" content="<?= $site_desc ?>">
        <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
        <meta property="og:url" content="<?= $canonical_url ?>">
        <meta property="og:type" content="website">

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Bootstrap Icons (optional) -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


        <!-- CSS -->
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <!-- Header -->
        <header class="header">
            <div class="nav-container">
                <!-- Logo -->
                <a href="index.php" class="logo" aria-label="Home - <?= $site_name ?>">
                    <?php if (file_exists('assets/images/logo.jpg')): ?>
                        <img src="assets/images/logo.jpg" alt="<?= $site_name ?>" class="logo-img">
                    <?php else: ?>
                        <strong><?= $site_name ?></strong>
                    <?php endif; ?>
                </a>

                <!-- Mobile Nav Toggle -->
                <button class="mobile-menu-toggle" aria-label="Toggle navigation menu" aria-expanded="false"
                    aria-controls="main-menu">
                    <span></span><span></span><span></span>
                </button>

                <!-- Main Navigation -->
                <nav id="main-menu" role="navigation">
                    <ul class="nav-menu" aria-hidden="true">
                        <?php
                        $nav_items = [
                            'index.php' => 'Home',
                            'about.php' => 'About',
                            'players.php' => 'Players',
                            'gallery.php' => 'Media',
                            'contact.php' => 'Contact',
                        ];

                        foreach ($nav_items as $file => $label):
                            $active = $current_page === $file ? 'active' : '';
                            echo "<li><a href=\"$file\" class=\"$active\">$label</a></li>";
                        endforeach;

                        if (!empty($settings['registration_open']) && $settings['registration_open'] == '1'):
                            echo '<li><a href="register.php" class="btn btn-small">Register</a></li>';
                        endif;
                        ?>
                    </ul>
                </nav>
            </div>
        </header>