<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];
$requests = getSupportRequests($user_id, 50, 0);

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
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($requests as $request): ?>
                    <tr>
                        <td>#<?php echo $request['request_id']; ?></td>
                        <td><?php echo $request['title']; ?></td>
                        <td><?php echo $request['category']; ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $request['urgency'] == 'emergency' ? 'danger' : 
                                    ($request['urgency'] == 'high' ? 'warning' : 
                                    ($request['urgency'] == 'medium' ? 'info' : 'secondary')); 
                            ?>">
                                <?php echo ucfirst($request['urgency']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $request['status'] == 'completed' ? 'success' : 
                                    ($request['status'] == 'assigned' ? 'primary' : 
                                    ($request['status'] == 'open' ? 'warning' : 'secondary')); 
                            ?>">
                                <?php echo ucfirst($request['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                        <td>
                            <a href="request-detail.php?id=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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