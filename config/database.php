<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'football_academy');

// Create database connection
function getConnection()
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log the error for debugging
        error_log("Database connection failed: " . $e->getMessage());

        // Show user-friendly error message
        die("Database connection failed. Please check your database configuration and ensure the database server is running.");
    }
}

// Test database connection
function testConnection()
{
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function redirect($url)
{
    header("Location: $url");
    exit();
}

function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function isLoggedIn()
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function hasRole($role)
{
    return isLoggedIn() && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === $role;
}

function isAdmin()
{
    return hasRole('admin');
}

function isEditor()
{
    return hasRole('admin') || hasRole('editor');
}

function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generateToken()
{
    return bin2hex(random_bytes(32));
}

function validateToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function logAdminAction($action, $details = null)
{
    if (!isLoggedIn())
        return false;

    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['admin_id'],
            $action,
            $details ? json_encode($details) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Failed to log admin action: " . $e->getMessage());
        return false;
    }
}

// Create uploads directory if it doesn't exist
function ensureUploadDirectories()
{
    $directories = [
        'uploads',
        'uploads/players',
        'uploads/gallery',
        'uploads/news',
        'uploads/testimonials'
    ];

    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Call this function to ensure directories exist
ensureUploadDirectories();
?>