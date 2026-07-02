-- =====================================================
-- SMART LOCAL BUSINESS DIRECTORY SYSTEM
-- Complete Database Schema
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS business_directory;
USE business_directory;

-- =====================================================
-- 1. USERS TABLE (with complete profile)
-- =====================================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    alternative_phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    role ENUM('admin', 'business_owner', 'user') DEFAULT 'user',
    profile_image VARCHAR(255) DEFAULT 'default-avatar.png',
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(100),
    reset_token VARCHAR(100),
    reset_expires DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    login_attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_city (city),
    INDEX idx_status (is_active)
);

-- =====================================================
-- 2. CATEGORIES TABLE (with subcategories)
-- =====================================================
CREATE TABLE categories (
    cat_id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT DEFAULT NULL,
    cat_name VARCHAR(50) NOT NULL,
    cat_slug VARCHAR(50) UNIQUE NOT NULL,
    cat_icon VARCHAR(100) DEFAULT 'fa-store',
    cat_image VARCHAR(255),
    cat_description TEXT,
    display_order INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(cat_id) ON DELETE CASCADE,
    INDEX idx_parent (parent_id),
    INDEX idx_slug (cat_slug),
    INDEX idx_active (is_active)
);

-- =====================================================
-- 3. BUSINESSES TABLE (complete listing)
-- =====================================================
CREATE TABLE businesses (
    biz_id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    cat_id INT NOT NULL,
    subcat_id INT DEFAULT NULL,
    biz_name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    tagline VARCHAR(200),
    description TEXT NOT NULL,
    short_description VARCHAR(500),
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    country VARCHAR(50) DEFAULT 'Nepal',
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    phone VARCHAR(20) NOT NULL,
    alternative_phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(200),
    facebook_url VARCHAR(255),
    instagram_url VARCHAR(255),
    twitter_url VARCHAR(255),
    whatsapp_number VARCHAR(20),
    business_hours JSON,
    special_hours JSON,
    logo VARCHAR(255) DEFAULT 'default-business.png',
    cover_image VARCHAR(255),
    gallery_images TEXT,
    video_url VARCHAR(255),
    virtual_tour_url VARCHAR(255),
    price_range INT CHECK (price_range BETWEEN 1 AND 4),
    amenities TEXT,
    established_year INT,
    employee_count INT,
    languages_spoken TEXT,
    payment_methods TEXT,
    delivery_available BOOLEAN DEFAULT FALSE,
    home_service_available BOOLEAN DEFAULT FALSE,
    emergency_service BOOLEAN DEFAULT FALSE,
    average_rating DECIMAL(3,2) DEFAULT 0,
    total_reviews INT DEFAULT 0,
    total_favorites INT DEFAULT 0,
    total_views INT DEFAULT 0,
    total_shares INT DEFAULT 0,
    views_today INT DEFAULT 0,
    views_week INT DEFAULT 0,
    views_month INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected', 'suspended', 'featured') DEFAULT 'pending',
    is_featured BOOLEAN DEFAULT FALSE,
    featured_until DATE,
    rejection_reason TEXT,
    seo_title VARCHAR(200),
    seo_description TEXT,
    seo_keywords TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (cat_id) REFERENCES categories(cat_id) ON DELETE CASCADE,
    FOREIGN KEY (subcat_id) REFERENCES categories(cat_id) ON DELETE SET NULL,
    INDEX idx_owner (owner_id),
    INDEX idx_category (cat_id),
    INDEX idx_status (status),
    INDEX idx_city (city),
    INDEX idx_rating (average_rating),
    INDEX idx_views (total_views),
    INDEX idx_featured (is_featured),
    INDEX idx_slug (slug),
    INDEX idx_location (latitude, longitude),
    FULLTEXT idx_search (biz_name, description, short_description, tagline)
);

-- =====================================================
-- 4. BUSINESS IMAGES TABLE
-- =====================================================
CREATE TABLE business_images (
    img_id INT PRIMARY KEY AUTO_INCREMENT,
    biz_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_alt VARCHAR(200),
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (biz_id) REFERENCES businesses(biz_id) ON DELETE CASCADE,
    INDEX idx_business (biz_id),
    INDEX idx_primary (is_primary)
);

-- =====================================================
-- 5. REVIEWS TABLE (with photos and helpful votes)
-- =====================================================
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    biz_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(200),
    comment TEXT NOT NULL,
    pros TEXT,
    cons TEXT,
    images TEXT,
    helpful_count INT DEFAULT 0,
    not_helpful_count INT DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    owner_response TEXT,
    owner_response_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (biz_id) REFERENCES businesses(biz_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (biz_id, user_id),
    INDEX idx_business (biz_id),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at),
    INDEX idx_helpful (helpful_count)
);

