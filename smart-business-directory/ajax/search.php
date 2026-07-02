<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$results = [];

if (strlen($query) >= 2) {
    $sql = "SELECT biz_id, biz_name, cat_name, city 
            FROM businesses b
            JOIN categories c ON b.cat_id = c.cat_id
            WHERE b.status = 'approved' 
            AND (b.biz_name LIKE '%$query%' OR b.description LIKE '%$query%')
            LIMIT 10";
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $results[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($results);
?>