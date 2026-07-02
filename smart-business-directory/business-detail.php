<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$biz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get business details
$query = "SELECT b.*, c.cat_name, c.cat_id, u.fullname as owner_name, u.email as owner_email, u.phone as owner_phone
          FROM businesses b 
          JOIN categories c ON b.cat_id = c.cat_id 
          JOIN users u ON b.owner_id = u.user_id
          WHERE b.biz_id = $biz_id AND b.status = 'approved'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Business not found';
    redirect('businesses.php');
}

$business = mysqli_fetch_assoc($result);

// Increment view count
mysqli_query($conn, "UPDATE businesses SET views = views + 1 WHERE biz_id = $biz_id");

// Get business images
$images_query = "SELECT * FROM business_images WHERE biz_id = $biz_id ORDER BY is_primary DESC";
$images_result = mysqli_query($conn, $images_query);
$images = [];
while ($row = mysqli_fetch_assoc($images_result)) {
    $images[] = $row;
}

// Get reviews
$reviews_query = "SELECT r.*, u.fullname, u.profile_image 
                  FROM reviews r 
                  JOIN users u ON r.user_id = u.user_id 
                  WHERE r.biz_id = $biz_id AND r.is_approved = 1 
                  ORDER BY r.created_at DESC";
$reviews_result = mysqli_query($conn, $reviews_query);

$rating_data = getBusinessRating($biz_id);

// Handle review submission
$review_error = '';
$review_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review']) && isLoggedIn()) {
    $rating = (int)$_POST['rating'];
    $title = sanitize($_POST['title']);
    $comment = sanitize($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    if ($rating < 1 || $rating > 5) {
        $review_error = 'Please select a valid rating';
    } elseif (empty($comment)) {
        $review_error = 'Please write a review';
    } else {
        // Check if user already reviewed
        $check_query = "SELECT review_id FROM reviews WHERE biz_id = $biz_id AND user_id = $user_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $update_query = "UPDATE reviews SET rating = $rating, title = '$title', comment = '$comment', 
                             updated_at = NOW() WHERE biz_id = $biz_id AND user_id = $user_id";
            if (mysqli_query($conn, $update_query)) {
                $review_success = 'Your review has been updated!';
            }
        } else {
            $insert_query = "INSERT INTO reviews (biz_id, user_id, rating, title, comment) 
                            VALUES ($biz_id, $user_id, $rating, '$title', '$comment')";
            if (mysqli_query($conn, $insert_query)) {
                $review_success = 'Thank you for your review!';
            }
        }
        
        // Update average rating
        $avg_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE biz_id = $biz_id AND is_approved = 1";
        $avg_result = mysqli_query($conn, $avg_query);
        $avg_data = mysqli_fetch_assoc($avg_result);
        $new_avg = round($avg_data['avg_rating'], 2);
        $total_reviews = $avg_data['total'];
        
        mysqli_query($conn, "UPDATE businesses SET average_rating = $new_avg, total_reviews = $total_reviews WHERE biz_id = $biz_id");
        
        // Refresh data
        $rating_data = getBusinessRating($biz_id);
        $reviews_result = mysqli_query($conn, $reviews_query);
    }
}

// Handle favorite toggle
if (isset($_GET['toggle_favorite']) && isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $check_fav = "SELECT * FROM favorites WHERE user_id = $user_id AND biz_id = $biz_id";
    $fav_result = mysqli_query($conn, $check_fav);
    
    if (mysqli_num_rows($fav_result) > 0) {
        mysqli_query($conn, "DELETE FROM favorites WHERE user_id = $user_id AND biz_id = $biz_id");
        mysqli_query($conn, "UPDATE businesses SET total_favorites = total_favorites - 1 WHERE biz_id = $biz_id");
    } else {
        mysqli_query($conn, "INSERT INTO favorites (user_id, biz_id) VALUES ($user_id, $biz_id)");
        mysqli_query($conn, "UPDATE businesses SET total_favorites = total_favorites + 1 WHERE biz_id = $biz_id");
    }
    
    redirect("business-detail.php?id=$biz_id");
}

