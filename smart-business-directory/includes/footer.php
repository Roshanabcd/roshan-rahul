    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
    
    <footer class="site-footer mt-5 text-white" style="background: #0f172a; border-top: 1px solid rgba(255,255,255,0.1);">
        <div class="container py-5">
            <div class="row g-4 align-items-start">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fas fa-store"></i></span>
                        <span style="font-family: 'Outfit', sans-serif;"><?php echo getSetting('site_name') ?: 'LocalConnect'; ?></span>
                    </h5>
                    <p class="text-white-50 mb-0" style="font-size: 0.95rem; line-height: 1.6;">Discover trusted local businesses, read honest reviews, and connect with nearby services in a simple and modern marketplace.</p>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-semibold mb-3 text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">Explore</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>" class="text-white-50 text-decoration-none hover-white transition">Home</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>businesses.php" class="text-white-50 text-decoration-none hover-white transition">Browse Businesses</a></li>
                        <?php if(!isLoggedIn()): ?>
                        <li><a href="<?php echo SITE_URL; ?>register.php" class="text-white-50 text-decoration-none hover-white transition">Join Now</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-semibold mb-3 text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">Contact</h6>
                    <ul class="list-unstyled text-white-50" style="font-size: 0.95rem;">
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i><?php echo getSetting('contact_email') ?: 'info@localconnect.com'; ?></li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-primary"></i><?php echo getSetting('contact_phone') ?: '+977 9812345678'; ?></li>
                        <li><i class="fas fa-map-marker-alt me-2 text-primary"></i>Birgunj, Nepal</li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-semibold mb-3 text-uppercase" style="letter-spacing: 1px; font-size: 0.85rem;">Follow Us</h6>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#" class="text-white-50 hover-white transition" aria-label="Facebook"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-white-50 hover-white transition" aria-label="Instagram"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white-50 hover-white transition" aria-label="Twitter"><i class="fab fa-twitter fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom mt-5 pt-4" style="border-top: 1px solid rgba(255,255,255,0.05);">
                <div class="d-flex justify-content-center align-items-center flex-wrap">
                    <p class="mb-0 text-white-50 small text-center">&copy; <?php echo date('Y'); ?> <?php echo getSetting('site_name') ?: 'LocalConnect'; ?>. All rights reserved.</p>
                    <!-- <div class="text-white-50 small">Designed with <i class="fas fa-heart text-danger"></i> for simplicity</div> -->
                </div>
            </div>
        </div>
    </footer>
</body>
</html>