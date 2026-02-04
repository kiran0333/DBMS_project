<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Delete existing admin account
$delete_admin = "DELETE FROM users WHERE user_type = 'admin'";
if ($conn->query($delete_admin)) {
    // Create new admin account
    $username = 'admin';
    $password = '123'; // This is the actual password that will be used
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $email = 'admin@example.com';
    $full_name = 'System Administrator';
    $phone_number = '1234567890';
    $user_type = 'admin';
    $status = 'active';

    $sql = "INSERT INTO users (username, password, email, full_name, phone_number, user_type, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $username, $hashed_password, $email, $full_name, $phone_number, $user_type, $status);

    if ($stmt->execute()) {
        echo "Admin account has been reset successfully!<br>";
        echo "New admin credentials:<br>";
        echo "Username: admin<br>";
        echo "Password: 123<br>";
        echo "<strong>Please change these credentials immediately after logging in!</strong><br>";
        echo "<a href='../index.php'>Go to Login Page</a>";
    } else {
        echo "Error creating new admin account: " . $conn->error;
    }
} else {
    echo "Error removing old admin account: " . $conn->error;
} 