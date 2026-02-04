<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save form data in case of validation failure
    $_SESSION['old_data'] = [
        'username' => $_POST['username'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone_number' => $_POST['phone_number'] ?? '',
        'address' => $_POST['address'] ?? ''
    ];

    // Get and sanitize input
    $username = $conn->real_escape_string($_POST['username']);
    $plain_password = $_POST['password']; // Store plain password for later login
    $password = password_hash($plain_password, PASSWORD_DEFAULT);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $address = $conn->real_escape_string($_POST['address']);
    
    // Validate required fields
    if (empty($username) || empty($_POST['password']) || empty($full_name) || empty($email) || empty($phone_number) || empty($address)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: ../register.php');
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format';
        header('Location: ../register.php');
        exit();
    }

    // Validate phone number format
    if (!preg_match('/^[0-9]{10,15}$/', $phone_number)) {
        $_SESSION['error'] = 'Phone number must be between 10 and 15 digits';
        header('Location: ../register.php');
        exit();
    }
    
    // Check if username or email already exists
    $check_sql = "SELECT * FROM users WHERE username = ? OR email = ? OR phone_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("sss", $username, $email, $phone_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $existing_user = $check_result->fetch_assoc();
        if ($existing_user['username'] === $username) {
            $_SESSION['error'] = 'Username already exists';
        } elseif ($existing_user['email'] === $email) {
            $_SESSION['error'] = 'Email already registered';
        } else {
            $_SESSION['error'] = 'Phone number already registered';
        }
        header('Location: ../register.php');
        exit();
    }
    
    // Insert new user
    $sql = "INSERT INTO users (username, password, full_name, email, phone_number, address, user_type, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'user', 'active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $username, $password, $full_name, $email, $phone_number, $address);
    
    if ($stmt->execute()) {
        // Get the new user's ID
        $user_id = $conn->insert_id;
        
        // Clear saved form data
        unset($_SESSION['old_data']);
        
        // Set up user session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['user_type'] = 'user';
        
        // Redirect to user dashboard
        header('Location: ../user/dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        header('Location: ../register.php');
        exit();
    }
}

// If not POST request, redirect to registration page
header('Location: ../register.php');
exit();
?> 