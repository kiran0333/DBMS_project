<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    die('Unauthorized access');
}

// Get search and sort parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';

// Base query
$sql = "SELECT u.*, 
        COALESCE(SUM(t.amount), 0) as total_transactions,
        COUNT(DISTINCT t.transaction_id) as transaction_count
        FROM users u 
        LEFT JOIN bank_accounts ba ON u.user_id = ba.user_id
        LEFT JOIN transactions t ON (ba.account_id = t.from_account_id OR ba.account_id = t.to_account_id)
        WHERE u.user_type = 'user'";

// Add search condition if search term is provided
if (!empty($search)) {
    $search = "%$search%";
    $sql .= " AND (u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
}

// Group by user
$sql .= " GROUP BY u.user_id";

// Add sorting
switch ($sort_by) {
    case 'name':
        $sql .= " ORDER BY u.full_name ASC";
        break;
    case 'highest_transaction':
        $sql .= " ORDER BY total_transactions DESC";
        break;
    case 'first_register':
        $sql .= " ORDER BY u.created_at ASC";
        break;
    default:
        $sql .= " ORDER BY u.full_name ASC";
}

$stmt = $conn->prepare($sql);

// Bind search parameters if search term is provided
if (!empty($search)) {
    $stmt->bind_param("sss", $search, $search, $search);
}

$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get feedback counts for each user
foreach ($users as &$user) {
    $feedback_sql = "SELECT COUNT(*) as feedback_count FROM feedback WHERE user_id = ?";
    $feedback_stmt = $conn->prepare($feedback_sql);
    $feedback_stmt->bind_param("i", $user['user_id']);
    $feedback_stmt->execute();
    $feedback_result = $feedback_stmt->get_result()->fetch_assoc();
    $user['feedback_count'] = $feedback_result['feedback_count'];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($users);
?> 