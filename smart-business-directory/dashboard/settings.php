<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $query = "SELECT password FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    
    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = "UPDATE users SET password = '$hashed' WHERE user_id = $user_id";
        if (mysqli_query($conn, $update)) {
            $success = 'Password changed successfully!';
        }
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
                    <li class="nav-item"><a class="nav-link" href="my-reviews.php"><i class="fas fa-star"></i> My Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link active" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Account Settings</h1>
            </div>
            
            <?php if($success): echo showSuccess($success); endif; ?>
            <?php if($error): echo showError($error); endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <h5>Change Password</h5>
                    <hr>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>