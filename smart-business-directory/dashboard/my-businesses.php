<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $biz_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM businesses WHERE biz_id = $biz_id AND owner_id = $user_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = 'Business deleted successfully';
        redirect('my-businesses.php');
    }
}

// Get user's businesses
$query = "SELECT b.*, c.cat_name,
          (SELECT AVG(rating) FROM reviews WHERE biz_id = b.biz_id AND is_approved = 1) as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE biz_id = b.biz_id AND is_approved = 1) as review_count
          FROM businesses b 
          JOIN categories c ON b.cat_id = c.cat_id
          WHERE b.owner_id = $user_id
          ORDER BY b.created_at DESC";
$businesses = mysqli_query($conn, $query);

include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="my-businesses.php"><i class="fas fa-store"></i> My Businesses</a></li>
                    <li class="nav-item"><a class="nav-link" href="add-business.php"><i class="fas fa-plus-circle"></i> Add Business</a></li>
                    <li class="nav-item"><a class="nav-link" href="my-reviews.php"><i class="fas fa-star"></i> My Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Businesses</h1>
                <a href="add-business.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Business</a>
            </div>
            
            <?php if(isset($_SESSION['success'])): ?>
                <?php echo showSuccess($_SESSION['success']); unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <?php if(mysqli_num_rows($businesses) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Logo</th>
                                        <th>Business Name</th>
                                        <th>Category</th>
                                        <th>Rating</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($business = mysqli_fetch_assoc($businesses)): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo UPLOAD_URL . 'businesses/' . ($business['logo'] ?? 'default-business.png'); ?>" 
                                                 width="50" height="50" style="object-fit: cover; border-radius: 8px;">
                                        </td>
                                        <td>
                                            <strong><?php echo $business['biz_name']; ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $business['city']; ?></small>
                                        </td>
                                        <td><?php echo $business['cat_name']; ?></td>
                                        <td>
                                            <?php echo displayStars($business['avg_rating']); ?>
                                            <small class="text-muted">(<?php echo $business['review_count']; ?>)</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $business['status'] == 'approved' ? 'success' : 
                                                    ($business['status'] == 'pending' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($business['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($business['views']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($business['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../business-detail.php?id=<?php echo $business['biz_id']; ?>" 
                                                   class="btn btn-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="edit-business.php?id=<?php echo $business['biz_id']; ?>" 
                                                   class="btn btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="?delete=<?php echo $business['biz_id']; ?>" 
                                                   class="btn btn-danger" title="Delete" 
                                                   onclick="return confirm('Are you sure you want to delete this business?')">
                                                   <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-store fa-4x text-muted mb-3"></i>
                            <h4>No Businesses Yet</h4>
                            <p class="text-muted">You haven't added any businesses to your account.</p>
                            <a href="add-business.php" class="btn btn-primary">Add Your First Business</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>