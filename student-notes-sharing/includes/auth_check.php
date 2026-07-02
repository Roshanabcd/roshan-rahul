<?php
/**
 * Authentication Check
 * 
 * Include this file to protect pages that require login
 */

require_once __DIR__ . '/functions.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to access this page.";
    redirect('login.php');
}

// Optional: Check for specific role
if (isset($required_role)) {
    if ($required_role === 'admin' && !isAdmin()) {
        $_SESSION['error'] = "Access denied. Admin privileges required.";
        redirect('dashboard.php');
    }
}
?>