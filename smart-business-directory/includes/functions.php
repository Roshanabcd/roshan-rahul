<?php
require_once 'config.php';

// =====================================================
// SANITIZATION FUNCTIONS
// =====================================================

function sanitize($data) {
    global $conn;
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8'));
}

function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function sanitizeUrl($url) {
    return filter_var(trim($url), FILTER_SANITIZE_URL);
}

function sanitizePhone($phone) {
    return preg_replace('/[^0-9+]/', '', $phone);
}

// =====================================================
// VALIDATION FUNCTIONS
// =====================================================

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone) || preg_match('/^\+[0-9]{10,15}$/', $phone);
}

function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

function validatePincode($pincode) {
    return preg_match('/^[0-9]{6}$/', $pincode);
}

// =====================================================
// AUTHENTICATION FUNCTIONS
// =====================================================

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] == $role;
}

function isAdmin() {
    return hasRole('admin');
}

function isBusinessOwner() {
    return hasRole('business_owner');
}

function isUser() {
    return hasRole('user');
}

function redirect($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION[$type] = $message;
    }
    header("Location: " . SITE_URL . ltrim($url, '/'));
    exit();
}

// =====================================================
// MESSAGE FUNCTIONS
// =====================================================

function showSuccess($message) {
    return '<div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

function showError($message) {
    return '<div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Error!</strong> ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

function showWarning($message) {
    return '<div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

function showInfo($message) {
    return '<div class="alert alert-info alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Info!</strong> ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

// =====================================================
// CATEGORY FUNCTIONS
// =====================================================

function getAllCategories($activeOnly = true) {
    global $conn;
    $sql = "SELECT * FROM categories";
    if ($activeOnly) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY display_order, cat_name";
    $result = mysqli_query($conn, $sql);
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    return $categories;
}

function getMainCategories() {
    global $conn;
    $query = "SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY display_order";
    $result = mysqli_query($conn, $query);
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    return $categories;
}

function getSubCategories($parent_id) {
    global $conn;
    $query = "SELECT * FROM categories WHERE parent_id = $parent_id AND is_active = 1 ORDER BY cat_name";
    $result = mysqli_query($conn, $query);
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    return $categories;
}

function getCategoryName($cat_id) {
    global $conn;
    $query = "SELECT cat_name FROM categories WHERE cat_id = $cat_id";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['cat_name'];
    }
    return 'Uncategorized';
}

function getCategoryBySlug($slug) {
    global $conn;
    $slug = sanitize($slug);
    $query = "SELECT * FROM categories WHERE cat_slug = '$slug'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// =====================================================
// BUSINESS FUNCTIONS
// =====================================================

function getBusinessRating($biz_id) {
    global $conn;
    $query = "SELECT 
                COALESCE(AVG(rating), 0) as avg_rating, 
                COUNT(*) as total_reviews,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
              FROM reviews 
              WHERE biz_id = $biz_id AND is_approved = 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return [
        'rating' => round($row['avg_rating'], 1),
        'total' => $row['total_reviews'],
        'five_star' => $row['five_star'],
        'four_star' => $row['four_star'],
        'three_star' => $row['three_star'],
        'two_star' => $row['two_star'],
        'one_star' => $row['one_star'],
        'percentage' => $row['total_reviews'] > 0 ? round(($row['avg_rating'] / 5) * 100) : 0
    ];
}

function getBusinessById($biz_id) {
    global $conn;
    $query = "SELECT b.*, c.cat_name, c.cat_slug, u.fullname as owner_name, u.email as owner_email, u.phone as owner_phone
              FROM businesses b 
              JOIN categories c ON b.cat_id = c.cat_id 
              JOIN users u ON b.owner_id = u.user_id
              WHERE b.biz_id = $biz_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getBusinessBySlug($slug) {
    global $conn;
    $slug = sanitize($slug);
    $query = "SELECT b.*, c.cat_name, c.cat_slug, u.fullname as owner_name 
              FROM businesses b 
              JOIN categories c ON b.cat_id = c.cat_id 
              JOIN users u ON b.owner_id = u.user_id 
              WHERE b.slug = '$slug' AND b.status IN ('approved', 'featured')";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getBusinessImages($biz_id) {
    global $conn;
    $query = "SELECT * FROM business_images WHERE biz_id = $biz_id ORDER BY is_primary DESC, display_order";
    $result = mysqli_query($conn, $query);
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row;
    }
    return $images;
}

function getFeaturedBusinesses($limit = FEATURED_LIMIT) {
    global $conn;
    $query = "SELECT b.*, c.cat_name,
              COALESCE(AVG(r.rating), 0) as avg_rating,
              COUNT(r.review_id) as review_count
              FROM businesses b 
              JOIN categories c ON b.cat_id = c.cat_id 
              LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
              WHERE b.status = 'approved' AND b.is_featured = 1
              GROUP BY b.biz_id
              ORDER BY b.featured_until DESC, b.created_at DESC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    $businesses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $businesses[] = $row;
    }
    return $businesses;
}

function getRecentBusinesses($limit = 12) {
    global $conn;
    $query = "SELECT b.*, c.cat_name,
              COALESCE(AVG(r.rating), 0) as avg_rating,
              COUNT(r.review_id) as review_count
              FROM businesses b 
              JOIN categories c ON b.cat_id = c.cat_id 
              LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
              WHERE b.status = 'approved'
              GROUP BY b.biz_id
              ORDER BY b.created_at DESC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    $businesses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $businesses[] = $row;
    }
    return $businesses;
}

function getPopularBusinesses($limit = 12) {
    global $conn;
    $query = "SELECT b.*, c.cat_name,
              COALESCE(AVG(r.rating), 0) as avg_rating,
              COUNT(r.review_id) as review_count
              FROM businesses b 
              JOIN categories c ON b.cat_id = c.cat_id 
              LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
              WHERE b.status = 'approved'
              GROUP BY b.biz_id
              ORDER BY b.total_views DESC, b.total_favorites DESC
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    $businesses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $businesses[] = $row;
    }
    return $businesses;
}

function getNearbyBusinesses($lat, $lng, $radius = NEARBY_RADIUS, $limit = 20) {
    global $conn;
    $query = "SELECT b.*, c.cat_name,
              (6371 * acos(cos(radians($lat)) * cos(radians(b.latitude)) * 
              cos(radians(b.longitude) - radians($lng)) + 
              sin(radians($lat)) * sin(radians(b.latitude)))) AS distance,
              COALESCE(AVG(r.rating), 0) as avg_rating,
              COUNT(r.review_id) as review_count
              FROM businesses b 
              JOIN categories c ON b.cat_id = c.cat_id 
              LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
              WHERE b.status = 'approved' 
                AND b.latitude IS NOT NULL 
                AND b.longitude IS NOT NULL
              GROUP BY b.biz_id
              HAVING distance < $radius 
              ORDER BY distance 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    $businesses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $businesses[] = $row;
    }
    return $businesses;
}

