<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];

// Handle remove favorite
if (isset($_GET['remove'])) {
    $biz_id = (int)$_GET['remove'];
    removeFavorite($user_id, $biz_id);
    redirect('favorites.php');
}

// Get favorites
$favorites = getUserFavorites($user_id, 50, 0);

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
                    <li class="nav-item"><a class="nav-link" href="my-reviews.php"><i class="fas fa-star"></i> My Reviews</a></li>
                    <li class="nav-item"><a class="nav-link active" href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Favorites</h1>
            </div>
            
            <div class="row g-4">
                <?php if(!empty($favorites)): ?>
                    <?php foreach($favorites as $business): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card business-card h-100">
                            <img src="<?php echo UPLOAD_URL . 'businesses/' . ($business['logo'] ?? 'default-business.png'); ?>" 
                                 class="card-img-top" alt="<?php echo $business['biz_name']; ?>"
                                 style="height: 160px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $business['biz_name']; ?></h5>
                                <div class="mb-2">
                                    <?php echo displayStars($business['avg_rating']); ?>
                                    <span class="text-muted">(<?php echo $business['review_count']; ?>)</span>
                                </div>
                                <a href="../business-detail.php?id=<?php echo $business['biz_id']; ?>" class="btn btn-primary btn-sm w-100 mb-2">View Details</a>
                                <a href="?remove=<?php echo $business['biz_id']; ?>" class="btn btn-outline-danger btn-sm w-100">Remove</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-heart-broken fa-3x text-muted mb-3"></i>
                        <p>You haven't saved any favorites yet.</p>
                        <a href="../businesses.php" class="btn btn-primary">Browse Businesses</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>