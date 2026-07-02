<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver_id = (int)$_POST['receiver_id'];
    $message = sanitize($_POST['message']);
    $sender_id = $_SESSION['user_id'];
    
    sendMessage($sender_id, $receiver_id, $message);
    
    echo json_encode(['success' => true]);
}
?>