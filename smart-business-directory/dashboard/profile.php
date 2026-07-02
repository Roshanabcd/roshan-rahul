<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];
$user_data = getUserById($user_id);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = sanitize($_POST['fullname']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $pincode = sanitize($_POST['pincode']);
    
    // Handle avatar upload
    $profile_image = $user_data['profile_image'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $upload_result = uploadImage($_FILES['avatar'], 'avatars', $profile_image != 'default-avatar.png' ? $profile_image : null);
        if (isset($upload_result['success'])) {
            $profile_image = $upload_result['filename'];
        } elseif (isset($upload_result['error'])) {
            $error = $upload_result['error'];
        }
    }
    
    if (empty($error)) {
        $query = "UPDATE users SET fullname='$fullname', phone='$phone', address='$address', city='$city', state='$state', pincode='$pincode', profile_image='$profile_image' WHERE user_id=$user_id";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['fullname'] = $fullname;
            $_SESSION['profile_image'] = $profile_image;
            $success = 'Profile updated successfully!';
            $user_data = getUserById($user_id);
        } else {
            $error = 'Failed to update profile';
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
                    <li class="nav-item"><a class="nav-link active" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Profile</h1>
            </div>
            
            <?php if($success): echo showSuccess($success); endif; ?>
            <?php if($error): echo showError($error); endif; ?>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <img src="<?php echo UPLOAD_URL . 'avatars/' . ($user_data['profile_image'] ?? 'default-avatar.png'); ?>" 
                                 class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;" id="avatarPreview">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*" onchange="previewAvatar(this)">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('avatarInput').click()">
                                    <i class="fas fa-camera"></i> Change Avatar
                                </button>
                                <hr>
                                <div class="text-start">
                                    <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($user_data['created_at'])); ?></p>
                                    <p><strong>Email:</strong> <?php echo $user_data['email']; ?></p>
                                    <p><strong>Role:</strong> <?php echo ucfirst($user_data['role']); ?></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="fullname" class="form-control" value="<?php echo $user_data['fullname']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control" value="<?php echo $user_data['phone']; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" value="<?php echo $user_data['city']; ?>">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea name="address" class="form-control" rows="2"><?php echo $user_data['address']; ?></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" class="form-control" value="<?php echo $user_data['state']; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">PIN Code</label>
                                        <input type="text" name="pincode" class="form-control" value="<?php echo $user_data['pincode']; ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
            input.form.submit();
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php'; ?>