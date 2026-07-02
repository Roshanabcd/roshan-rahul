<?php
/**
 * Rate Note Handler
 * 
 * Processes rating submissions for notes
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to rate notes.";
    header("Location: login.php");
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// Include database configuration
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get and validate inputs
    $userId = $_SESSION['user_id'];
    $noteId = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
    $stars = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    
    // Validate note ID
    if ($noteId <= 0) {
        $_SESSION['error'] = "Invalid note selected.";
        header("Location: index.php");
        exit();
    }
    
    // Validate rating value (1-5)
    if ($stars < 1 || $stars > 5) {
        $_SESSION['error'] = "Rating must be between 1 and 5 stars.";
        header("Location: index.php");
        exit();
    }
    
    // Check if note exists
    $stmt = $pdo->prepare("SELECT note_id, user_id FROM notes WHERE note_id = :note_id AND status = 'active'");
    $stmt->execute(['note_id' => $noteId]);
    $note = $stmt->fetch();
    
    if (!$note) {
        $_SESSION['error'] = "Note not found or has been removed.";
        header("Location: index.php");
        exit();
    }
    
    // Prevent users from rating their own notes (optional)
    // Uncomment if you want this feature
    /*
    if ($note['user_id'] == $userId) {
        $_SESSION['error'] = "You cannot rate your own notes.";
        header("Location: index.php");
        exit();
    }
    */
    
    // Check if user already rated this note
    $stmt = $pdo->prepare("SELECT rating_id, stars FROM ratings WHERE user_id = :user_id AND note_id = :note_id");
    $stmt->execute([
        'user_id' => $userId,
        'note_id' => $noteId
    ]);
    $existingRating = $stmt->fetch();
    
    if ($existingRating) {
        // Update existing rating
        $stmt = $pdo->prepare("UPDATE ratings SET stars = :stars WHERE rating_id = :rating_id AND user_id = :user_id");
        $stmt->execute([
            'stars' => $stars,
            'rating_id' => $existingRating['rating_id'],
            'user_id' => $userId
        ]);
        $_SESSION['success'] = "Your rating has been updated to {$stars} stars!";
    } else {
        // Insert new rating
        $stmt = $pdo->prepare("INSERT INTO ratings (user_id, note_id, stars) VALUES (:user_id, :note_id, :stars)");
        $stmt->execute([
            'user_id' => $userId,
            'note_id' => $noteId,
            'stars' => $stars
        ]);
        $_SESSION['success'] = "Thank you! You rated this note {$stars} stars.";
    }
    
    // Redirect back to the referring page or index
    $redirectUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    header("Location: " . $redirectUrl);
    exit();
    
} catch (PDOException $e) {
    // Log the error
    error_log("Rating Error: " . $e->getMessage());
    
    // Check for specific foreign key constraint error
    if ($e->getCode() == '23000') {
        $_SESSION['error'] = "Cannot rate this note. The note may have been deleted.";
    } else {
        $_SESSION['error'] = "An error occurred while saving your rating. Please try again.";
    }
    
    header("Location: index.php");
    exit();
}
?>