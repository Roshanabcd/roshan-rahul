<?php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $roll_no = trim($_POST['roll_no']);
    $semester = $_POST['semester'];
    $password = $_POST['password'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already registered!'); window.location.href='../register.php';</script>";
    } else {
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, roll_no, semester) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashed_password, $roll_no, $semester);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='../login.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    $check->close();
    $conn->close();
}
?>