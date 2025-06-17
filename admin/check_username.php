<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Check if username is provided
if (!isset($_POST['username']) || empty($_POST['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Username is required']);
    exit();
}

$username = trim($_POST['username']);

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    
    $exists = $stmt->rowCount() > 0;
    
    header('Content-Type: application/json');
    echo json_encode(['exists' => $exists]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
