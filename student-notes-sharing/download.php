<?php
require_once 'config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request.");
}

$noteId = intval($_GET['id']);
$pdo = getDBConnection();

// Get note info
$stmt = $pdo->prepare("SELECT * FROM notes WHERE note_id = :note_id");
$stmt->execute(['note_id' => $noteId]);
$note = $stmt->fetch();

if (!$note) {
    die("Note not found.");
}

// Increment download count
$stmt = $pdo->prepare("UPDATE notes SET download_count = download_count + 1 WHERE note_id = :note_id");
$stmt->execute(['note_id' => $noteId]);

// File path
$filePath = __DIR__ . '/' . $note['file_path'];

if (!file_exists($filePath)) {
    die("File not found on server.");
}

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($note['file_name']) . '"');
header('Content-Length: ' . filesize($filePath));
header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate');

// Clear output buffering
ob_clean();
flush();

// Output file
readfile($filePath);
exit();
?>