function incrementBusinessView($biz_id) {
    global $conn;
    $today = date('Y-m-d');
    $query = "UPDATE businesses SET 
              total_views = total_views + 1,
              views_today = views_today + 1,
              views_week = views_week + 1,
              views_month = views_month + 1
              WHERE biz_id = $biz_id";
    return mysqli_query($conn, $query);
}

// =====================================================
// REVIEW FUNCTIONS
// =====================================================

function getBusinessReviews($biz_id, $limit = REVIEWS_PER_PAGE, $offset = 0) {
    global $conn;
    $query = "SELECT r.*, u.fullname, u.profile_image 
              FROM reviews r 
              JOIN users u ON r.user_id = u.user_id 
              WHERE r.biz_id = $biz_id AND r.is_approved = 1 
              ORDER BY r.helpful_count DESC, r.created_at DESC 
              LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $query);
    $reviews = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
    return $reviews;
}

function addReview($biz_id, $user_id, $rating, $comment, $title = '') {
    global $conn;
    $rating = (int)$rating;
    $comment = sanitize($comment);
    $title = sanitize($title);
    
    $query = "INSERT INTO reviews (biz_id, user_id, rating, title, comment) 
              VALUES ($biz_id, $user_id, $rating, '$title', '$comment')";
    
    if (mysqli_query($conn, $query)) {
        // Update business rating
        $rating_data = getBusinessRating($biz_id);
        $update_query = "UPDATE businesses SET 
                         average_rating = {$rating_data['rating']}, 
                         total_reviews = {$rating_data['total']} 
                         WHERE biz_id = $biz_id";
        mysqli_query($conn, $update_query);
        return true;
    }
    return false;
}

