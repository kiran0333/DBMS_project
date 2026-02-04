<?php
// Start session and include database before any output
session_start();
require_once '../config/database.php';

// Redirect if accessed directly without POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['type'])) {
    $_SESSION['error'] = 'Invalid access method';
    header('Location: ../index.php');
    exit();
}

$username = $conn->real_escape_string($_POST['username']);
$password = $_POST['password'];
$type = $_POST['type'];

// Save username in case of login failure
$_SESSION['old_username'] = $username;

// First check if user exists and get their data
$sql = "SELECT * FROM users WHERE username = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Verify password and user type
    if (password_verify($password, $user['password'])) {
        // Check if user type matches
        if ($type === $user['user_type']) {
            // Clear saved username on successful login
            unset($_SESSION['old_username']);
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Redirect based on user type
            if ($user['user_type'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Invalid login type selected';
        }
    } else {
        $_SESSION['error'] = 'Invalid password';
    }
} else {
    $_SESSION['error'] = 'User not found or account is inactive';
}

header('Location: ../index.php');
exit();
?> 