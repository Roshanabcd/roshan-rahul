<?php
/**
 * ============================================
 * REGISTRATION PAGE
 * ============================================
 * Handles new user registration with validation
 */

require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDBConnection();
    
    // ============================================
    // STEP 1: Collect and sanitize form data
    // ============================================
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $roll_no = trim($_POST['roll_no'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // ============================================
    // STEP 2: Server-side validation
    // ============================================
    
    // Validate name (2-50 characters, letters and spaces only)
    if (empty($name)) {
        $errors['name'] = "Name is required.";
    } elseif (strlen($name) < 2 || strlen($name) > 50) {
        $errors['name'] = "Name must be between 2 and 50 characters.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors['name'] = "Name can only contain letters and spaces.";
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    } elseif (strlen($email) > 100) {
        $errors['email'] = "Email must be less than 100 characters.";
    }
    
    // Validate roll number (optional but if provided must be alphanumeric)
    if (!empty($roll_no) && !preg_match("/^[a-zA-Z0-9\-]+$/", $roll_no)) {
        $errors['roll_no'] = "Roll number can only contain letters, numbers, and hyphens.";
    }
    
    // Validate semester
    $valid_semesters = ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th'];
    if (empty($semester)) {
        $errors['semester'] = "Please select your semester.";
    } elseif (!in_array($semester, $valid_semesters)) {
        $errors['semester'] = "Invalid semester selected.";
    }
    
    // Validate password strength
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    } elseif (strlen($password) > 50) {
        $errors['password'] = "Password must be less than 50 characters.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors['password'] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors['password'] = "Password must contain at least one number.";
    }
    
    // Validate confirm password
    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }
    
    // ============================================
    // STEP 3: Check for duplicate email
    // ============================================
    if (!isset($errors['email'])) {
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            
            if ($stmt->fetch()) {
                $errors['email'] = "This email is already registered. Please use a different email or login.";
            }
        } catch (PDOException $e) {
            error_log("Registration email check error: " . $e->getMessage());
            $errors['general'] = "A system error occurred. Please try again later.";
        }
    }
    
    // ============================================
    // STEP 4: If no errors, create user account
    // ============================================
    if (empty($errors)) {
        try {
            // Hash the password using bcrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
            
            // Insert new user into database
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, roll_no, semester, role) 
                VALUES (:name, :email, :password, :roll_no, :semester, 'student')
            ");
            
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashed_password,
                'roll_no' => $roll_no,
                'semester' => $semester
            ]);
            
            // Get the new user ID
            $newUserId = $pdo->lastInsertId();
            
            // Log the registration (optional)
            error_log("New user registered: ID={$newUserId}, Email={$email}");
            
            // Set success message and redirect to login
            $_SESSION['success'] = "Registration successful! Please login with your credentials.";
            redirect('login.php');
            
        } catch (PDOException $e) {
            error_log("Registration insert error: " . $e->getMessage());
            
            if ($e->getCode() == '23000') {
                $errors['email'] = "This email is already registered.";
            } else {
                $errors['general'] = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!-- ============================================ -->
<!-- REGISTRATION FORM HTML -->
<!-- ============================================ -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card shadow-lg border-0 rounded-3">
                <!-- Card Header -->
                <div class="card-header bg-primary text-white text-center py-3">
                    <h3 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </h3>
                    <small>Join Student Notes Sharing Platform</small>
                </div>
                
                <!-- Card Body -->
                <div class="card-body p-4">
                    
                    <!-- General Error -->
                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $errors['general']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Registration Form -->
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" 
                          id="registrationForm" novalidate>
                        
                        <!-- Full Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                       placeholder="Enter your full name"
                                       maxlength="50"
                                       required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       placeholder="yourname@example.com"
                                       maxlength="100"
                                       required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Roll Number & Semester Row -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="roll_no" class="form-label">Roll Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                    <input type="text" 
                                           name="roll_no" 
                                           id="roll_no" 
                                           class="form-control <?php echo isset($errors['roll_no']) ? 'is-invalid' : ''; ?>"
                                           value="<?php echo isset($_POST['roll_no']) ? htmlspecialchars($_POST['roll_no']) : ''; ?>"
                                           placeholder="e.g., NIC-001"
                                           maxlength="20">
                                    <?php if (isset($errors['roll_no'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['roll_no']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="semester" class="form-label">
                                    Semester <span class="text-danger">*</span>
                                </label>
                                <select name="semester" id="semester" class="form-select <?php echo isset($errors['semester']) ? 'is-invalid' : ''; ?>" required>
                                    <option value="">-- Select Semester --</option>
                                    <?php 
                                    $valid_semesters = ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th'];
                                    foreach ($valid_semesters as $sem): 
                                    ?>
                                        <option value="<?php echo $sem; ?>" <?php echo (isset($_POST['semester']) && $_POST['semester'] == $sem) ? 'selected' : ''; ?>>
                                            <?php echo $sem; ?> Semester
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['semester'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['semester']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                                       placeholder="Create a strong password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Must be at least 6 characters with 1 uppercase letter and 1 number.</div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">
                                Confirm Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       name="confirm_password" 
                                       id="confirm_password" 
                                       class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                                       placeholder="Confirm your password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Card Footer -->
                <div class="card-footer bg-light text-center py-3">
                    <p class="mb-0">
                        Already have an account? 
                        <a href="login.php" class="fw-bold">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Password Visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
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

document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const confirmInput = document.getElementById('confirm_password');
    const icon = this.querySelector('i');
    
    if (confirmInput.type === 'password') {
        confirmInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        confirmInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
                           