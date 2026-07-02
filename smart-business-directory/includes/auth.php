<?php
require_once 'config.php';
require_once 'functions.php';

// Login function
function loginUser($email, $password) {
    global $conn;
    
    $email = sanitize($email);
    $query = "SELECT * FROM users WHERE email = '$email' AND is_active = 1";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['profile_image'] = $row['profile_image'];
            $_SESSION['logged_in'] = true;
            return true;
        }
    }
    return false;
}

// Register function
function registerUser($fullname, $email, $password, $phone = '', $role = 'user') {
    global $conn;
    
    $fullname = sanitize($fullname);
    $email = sanitize($email);
    $phone = sanitize($phone);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if email exists
    $check_query = "SELECT user_id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        return ['error' => 'Email already registered'];
    }
    
    $query = "INSERT INTO users (fullname, email, password, phone, role) 
              VALUES ('$fullname', '$email', '$hashed_password', '$phone', '$role')";
    
    if (mysqli_query($conn, $query)) {
        return ['success' => true, 'user_id' => mysqli_insert_id($conn)];
    }
    
    return ['error' => 'Registration failed: ' . mysqli_error($conn)];
}

// Logout function
function logoutUser() {
    session_destroy();
    redirect('index.php');
}
