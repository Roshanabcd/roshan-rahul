<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get request details
$query = "SELECT sr.*, b.biz_name as assigned_business_name
          FROM support_requests sr
          LEFT JOIN businesses b ON sr.assigned_biz_id = b.biz_id
          WHERE sr.request_id = $request_id AND sr.user_id = {$_SESSION['user_id']}";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    redirect('my-requests.php');
}

$request = mysqli_fetch_assoc($result);

// Get offers for this request
$offers_query = "SELECT o.*, b.biz_name, b.logo, b.phone, b.email
                 FROM support_offers o
                 JOIN businesses b ON o.business_id = b.biz_id
                 WHERE o.request_id = $request_id
                 ORDER BY o.price ASC";
$offers = mysqli_query($conn, $offers_query);

include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2><?php echo $request['title']; ?></h2>
                        <span class="badge bg-<?php 
                            echo $request['status'] == 'open' ? 'warning' : 
                                ($request['status'] == 'assigned' ? 'info' : 
                                ($request['status'] == 'completed' ? 'success' : 'secondary')); 
                        ?> fs-6">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </div>
                    
                    <p class="text-muted mb-3">
                        <i class="fas fa-calendar me-1"></i>Posted: <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-tag me-1"></i><?php echo $request['category']; ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-clock me-1"></i>Urgency: <?php echo ucfirst($request['urgency']); ?>
                    </p>
                    
                    <h5>Description</h5>
                    <p><?php echo nl2br($request['description']); ?></p>
                    
                    <h5>Location</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i><?php echo $request['address']; ?></p>
                    
                    <?php if($request['status'] == 'completed' && $request['rating']): ?>
                        <div class="alert alert-success mt-3">
                            <h6>Your Rating</h6>
                            <?php echo displayStars($request['rating']); ?>
                            <p class="mt-2"><?php echo $request['review_text']; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Offers Received (<?php echo mysqli_num_rows($offers); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if(mysqli_num_rows($offers) > 0): ?>
                        <?php while($offer = mysqli_fetch_assoc($offers)): ?>
                        <div class="offer-item mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo $offer['biz_name']; ?></strong>
                                <span class="text-success fw-bold">₹<?php echo number_format($offer['price'], 2); ?></span>
                            </div>
                            <p class="small text-muted mt-1"><?php echo $offer['message']; ?></p>
                            <div class="d-flex gap-2">
                                <a href="tel:<?php echo $offer['phone']; ?>" class="btn btn-sm btn-success"><i class="fas fa-phone"></i> Call</a>
                                <a href="../chat/index.php?business=<?php echo $offer['business_id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-comment"></i> Chat</a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No offers yet. Businesses will contact you.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>