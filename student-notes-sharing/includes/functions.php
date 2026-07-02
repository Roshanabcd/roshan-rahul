<?php
/**
 * Helper Functions
 * 
 * Contains utility functions used throughout the application
 */

// Prevent direct access
if (!defined('ALLOW_ACCESS')) {
    define('ALLOW_ACCESS', true);
}

require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize user input
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool True if admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect to URL (FIXED: Uses JavaScript fallback if headers already sent)
 * 
 * @param string $url Target URL
 */
function redirect($url) {
    // Check if headers already sent
    if (headers_sent()) {
        // Use JavaScript redirect as fallback
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $url . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
        echo '</noscript>';
        echo '<p>Redirecting... <a href="' . $url . '">Click here</a> if not redirected.</p>';
        exit();
    } else {
        // Use PHP header redirect (normal behavior)
        header("Location: " . $url);
        exit();
    }
}

/**
 * Get user by ID
 * 
 * @param int $userId User ID
 * @return array|false User data or false
 */
function getUserById($userId) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting user: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all notes with optional filters
 * 
 * @param PDO $pdo Database connection
 * @param string $search Search term
 * @param string $semester Semester filter
 * @return array Notes data
 */
function getAllNotes($pdo, $search = '', $semester = '') {
    try {
        $sql = "SELECT n.*, u.name as uploader_name, 
                COALESCE(AVG(r.stars), 0) as avg_rating,
                COUNT(r.rating_id) as rating_count
                FROM notes n 
                INNER JOIN users u ON n.user_id = u.user_id 
                LEFT JOIN ratings r ON n.note_id = r.note_id
                WHERE n.status = 'active'";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (n.title LIKE :search_title 
                       OR n.subject LIKE :search_subject 
                       OR n.description LIKE :search_desc)";
            $searchTerm = "%{$search}%";
            $params['search_title'] = $searchTerm;
            $params['search_subject'] = $searchTerm;
            $params['search_desc'] = $searchTerm;
        }
        
        if (!empty($semester)) {
            $sql .= " AND n.semester = :semester";
            $params['semester'] = $semester;
        }
        
        $sql .= " GROUP BY n.note_id 
                  ORDER BY n.created_at DESC 
                  LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting notes: " . $e->getMessage());
        return [];
    }
}

/**
 * Get notes by specific user
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array User's notes
 */
function getNotesByUser($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT n.*, 
                               COALESCE(AVG(r.stars), 0) as avg_rating,
                               COUNT(r.rating_id) as rating_count
                               FROM notes n 
                               LEFT JOIN ratings r ON n.note_id = r.note_id
                               WHERE n.user_id = :user_id 
                               GROUP BY n.note_id 
                               ORDER BY n.created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting user notes: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user's rating for a note
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param int $noteId Note ID
 * @return int|false Rating value or false
 */
function getUserRating($pdo, $userId, $noteId) {
    try {
        $stmt = $pdo->prepare("SELECT stars FROM ratings WHERE user_id = :user_id AND note_id = :note_id LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'note_id' => $noteId]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (int)$result : false;
    } catch (PDOException $e) {
        error_log("Error getting rating: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all users (for admin)
 * 
 * @param PDO $pdo Database connection
 * @return array All users
 */
function getAllUsers($pdo) {
    try {
        $stmt = $pdo->query("SELECT u.*, COUNT(n.note_id) as note_count 
                             FROM users u 
                             LEFT JOIN notes n ON u.user_id = n.user_id 
                             GROUP BY u.user_id 
                             ORDER BY u.created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting users: " . $e->getMessage());
        return [];
    }
}

/**
 * Get distinct semesters from notes
 * 
 * @param PDO $pdo Database connection
 * @return array Semester list
 */
function getDistinctSemesters($pdo) {
    try {
        $stmt = $pdo->query("SELECT DISTINCT semester FROM notes WHERE semester != '' AND status = 'active' ORDER BY semester");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error getting semesters: " . $e->getMessage());
        return [];
    }
}

/**
 * Display flash message
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content (optional, uses session if not provided)
 * @return string HTML for flash message
 */
function showFlashMessage($type = 'info', $message = '') {
    // If message not provided, check session
    if (empty($message) && isset($_SESSION['flash_' . $type])) {
        $message = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
    }
    
    // Also check old style session keys
    if (empty($message) && isset($_SESSION[$type])) {
        $message = $_SESSION[$type];
        unset($_SESSION[$type]);
    }
    
    if (empty($message)) {
        return '';
    }
    
    $icons = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'danger' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle'
    ];
    
    $icon = isset($icons[$type]) ? $icons[$type] : 'fa-info-circle';
    
    return '
    <div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
        <i class="fas ' . $icon . ' me-2"></i>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

/**
 * Set flash message in session
 * 
 * @param string $type Message type
 * @param string $message Message content
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Validate file upload
 * 
 * @param array $file $_FILES array element
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return array ['valid' => bool, 'error' => string]
 */
function validateFileUpload($file, $allowedTypes = [], $maxSize = 10485760) {
    $result = ['valid' => false, 'error' => ''];
    
    // Check for upload errors
    if (!isset($file) || !is_array($file)) {
        $result['error'] = 'Please select a file to upload. No file was selected.';
        return $result;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload limit in server configuration.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'Please select a file to upload. No file was selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];
        
        $errorCode = $file['error'];
        $result['error'] = isset($errorMessages[$errorCode]) 
            ? $errorMessages[$errorCode] 
            : 'Unknown upload error. Code: ' . $errorCode;
        return $result;
    }
    
    // Check file type
    if (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes)) {
        $result['error'] = 'Invalid file type. Allowed types: PDF, DOCX, PPT.';
        return $result;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $result['error'] = 'File is too large. Maximum size: ' . ($maxSize / 1048576) . 'MB.';
        return $result;
    }
    
    $result['valid'] = true;
    return $result;
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    // Regenerate token after successful validation
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return true;
}

/**
 * Get file extension from filename
 * 
 * @param string $filename Filename
 * @return string Extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Generate unique filename
 * 
 * @param string $originalName Original filename
 * @return string Unique filename
 */
function generateUniqueFilename($originalName) {
    $extension = getFileExtension($originalName);
    return time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
}

/**
 * Format file size for display
 * 
 * @param int $bytes Size in bytes
 * @return string Formatted size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Check if request is AJAX
 * 
 * @return bool True if AJAX request
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
?>