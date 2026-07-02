<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cat_name = sanitize($_POST['cat_name']);
    $cat_slug = createSlug($cat_name);
    $cat_icon = sanitize($_POST['cat_icon']);
    $display_order = (int)$_POST['display_order'];
    
    if (isset($_POST['add_category'])) {
        $query = "INSERT INTO categories (cat_name, cat_slug, cat_icon, display_order) VALUES ('$cat_name', '$cat_slug', '$cat_icon', $display_order)";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = 'Category added successfully';
        }
    } elseif (isset($_POST['edit_category'])) {
        $cat_id = (int)$_POST['cat_id'];
        $query = "UPDATE categories SET cat_name='$cat_name', cat_slug='$cat_slug', cat_icon='$cat_icon', display_order=$display_order WHERE cat_id=$cat_id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = 'Category updated successfully';
        }
    }
    redirect('categories.php');
}

if (isset($_GET['delete'])) {
    $cat_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE cat_id = $cat_id");
    $_SESSION['success'] = 'Category deleted successfully';
    redirect('categories.php');
}

$categories = getAllCategories(false);
$edit_category = null;
if (isset($_GET['edit'])) {
    $cat_id = (int)$_GET['edit'];
    $query = "SELECT * FROM categories WHERE cat_id = $cat_id";
    $result = mysqli_query($conn, $query);
    $edit_category = mysqli_fetch_assoc($result);
}

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
                    <li class="nav-item"><a class="nav-link text-white active" href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Categories</h1>
            </div>
            
            <?php if(isset($_SESSION['success'])): ?>
                <?php echo showSuccess($_SESSION['success']); unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php if($edit_category): ?>
                                    <input type="hidden" name="cat_id" value="<?php echo $edit_category['cat_id']; ?>">
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label class="form-label">Category Name</label>
                                    <input type="text" name="cat_name" class="form-control" value="<?php echo $edit_category['cat_name'] ?? ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icon Class (Font Awesome)</label>
                                    <input type="text" name="cat_icon" class="form-control" value="<?php echo $edit_category['cat_icon'] ?? 'fa-store'; ?>" placeholder="fa-store">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" value="<?php echo $edit_category['display_order'] ?? 0; ?>">
                                </div>
                                <button type="submit" name="<?php echo $edit_category ? 'edit_category' : 'add_category'; ?>" class="btn btn-primary w-100">
                                    <?php echo $edit_category ? 'Update Category' : 'Add Category'; ?>
                                </button>
                                <?php if($edit_category): ?>
                                    <a href="categories.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Icon</th>
                                            <th>Category</th>
                                            <th>Slug</th>
                                            <th>Order</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($categories as $cat): ?>
                                        <tr>
                                            <td><i class="fas <?php echo $cat['cat_icon']; ?>"></i></td>
                                            <td><?php echo $cat['cat_name']; ?></td>
                                            <td><?php echo $cat['cat_slug']; ?></td>
                                            <td><?php echo $cat['display_order']; ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $cat['cat_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="?delete=<?php echo $cat['cat_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>