$is_favorited = isLoggedIn() ? isFavorited($_SESSION['user_id'], $biz_id) : false;

// Parse business hours
$business_hours = json_decode($business['business_hours'], true);

include 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Business Header -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <img src="<?php echo UPLOAD_URL . 'businesses/' . ($business['logo'] ?? 'default-business.png'); ?>" 
                                 class="img-fluid rounded-3" alt="<?php echo $business['biz_name']; ?>"
                                 style="max-height: 150px; width: auto;">
                        </div>
                        <div class="col-md-9">
                            <h2 class="mb-2"><?php echo $business['biz_name']; ?></h2>
                            <p class="text-muted mb-2">
                                <i class="fas fa-tag me-2"></i><?php echo $business['cat_name']; ?>
                            </p>
                            <div class="mb-3">
                                <?php echo displayStars($rating_data['rating']); ?>
                                <span class="text-muted ms-2">(<?php echo $rating_data['total']; ?> reviews)</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if(isLoggedIn()): ?>
                                    <a href="?toggle_favorite=1" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-heart <?php echo $is_favorited ? 'text-danger' : ''; ?>"></i>
                                        <?php echo $is_favorited ? 'Saved' : 'Save to Favorites'; ?>
                                    </a>
                                <?php endif; ?>
                                <a href="tel:<?php echo $business['phone']; ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-phone me-1"></i>Call Now
                                </a>
                                <?php if($business['website']): ?>
                                <a href="<?php echo $business['website']; ?>" target="_blank" class="btn btn-info btn-sm text-white">
                                    <i class="fas fa-globe me-1"></i>Website
                                </a>
                                <?php endif; ?>
                                <?php if(isLoggedIn()): ?>
                                <a href="chat/index.php?user=<?php echo $business['owner_id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-comment-dots me-1"></i>Chat
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Business Description -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>About</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br($business['description']); ?></p>
                </div>
            </div>
            
            <!-- Business Details -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact & Location</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-phone text-primary me-2"></i>Phone</h6>
                            <p><a href="tel:<?php echo $business['phone']; ?>"><?php echo $business['phone']; ?></a></p>
                            
                            <?php if($business['email']): ?>
                            <h6><i class="fas fa-envelope text-primary me-2"></i>Email</h6>
                            <p><a href="mailto:<?php echo $business['email']; ?>"><?php echo $business['email']; ?></a></p>
                            <?php endif; ?>
                            
                            <h6><i class="fas fa-map-marker-alt text-primary me-2"></i>Address</h6>
                            <p><?php echo $business['address_line1']; ?><br>
                            <?php echo $business['city'] . ', ' . $business['state'] . ' - ' . $business['pincode']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <?php if($business_hours): ?>
                            <h6><i class="fas fa-clock text-primary me-2"></i>Business Hours</h6>
                            <ul class="list-unstyled">
                                <?php foreach($business_hours as $day => $hours): ?>
                                <li><strong><?php echo ucfirst($day); ?>:</strong> <?php echo $hours['open'] . ' - ' . $hours['close']; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Google Map -->
                    <?php if($business['latitude'] && $business['longitude']): ?>
                    <div class="mt-3">
                        <div id="businessMap" style="height: 300px; width: 100%; border-radius: 10px;"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Business Images -->
            <?php if(count($images) > 0): ?>
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-images me-2"></i>Gallery</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php foreach($images as $image): ?>
                        <div class="col-md-3 col-4">
                            <img src="<?php echo UPLOAD_URL . 'businesses/' . $image['image_path']; ?>" 
                                 class="img-fluid rounded-3" style="height: 100px; width: 100%; object-fit: cover; cursor: pointer;"
                                 onclick="window.open(this.src)">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Reviews Section -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Reviews (<?php echo $rating_data['total']; ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if($review_success): ?>
                        <?php echo showSuccess($review_success); ?>
                    <?php endif; ?>
                    
                    <?php if($review_error): ?>
                        <?php echo showError($review_error); ?>
                    <?php endif; ?>
                    
                    <?php if(isLoggedIn() && $_SESSION['user_id'] != $business['owner_id']): ?>
                    <div class="mb-4 p-3 bg-light rounded-3">
                        <h6 class="mb-3">Write a Review</h6>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Your Rating</label>
                                <div class="rating-input">
                                    <input type="radio" name="rating" value="5" id="star5"><label for="star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="4" id="star4"><label for="star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="3" id="star3"><label for="star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="2" id="star2"><label for="star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="1" id="star1"><label for="star1"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="title" placeholder="Review Title (Optional)">
                            </div>
                            <div class="mb-3">
                                <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience..." required></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(mysqli_num_rows($reviews_result) > 0): ?>
                        <?php while($review = mysqli_fetch_assoc($reviews_result)): ?>
                        <div class="review-item mb-3 pb-3 border-bottom">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <img src="<?php echo UPLOAD_URL . 'avatars/' . ($review['profile_image'] ?? 'default-avatar.png'); ?>" 
                                         class="rounded-circle" width="50" height="50" style="object-fit: cover;">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1"><?php echo $review['fullname']; ?></h6>
                                        <small class="text-muted"><?php echo timeAgo($review['created_at']); ?></small>
                                    </div>
                                    <div class="mb-1">
                                        <?php echo displayStars($review['rating']); ?>
                                    </div>
                                    <?php if($review['title']): ?>
                                    <h6 class="mt-2"><?php echo $review['title']; ?></h6>
                                    <?php endif; ?>
                                    <p class="mb-0"><?php echo nl2br($review['comment']); ?></p>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-secondary helpful-btn" data-review="<?php echo $review['review_id']; ?>">
                                            <i class="far fa-thumbs-up"></i> Helpful (<span class="helpful-count"><?php echo $review['helpful_count']; ?></span>)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No reviews yet. Be the first to review!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body">
                    <h5><i class="fas fa-chart-line me-2"></i>Business Stats</h5>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-eye text-primary"></i> Total Views</span>
                        <strong><?php echo number_format($business['views']); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-heart text-danger"></i> Favorites</span>
                        <strong><?php echo number_format($business['total_favorites']); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-calendar-alt text-success"></i> Listed Since</span>
                        <strong><?php echo date('M Y', strtotime($business['created_at'])); ?></strong>
                    </div>
                </div>
            </div>
            
            <?php if(isLoggedIn() && $_SESSION['user_id'] != $business['owner_id']): ?>
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <h5><i class="fas fa-headset me-2"></i>Need Support?</h5>
                    <hr>
                    <p>Request support service from this business</p>
                    <a href="<?php echo SITE_URL; ?>support/new-request.php?business=<?php echo $biz_id; ?>" class="btn btn-primary w-100">
                        Request Support <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
}
.rating-input input {
    display: none;
}
.rating-input label {
    cursor: pointer;
    color: #ddd;
    font-size: 1.5rem;
}
.rating-input label:hover,
.rating-input label:hover ~ label,
.rating-input input:checked ~ label {
    color: #ffc107;
}
.review-item:last-child {
    border-bottom: none !important;
}
</style>

<?php if($business['latitude'] && $business['longitude']): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initBusinessMap" async defer></script>
<script>
function initBusinessMap() {
    var location = {lat: <?php echo $business['latitude']; ?>, lng: <?php echo $business['longitude']; ?>};
    var map = new google.maps.Map(document.getElementById('businessMap'), {
        zoom: 15,
        center: location,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true
    });
    var marker = new google.maps.Marker({
        position: location,
        map: map,
        title: '<?php echo addslashes($business['biz_name']); ?>',
        animation: google.maps.Animation.DROP
    });
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>