function isUserReviewed($user_id, $biz_id) {
    global $conn;
    $query = "SELECT review_id FROM reviews WHERE user_id = $user_id AND biz_id = $biz_id";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

function getUserReview($user_id, $biz_id) {
    global $conn;
    $query = "SELECT * FROM reviews WHERE user_id = $user_id AND biz_id = $biz_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function updateReview($review_id, $rating, $comment, $title = '') {
    global $conn;
    $rating = (int)$rating;
    $comment = sanitize($comment);
    $title = sanitize($title);
    
    $query = "UPDATE reviews SET rating = $rating, title = '$title', comment = '$comment', updated_at = NOW() 
              WHERE review_id = $review_id";
    return mysqli_query($conn, $query);
}

function deleteReview($review_id, $biz_id) {
    global $conn;
    $query = "DELETE FROM reviews WHERE review_id = $review_id";
    if (mysqli_query($conn, $query)) {
        // Update business rating
        $rating_data = getBusinessRating($biz_id);
        $update_query = "UPDATE businesses SET 
                         average_rating = {$rating_data['rating']}, 
                         total_reviews = {$rating_data['total']} 
                         WHERE biz_id = $biz_id";
        mysqli_query($conn, $update_query);
        return true;
    }
    return false;
}

function markReviewHelpful($review_id, $user_id) {
    global $conn;
    // Check if already voted
    $check = "SELECT id FROM review_helpful WHERE review_id = $review_id AND user_id = $user_id";
    $result = mysqli_query($conn, $check);
    if (mysqli_num_rows($result) > 0) {
        return false;
    }
    
    $query = "INSERT INTO review_helpful (review_id, user_id, vote_type) VALUES ($review_id, $user_id, 'helpful')";
    if (mysqli_query($conn, $query)) {
        mysqli_query($conn, "UPDATE reviews SET helpful_count = helpful_count + 1 WHERE review_id = $review_id");
        return true;
    }
    return false;
}

// =====================================================
// FAVORITE FUNCTIONS
// =====================================================

function isFavorited($user_id, $biz_id) {
    global $conn;
    $query = "SELECT * FROM favorites WHERE user_id = $user_id AND biz_id = $biz_id";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

function addFavorite($user_id, $biz_id) {
    global $conn;
    if (!isFavorited($user_id, $biz_id)) {
        $query = "INSERT INTO favorites (user_id, biz_id) VALUES ($user_id, $biz_id)";
        return mysqli_query($conn, $query);
    }
    return false;
}

function removeFavorite($user_id, $biz_id) {
    global $conn;
    $query = "DELETE FROM favorites WHERE user_id = $user_id AND biz_id = $biz_id";
    return mysqli_query($conn, $query);
}

function getUserFavorites($user_id, $limit = 20, $offset = 0) {
    global $conn;
    $query = "SELECT b.*, c.cat_name,
              COALESCE(AVG(r.rating), 0) as avg_rating,
              COUNT(r.review_id) as review_count
              FROM favorites f
              JOIN businesses b ON f.biz_id = b.biz_id
              JOIN categories c ON b.cat_id = c.cat_id
              LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
              WHERE f.user_id = $user_id AND b.status = 'approved'
              GROUP BY b.biz_id
              ORDER BY f.created_at DESC
              LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $query);
    $favorites = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $favorites[] = $row;
    }
    return $favorites;
}

function getFavoritesCount($user_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM favorites WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// =====================================================
// SUPPORT FUNCTIONS
// =====================================================

function createSupportRequest($data) {
    global $conn;
    $user_id = $data['user_id'];
    $title = sanitize($data['title']);
    $description = sanitize($data['description']);
    $category = sanitize($data['category']);
    $urgency = sanitize($data['urgency']);
    $address = sanitize($data['address']);
    $city = sanitize($data['city']);
    
    $query = "INSERT INTO support_requests (user_id, title, description, category, urgency, address, city) 
              VALUES ($user_id, '$title', '$description', '$category', '$urgency', '$address', '$city')";
    
    if (mysqli_query($conn, $query)) {
        return mysqli_insert_id($conn);
    }
    return false;
}

function getSupportRequests($user_id, $limit = 10, $offset = 0) {
    global $conn;
    $query = "SELECT sr.*, b.biz_name as assigned_business_name
              FROM support_requests sr
              LEFT JOIN businesses b ON sr.assigned_biz_id = b.biz_id
              WHERE sr.user_id = $user_id
              ORDER BY sr.created_at DESC
              LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $query);
    $requests = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
    return $requests;
}

