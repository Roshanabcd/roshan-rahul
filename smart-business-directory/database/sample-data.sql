-- Sample Data for Testing

-- Insert sample users
INSERT INTO users (fullname, email, password, role, is_active) VALUES
('Roshan Sharma', 'roshan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1),
('Ram Sharma', 'ram@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'business_owner', 1),
('Sita Kumari', 'Sita@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'business_owner', 1);

-- Insert sample businesses
INSERT INTO businesses (owner_id, cat_id, biz_name, slug, description, address, city, phone, email, status, average_rating) VALUES
(2, 1, 'Tasty Bites Restaurant', 'tasty-bites', 'Best Indian restaurant in town', '123 Main Street', 'Birgunj', '9876543210', 'tasty@example.com', 'approved', 4.5),
(2, 3, 'Furniture World', 'furniture-world', 'Quality furniture at affordable prices', '45 Park Avenue', 'Kalaiya', '9876543211', 'furniture@example.com', 'approved', 4.2),
(3, 4, 'Glamour Salon', 'glamour-salon', 'Professional beauty services', '78 Lake Road', 'Kathmandu', '9876543212', 'glamour@example.com', 'approved', 4.8);

-- Insert sample reviews
INSERT INTO reviews (biz_id, user_id, rating, comment, is_approved) VALUES
(1, 1, 5, 'Amazing food and great service!', 1),
(1, 2, 4, 'Good food but a bit expensive', 1),
(2, 1, 5, 'Excellent quality furniture', 1);

-- Insert sample favorites
INSERT INTO favorites (user_id, biz_id) VALUES
(1, 1),
(1, 2);

-- Insert sample support requests
INSERT INTO support_requests (user_id, title, description, category, urgency, address, city, status) VALUES
(1, 'AC Not Working', 'My AC is not cooling properly', 'AC Repair', 'high', '123 Home Street', 'Birgunj', 'open'),
(1, 'Plumbing Issue', 'Leaking tap in kitchen', 'Plumbing', 'medium', '123 Home Street', 'Birgunj', 'open');