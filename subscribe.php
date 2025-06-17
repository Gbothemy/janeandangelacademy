<?php
require_once 'config/database.php';

// Handle newsletter subscription
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('error', 'Please enter a valid email address.');
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
        exit();
    }
    
    try {
        $pdo = getConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id, status FROM subscribers WHERE email = ?");
        $stmt->execute([$email]);
        $subscriber = $stmt->fetch();
        
        if ($subscriber) {
            if ($subscriber['status'] == 'unsubscribed') {
                // Re-subscribe
                $stmt = $pdo->prepare("UPDATE subscribers SET status = 'active', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$subscriber['id']]);
                setFlashMessage('success', 'You have been re-subscribed to our newsletter.');
            } else {
                setFlashMessage('info', 'You are already subscribed to our newsletter.');
            }
        } else {
            // Generate confirmation token
            $token = bin2hex(random_bytes(32));
            
            // Add new subscriber
            $stmt = $pdo->prepare("INSERT INTO subscribers (email, name, confirmation_token) VALUES (?, ?, ?)");
            $stmt->execute([$email, $name, $token]);
            
            // Send confirmation email (this would typically use a proper email library)
            $subject = 'Confirm your subscription to Elite Football Academy newsletter';
            $message = "Thank you for subscribing to our newsletter!\n\n";
            $message .= "Please confirm your subscription by clicking the link below:\n";
            $message .= "https://elitefootballacademy.com/confirm-subscription.php?token=$token\n\n";
            $message .= "If you did not request this subscription, you can ignore this email.";
            
            // Uncomment to send email when email functionality is set up
            // mail($email, $subject, $message);
            
            setFlashMessage('success', 'Thank you for subscribing! Please check your email to confirm your subscription.');
        }
    } catch (PDOException $e) {
        setFlashMessage('error', 'An error occurred. Please try again later.');
    }
    
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
    exit();
}

// Handle subscription confirmation
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE confirmation_token = ?");
        $stmt->execute([$token]);
        $subscriber = $stmt->fetch();
        
        if ($subscriber) {
            $stmt = $pdo->prepare("UPDATE subscribers SET confirmation_token = NULL, confirmed_at = NOW() WHERE id = ?");
            $stmt->execute([$subscriber['id']]);
            setFlashMessage('success', 'Your subscription has been confirmed. Thank you!');
        } else {
            setFlashMessage('error', 'Invalid or expired confirmation token.');
        }
    } catch (PDOException $e) {
        setFlashMessage('error', 'An error occurred. Please try again later.');
    }
    
    header('Location: index.php');
    exit();
}

// Handle unsubscribe
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['unsubscribe']) && isset($_GET['email'])) {
    $email = filter_var(trim($_GET['email']), FILTER_SANITIZE_EMAIL);
    
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("UPDATE subscribers SET status = 'unsubscribed' WHERE email = ?");
        $stmt->execute([$email]);
        
        setFlashMessage('success', 'You have been unsubscribed from our newsletter.');
    } catch (PDOException $e) {
        setFlashMessage('error', 'An error occurred. Please try again later.');
    }
    
    header('Location: index.php');
    exit();
}

// Redirect to home page if accessed directly
header('Location: index.php');
exit();
?>
