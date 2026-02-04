<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['user_id'])) {
    $_SESSION['error'] = 'User ID not provided';
    header('Location: dashboard.php');
    exit();
}

$user_id = intval($_GET['user_id']);

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
    $sql = "DELETE FROM users WHERE user_id = ? AND user_type = 'user'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    $_SESSION['success'] = "User and all related data deleted successfully";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error'] = "Failed to delete user: " . $e->getMessage();
}

header('Location: dashboard.php');
exit();
?> 