-- =====================================================
-- 6. REVIEW HELPFUL VOTES TABLE
-- =====================================================
CREATE TABLE review_helpful (
    id INT PRIMARY KEY AUTO_INCREMENT,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    vote_type ENUM('helpful', 'not_helpful') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(review_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (review_id, user_id),
    INDEX idx_review (review_id)
);

-- =====================================================
-- 7. FAVORITES TABLE
-- =====================================================
CREATE TABLE favorites (
    fav_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    biz_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (biz_id) REFERENCES businesses(biz_id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, biz_id),
    INDEX idx_user (user_id),
    INDEX idx_business (biz_id)
);

-- =====================================================
-- 8. SUPPORT REQUESTS TABLE (Marketplace)
-- =====================================================
CREATE TABLE support_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    assigned_biz_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100),
    subcategory VARCHAR(100),
    urgency ENUM('low', 'medium', 'high', 'emergency') DEFAULT 'medium',
    budget_min DECIMAL(10,2),
    budget_max DECIMAL(10,2),
    address TEXT NOT NULL,
    city VARCHAR(100),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    preferred_date DATE,
    preferred_time_start TIME,
    preferred_time_end TIME,
    images TEXT,
    status ENUM('open', 'bidding', 'assigned', 'in_progress', 'completed', 'cancelled', 'expired') DEFAULT 'open',
    completed_at TIMESTAMP,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    viewed_count INT DEFAULT 0,
    offer_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_biz_id) REFERENCES businesses(biz_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_urgency (urgency),
    INDEX idx_created (created_at),
    INDEX idx_city (city),
    INDEX idx_location (latitude, longitude)
);

-- =====================================================
-- 9. SUPPORT OFFERS TABLE
-- =====================================================
CREATE TABLE support_offers (
    offer_id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    business_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    estimated_days INT,
    estimated_hours INT,
    message TEXT NOT NULL,
    terms TEXT,
    status ENUM('pending', 'accepted', 'rejected', 'expired', 'countered') DEFAULT 'pending',
    viewed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (request_id) REFERENCES support_requests(request_id) ON DELETE CASCADE,
    FOREIGN KEY (business_id) REFERENCES businesses(biz_id) ON DELETE CASCADE,
    INDEX idx_request (request_id),
    INDEX idx_business (business_id),
    INDEX idx_status (status),
    INDEX idx_price (price)
);

-- =====================================================
-- 10. CHAT MESSAGES TABLE (Real-time)
-- =====================================================
CREATE TABLE chat_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id VARCHAR(100) NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'image', 'file', 'location') DEFAULT 'text',
    attachment_url VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_created (created_at),
    INDEX idx_unread (receiver_id, is_read)
);

-- =====================================================
-- 11. NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE notifications (
    notif_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500),
    icon VARCHAR(100),
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_unread (user_id, is_read),
    INDEX idx_created (created_at)
);

-- =====================================================
-- 12. USER ACTIVITY LOG TABLE
-- =====================================================
CREATE TABLE user_activity (
    activity_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (activity_type),
    INDEX idx_created (created_at)
);

-- =====================================================
-- 13. REPORTS TABLE (For reporting issues)
-- =====================================================
CREATE TABLE reports (
    report_id INT PRIMARY KEY AUTO_INCREMENT,
    reporter_id INT NOT NULL,
    reported_type ENUM('business', 'review', 'user', 'support_request') NOT NULL,
    reported_id INT NOT NULL,
    reason VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    admin_notes TEXT,
    resolved_by INT,
    resolved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_reported (reported_type, reported_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- =====================================================
-- 14. COUPONS & OFFERS TABLE
-- =====================================================
CREATE TABLE coupons (
    coupon_id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    code VARCHAR(50),
    discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2),
    max_discount_amount DECIMAL(10,2),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    usage_limit INT DEFAULT 1,
    used_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(biz_id) ON DELETE CASCADE,
    INDEX idx_business (business_id),
    INDEX idx_active (is_active, start_date, end_date)
);

-- =====================================================
-- 15. SYSTEM SETTINGS TABLE
-- =====================================================
CREATE TABLE system_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json', 'file') DEFAULT 'text',
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_key (setting_key)
);

-- =====================================================
-- 16. EMAIL SUBSCRIPTIONS TABLE
-- =====================================================
CREATE TABLE email_subscriptions (
    sub_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    verification_token VARCHAR(100),
    verified_at DATETIME,
    unsubscribed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_active (is_active)
);

