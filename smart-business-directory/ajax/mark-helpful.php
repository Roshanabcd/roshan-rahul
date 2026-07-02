<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$review_id = isset($_GET['review']) ? (int)$_GET['review'] : 0;
$user_id = $_SESSION['user_id'];

$result = markReviewHelpful($review_id, $user_id);

header('Content-Type: application/json');
echo json_encode(['success' => $result]);
?>