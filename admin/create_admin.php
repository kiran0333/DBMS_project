<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Delete any existing admin accounts
$delete_admin = "DELETE FROM users WHERE user_type = 'admin'";
$conn->query($delete_admin);

// Create new admin account with proper password hashing
$username = 'admin';
$plain_password = 'admin123'; // This will be the actual password to use
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
$email = 'admin@example.com';
$full_name = 'System Administrator';
$phone_number = '1234567890';
$address = 'Admin Office'; // Added address field
$user_type = 'admin';
$status = 'active';

// First, check if the insert was successful
$sql = "INSERT INTO users (username, password, email, full_name, phone_number, address, user_type, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $username, $hashed_password, $email, $full_name, $phone_number, $address, $user_type, $status);

if ($stmt->execute()) {
    // Now verify if we can retrieve and verify the password
    $verify_sql = "SELECT password FROM users WHERE username = ? AND user_type = 'admin'";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("s", $username);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result()->fetch_assoc();
    
    if ($result && password_verify($plain_password, $result['password'])) {
        echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h2 style='color: #28a745;'>Admin Account Created Successfully!</h2>";
        echo "<p><strong>Use these credentials to login:</strong></p>";
        echo "<ul>";
        echo "<li>Username: admin</li>";
        echo "<li>Password: admin123</li>";
        echo "</ul>";
        echo "<p><strong>Steps to login:</strong></p>";
        echo "<ol>";
        echo "<li>Go to <a href='../index.php'>login page</a></li>";
        echo "<li>Click on 'Admin Login' button</li>";
        echo "<li>Enter the username and password above</li>";
        echo "</ol>";
        echo "<p style='color: #dc3545;'><strong>Important:</strong> Please change this password after logging in!</p>";
        echo "<p><a href='../index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
        echo "</div>";
    } else {
        echo "Error: Password verification failed after creation. Please contact support.";
    }
} else {
    echo "Error creating admin account: " . $conn->error;
} 