<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// Handle review actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $review_id = (int)$_GET['id'];
    
    if ($_GET['action'] == 'approve') {
        mysqli_query($conn, "UPDATE reviews SET is_approved = 1 WHERE review_id = $review_id");
        $_SESSION['success'] = 'Review approved successfully';
    } elseif ($_GET['action'] == 'hide') {
        mysqli_query($conn, "UPDATE reviews SET is_approved = 0 WHERE review_id = $review_id");
        $_SESSION['success'] = 'Review hidden successfully';
    } elseif ($_GET['action'] == 'delete') {
        mysqli_query($conn, "DELETE FROM reviews WHERE review_id = $review_id");
        $_SESSION['success'] = 'Review deleted successfully';
    }
    redirect('reviews.php');
}

// Get all reviews
$query = "SELECT r.*, u.fullname as user_name, b.biz_name as business_name
          FROM reviews r
          JOIN users u ON r.user_id = u.user_id
          JOIN businesses b ON r.biz_id = b.biz_id
          ORDER BY r.created_at DESC";
$reviews = mysqli_query($conn, $query);

include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-dark text-white sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="businesses.php"><i class="fas fa-store"></i> Businesses</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li class="nav-item"><a class="nav-link text-white active" href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Reviews</h1>
                <span class="text-muted">Total Reviews: <?php echo mysqli_num_rows($reviews); ?></span>
            </div>
            
            <?php if(isset($_SESSION['success'])): ?>
                <?php echo showSuccess($_SESSION['success']); unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Business</th>
                                    <th>User</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($review = mysqli_fetch_assoc($reviews)): ?>
                                <tr>
                                    <td><?php echo $review['review_id']; ?></td>
                                    <td><?php echo $review['business_name']; ?></td>
                                    <td><?php echo $review['user_name']; ?></td>
                                    <td><?php echo displayStars($review['rating']); ?></td>
                                    <td><?php echo substr($review['comment'], 0, 50); ?>...</td>
                                    <td>
                                        <?php if($review['is_approved']): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if($review['is_approved']): ?>
                                                <a href="?action=hide&id=<?php echo $review['review_id']; ?>" class="btn btn-sm btn-warning">Hide</a>
                                            <?php else: ?>
                                                <a href="?action=approve&id=<?php echo $review['review_id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?php echo $review['review_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>