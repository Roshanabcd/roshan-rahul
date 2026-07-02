<?php
/**
 * Database Configuration File
 * 
 * This file contains database connection settings and functions
 */

// Prevent direct access
if (!defined('ALLOW_ACCESS')) {
    define('ALLOW_ACCESS', true);
}

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_notes_sharing');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection
 * 
 * @return PDO Database connection object
 * @throws PDOException If connection fails
 */
function getDBConnection() {
    static $pdo = null;
    
    // Return existing connection if available
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error (don't expose details in production)
        error_log("Database Connection Failed: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

/**
 * Start session if not already started
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>