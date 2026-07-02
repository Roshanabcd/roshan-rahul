<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'user_id' => $_SESSION['user_id'],
        'title' => sanitize($_POST['title']),
        'description' => sanitize($_POST['description']),
        'category' => sanitize($_POST['category']),
        'urgency' => sanitize($_POST['urgency']),
        'address' => sanitize($_POST['address']),
        'city' => sanitize($_POST['city'])
    ];
    
    $request_id = createSupportRequest($data);
    
    if ($request_id) {
        redirect('my-requests.php', 'Support request posted successfully!', 'success');
    } else {
        $error = 'Failed to post request. Please try again.';
    }
}

include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="mb-4">Post a Support Request</h2>
                    <p class="text-muted mb-4">Describe your problem and businesses will send you offers.</p>
                    
                    <?php if($error): echo showError($error); endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g., Need AC repair" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option>Plumbing</option>
                                <option>Electrical</option>
                                <option>AC Repair</option>
                                <option>Mobile Repair</option>
                                <option>Home Cleaning</option>
                                <option>Painting</option>
                                <option>Carpentry</option>
                                <option>Pest Control</option>
                                <option>Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Urgency *</label>
                            <select name="urgency" class="form-select" required>
                                <option value="low">Low - Can wait a few days</option>
                                <option value="medium">Medium - Need within 2-3 days</option>
                                <option value="high">High - Need within 24 hours</option>
                                <option value="emergency">Emergency - Immediate help needed</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Describe your problem in detail..." required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Address *</label>
                                <input type="text" name="address" class="form-control" placeholder="Your complete address" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" name="city" class="form-control" placeholder="City" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Post Request</button>
                        <a href="my-requests.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>