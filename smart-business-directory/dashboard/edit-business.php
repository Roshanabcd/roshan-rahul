<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$biz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get business details
$query = "SELECT * FROM businesses WHERE biz_id = $biz_id AND owner_id = {$_SESSION['user_id']}";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Business not found';
    redirect('my-businesses.php');
}

$business = mysqli_fetch_assoc($result);
$categories = getAllCategories();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $biz_name = sanitize($_POST['biz_name']);
    $cat_id = (int)$_POST['cat_id'];
    $description = sanitize($_POST['description']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $website = sanitize($_POST['website']);
    $latitude = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;
    
    if (empty($biz_name) || empty($cat_id) || empty($description) || empty($address) || empty($city)) {
        $error = 'Please fill in all required fields';
    } else {
        $slug = createSlug($biz_name);
        $logo = $business['logo'];
        
        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $upload_result = uploadImage($_FILES['logo'], 'businesses', $logo != 'default-business.png' ? $logo : null);
            if (isset($upload_result['success'])) {
                $logo = $upload_result['filename'];
            } elseif (isset($upload_result['error'])) {
                $error = $upload_result['error'];
            }
        }
        
        if (empty($error)) {
            $update_query = "UPDATE businesses SET 
                            cat_id = $cat_id,
                            biz_name = '$biz_name',
                            slug = '$slug',
                            description = '$description',
                            address = '$address',
                            city = '$city',
                            phone = '$phone',
                            email = '$email',
                            website = '$website',
                            latitude = " . ($latitude ?: 'NULL') . ",
                            longitude = " . ($longitude ?: 'NULL') . ",
                            logo = '$logo',
                            status = 'pending'
                            WHERE biz_id = $biz_id";
            
            if (mysqli_query($conn, $update_query)) {
                $success = 'Business updated successfully! It will be reviewed again by admin.';
                // Refresh business data
                $result = mysqli_query($conn, "SELECT * FROM businesses WHERE biz_id = $biz_id");
                $business = mysqli_fetch_assoc($result);
            } else {
                $error = 'Failed to update business: ' . mysqli_error($conn);
            }
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
                    <li class="nav-item"><a class="nav-link active" href="my-businesses.php"><i class="fas fa-store"></i> My Businesses</a></li>
                    <li class="nav-item"><a class="nav-link" href="add-business.php"><i class="fas fa-plus-circle"></i> Add Business</a></li>
                    <li class="nav-item"><a class="nav-link" href="my-reviews.php"><i class="fas fa-star"></i> My Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Business</h1>
                <a href="my-businesses.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
            
            <?php if($error): echo showError($error); endif; ?>
            <?php if($success): echo showSuccess($success); endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="biz_name" class="form-label">Business Name *</label>
                                <input type="text" class="form-control" id="biz_name" name="biz_name" 
                                       value="<?php echo htmlspecialchars($business['biz_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="cat_id" class="form-label">Category *</label>
                                <select class="form-select" id="cat_id" name="cat_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['cat_id']; ?>" 
                                        <?php echo $business['cat_id'] == $cat['cat_id'] ? 'selected' : ''; ?>>
                                        <?php echo $cat['cat_name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($business['description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="address" class="form-label">Address *</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo htmlspecialchars($business['address']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($business['city']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($business['phone']); ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($business['email']); ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       value="<?php echo htmlspecialchars($business['website']); ?>">
                            </div>
                        </div>
                        
                        <!-- Location Picker -->
                        <div class="mb-3">
                            <label class="form-label">Business Location (Optional)</label>
                            <div id="locationPicker" style="height: 300px; width: 100%; border-radius: 10px; margin-bottom: 10px;"></div>
                            <input type="hidden" name="latitude" id="latitude" value="<?php echo $business['latitude']; ?>">
                            <input type="hidden" name="longitude" id="longitude" value="<?php echo $business['longitude']; ?>">
                            <small class="text-muted">Click on map to set your business location</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="logo" class="form-label">Business Logo</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*" onchange="previewImage(this, 'logoPreview')">
                                <small class="text-muted">Current: <?php echo $business['logo']; ?></small>
                                <div class="mt-2">
                                    <img id="logoPreview" src="<?php echo UPLOAD_URL . 'businesses/' . $business['logo']; ?>" 
                                         width="100" class="img-thumbnail">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Business</button>
                        <a href="my-businesses.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
<script>
let map, marker;

function initMap() {
    const defaultLocation = { lat: 27.7172, lng: 85.3240 };
    const currentLat = parseFloat(document.getElementById('latitude').value);
    const currentLng = parseFloat(document.getElementById('longitude').value);
    const center = (currentLat && currentLng) ? { lat: currentLat, lng: currentLng } : defaultLocation;
    
    map = new google.maps.Map(document.getElementById('locationPicker'), {
        zoom: 13,
        center: center,
        mapTypeId: 'roadmap'
    });
    
    if (currentLat && currentLng) {
        marker = new google.maps.Marker({
            position: center,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP
        });
        
        marker.addListener('dragend', function() {
            updateLocation(marker.getPosition().lat(), marker.getPosition().lng());
        });
    }
    
    map.addListener('click', function(e) {
        if (marker) marker.setMap(null);
        marker = new google.maps.Marker({
            position: e.latLng,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP
        });
        
        marker.addListener('dragend', function() {
            updateLocation(marker.getPosition().lat(), marker.getPosition().lng());
        });
        
        updateLocation(e.latLng.lat(), e.latLng.lng());
    });
}

function updateLocation(lat, lng) {
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
}

function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php'; ?>