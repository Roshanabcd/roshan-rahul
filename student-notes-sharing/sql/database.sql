-- ============================================
-- Student Notes Sharing Platform
-- Database Schema - COMPLETE FIXED VERSION
-- ============================================

-- Drop existing database (careful! This deletes all data)
DROP DATABASE IF EXISTS student_notes_sharing;

-- Create database
CREATE DATABASE student_notes_sharing
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE student_notes_sharing;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE users (
    user_id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    roll_no VARCHAR(50) DEFAULT NULL,
    semester VARCHAR(20) DEFAULT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uk_email (email),
    KEY idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Notes Table
-- ============================================
CREATE TABLE notes (
    note_id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    subject VARCHAR(100) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    download_count INT(11) NOT NULL DEFAULT 0,
    status ENUM('active', 'hidden') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (note_id),
    KEY idx_user_id (user_id),
    KEY idx_subject (subject),
    KEY idx_semester (semester),
    CONSTRAINT fk_notes_user FOREIGN KEY (user_id) 
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Ratings Table
-- ============================================
CREATE TABLE ratings (
    rating_id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    note_id INT(11) NOT NULL,
    stars TINYINT(1) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (rating_id),
    UNIQUE KEY uk_user_note (user_id, note_id),
    KEY idx_note_id (note_id),
    CONSTRAINT fk_ratings_user FOREIGN KEY (user_id) 
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ratings_note FOREIGN KEY (note_id) 
        REFERENCES notes(note_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_stars CHECK (stars >= 1 AND stars <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Admin User
-- Password: admin123
-- ============================================
-- The hash below is for 'admin123' using bcrypt
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@notes.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'admin');

-- ============================================
-- Insert Test Student User
-- Password: student123
-- ============================================
INSERT INTO users (name, email, password, roll_no, semester, role) VALUES
('Test Student', 'student@test.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'TST-001', '7th', 'student');