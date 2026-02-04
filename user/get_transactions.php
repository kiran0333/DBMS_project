<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if account_id is provided
if (!isset($_GET['account_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Account ID is required']);
    exit();
}

$account_id = $_GET['account_id'];
$user_id = $_SESSION['user_id'];

// Verify account belongs to user
$verify_sql = "SELECT account_id FROM bank_accounts WHERE account_id = ? AND user_id = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("ii", $account_id, $user_id);
$verify_stmt->execute();
$result = $verify_stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Get detailed transactions
$sql = "SELECT 
            t.transaction_id,
            t.amount,
            CASE 
                WHEN t.from_account_id = ? THEN 'debit'
                ELSE 'credit'
            END as type,
            DATE_FORMAT(t.created_at, '%Y-%m-%d %H:%i:%s') as transaction_time,
            from_acc.bank_name as from_bank,
            from_acc.account_number as from_account,
            to_acc.bank_name as to_bank,
            to_acc.account_number as to_account,
            CASE 
                WHEN t.from_account_id = ? THEN 
                    CASE 
                        WHEN t.transaction_type = 'Self' THEN 'Self Transfer'
                        ELSE CONCAT('Transfer to ', u_to.full_name)
                    END
                ELSE 
                    CASE 
                        WHEN t.transaction_type = 'Self' THEN 'Self Transfer'
                        ELSE CONCAT('From ', u_from.full_name)
                    END
            END as description,
            t.status,
            t.transaction_type
        FROM transactions t
        LEFT JOIN bank_accounts from_acc ON t.from_account_id = from_acc.account_id
        LEFT JOIN bank_accounts to_acc ON t.to_account_id = to_acc.account_id
        LEFT JOIN users u_from ON from_acc.user_id = u_from.user_id
        LEFT JOIN users u_to ON to_acc.user_id = u_to.user_id
        WHERE (t.from_account_id = ? OR t.to_account_id = ?)
        AND t.status = 'success'
        ORDER BY t.created_at DESC
        LIMIT 20";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $account_id, $account_id, $account_id, $account_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Format the transactions for display
$formatted_transactions = array_map(function($transaction) {
    return [
        'id' => $transaction['transaction_id'],
        'date' => $transaction['transaction_time'],
        'type' => $transaction['type'],
        'amount' => number_format($transaction['amount'], 2),
        'from_account' => $transaction['from_account'] . ' (' . $transaction['from_bank'] . ')',
        'to_account' => $transaction['to_account'] . ' (' . $transaction['to_bank'] . ')',
        'description' => $transaction['description'],
        'status' => $transaction['status']
    ];
}, $transactions);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($formatted_transactions); 