<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied.";
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$pdo = getDBConnection();
$noteId = intval($_GET['id']);

// Get note info
$stmt = $pdo->prepare("SELECT * FROM notes WHERE note_id = :note_id");
$stmt->execute(['note_id' => $noteId]);
$note = $stmt->fetch();

if ($note) {
    // Delete file
    $filePath = __DIR__ . '/../' . $note['file_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Delete from database (cascade will delete ratings)
    $stmt = $pdo->prepare("DELETE FROM notes WHERE note_id = :note_id");
    $stmt->execute(['note_id' => $noteId]);
    
    $_SESSION['success'] = "Note deleted successfully.";
} else {
    $_SESSION['error'] = "Note not found.";
}

header("Location: index.php");
exit();
?>