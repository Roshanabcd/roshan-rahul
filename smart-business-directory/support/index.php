<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];
$user_data = getUserById($user_id);

// Get user's support requests
$requests = getSupportRequests($user_id, 20, 0);

include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Support Requests</h2>
        <a href="new-request.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Request</a>
    </div>
    
    <?php if(isset($_SESSION['success'])): ?>
        <?php echo showSuccess($_SESSION['success']); unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if(!empty($requests)): ?>
        <div class="row g-4">
            <?php foreach($requests as $request): ?>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title"><?php echo $request['title']; ?></h5>
                            <span class="badge bg-<?php 
                                echo $request['status'] == 'open' ? 'warning' : 
                                    ($request['status'] == 'assigned' ? 'info' : 
                                    ($request['status'] == 'completed' ? 'success' : 'secondary')); 
                            ?>">
                                <?php echo ucfirst($request['status']); ?>
                            </span>
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-tag me-1"></i><?php echo $request['category']; ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-clock me-1"></i><?php echo ucfirst($request['urgency']); ?>
                        </p>
                        <p class="card-text"><?php echo substr($request['description'], 0, 150); ?>...</p>
                        <a href="request-detail.php?id=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-ticket-alt fa-3x mb-3"></i>
            <h5>No Support Requests Yet</h5>
            <p>Need help? Post a support request and businesses will contact you.</p>
            <a href="new-request.php" class="btn btn-primary">Post Your First Request</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>