<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'money_transfer';
$db_user = 'root';  // Default XAMPP MySQL username
$db_pass = 'MYSQL@kiran123';      // Default XAMPP MySQL password

// Create connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}