<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Only allow this script to run if no admin exists
$check_admin = "SELECT COUNT(*) as admin_count FROM users WHERE user_type = 'admin'";
$result = $conn->query($check_admin);
$row = $result->fetch_assoc();

if ($row['admin_count'] > 0) {
    die('Admin account already exists. This script can only be run once.');
}

// Create admin account
$username = 'admin';
$password = '123'; // This is the actual password that will be used
$hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password for security
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
    echo "Admin account created successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: 123<br>"; // Show the actual password that was set
    echo "<strong>Please change these credentials immediately after logging in!</strong><br>";
    echo "<a href='../index.php'>Go to Login Page</a>";
} else {
    echo "Error creating admin account: " . $conn->error;
} 