<?php
session_start();
require_once __DIR__ . '/../config/database.php';


// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

if (isset($_GET['user_id']) && isset($_GET['status'])) {
    $user_id = intval($_GET['user_id']);
    $status = $_GET['status'] === 'active' ? 'active' : 'hold';
    
    // Update user status
    $sql = "UPDATE users SET status = ? WHERE user_id = ? AND user_type = 'user'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "User status updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update user status";
    }
}

header('Location: dashboard.php');
exit(); 