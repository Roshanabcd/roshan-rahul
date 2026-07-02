<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];
$user_data = getUserById($user_id);

// Get user statistics
$business_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM businesses WHERE owner_id = $user_id"))['total'];
$review_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM reviews WHERE user_id = $user_id"))['total'];
$favorite_count = getFavoritesCount($user_id);
$support_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM support_requests WHERE user_id = $user_id"))['total'];

// Get recent businesses added by user
$recent_businesses = mysqli_query($conn, "SELECT * FROM businesses WHERE owner_id = $user_id ORDER BY created_at DESC LIMIT 5");

// Get recent support requests
$recent_requests = mysqli_query($conn, "SELECT * FROM support_requests WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// Get unread notifications count
$unread_notifications = getUnreadNotificationCount($user_id);

include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="my-businesses.php"><i class="fas fa-store"></i> My Businesses</a></li>
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
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="text-muted">Welcome back, <?php echo $_SESSION['fullname']; ?>!</span>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">My Businesses</h6>
                                    <h2 class="mb-0"><?php echo $business_count; ?></h2>
                                </div>
                                <i class="fas fa-store fa-3x opacity-50"></i>
                            </div>
                            <a href="my-businesses.php" class="text-white text-decoration-none mt-2 d-block">View All →</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">My Reviews</h6>
                                    <h2 class="mb-0"><?php echo $review_count; ?></h2>
                                </div>
                                <i class="fas fa-star fa-3x opacity-50"></i>
                            </div>
                            <a href="my-reviews.php" class="text-white text-decoration-none mt-2 d-block">View All →</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-danger h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Favorites</h6>
                                    <h2 class="mb-0"><?php echo $favorite_count; ?></h2>
                                </div>
                                <i class="fas fa-heart fa-3x opacity-50"></i>
                            </div>
                            <a href="favorites.php" class="text-white text-decoration-none mt-2 d-block">View All →</a>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="row">
                <!-- Recent Businesses -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-store me-2"></i>Recent Businesses</h5>
                        </div>
                        <div class="card-body">
                            <?php if(mysqli_num_rows($recent_businesses) > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while($business = mysqli_fetch_assoc($recent_businesses)): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?php echo $business['biz_name']; ?></h6>
                                                <small class="text-muted"><?php echo date('M d, Y', strtotime($business['created_at'])); ?></small>
                                            </div>
                                            <span class="badge bg-<?php echo $business['status'] == 'approved' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($business['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <a href="my-businesses.php" class="btn btn-sm btn-outline-primary mt-3">View All Businesses</a>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No businesses added yet.</p>
                                <a href="add-business.php" class="btn btn-primary w-100">Add Your First Business</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                

            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="add-business.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                Add Business
                            </a>
                        </div>

                        <div class="col-md-3">
                            <a href="../businesses.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-search fa-2x d-block mb-2"></i>
                                Browse Businesses
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="profile.php" class="btn btn-outline-secondary w-100 py-3">
                                <i class="fas fa-user-edit fa-2x d-block mb-2"></i>
                                Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>