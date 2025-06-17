<?php
// Redirect to dashboard if logged in, otherwise to login page
require_once '../config/database.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
?>
