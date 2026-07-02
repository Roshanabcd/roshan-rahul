<?php
// =====================================================
// DATABASE CONFIGURATION
// =====================================================
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'a');
define('DB_NAME', 'business_directory');

// =====================================================
// SITE CONFIGURATION
// =====================================================
define('SITE_NAME', 'Smart Local Business Directory');
define('SITE_URL', 'http://localhost:8888/smart-business-directory/');
define('SITE_DESC', 'Find the best local businesses, read reviews, and get services');
define('ADMIN_EMAIL', 'admin@businessdirectory.com');
define('ADMIN_PHONE', '+977 9812345678');

// =====================================================
// UPLOAD PATHS
// =====================================================
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/smart-business-directory/');
define('UPLOAD_PATH', BASE_PATH . 'assets/uploads/');
define('UPLOAD_URL', SITE_URL . 'assets/uploads/');

define('BUSINESS_UPLOAD_PATH', UPLOAD_PATH . 'businesses/');
define('AVATAR_UPLOAD_PATH', UPLOAD_PATH . 'avatars/');
define('REVIEW_UPLOAD_PATH', UPLOAD_PATH . 'reviews/');
define('TEMP_UPLOAD_PATH', UPLOAD_PATH . 'temp/');

// =====================================================
// FILE UPLOAD LIMITS
// =====================================================
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,webp');
define('MAX_IMAGES_PER_BUSINESS', 20);
define('MAX_IMAGES_PER_REVIEW', 5);

// =====================================================
// PAGINATION SETTINGS
// =====================================================
define('BUSINESSES_PER_PAGE', 12);
define('REVIEWS_PER_PAGE', 10);
define('FEATURED_LIMIT', 8);
define('NEARBY_RADIUS', 10); // kilometers

// =====================================================
// SECURITY SETTINGS
// =====================================================
define('SESSION_TIMEOUT', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('PASSWORD_MIN_LENGTH', 6);

// =====================================================
// EMAIL CONFIGURATION
// =====================================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_ENCRYPTION', 'tls');

// =====================================================
// GOOGLE MAPS API
// =====================================================
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

// =====================================================
// CURRENCY SETTINGS
// =====================================================
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// =====================================================
// DATABASE CONNECTION
// =====================================================
function renderDatabaseError($message) {
    if (!headers_sent()) {
        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Temporarily Unavailable</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 40px; background: #f8fafc; color: #1f2937; }
        .box { max-width: 760px; margin: 0 auto; background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
        .hint { margin-top: 16px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Site Temporarily Unavailable</h1>
        <p>The application could not connect to the database right now.</p>
        <p><strong>Details:</strong> <code>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</code></p>
        <p class="hint">Please verify your MySQL server is running and that the database credentials in this project are correct.</p>
    </div>
</body>
</html>';
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF); // Prevent uncaught exceptions in PHP 8.1+ causing 500 errors
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    renderDatabaseError(mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// =====================================================
// TIMEZONE
// =====================================================
date_default_timezone_set('Asia/Kolkata');

// =====================================================
// SESSION START
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// ERROR REPORTING (Disable in production)
// =====================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =====================================================
// HELPER FUNCTIONS FOR CONFIG
// =====================================================

// Get setting from database
function getSetting($key, $default = '') {
    global $conn;
    $key = mysqli_real_escape_string($conn, $key);
    $query = "SELECT setting_value FROM system_settings WHERE setting_key = '$key'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $value = json_decode($row['setting_value'], true);
        return isset($value['value']) ? $value['value'] : $default;
    }
    return $default;
}

// Update setting
function updateSetting($key, $value) {
    global $conn;
    $key = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, json_encode(['value' => $value]));
    $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES ('$key', '$value')
              ON DUPLICATE KEY UPDATE setting_value = '$value'";
    return mysqli_query($conn, $query);
}

// Get site name from settings
$site_name = getSetting('site_name', SITE_NAME);
$site_desc = getSetting('site_description', SITE_DESC);
?>