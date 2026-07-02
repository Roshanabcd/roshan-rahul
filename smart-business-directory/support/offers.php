<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

if (!isBusinessOwner()) {
    redirect('../dashboard/index.php');
}

$business_id = 0;
$query = "SELECT biz_id FROM businesses WHERE owner_id = {$_SESSION['user_id']} LIMIT 1";
$result = mysqli_query($conn, $query);
if ($row = mysqli_fetch_assoc($result)) {
    $business_id = $row['biz_id'];
}

// Get business location
$biz_query = "SELECT latitude, longitude, city FROM businesses WHERE biz_id = $business_id";
$biz_result = mysqli_query($conn, $biz_query);
$business = mysqli_fetch_assoc($biz_result);

// Get nearby open requests
if ($business['latitude'] && $business['longitude']) {
    $requests = getOpenSupportRequestsNearby($business_id, $business['latitude'], $business['longitude'], 20);
} else {
    $requests = [];
}

// Handle offer submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_offer'])) {
    $request_id = (int)$_POST['request_id'];
    $price = (float)$_POST['price'];
    $message = sanitize($_POST['message']);
    
    $query = "INSERT INTO support_offers (request_id, business_id, price, message) 
              VALUES ($request_id, $business_id, $price, '$message')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Offer submitted successfully!';
        redirect('offers.php');
    }
}

include '../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4">Available Support Requests Near You</h2>
    
    <?php if(isset($_SESSION['success'])): ?>
        <?php echo showSuccess($_SESSION['success']); unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if(!empty($requests)): ?>
        <div class="row g-4">
            <?php foreach($requests as $request): ?>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5><?php echo $request['title']; ?></h5>
                            <span class="badge bg-<?php echo $request['urgency'] == 'emergency' ? 'danger' : 'warning'; ?>">
                                <?php echo ucfirst($request['urgency']); ?>
                            </span>
                        </div>
                        <p class="text-muted small">
                            <i class="fas fa-map-marker-alt me-1"></i><?php echo round($request['distance'], 1); ?> km away
                        </p>
                        <p><?php echo substr($request['description'], 0, 100); ?>...</p>
                        
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                            <div class="row">
                                <div class="col-md-5 mb-2">
                                    <input type="number" name="price" class="form-control" placeholder="Your Price (₹)" step="100" required>
                                </div>
                                <div class="col-md-7 mb-2">
                                    <input type="text" name="message" class="form-control" placeholder="Your offer message" required>
                                </div>
                            </div>
                            <button type="submit" name="submit_offer" class="btn btn-primary w-100">Submit Offer</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <h5>No Support Requests Nearby</h5>
            <p>There are no open support requests in your area right now.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>