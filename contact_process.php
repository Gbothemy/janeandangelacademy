<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate input
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        setFlashMessage('error', 'Please fill in all required fields');
        header('Location: contact.php');
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('error', 'Please enter a valid email address');
        header('Location: contact.php');
        exit();
    }
    
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        
        // Send notification email to admin (this would typically use a proper email library)
        $admin_email = 'admin@elitefootballacademy.com';
        $email_subject = "New Contact Form Submission: $subject";
        $email_message = "A new message has been submitted through the contact form:\n\n";
        $email_message .= "Name: $name\n";
        $email_message .= "Email: $email\n";
        $email_message .= "Phone: $phone\n";
        $email_message .= "Subject: $subject\n\n";
        $email_message .= "Message:\n$message\n";
        
        // Uncomment to send email when email functionality is set up
        // mail($admin_email, $email_subject, $email_message);
        
        setFlashMessage('success', 'Thank you for your message. We will get back to you soon!');
    } catch (PDOException $e) {
        setFlashMessage('error', 'There was an error sending your message. Please try again.');
    }
    
    header('Location: contact.php');
    exit();
}

// Redirect to contact page if accessed directly
header('Location: contact.php');
exit();
?>
