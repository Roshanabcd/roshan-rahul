<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get featured businesses
$featured_businesses = getFeaturedBusinesses();

// Get recent businesses
$recent_businesses = getRecentBusinesses(8);

// Get popular businesses
$popular_businesses = getPopularBusinesses(8);

// Get main categories
$categories = getMainCategories();

// Get site settings
$site_name = getSetting('site_name', SITE_NAME);
$site_desc = getSetting('site_description', SITE_DESC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="<?php echo $site_desc; ?>">
    <meta name="keywords" content="local business, business directory, find business, local services">
    <meta name="author" content="LocalConnect">
    <meta property="og:title" content="<?php echo $site_name; ?>">
    <meta property="og:description" content="<?php echo $site_desc; ?>">
    <meta property="og:type" content="website">
    <title><?php echo $site_name; ?> - Find Local Businesses Near You</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section position-relative">
    <div class="hero-overlay"></div>
    <div class="container position-relative z-1 py-5">
        <div class="row min-vh-75 align-items-center">
            <div class="col-lg-7 text-white" data-aos="fade-right">
                <h1 class="display-3 fw-bold mb-3">Find Best <span class="text-warning">Local Businesses</span> Near You</h1>
                <p class="lead mb-4">Discover trusted local businesses, read authentic reviews, and get professional services at your doorstep.</p>
                
                <!-- Search Form -->
                <form action="search.php" method="GET" class="mt-4">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-primary"></i></span>
                                <input type="text" name="q" class="form-control form-control-lg border-0" placeholder="What are you looking for?" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-map-marker-alt text-danger"></i></span>
                                <input type="text" name="location" class="form-control form-control-lg border-0" id="locationInput" placeholder="Enter your city">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-gradient btn-lg w-100">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="mt-4 d-flex flex-wrap gap-3">
                    <div><i class="fas fa-check-circle me-1"></i> 10,000+ Businesses</div>
                    <div><i class="fas fa-star me-1 text-warning"></i> 50,000+ Reviews</div>
                    <div><i class="fas fa-users me-1"></i> 100,000+ Happy Customers</div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block" data-aos="fade-left">
                <img src="assets/images/hero-illustration.svg" alt="Hero" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="fw-bold">Browse by <span class="text-gradient">Category</span></h2>
            <p class="text-muted">Explore businesses across various categories</p>
        </div>
        <div class="row g-4">
            <?php foreach(array_slice($categories, 0, 8) as $category): ?>
            <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo $category['display_order'] * 50; ?>">
                <a href="businesses.php?category=<?php echo $category['cat_slug']; ?>" class="text-decoration-none">
                    <div class="category-card text-center p-4 bg-white rounded-4 shadow-sm h-100 transition">
                        <div class="icon-wrapper bg-primary-soft rounded-circle mx-auto mb-3">
                            <i class="fas <?php echo $category['cat_icon']; ?> fa-2x text-primary"></i>
                        </div>
                        <h5 class="mb-0"><?php echo $category['cat_name']; ?></h5>
                        <small class="text-muted"><?php echo rand(50, 500); ?> businesses</small>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="businesses.php" class="btn btn-outline-primary">View All Categories <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>

<!-- Featured Businesses -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="fw-bold">Featured <span class="text-gradient">Businesses</span></h2>
            <p class="text-muted">Handpicked top-rated businesses for you</p>
        </div>
        <div class="row g-4">
            <?php if(!empty($featured_businesses)): ?>
                <?php foreach($featured_businesses as $business): ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="business-card bg-white rounded-4 overflow-hidden shadow-sm h-100">
                        <div class="position-relative">
                            <img src="<?php echo UPLOAD_URL . 'businesses/' . ($business['logo'] ?? 'default-business.png'); ?>" 
                                 class="card-img-top" alt="<?php echo $business['biz_name']; ?>"
                                 style="height: 200px; object-fit: cover; width: 100%;">
                            <span class="position-absolute top-0 end-0 m-2 badge bg-warning">
                                <i class="fas fa-star me-1"></i><?php echo number_format($business['avg_rating'], 1); ?>
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <h5 class="card-title mb-1"><?php echo $business['biz_name']; ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-tag me-1"></i><?php echo $business['cat_name']; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="star-rating">
                                    <?php echo displayStars($business['avg_rating']); ?>
                                    <span class="text-muted ms-1">(<?php echo $business['review_count']; ?>)</span>
                                </div>
                                <small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo $business['city']; ?></small>
                            </div>
                            <a href="business-detail.php?id=<?php echo $business['biz_id']; ?>" class="btn btn-primary w-100">
                                View Details <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No featured businesses yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="fw-bold">How <span class="text-gradient">It Works</span></h2>
            <p class="text-muted">Simple steps to find the best local businesses</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="step-card text-center p-4">
                    <div class="step-number mx-auto mb-3">1</div>
                    <i class="fas fa-search fa-3x text-primary mb-3"></i>
                    <h5>Search</h5>
                    <p class="text-muted">Search for businesses by name, category, or location</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="step-card text-center p-4">
                    <div class="step-number mx-auto mb-3">2</div>
                    <i class="fas fa-star fa-3x text-primary mb-3"></i>
                    <h5>Compare</h5>
                    <p class="text-muted">Read reviews, check ratings, and compare businesses</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="step-card text-center p-4">
                    <div class="step-number mx-auto mb-3">3</div>
                    <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                    <h5>Connect</h5>
                    <p class="text-muted">Contact businesses directly to get the services you need</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Businesses -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
            <h2 class="fw-bold mb-0">Recently <span class="text-gradient">Added</span></h2>
            <a href="businesses.php?sort=newest" class="btn btn-sm btn-outline-primary">View All <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach($recent_businesses as $business): ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="business-card bg-white rounded-4 overflow-hidden shadow-sm h-100">
                    <img src="<?php echo UPLOAD_URL . 'businesses/' . ($business['logo'] ?? 'default-business.png'); ?>" 
                         class="card-img-top" alt="<?php echo $business['biz_name']; ?>"
                         style="height: 160px; object-fit: cover; width: 100%;">
                    <div class="card-body p-3">
                        <h6 class="card-title mb-1"><?php echo $business['biz_name']; ?></h6>
                        <div class="star-rating small">
                            <?php echo displayStars($business['avg_rating']); ?>
                        </div>
                        <small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo $business['city']; ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-gradient text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Own a Business?</h2>
        <p class="lead mb-4">List your business on our platform and reach thousands of potential customers!</p>
        <?php if(isLoggedIn()): ?>
            <a href="dashboard/add-business.php" class="btn btn-light btn-lg px-5">Add Your Business</a>
        <?php else: ?>
            <a href="register.php" class="btn btn-light btn-lg px-5">Register as Business Owner</a>
        <?php endif; ?>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="fw-bold">What Our <span class="text-gradient">Customers Say</span></h2>
            <p class="text-muted">Trusted by thousands of users</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm">
                    <i class="fas fa-quote-left fa-2x text-primary opacity-25 mb-3"></i>
                    <p class="mb-3">"Found the best electrician through this platform. Quick response and quality service!"</p>
                    <div class="d-flex align-items-center">
                        <img src="assets/images/avatar1.jpg" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h6 class="mb-0">Rajesh Kumar</h6>
                            <small class="text-muted">Delhi</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm">
                    <i class="fas fa-quote-left fa-2x text-primary opacity-25 mb-3"></i>
                    <p class="mb-3">"Amazing platform! Helped me find a great caterer for my wedding within my budget."</p>
                    <div class="d-flex align-items-center">
                        <img src="assets/images/avatar2.jpg" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h6 class="mb-0">Priya Sharma</h6>
                            <small class="text-muted">Mumbai</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="testimonial-card bg-white p-4 rounded-4 shadow-sm">
                    <i class="fas fa-quote-left fa-2x text-primary opacity-25 mb-3"></i>
                    <p class="mb-3">"As a business owner, this platform has helped me get more customers. Highly recommended!"</p>
                    <div class="d-flex align-items-center">
                        <img src="assets/images/avatar3.jpg" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h6 class="mb-0">Amit Singh</h6>
                            <small class="text-muted">Business Owner</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/dark-mode.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>
</body>
</html>