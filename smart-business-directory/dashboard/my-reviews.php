<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];

// Get user's reviews
$query = "SELECT r.*, b.biz_name, b.slug, b.biz_id
          FROM reviews r
          JOIN businesses b ON r.biz_id = b.biz_id
          WHERE r.user_id = $user_id
          ORDER BY r.created_at DESC";
$reviews = mysqli_query($conn, $query);

// Handle delete
if (isset($_GET['delete'])) {
    $review_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM reviews WHERE review_id = $review_id AND user_id = $user_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = 'Review deleted successfully';
        redirect('my-reviews.php');
    }
}

include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="my-businesses.php"><i class="fas fa-store"></i> My Businesses</a></li>
                    <li class="nav-item"><a class="nav-link" href="add-business.php"><i class="fas fa-plus-circle"></i> Add Business</a></li>
                    <li class="nav-item"><a class="nav-link active" href="my-reviews.php"><i class="fas fa-star"></i> My Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Reviews</h1>
            </div>
            
            <?php if(isset($_SESSION['success'])): ?>
                <?php echo showSuccess($_SESSION['success']); unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <?php if(mysqli_num_rows($reviews) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Business</th>
                                        <th>Rating</th>
                                        <th>Review</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($review = mysqli_fetch_assoc($reviews)): ?>
                                    <tr>
                                        <td><a href="../business-detail.php?id=<?php echo $review['biz_id']; ?>"><?php echo $review['biz_name']; ?></a></td>
                                        <td><?php echo displayStars($review['rating']); ?></td>
                                        <td><?php echo substr($review['comment'], 0, 100); ?>...</td>
                                        <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                        <td>
                                            <a href="edit-review.php?id=<?php echo $review['review_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $review['review_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <p>You haven't written any reviews yet.</p>
                            <a href="../businesses.php" class="btn btn-primary">Browse Businesses to Review</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>