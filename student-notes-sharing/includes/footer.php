<?php
/**
 * Footer Template
 * 
 * Common footer for all pages
 */
?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5><i class="fas fa-book-open me-2"></i>Student Notes Sharing</h5>
                    <p class="text-muted small">
                        A centralized platform for students to share, discover, and download academic notes.
                    </p>
                </div>
                
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled small">
                        <li><a href="index.php" class="text-white-50 text-decoration-none">Home</a></li>
                        <li><a href="register.php" class="text-white-50 text-decoration-none">Register</a></li>
                        <li><a href="login.php" class="text-white-50 text-decoration-none">Login</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="dashboard.php" class="text-white-50 text-decoration-none">Dashboard</a></li>
                            <li><a href="upload.php" class="text-white-50 text-decoration-none">Upload Notes</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-md-4">
                    <h6>Contact</h6>
                    <ul class="list-unstyled small text-white-50">
                        <li class="mb-1">
                            <i class="fas fa-map-marker-alt me-2"></i>Shiromani Tole, Birgunj, Nepal
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-envelope me-2"></i>notes@nicollege.edu.np
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-3">
            
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-muted small mb-0">
                        &copy; <?php echo date('Y'); ?> Student Notes Sharing Platform. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button id="back-to-top" class="btn btn-primary btn-floating" title="Back to top" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1000; border-radius: 50%; width: 50px; height: 50px;">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- RateYo Star Rating Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <script>
    // Back to top button functionality
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#back-to-top').fadeIn();
        } else {
            $('#back-to-top').fadeOut();
        }
    });
    
    $('#back-to-top').click(function() {
        $('html, body').animate({scrollTop: 0}, 500);
        return false;
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
    </script>
</body>
</html>
<?php
// Flush the output buffer
ob_end_flush();
?>