<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = "Access denied.";
    redirect('../login.php');
}

$pdo = getDBConnection();

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    // Prevent deleting yourself
    if ($deleteId == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id AND role != 'admin'");
            $stmt->execute(['user_id' => $deleteId]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "User deleted successfully.";
            } else {
                $_SESSION['error'] = "Cannot delete admin users.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting user.";
        }
    }
    redirect('users.php');
}

// Get all users
$users = getAllUsers($pdo);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="users.php">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="notes.php">
                            <i class="fas fa-sticky-note me-2"></i>Manage Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-users me-2"></i>Manage Users</h1>
            </div>
            
            <?php echo showFlashMessage('success'); ?>
            <?php echo showFlashMessage('error'); ?>
            
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Roll No</th>
                                    <th>Semester</th>
                                    <th>Role</th>
                                    <th>Notes</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['roll_no'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($user['semester'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><span class="badge bg-info"><?php echo $user['note_count']; ?></span></td>
                                        <td><small><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small></td>
                                        <td>
                                            <?php if ($user['role'] != 'admin'): ?>
                                                <a href="?delete=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-danger btn-sm delete-confirm"
                                                   onclick="return confirm('Are you sure you want to delete this user and all their notes?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Admin</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 48px 0 0; }
.sidebar .nav-link { font-weight: 500; padding: 10px 20px; }
.sidebar .nav-link:hover { background: rgba(255,255,255,0.1); }
@media (max-width: 767.98px) { .sidebar { position: relative; padding: 0; } }
</style>

<?php require_once '../includes/footer.php'; ?>