-- =====================================================
-- 17. BUSINESS CLAIM REQUESTS TABLE
-- =====================================================
CREATE TABLE business_claims (
    claim_id INT PRIMARY KEY AUTO_INCREMENT,
    business_id INT NOT NULL,
    claimant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    proof_document VARCHAR(255),
    message TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    reviewed_by INT,
    reviewed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(biz_id) ON DELETE CASCADE,
    FOREIGN KEY (claimant_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_business (business_id),
    INDEX idx_status (status)
);

-- =====================================================
-- INSERT DEFAULT CATEGORIES
-- =====================================================
INSERT INTO categories (cat_name, cat_slug, cat_icon, display_order, is_featured) VALUES
('Restaurants', 'restaurants', 'fa-utensils', 1, TRUE),
('Cafes & Coffee Shops', 'cafes', 'fa-mug-hot', 2, TRUE),
('Furniture & Home Decor', 'furniture', 'fa-couch', 3, TRUE),
('Salons & Spas', 'salons', 'fa-cut', 4, TRUE),
('Grocery & Kirana', 'grocery', 'fa-shopping-basket', 5, TRUE),
('Electronics & Appliances', 'electronics', 'fa-mobile-alt', 6, TRUE),
('Fashion & Clothing', 'fashion', 'fa-tshirt', 7, TRUE),
('Fitness & Gyms', 'fitness', 'fa-dumbbell', 8, TRUE),
('Automotive & Repair', 'automotive', 'fa-car', 9, TRUE),
('Plumbing Services', 'plumbing', 'fa-wrench', 10, TRUE),
('Electrical Services', 'electrical', 'fa-bolt', 11, TRUE),
('Carpentry & Woodwork', 'carpentry', 'fa-hammer', 12, TRUE),
('Painting Services', 'painting', 'fa-paintbrush', 13, TRUE),
('Cleaning Services', 'cleaning', 'fa-broom', 14, TRUE),
('Pest Control', 'pest-control', 'fa-bug', 15, TRUE),
('AC Repair & Service', 'ac-repair', 'fa-snowflake', 16, TRUE),
('Mobile & Laptop Repair', 'repair', 'fa-mobile', 17, TRUE),
('Home Tuition & Coaching', 'tuition', 'fa-chalkboard-user', 18, TRUE),
('Event Planning', 'events', 'fa-calendar-check', 19, TRUE),
('Photography & Videography', 'photography', 'fa-camera', 20, TRUE),
('Catering Services', 'catering', 'fa-utensil-spoon', 21, TRUE),
('Legal Services', 'legal', 'fa-gavel', 22, TRUE),
('Accounting & Tax', 'accounting', 'fa-calculator', 23, TRUE),
('Medical & Clinics', 'medical', 'fa-hospital-user', 24, TRUE),
('Dental Care', 'dental', 'fa-tooth', 25, TRUE),
('Veterinary & Pet Care', 'veterinary', 'fa-paw', 26, TRUE),
('Pet Grooming', 'pet-grooming', 'fa-dog', 27, TRUE),
('Laundry & Dry Cleaning', 'laundry', 'fa-shirt', 28, TRUE),
('Packers & Movers', 'movers', 'fa-truck', 29, TRUE),
('Beauty Parlours', 'beauty', 'fa-spa', 30, TRUE),
('Tailoring & Alteration', 'tailoring', 'fa-scissors', 31, TRUE),
('Jewelry Stores', 'jewelry', 'fa-gem', 32, TRUE),
('Opticians', 'opticians', 'fa-eye', 33, TRUE),
('Bookstores', 'books', 'fa-book', 34, TRUE),
('Stationery Stores', 'stationery', 'fa-pen', 35, TRUE),
('Hardware Stores', 'hardware', 'fa-tools', 36, TRUE),
('Pharmacy & Medical Stores', 'pharmacy', 'fa-capsules', 37, TRUE),
('Travel Agencies', 'travel', 'fa-plane', 38, TRUE),
('Hotels & Lodging', 'hotels', 'fa-hotel', 39, TRUE),
('Real Estate', 'realestate', 'fa-building', 40, TRUE);

-- =====================================================
-- INSERT DEFAULT SYSTEM SETTINGS
-- =====================================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', '{"value": "Smart Local Business Directory"}', 'json', 'Website name'),
('site_tagline', '{"value": "Find Best Local Businesses Near You"}', 'json', 'Website tagline'),
('site_description', '{"value": "Discover and connect with local businesses. Read reviews, get ratings, and find the best services in your area."}', 'json', 'Meta description'),
('contact_email', '{"value": "info@businessdirectory.com"}', 'json', 'Contact email address'),
('contact_phone', '{"value": "+977 9800000000"}', 'json', 'Contact phone number'),
('contact_address', '{"value": "123 Business Street, Kathmandu, Nepal"}', 'json', 'Physical address'),
('facebook_url', '{"value": "https://facebook.com/businessdir"}', 'json', 'Facebook page URL'),
('twitter_url', '{"value": "https://twitter.com/businessdir"}', 'json', 'Twitter profile URL'),
('instagram_url', '{"value": "https://instagram.com/businessdir"}', 'json', 'Instagram profile URL'),
('whatsapp_number', '{"value": "+977980000000"}', 'json', 'WhatsApp business number'),
('businesses_per_page', '{"value": 12}', 'json', 'Number of businesses per page'),
('reviews_per_page', '{"value": 10}', 'json', 'Number of reviews per page'),
('featured_limit', '{"value": 8}', 'json', 'Number of featured businesses on homepage'),
('nearby_radius', '{"value": 10}', 'json', 'Nearby search radius in kilometers'),
('currency_symbol', '{"value": "रू"}', 'json', 'Currency symbol'),
('currency_code', '{"value": "NPR"}', 'json', 'Currency code'),
('maintenance_mode', '{"value": false}', 'json', 'Put site in maintenance mode'),
('registration_enabled', '{"value": true}', 'json', 'Allow new user registration'),
('email_verification', '{"value": true}', 'json', 'Require email verification'),
('google_maps_api_key', '{"value": ""}', 'json', 'Google Maps API key'),
('smtp_host', '{"value": ""}', 'json', 'SMTP server host'),
('smtp_port', '{"value": 587}', 'json', 'SMTP server port'),
('smtp_user', '{"value": ""}', 'json', 'SMTP username'),
('smtp_pass', '{"value": ""}', 'json', 'SMTP password');

-- =====================================================
-- CREATE DEFAULT ADMIN USER (password: Admin@123)
-- =====================================================
-- Note: Use password_hash() in PHP to generate this
INSERT INTO users (fullname, email, password, role, email_verified, is_active) VALUES
('Super Admin', 'admin@businessdirectory.com', '$2y$10$YourHashedPasswordHere', 'admin', TRUE, TRUE);

-- =====================================================
-- CREATE INDEXES FOR PERFORMANCE
-- =====================================================
CREATE INDEX idx_businesses_search ON businesses(biz_name, city, status);
CREATE INDEX idx_businesses_description ON businesses(description(100));
CREATE INDEX idx_reviews_business_rating ON reviews(biz_id, rating);
CREATE INDEX idx_support_requests_location ON support_requests(latitude, longitude);
CREATE INDEX idx_chat_conversation_read ON chat_messages(conversation_id, is_read);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_user_activity_user_date ON user_activity(user_id, created_at);

-- =====================================================
-- CREATE VIEWS FOR COMMON QUERIES
-- =====================================================

-- Business with average rating view
CREATE VIEW view_business_with_rating AS
SELECT 
    b.*,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(r.review_id) as review_count,
    c.cat_name as category_name,
    c.cat_slug as category_slug,
    u.fullname as owner_name
FROM businesses b
LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
LEFT JOIN categories c ON b.cat_id = c.cat_id
LEFT JOIN users u ON b.owner_id = u.user_id
WHERE b.status IN ('approved', 'featured')
GROUP BY b.biz_id;

-- User activity summary view
CREATE VIEW view_user_activity_summary AS
SELECT 
    u.user_id,
    u.fullname,
    u.email,
    u.role,
    COUNT(DISTINCT b.biz_id) as total_businesses,
    COUNT(DISTINCT r.review_id) as total_reviews,
    COUNT(DISTINCT f.fav_id) as total_favorites,
    COUNT(DISTINCT sr.request_id) as total_support_requests,
    u.created_at as registered_since,
    u.last_login
FROM users u
LEFT JOIN businesses b ON u.user_id = b.owner_id
LEFT JOIN reviews r ON u.user_id = r.user_id
LEFT JOIN favorites f ON u.user_id = f.user_id
LEFT JOIN support_requests sr ON u.user_id = sr.user_id
GROUP BY u.user_id;

-- =====================================================
-- CREATE TRIGGERS
-- =====================================================

-- Update business average rating when review is added/updated
DELIMITER $$
CREATE TRIGGER update_business_rating AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE businesses 
    SET average_rating = (SELECT AVG(rating) FROM reviews WHERE biz_id = NEW.biz_id AND is_approved = 1),
        total_reviews = (SELECT COUNT(*) FROM reviews WHERE biz_id = NEW.biz_id AND is_approved = 1)
    WHERE biz_id = NEW.biz_id;
END$$
DELIMITER ;

-- Update favorite count when favorite is added
DELIMITER $$
CREATE TRIGGER update_favorite_count AFTER INSERT ON favorites
FOR EACH ROW
BEGIN
    UPDATE businesses SET total_favorites = total_favorites + 1 WHERE biz_id = NEW.biz_id;
END$$
DELIMITER ;

-- Update favorite count when favorite is removed
DELIMITER $$
CREATE TRIGGER update_favorite_count_delete AFTER DELETE ON favorites
FOR EACH ROW
BEGIN
    UPDATE businesses SET total_favorites = total_favorites - 1 WHERE biz_id = OLD.biz_id;
END$$
DELIMITER ;

-- =====================================================
-- CREATE STORED PROCEDURES
-- =====================================================

-- Search businesses by location and keyword
DELIMITER $$
CREATE PROCEDURE search_businesses(
    IN search_keyword VARCHAR(200),
    IN category_id INT,
    IN city_name VARCHAR(100),
    IN min_rating DECIMAL(3,2),
    IN sort_by VARCHAR(20),
    IN limit_count INT,
    IN offset_count INT
)
BEGIN
    DECLARE order_clause VARCHAR(100);
    
    SET order_clause = 'b.created_at DESC';
    
    IF sort_by = 'rating' THEN
        SET order_clause = 'avg_rating DESC';
    ELSEIF sort_by = 'reviews' THEN
        SET order_clause = 'review_count DESC';
    ELSEIF sort_by = 'views' THEN
        SET order_clause = 'b.total_views DESC';
    ELSEIF sort_by = 'oldest' THEN
        SET order_clause = 'b.created_at ASC';
    END IF;
    
    SET @sql = CONCAT('
        SELECT b.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.review_id) as review_count,
               c.cat_name, c.cat_slug,
               (SELECT COUNT(*) FROM favorites WHERE biz_id = b.biz_id) as fav_count
        FROM businesses b
        LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
        JOIN categories c ON b.cat_id = c.cat_id
        WHERE b.status IN ("approved", "featured")
    ');
    
    IF search_keyword IS NOT NULL AND search_keyword != '' THEN
        SET @sql = CONCAT(@sql, ' AND (b.biz_name LIKE "%', search_keyword, '%" OR b.description LIKE "%', search_keyword, '%")');
    END IF;
    
    IF category_id IS NOT NULL AND category_id > 0 THEN
        SET @sql = CONCAT(@sql, ' AND b.cat_id = ', category_id);
    END IF;
    
    IF city_name IS NOT NULL AND city_name != '' THEN
        SET @sql = CONCAT(@sql, ' AND b.city LIKE "%', city_name, '%"');
    END IF;
    
    IF min_rating IS NOT NULL AND min_rating > 0 THEN
        SET @sql = CONCAT(@sql, ' HAVING avg_rating >= ', min_rating);
    END IF;
    
    SET @sql = CONCAT(@sql, ' GROUP BY b.biz_id ORDER BY ', order_clause);
    SET @sql = CONCAT(@sql, ' LIMIT ', limit_count, ' OFFSET ', offset_count);
    
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$
DELIMITER ;

-- Get nearby businesses
DELIMITER $$
CREATE PROCEDURE get_nearby_businesses(
    IN lat DECIMAL(10,8),
    IN lng DECIMAL(11,8),
    IN radius_km INT,
    IN limit_count INT
)
BEGIN
    SELECT 
        b.*,
        c.cat_name,
        (6371 * acos(cos(radians(lat)) * cos(radians(b.latitude)) * 
        cos(radians(b.longitude) - radians(lng)) + 
        sin(radians(lat)) * sin(radians(b.latitude)))) AS distance,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.review_id) as review_count
    FROM businesses b
    JOIN categories c ON b.cat_id = c.cat_id
    LEFT JOIN reviews r ON b.biz_id = r.biz_id AND r.is_approved = 1
    WHERE b.status IN ('approved', 'featured')
        AND b.latitude IS NOT NULL
        AND b.longitude IS NOT NULL
    HAVING distance < radius_km
    ORDER BY distance
    LIMIT limit_count;
END$$
DELIMITER ;

-- =====================================================
-- COMPLETED - DATABASE SCHEMA
-- =====================================================