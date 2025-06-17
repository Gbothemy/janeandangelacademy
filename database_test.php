<?php
require_once 'config/database.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test basic connection
    $pdo = getConnection();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test if tables exist
    $tables = [
        'admin_users',
        'players', 
        'contact_messages',
        'gallery',
        'news',
        'schedule',
        'payments',
        'admin_logs',
        'subscribers',
        'settings',
        'testimonials',
        'events'
    ];
    
    echo "<h2>Table Status:</h2>";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✓ Table '$table' exists with $count records</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Table '$table' missing or error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test admin user
    echo "<h2>Admin User Test:</h2>";
    $stmt = $pdo->query("SELECT username, email, role FROM admin_users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p style='color: green;'>✓ Admin user found: " . htmlspecialchars($admin['username']) . " (" . htmlspecialchars($admin['email']) . ")</p>";
        echo "<p><strong>Default login credentials:</strong><br>";
        echo "Username: admin<br>";
        echo "Password: admin123</p>";
    } else {
        echo "<p style='color: red;'>✗ No admin user found</p>";
    }
    
    // Test settings
    echo "<h2>Settings Test:</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    $settingsCount = $stmt->fetchColumn();
    echo "<p style='color: green;'>✓ Found $settingsCount settings in database</p>";
    
    echo "<h2>Next Steps:</h2>";
    echo "<ol>";
    echo "<li><a href='index.php'>Visit the main website</a></li>";
    echo "<li><a href='admin/login.php'>Login to admin panel</a> (username: admin, password: admin123)</li>";
    echo "<li>Delete this test file (database_test.php) for security</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h2>Troubleshooting Steps:</h2>";
    echo "<ol>";
    echo "<li>Make sure MySQL/MariaDB server is running</li>";
    echo "<li>Check database credentials in config/database.php</li>";
    echo "<li>Run the database setup script: scripts/complete_database_setup.sql</li>";
    echo "<li>Ensure the 'football_academy' database exists</li>";
    echo "<li>Check file permissions for uploads directory</li>";
    echo "</ol>";
}
?>
