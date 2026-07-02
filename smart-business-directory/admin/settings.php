<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settings = [
        'site_name' => sanitize($_POST['site_name']),
        'site_description' => sanitize($_POST['site_description']),
        'contact_email' => sanitize($_POST['contact_email']),
        'contact_phone' => sanitize($_POST['contact_phone']),
        'contact_address' => sanitize($_POST['contact_address']),
        'businesses_per_page' => (int)$_POST['businesses_per_page'],
        'reviews_per_page' => (int)$_POST['reviews_per_page'],
        'featured_limit' => (int)$_POST['featured_limit'],
        'nearby_radius' => (int)$_POST['nearby_radius']
    ];
    
    foreach ($settings as $key => $value) {
        updateSetting($key, $value);
    }
    
    $success = 'Settings updated successfully!';
}

// Get current settings
$site_name = getSetting('site_name', SITE_NAME);
$site_description = getSetting('site_description', SITE_DESC);
$contact_email = getSetting('contact_email', ADMIN_EMAIL);
$contact_phone = getSetting('contact_phone', '+977 9800000000');
$contact_address = getSetting('contact_address', '123 Business Street, City, India');
$businesses_per_page = getSetting('businesses_per_page', 12);
$reviews_per_page = getSetting('reviews_per_page', 10);
$featured_limit = getSetting('featured_limit', 8);
$nearby_radius = getSetting('nearby_radius', 10);

include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-dark text-white sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="businesses.php"><i class="fas fa-store"></i> Businesses</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-white active" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">System Settings</h1>
            </div>
            
            <?php if($success): echo showSuccess($success); endif; ?>
            <?php if($error): echo showError($error); endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <h5>General Settings</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Name</label>
                                <input type="text" name="site_name" class="form-control" value="<?php echo $site_name; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site Description</label>
                                <input type="text" name="site_description" class="form-control" value="<?php echo $site_description; ?>" required>
                            </div>
                        </div>
                        
                        <h5 class="mt-4">Contact Information</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" name="contact_email" class="form-control" value="<?php echo $contact_email; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="contact_phone" class="form-control" value="<?php echo $contact_phone; ?>" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Contact Address</label>
                                <textarea name="contact_address" class="form-control" rows="2"><?php echo $contact_address; ?></textarea>
                            </div>
                        </div>
                        
                        <h5 class="mt-4">Pagination Settings</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Businesses Per Page</label>
                                <input type="number" name="businesses_per_page" class="form-control" value="<?php echo $businesses_per_page; ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Reviews Per Page</label>
                                <input type="number" name="reviews_per_page" class="form-control" value="<?php echo $reviews_per_page; ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Featured Limit</label>
                                <input type="number" name="featured_limit" class="form-control" value="<?php echo $featured_limit; ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Nearby Radius (km)</label>
                                <input type="number" name="nearby_radius" class="form-control" value="<?php echo $nearby_radius; ?>" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>