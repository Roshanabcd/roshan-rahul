<?php
/**
 * Admin Password Reset Script
 * 
 * IMPORTANT: Delete this file after resetting the password!
 * 
 * Usage: http://localhost/student-notes-sharing/reset_admin.php
 */

require_once 'config/database.php';

// Password to set
$newPassword = 'admin123';
$email = 'admin@notes.com';

try {
    $pdo = getDBConnection();
    
    // Generate new hash
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
        $stmt->execute([
            'password' => $hashedPassword,
            'email' => $email
        ]);
        echo "<h2 style='color: green;'>✅ Admin password updated successfully!</h2>";
        echo "<p>Admin ID: <strong>" . $admin['user_id'] . "</strong></p>";
        echo "<p>Name: <strong>" . htmlspecialchars($admin['name']) . "</strong></p>";
    } else {
        // Create new admin
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->execute([
            'name' => 'Admin',
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'admin'
        ]);
        echo "<h2 style='color: green;'>✅ New admin user created successfully!</h2>";
    }
    
    echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>Login Credentials:</h3>";
    echo "<p><strong>Email:</strong> " . $email . "</p>";
    echo "<p><strong>Password:</strong> " . $newPassword . "</p>";
    echo "<p><strong>Hash Generated:</strong> " . $hashedPassword . "</p>";
    echo "</div>";
    
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
    
    echo "<hr>";
    echo "<p style='color: red;'><strong>⚠️ SECURITY WARNING:</strong> Delete this file (reset_admin.php) immediately after use!</p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>