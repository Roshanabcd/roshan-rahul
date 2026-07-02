<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$token = isset($_GET['token']) ? sanitize($_GET['token']) : '';

if (empty($token)) {
    redirect('login.php', 'Invalid verification link', 'error');
}

$query = "UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = '$token'";

if (mysqli_query($conn, $query) && mysqli_affected_rows($conn) > 0) {
    redirect('login.php', 'Email verified successfully! You can now login.', 'success');
} else {
    redirect('login.php', 'Invalid or expired verification link', 'error');
}
?>