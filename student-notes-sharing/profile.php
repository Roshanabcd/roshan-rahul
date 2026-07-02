<?php
require_once 'includes/header.php';
require_once 'includes/auth_check.php';

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Get user info
$user = getUserById($userId);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $roll_no = sanitize($_POST['roll_no']);
    $semester = sanitize($_POST['semester']);
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    // Check for password change
    if (!empty($_POST['new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
    }
    
    if (empty($errors)) {
        // Update profile
        if (!empty($_POST['new_password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = :name, roll_no = :roll_no, semester = :semester, password = :password WHERE user_id = :user_id");
            $stmt->execute([
                'name' => $name,
                'roll_no' => $roll_no,
                'semester' => $semester,
                'password' => $hashed_password,
                'user_id' => $userId
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = :name, roll_no = :roll_no, semester = :semester WHERE user_id = :user_id");
            $stmt->execute([
                'name' => $name,
                'roll_no' => $roll_no,
                'semester' => $semester,
                'user_id' => $userId
            ]);
        }
        
        $_SESSION['user_name'] = $name;
        $success = "Profile updated successfully!";
        $user = getUserById($userId); // Refresh user data
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="auth-form">
                <h3 class="mb-4"><i class="fas fa-user-cog me-2"></i>Profile Settings</h3>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo sanitize($user['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo sanitize($user['email']); ?>" disabled>
                        <small class="text-muted">Email cannot be changed.</small>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Roll Number</label>
                            <input type="text" name="roll_no" class="form-control" value="<?php echo sanitize($user['roll_no']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select">
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>th" <?php echo ($user['semester'] == $i.'th') ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>th Semester
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    <h5 class="mb-3">Change Password (optional)</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>