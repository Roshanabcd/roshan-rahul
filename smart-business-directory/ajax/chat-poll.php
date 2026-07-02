<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$query = "SELECT * FROM chat_messages WHERE receiver_id = $user_id AND message_id > $last_id ORDER BY created_at ASC";
$result = mysqli_query($conn, $query);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

header('Content-Type: application/json');
echo json_encode($messages);
?>