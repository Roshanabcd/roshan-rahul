<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$token = isset($_GET['token']) ? sanitize($_GET['token']) : '';
$error = '';
$success = '';

// Verify token
$query = "SELECT user_id, email FROM users WHERE reset_token = '$token' AND reset_expires > NOW()";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $error = 'Invalid or expired reset link. Please request a new one.';
} else {
    $user = mysqli_fetch_assoc($result);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update = "UPDATE users SET password = '$hashed_password', reset_token = NULL, reset_expires = NULL WHERE user_id = {$user['user_id']}";
            
            if (mysqli_query($conn, $update)) {
                $success = 'Password reset successful! You can now login with your new password.';
                redirect('login.php', 'Password reset successful! Please login.', 'success');
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
}

include 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Reset Password</h2>
                    
                    <?php if($error): ?>
                        <?php echo showError($error); ?>
                    <?php endif; ?>
                    
                    <?php if(empty($error) && empty($success)): ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>