<?php
/**
 * Login Page
 * 
 * Allows users to log in to the platform
 */

require_once 'includes/header.php';

// If already logged in, redirect
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$debug = false; // Set to false in production

// Enable debug mode if parameter present (remove in production)
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    $debug = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Basic validation
        if (empty($email) || empty($password)) {
            $error = "Please enter both email and password.";
        } else {
            // Find user by email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Debug info (remove in production)
                if ($debug) {
                    echo '<div class="alert alert-info">';
                    echo '<strong>Debug Info:</strong><br>';
                    echo 'User Found: ' . htmlspecialchars($user['name']) . '<br>';
                    echo 'Role: ' . htmlspecialchars($user['role']) . '<br>';
                    echo 'Password Hash: ' . htmlspecialchars(substr($user['password'], 0, 30)) . '...<br>';
                    echo 'Password Verify Result: ' . (password_verify($password, $user['password']) ? 'TRUE' : 'FALSE') . '<br>';
                    echo '</div>';
                }
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in_time'] = time();
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set success message
                    $_SESSION['success'] = "Welcome back, " . $user['name'] . "!";
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        redirect('admin/index.php');
                    } else {
                        redirect('dashboard.php');
                    }
                } else {
                    $error = "Invalid email or password.";
                    
                    // Check if password needs rehash
                    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                        // Update password hash in database
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
                        $updateStmt->execute([
                            'password' => $newHash,
                            'user_id' => $user['user_id']
                        ]);
                    }
                }
            } else {
                $error = "Invalid email or password.";
                
                // For debugging: show if any users exist
                if ($debug) {
                    $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
                    $count = $countStmt->fetchColumn();
                    echo '<div class="alert alert-warning">';
                    echo 'Total users in database: ' . $count;
                    echo '</div>';
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $error = "Database error. Please try again later.";
        
        if ($debug) {
            $error .= " Error: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <!-- Login Card -->
            <div class="card shadow-sm mt-5">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </h4>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Error Message -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Flash Messages -->
                    <?php echo showFlashMessage('success'); ?>
                    <?php echo showFlashMessage('error'); ?>
                    <?php echo showFlashMessage('info'); ?>
                    
                    <!-- Login Form -->
                    <form method="POST" action="" novalidate>
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       class="form-control" 
                                       placeholder="Enter your email"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required
                                       autocomplete="email">
                            </div>
                        </div>
                        
                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control" 
                                       placeholder="Enter your password"
                                       required
                                       autocomplete="current-password">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                        
                        <!-- Register Link -->
                        <div class="text-center">
                            <p class="mb-0">
                                Don't have an account? 
                                <a href="register.php" class="fw-bold">Register here</a>
                            </p>
                        </div>
                    </form>
                    
                </div>
            </div>
            
            <!-- Demo Credentials (Remove in production) -->
            <div class="card mt-3 border-info">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-2"></i>Demo Credentials
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td><strong>Admin:</strong></td>
                            <td>admin@notes.com</td>
                            <td>admin123</td>
                        </tr>
                        <tr>
                            <td><strong>Student:</strong></td>
                            <td>student@test.com</td>
                            <td>student123</td>
                        </tr>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Password Toggle Script -->
<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    var passwordInput = document.getElementById('password');
    var icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>