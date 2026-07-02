<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get filter parameters
$category_slug = isset($_GET['category']) ? $_GET['category'] : '';
$city = isset($_GET['city']) ? $_GET['city'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$min_rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = getSetting('businesses_per_page') ?: 12;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["b.status = 'approved'"];

if (!empty($category_slug)) {
    $where_conditions[] = "c.cat_slug = '$category_slug'";
}

if (!empty($city)) {
    $where_conditions[] = "b.city LIKE '%$city%'";
}

if (!empty($search)) {
    $search = sanitize($search);
    $where_conditions[] = "(b.biz_name LIKE '%$search%' OR b.description LIKE '%$search%')";
}

if ($min_rating > 0) {
    $where_conditions[] = "b.average_rating >= $min_rating";
}

$where_clause = implode(' AND ', $where_conditions);

// Order by
$order_by = "b.created_at DESC";
switch($sort) {
    case 'rating':
        $order_by = "b.average_rating DESC";
        break;
    case 'reviews':
        $order_by = "b.total_reviews DESC";
        break;
    case 'views':
        $order_by = "b.views DESC";
        break;
    case 'oldest':
        $order_by = "b.created_at ASC";
        break;
    default:
        $order_by = "b.created_at DESC";
}

// Get businesses
$query = "SELECT b.*, c.cat_name, c.cat_slug,
          (SELECT AVG(rating) FROM reviews WHERE biz_id = b.biz_id AND is_approved = 1) as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE biz_id = b.biz_id AND is_approved = 1) as review_count
          FROM businesses b 
          JOIN categories c ON b.cat_id = c.cat_id
          WHERE $where_clause
          ORDER BY $order_by
          LIMIT $offset, $limit";
$businesses = mysqli_query($conn, $query);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM businesses b 
                JOIN categories c ON b.cat_id = c.cat_id 
                WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Get categories for filter
$categories = getAllCategories();

// Get cities for filter
$cities_query = "SELECT DISTINCT city FROM businesses WHERE status = 'approved' AND city IS NOT NULL AND city != ''";
$cities_result = mysqli_query($conn, $cities_query);
$cities = [];
while ($row = mysqli_fetch_assoc($cities_result)) {
    $cities[] = $row['city'];
}

include 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Businesses</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="filterForm">
                        <?php if(!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['cat_slug']; ?>" <?php echo $category_slug == $cat['cat_slug'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['cat_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">City</label>
                            <select name="city" class="form-select" onchange="this.form.submit()">
                                <option value="">All Cities</option>
                                <?php foreach($cities as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo $city == $c ? 'selected' : ''; ?>>
                                    <?php echo $c; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Minimum Rating</label>
                            <select name="rating" class="form-select" onchange="this.form.submit()">
                                <option value="0">Any Rating</option>
                                <option value="1" <?php echo $min_rating == 1 ? 'selected' : ''; ?>>★ 1 & above</option>
                                <option value="2" <?php echo $min_rating == 2 ? 'selected' : ''; ?>>★ 2 & above</option>
                                <option value="3" <?php echo $min_rating == 3 ? 'selected' : ''; ?>>★ 3 & above</option>
                                <option value="4" <?php echo $min_rating == 4 ? 'selected' : ''; ?>>★ 4 & above</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sort By</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="reviews" <?php echo $sort == 'reviews' ? 'selected' : ''; ?>>Most Reviewed</option>
                                <option value="views" <?php echo $sort == 'views' ? 'selected' : ''; ?>>Most Viewed</option>
                            </select>
                        </div>
                        
                        <a href="businesses.php" class="btn btn-secondary w-100">
                            <i class="fas fa-undo-alt me-2"></i>Clear Filters
                        </a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Business Listings -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <?php if(!empty($search)): ?>
                        Search Results for: "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        All Businesses
                    <?php endif; ?>
                </h4>
                <span class="text-muted"><?php echo $total_rows; ?> businesses found</span>
            </div>
            
            <?php if(mysqli_num_rows($businesses) > 0): ?>
                <div class="row g-4">
                    <?php while($business = mysqli_fetch_assoc($businesses)): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card business-card h-100 shadow-sm border-0 rounded-3">
                            <img src="<?php echo UPLOAD_URL . 'businesses/' . ($business['logo'] ?? 'default-business.png'); ?>" 
                                 class="card-img-top" alt="<?php echo $business['biz_name']; ?>"
                                 style="height: 160px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $business['biz_name']; ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-tag me-1"></i><?php echo $business['cat_name']; ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo $business['city']; ?>
                                </p>
                                <div class="mb-2">
                                    <?php echo displayStars($business['avg_rating']); ?>
                                    <span class="text-muted small">(<?php echo $business['review_count']; ?>)</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pb-3">
                                <a href="business-detail.php?id=<?php echo $business['biz_id']; ?>" class="btn btn-primary btn-sm w-100">
                                    View Details <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&category=<?php echo $category_slug; ?>&city=<?php echo $city; ?>&rating=<?php echo $min_rating; ?>&sort=<?php echo $sort; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category_slug; ?>&city=<?php echo $city; ?>&rating=<?php echo $min_rating; ?>&sort=<?php echo $sort; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&category=<?php echo $category_slug; ?>&city=<?php echo $city; ?>&rating=<?php echo $min_rating; ?>&sort=<?php echo $sort; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h5>No businesses found</h5>
                    <p>Try adjusting your search or filter criteria</p>
                    <a href="businesses.php" class="btn btn-primary">View All Businesses</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.business-card {
    transition: all 0.3s ease;
}
.business-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}
.card-header {
    border-bottom: 1px solid #eee;
}
</style>

<?php include 'includes/footer.php'; ?> 