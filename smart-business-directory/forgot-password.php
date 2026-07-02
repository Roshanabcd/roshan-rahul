<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        $query = "SELECT user_id, fullname FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $token = generateRandomString(64);
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $update = "UPDATE users SET reset_token = '$token', reset_expires = '$expires' WHERE email = '$email'";
            mysqli_query($conn, $update);
            
            $reset_link = SITE_URL . "reset-password.php?token=" . $token;
            $subject = "Password Reset Request - " . SITE_NAME;
            $message = "
            <html>
            <body>
                <h2>Password Reset Request</h2>
                <p>Hello {$user['fullname']},</p>
                <p>We received a request to reset your password. Click the link below to reset it:</p>
                <p><a href='$reset_link'>$reset_link</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
            </body>
            </html>
            ";
            
            sendEmail($email, $subject, $message);
            $success = 'Password reset link has been sent to your email.';
        } else {
            $error = 'No account found with this email address.';
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
                    <h2 class="text-center mb-4">Forgot Password?</h2>
                    <p class="text-center text-muted mb-4">Enter your email to reset your password</p>
                    
                    <?php if($success): ?>
                        <?php echo showSuccess($success); ?>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <?php echo showError($error); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>