function getOpenSupportRequestsNearby($business_id, $lat, $lng, $radius = 20) {
    global $conn;
    // Get business location first
    $biz_query = "SELECT latitude, longitude FROM businesses WHERE biz_id = $business_id";
    $biz_result = mysqli_query($conn, $biz_query);
    $business = mysqli_fetch_assoc($biz_result);
    
    if (!$business['latitude'] || !$business['longitude']) {
        return [];
    }
    
    $lat = $business['latitude'];
    $lng = $business['longitude'];
    
    $query = "SELECT sr.*, 
              (6371 * acos(cos(radians($lat)) * cos(radians(sr.latitude)) * 
              cos(radians(sr.longitude) - radians($lng)) + 
              sin(radians($lat)) * sin(radians(sr.latitude)))) AS distance
              FROM support_requests sr
              WHERE sr.status = 'open' 
                AND sr.latitude IS NOT NULL 
                AND sr.longitude IS NOT NULL
              HAVING distance < $radius
              ORDER BY distance, sr.urgency DESC, sr.created_at ASC
              LIMIT 50";
    $result = mysqli_query($conn, $query);
    $requests = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
    return $requests;
}

// =====================================================
// CHAT FUNCTIONS
// =====================================================

function sendMessage($sender_id, $receiver_id, $message) {
    global $conn;
    $conversation_id = generateConversationId($sender_id, $receiver_id);
    $message = sanitize($message);
    
    $query = "INSERT INTO chat_messages (conversation_id, sender_id, receiver_id, message) 
              VALUES ('$conversation_id', $sender_id, $receiver_id, '$message')";
    
    if (mysqli_query($conn, $query)) {
        // Create notification for receiver
        createNotification($receiver_id, 'message', 'New Message', 'You have a new message from user', SITE_URL . 'chat/');
        return mysqli_insert_id($conn);
    }
    return false;
}

function generateConversationId($user1, $user2) {
    return $user1 < $user2 ? $user1 . '_' . $user2 : $user2 . '_' . $user1;
}

function getConversations($user_id) {
    global $conn;
    $query = "SELECT conv.other_user_id, u.fullname, u.profile_image, m.message AS last_message, m.created_at AS last_time, 
                     COALESCE(unread_counts.unread_count, 0) AS unread_count
              FROM (
                  SELECT conversation_id,
                         CASE WHEN sender_id = $user_id THEN receiver_id ELSE sender_id END AS other_user_id,
                         MAX(created_at) AS last_time
                  FROM chat_messages
                  WHERE sender_id = $user_id OR receiver_id = $user_id
                  GROUP BY conversation_id
              ) AS conv
              JOIN users u ON u.user_id = conv.other_user_id
              LEFT JOIN chat_messages m ON m.conversation_id = conv.conversation_id AND m.created_at = conv.last_time
              LEFT JOIN (
                  SELECT conversation_id, COUNT(*) AS unread_count
                  FROM chat_messages
                  WHERE receiver_id = $user_id AND is_read = 0
                  GROUP BY conversation_id
              ) AS unread_counts ON unread_counts.conversation_id = conv.conversation_id
              ORDER BY conv.last_time DESC";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        error_log('getConversations query failed: ' . mysqli_error($conn));
        return [];
    }
    $conversations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $conversations[] = $row;
    }
    return $conversations;
}

function getMessages($user_id, $other_user_id, $limit = 50, $offset = 0) {
    global $conn;
    $conversation_id = generateConversationId($user_id, $other_user_id);
    
    // Mark messages as read
    mysqli_query($conn, "UPDATE chat_messages SET is_read = 1, read_at = NOW() 
                         WHERE conversation_id = '$conversation_id' AND receiver_id = $user_id");
    
    $query = "SELECT cm.*, u.fullname, u.profile_image
              FROM chat_messages cm
              JOIN users u ON cm.sender_id = u.user_id
              WHERE cm.conversation_id = '$conversation_id'
              ORDER BY cm.created_at ASC
              LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $query);
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
    return $messages;
}

