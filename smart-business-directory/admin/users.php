<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'activate') {
        mysqli_query($conn, "UPDATE users SET is_active = 1 WHERE user_id = $user_id");
        $_SESSION['success'] = 'User activated successfully';
    } elseif ($action == 'deactivate') {
        mysqli_query($conn, "UPDATE users SET is_active = 0 WHERE user_id = $user_id");
        $_SESSION['success'] = 'User deactivated successfully';
    } elseif ($action == 'delete') {
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        $_SESSION['success'] = 'User deleted successfully';
    } elseif ($action == 'make_admin') {
        mysqli_query($conn, "UPDATE users SET role = 'admin' WHERE user_id = $user_id");
        $_SESSION['success'] = 'User promoted to admin';
    } elseif ($action == 'make_owner') {
        mysqli_query($conn, "UPDATE users SET role = 'business_owner' WHERE user_id = $user_id");
        $_SESSION['success'] = 'User role changed to business owner';
    } elseif ($action == 'make_user') {
        mysqli_query($conn, "UPDATE users SET role = 'user' WHERE user_id = $user_id");
        $_SESSION['success'] = 'User role changed to regular user';
    }
    redirect('users.php');
}

// Get all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$users = mysqli_query($conn, $query);

include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-dark text-white sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white active" href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="businesses.php"><i class="fas fa-store"></i> Businesses</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Users</h1>
                <span class="text-muted">Total Users: <?php echo mysqli_num_rows($users); ?></span>
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
                                    <th>Avatar</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($user = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td>
                                        <img src="<?php echo UPLOAD_URL . 'avatars/' . ($user['profile_image'] ?? 'default-avatar.png'); ?>" 
                                             width="40" height="40" class="rounded-circle" style="object-fit: cover;">
                                    </td>
                                    <td><?php echo $user['fullname']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['phone'] ?? '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'business_owner' ? 'primary' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($user['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if($user['is_active']): ?>
                                                <a href="?action=deactivate&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning">Deactivate</a>
                                            <?php else: ?>
                                                <a href="?action=activate&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-success">Activate</a>
                                            <?php endif; ?>
                                            
                                            <?php if($user['role'] != 'admin'): ?>
                                                <a href="?action=make_admin&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger">Make Admin</a>
                                            <?php endif; ?>
                                            
                                            <?php if($user['role'] != 'business_owner'): ?>
                                                <a href="?action=make_owner&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info">Make Owner</a>
                                            <?php endif; ?>
                                            
                                            <a href="?action=delete&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-dark" onclick="return confirm('Delete this user?')">Delete</a>
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