<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$search_query = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$location = isset($_GET['location']) ? sanitize($_GET['location']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = BUSINESSES_PER_PAGE;
$offset = ($page - 1) * $limit;

// Build query
$where = ["b.status = 'approved'"];

if (!empty($search_query)) {
    $where[] = "(b.biz_name LIKE '%$search_query%' OR b.description LIKE '%$search_query%' OR b.tagline LIKE '%$search_query%')";
}

if (!empty($location)) {
    $where[] = "(b.city LIKE '%$location%' OR b.address LIKE '%$location%')";
}

if ($category > 0) {
    $where[] = "b.cat_id = $category";
}

$where_clause = implode(" AND ", $where);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM businesses b WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Get businesses
$query = "SELECT b.*, c.cat_name,
          COALESCE(AVG(r.rating), 0) as avg_rating,
          COUNT(r.review_id) as review_count
          FROM businesses b 
          JOIN categories c ON b.cat_id = c.cat_id 
          LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
          WHERE $where_clause
          GROUP BY b.biz_id
          ORDER BY b.created_at DESC
          LIMIT $limit OFFSET $offset";
$businesses = mysqli_query($conn, $query);

// Get categories for filter
$categories = getAllCategories();

include 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-filter me-2"></i>Filter Results</h5>
                    <form method="GET" action="">
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="City or area" value="<?php echo htmlspecialchars($location); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['cat_id']; ?>" <?php echo $category == $cat['cat_id'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['cat_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        <a href="search.php?q=<?php echo urlencode($search_query); ?>" class="btn btn-link w-100 mt-2">Clear Filters</a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Results -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
                <span class="text-muted"><?php echo $total_rows; ?> businesses found</span>
            </div>
            
            <?php if(mysqli_num_rows($businesses) > 0): ?>
                <div class="row g-4">
                    <?php while($business = mysqli_fetch_assoc($businesses)): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card business-card h-100 shadow-sm">
                            <img src="<?php echo UPLOAD_URL . 'businesses/' . ($business['logo'] ?? 'default-business.png'); ?>" 
                                 class="card-img-top" alt="<?php echo $business['biz_name']; ?>"
                                 style="height: 180px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $business['biz_name']; ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-tag me-1"></i><?php echo $business['cat_name']; ?>
                                </p>
                                <p class="card-text text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo $business['city']; ?>
                                </p>
                                <div class="mb-2">
                                    <?php echo displayStars($business['avg_rating']); ?>
                                    <span class="text-muted small">(<?php echo $business['review_count']; ?> reviews)</span>
                                </div>
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
                            <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&location=<?php echo urlencode($location); ?>&category=<?php echo $category; ?>&page=<?php echo $page-1; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= min($total_pages, 5); $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&location=<?php echo urlencode($location); ?>&category=<?php echo $category; ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&location=<?php echo urlencode($location); ?>&category=<?php echo $category; ?>&page=<?php echo $page+1; ?>">
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
                    <h4>No results found</h4>
                    <p>Try different keywords or browse all businesses</p>
                    <a href="businesses.php" class="btn btn-primary">Browse All Businesses</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>