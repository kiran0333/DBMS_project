<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $password = $_POST['password'];
    
    // Verify password
    $sql = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Invalid password";
        header('Location: account_details.php');
        exit();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete user's transactions
        $sql = "DELETE t FROM transactions t 
                JOIN bank_accounts ba ON (t.from_account_id = ba.account_id OR t.to_account_id = ba.account_id)
                WHERE ba.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete user's feedback
        $sql = "DELETE FROM feedback WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete user's bank accounts
        $sql = "DELETE FROM bank_accounts WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete user
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Destroy session and redirect to login
        session_destroy();
        header('Location: ../index.php?msg=account_deleted');
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Failed to delete account: " . $e->getMessage();
        header('Location: account_details.php');
        exit();
    }
}

header('Location: account_details.php');
exit();
?> 