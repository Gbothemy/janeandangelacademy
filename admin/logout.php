<?php
require_once '../config/database.php';

// Check if admin is logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Log the logout action
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['admin_id'],
            'logout',
            json_encode(['success' => true]),
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch (PDOException $e) {
        // Continue with logout even if logging fails
    }
}

// Destroy the session
session_start();
session_unset();
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>
