<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

include 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">About <?php echo SITE_NAME; ?></h1>
                <p class="lead text-muted">Connecting Local Businesses with Local Customers</p>
            </div>
            
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-5">
                    <h3>Our Mission</h3>
                    <p>To empower local businesses by providing a platform that connects them with customers in their community, making it easy to discover, review, and support local enterprises.</p>
                    
                    <h3 class="mt-4">Our Vision</h3>
                    <p>To become the most trusted local business directory, helping people find quality services while helping businesses grow through genuine customer reviews and connections.</p>
                    
                    <h3 class="mt-4">What We Offer</h3>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-primary fa-2x me-3"></i>
                                <div>
                                    <h5>For Customers</h5>
                                    <p class="text-muted">Discover local businesses, read authentic reviews, compare services, and connect directly with business owners.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-primary fa-2x me-3"></i>
                                <div>
                                    <h5>For Business Owners</h5>
                                    <p class="text-muted">List your business, manage your online presence, respond to reviews, and reach more customers.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5">
                    <h3>Our Numbers</h3>
                    <div class="row text-center mt-4">
                        <div class="col-md-4">
                            <div class="stat-number display-4 fw-bold text-primary">10K+</div>
                            <p class="text-muted">Businesses Listed</p>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-number display-4 fw-bold text-primary">50K+</div>
                            <p class="text-muted">Happy Customers</p>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-number display-4 fw-bold text-primary">100K+</div>
                            <p class="text-muted">Reviews Posted</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>