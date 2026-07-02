<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first.";
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$pdo = getDBConnection();
$noteId = intval($_GET['id']);
$userId = $_SESSION['user_id'];

// Get note info
$stmt = $pdo->prepare("SELECT * FROM notes WHERE note_id = :note_id AND user_id = :user_id");
$stmt->execute(['note_id' => $noteId, 'user_id' => $userId]);
$note = $stmt->fetch();

if (!$note) {
    $_SESSION['error'] = "Note not found or unauthorized.";
    header("Location: dashboard.php");
    exit();
}

// Delete file
$filePath = __DIR__ . '/' . $note['file_path'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Delete from database
$stmt = $pdo->prepare("DELETE FROM notes WHERE note_id = :note_id");
$stmt->execute(['note_id' => $noteId]);

$_SESSION['success'] = "Note deleted successfully.";
header("Location: dashboard.php");
exit();
?>