function getUnreadCount($user_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM chat_messages WHERE receiver_id = $user_id AND is_read = 0";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// =====================================================
// NOTIFICATION FUNCTIONS
// =====================================================

function createNotification($user_id, $type, $title, $message, $link = '') {
    global $conn;
    $title = sanitize($title);
    $message = sanitize($message);
    $link = sanitize($link);
    
    $query = "INSERT INTO notifications (user_id, type, title, message, link) 
              VALUES ($user_id, '$type', '$title', '$message', '$link')";
    return mysqli_query($conn, $query);
}

function getUserNotifications($user_id, $limit = 20, $offset = 0) {
    global $conn;
    $query = "SELECT * FROM notifications 
              WHERE user_id = $user_id 
              ORDER BY created_at DESC 
              LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $query);
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    return $notifications;
}

function markNotificationRead($notif_id, $user_id) {
    global $conn;
    $query = "UPDATE notifications SET is_read = 1, read_at = NOW() 
              WHERE notif_id = $notif_id AND user_id = $user_id";
    return mysqli_query($conn, $query);
}

function markAllNotificationsRead($user_id) {
    global $conn;
    $query = "UPDATE notifications SET is_read = 1, read_at = NOW() 
              WHERE user_id = $user_id AND is_read = 0";
    return mysqli_query($conn, $query);
}

