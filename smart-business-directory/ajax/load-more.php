<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$query = "SELECT b.*, c.cat_name,
          COALESCE(AVG(r.rating), 0) as avg_rating,
          COUNT(r.review_id) as review_count
          FROM businesses b 
          JOIN categories c ON b.cat_id = c.cat_id 
          LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
          WHERE b.status = 'approved'
          GROUP BY b.biz_id
          ORDER BY b.created_at DESC
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
$html = '';

while ($business = mysqli_fetch_assoc($result)) {
    $html .= '
    <div class="col-md-4 col-lg-3">
        <div class="card business-card h-100">
            <img src="' . UPLOAD_URL . 'businesses/' . ($business['logo'] ?? 'default-business.png') . '" 
                 class="card-img-top" alt="' . $business['biz_name'] . '"
                 style="height: 160px; object-fit: cover;">
            <div class="card-body">
                <h5 class="card-title">' . $business['biz_name'] . '</h5>
                <div class="mb-2">' . displayStars($business['avg_rating']) . '</div>
                <a href="business-detail.php?id=' . $business['biz_id'] . '" class="btn btn-primary btn-sm w-100">View Details</a>
            </div>
        </div>
    </div>';
}

echo json_encode(['html' => $html, 'has_more' => mysqli_num_rows($result) == $limit]);
?>