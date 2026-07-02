<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Access denied';
    redirect('../index.php');
}

// Handle approve/reject/delete/feature
if (isset($_GET['action']) && isset($_GET['id'])) {
    $biz_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        mysqli_query($conn, "UPDATE businesses SET status = 'approved' WHERE biz_id = $biz_id");
        $_SESSION['success'] = 'Business approved successfully';
    } elseif ($action == 'reject') {
        mysqli_query($conn, "UPDATE businesses SET status = 'rejected' WHERE biz_id = $biz_id");
        $_SESSION['success'] = 'Business rejected';
    } elseif ($action == 'feature') {
        mysqli_query($conn, "UPDATE businesses SET is_featured = 1, featured_until = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE biz_id = $biz_id");
        $_SESSION['success'] = 'Business featured successfully';
    } elseif ($action == 'unfeature') {
        mysqli_query($conn, "UPDATE businesses SET is_featured = 0, featured_until = NULL WHERE biz_id = $biz_id");
        $_SESSION['success'] = 'Business removed from featured';
    } elseif ($action == 'delete') {
        mysqli_query($conn, "DELETE FROM businesses WHERE biz_id = $biz_id");
        $_SESSION['success'] = 'Business deleted successfully';
    }
    redirect('businesses.php');
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$where = [];
if ($status_filter) {
    $where[] = "b.status = '$status_filter'";
}
if ($search) {
    $search = sanitize($search);
    $where[] = "(b.biz_name LIKE '%$search%' OR u.fullname LIKE '%$search%')";
}
$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get all businesses
$query = "SELECT b.*, u.fullname as owner_name, c.cat_name 
          FROM businesses b 
          JOIN users u ON b.owner_id = u.user_id 
          JOIN categories c ON b.cat_id = c.cat_id 
          $where_clause
          ORDER BY b.created_at DESC";
$businesses = mysqli_query($conn, $query);

// Count businesses by status
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM businesses"))['count'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM businesses WHERE status = 'pending'"))['count'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM businesses WHERE status = 'approved'"))['count'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM businesses WHERE status = 'rejected'"))['count'];

include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-dark text-white sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link text-white active" href="businesses.php"><i class="fas fa-store"></i> Businesses</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Businesses</h1>
            </div>
            
            <?php if(isset($_SESSION['success'])): ?>
                <?php echo showSuccess($_SESSION['success']); unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6>Total Businesses</h6>
                            <h3><?php echo $total; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h6>Pending Approval</h6>
                            <h3><?php echo $pending; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6>Approved</h6>
                            <h3><?php echo $approved; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h6>Rejected</h6>
                            <h3><?php echo $rejected; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Search by business name or owner..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Business Name</th>
                                    <th>Owner</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Featured</th>
                                    <th>Views</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($businesses) > 0): ?>
                                    <?php while($biz = mysqli_fetch_assoc($businesses)): ?>
                                    <tr>
                                        <td><?php echo $biz['biz_id']; ?></td>
                                        <td>
                                            <img src="<?php echo UPLOAD_URL . 'businesses/' . ($biz['logo'] ?? 'default-business.png'); ?>" 
                                                 width="40" height="40" style="object-fit: cover; border-radius: 8px;">
                                        </td>
                                        <td>
                                            <strong><?php echo $biz['biz_name']; ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $biz['city']; ?></small>
                                        </td>
                                        <td><?php echo $biz['owner_name']; ?></td>
                                        <td><?php echo $biz['cat_name']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $biz['status'] == 'approved' ? 'success' : 
                                                    ($biz['status'] == 'pending' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($biz['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($biz['is_featured']): ?>
                                                <span class="badge bg-info">Featured</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($biz['views']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($biz['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if($biz['status'] == 'pending'): ?>
                                                    <a href="?action=approve&id=<?php echo $biz['biz_id']; ?>" class="btn btn-success" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="?action=reject&id=<?php echo $biz['biz_id']; ?>" class="btn btn-danger" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if($biz['is_featured']): ?>
                                                    <a href="?action=unfeature&id=<?php echo $biz['biz_id']; ?>" class="btn btn-warning" title="Remove Featured">
                                                        <i class="fas fa-star"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="?action=feature&id=<?php echo $biz['biz_id']; ?>" class="btn btn-info" title="Make Featured">
                                                        <i class="far fa-star"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="../business-detail.php?id=<?php echo $biz['biz_id']; ?>" class="btn btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $biz['biz_id']; ?>" class="btn btn-dark" title="Delete" onclick="return confirm('Delete this business permanently?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fas fa-store fa-3x text-muted mb-3 d-block"></i>
                                            No businesses found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>