function getUnreadNotificationCount($user_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = $user_id AND is_read = 0";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// =====================================================
// UTILITY FUNCTIONS
// =====================================================

function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function generateUniqueSlug($table, $field, $string) {
    global $conn;
    $slug = createSlug($string);
    $original_slug = $slug;
    $counter = 1;
    
    while (true) {
        $query = "SELECT COUNT(*) as count FROM $table WHERE $field = '$slug'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row['count'] == 0) {
            break;
        }
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    return $slug;
}

function uploadImage($file, $folder, $old_file = null) {
    $target_dir = UPLOAD_PATH . $folder . '/';
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = explode(',', ALLOWED_EXTENSIONS);
    
    if (!in_array($file_extension, $allowed)) {
        return ['error' => 'Only ' . ALLOWED_EXTENSIONS . ' files are allowed'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['error' => 'File size must be less than ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
    }
    
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Delete old file if exists
        if ($old_file && file_exists($target_dir . $old_file)) {
            unlink($target_dir . $old_file);
        }
        return ['success' => true, 'filename' => $new_filename];
    }
    
    return ['error' => 'Failed to upload image'];
}

function deleteImage($filename, $folder) {
    $file_path = UPLOAD_PATH . $folder . '/' . $filename;
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}

function displayStars($rating) {
    $html = '<div class="star-rating d-inline-flex">';
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($half && $i == $full + 1) {
            $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-warning"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}

function displayStarRatingInput() {
    return '
    <div class="star-rating-input d-flex flex-row-reverse justify-content-end gap-1">
        <input type="radio" name="rating" value="5" id="star5" required><label for="star5"><i class="fas fa-star"></i></label>
        <input type="radio" name="rating" value="4" id="star4"><label for="star4"><i class="fas fa-star"></i></label>
        <input type="radio" name="rating" value="3" id="star3"><label for="star3"><i class="fas fa-star"></i></label>
        <input type="radio" name="rating" value="2" id="star2"><label for="star2"><i class="fas fa-star"></i></label>
        <input type="radio" name="rating" value="1" id="star1"><label for="star1"><i class="fas fa-star"></i></label>
    </div>';
}

// =====================================================
// USER FUNCTIONS
// =====================================================

function getUserById($user_id) {
    global $conn;
    $query = "SELECT * FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function updateProfile($user_id, $data) {
    global $conn;
    $updates = [];
    foreach ($data as $key => $value) {
        $value = sanitize($value);
        $updates[] = "$key = '$value'";
    }
    $update_str = implode(', ', $updates);
    $query = "UPDATE users SET $update_str WHERE user_id = $user_id";
    return mysqli_query($conn, $query);
}

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $diff = $current_time - $time_ago;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $time_ago);
    }
}

function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function logUserActivity($user_id, $activity_type, $description = '') {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $description = sanitize($description);
    
    $query = "INSERT INTO user_activity (user_id, activity_type, activity_description, ip_address, user_agent) 
              VALUES ($user_id, '$activity_type', '$description', '$ip', '$user_agent')";
    return mysqli_query($conn, $query);
}

function getPriceRangeText($range) {
    $ranges = [
        1 => '₹ (Inexpensive)',
        2 => '₹₹ (Moderate)',
        3 => '₹₹₹ (Expensive)',
        4 => '₹₹₹₹ (Very Expensive)'
    ];
    return $ranges[$range] ?? 'Not specified';
}

function getPriceRangeIcons($range) {
    $icons = [
        1 => '₹',
        2 => '₹₹',
        3 => '₹₹₹',
        4 => '₹₹₹₹'
    ];
    return $icons[$range] ?? '₹';
}

function getBusinessHoursStatus($hours_json) {
    if (!$hours_json) return 'Not set';
    
    $hours = json_decode($hours_json, true);
    $today = date('l');
    $current_time = date('H:i');
    
    if (isset($hours[$today])) {
        $open = $hours[$today]['open'];
        $close = $hours[$today]['close'];
        
        if ($current_time >= $open && $current_time <= $close) {
            return '<span class="badge bg-success">Open Now</span>';
        } else {
            return '<span class="badge bg-danger">Closed</span>';
        }
    }
    return '<span class="badge bg-secondary">Closed</span>';
}

function sendEmail($to, $subject, $message, $from = ADMIN_EMAIL) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SITE_NAME . " <" . $from . ">" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// =====================================================
// ADMIN FUNCTIONS
// =====================================================

function getDashboardStats() {
    global $conn;
    
    $stats = [];
    
    $queries = [
        'total_users' => "SELECT COUNT(*) as count FROM users",
        'total_businesses' => "SELECT COUNT(*) as count FROM businesses",
        'pending_businesses' => "SELECT COUNT(*) as count FROM businesses WHERE status = 'pending'",
        'total_reviews' => "SELECT COUNT(*) as count FROM reviews",
        'total_support_requests' => "SELECT COUNT(*) as count FROM support_requests",
        'total_views_today' => "SELECT SUM(views_today) as count FROM businesses",
        'total_earnings' => "SELECT SUM(price) as count FROM support_offers WHERE status = 'accepted'"
    ];
    
    foreach ($queries as $key => $query) {
        $result = mysqli_query($conn, $query);
        $stats[$key] = mysqli_fetch_assoc($result)['count'] ?? 0;
    }
    
    return $stats;
}

function getRecentActivities($limit = 10) {
    global $conn;
    
    $activities = [];
    
    // New users
    $query = "SELECT 'user' as type, user_id as id, fullname as name, created_at as time FROM users ORDER BY created_at DESC LIMIT 5";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $row['activity'] = 'New user registered: ' . $row['name'];
        $activities[] = $row;
    }
    
    // New businesses
    $query = "SELECT 'business' as type, biz_id as id, biz_name as name, created_at as time FROM businesses ORDER BY created_at DESC LIMIT 5";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $row['activity'] = 'New business listed: ' . $row['name'];
        $activities[] = $row;
    }
    
    // New reviews
    $query = "SELECT 'review' as type, review_id as id, comment as name, created_at as time FROM reviews ORDER BY created_at DESC LIMIT 5";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $row['activity'] = 'New review posted';
        $activities[] = $row;
    }
    
    // Sort by time
    usort($activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
    return array_slice($activities, 0, $limit);
}

function getChartData($period = 'week') {
    global $conn;
    
    $data = [];
    
    if ($period == 'week') {
        $interval = "DATE(created_at)";
        $days = 7;
    } elseif ($period == 'month') {
        $interval = "DATE(created_at)";
        $days = 30;
    } else {
        $interval = "MONTH(created_at)";
        $days = 12;
    }
    
    // Users growth
    $query = "SELECT $interval as label, COUNT(*) as count FROM users 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
              GROUP BY $interval ORDER BY label";
    $result = mysqli_query($conn, $query);
    $data['users'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data['users'][] = $row;
    }
    
    // Businesses growth
    $query = "SELECT $interval as label, COUNT(*) as count FROM businesses 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
              GROUP BY $interval ORDER BY label";
    $result = mysqli_query($conn, $query);
    $data['businesses'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data['businesses'][] = $row;
    }
    
    return $data;
}
?>