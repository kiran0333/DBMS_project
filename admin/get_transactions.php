<?php
session_start();
require_once __DIR__ . '/../config/database.php';


// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    die('Unauthorized access');
}

if (!isset($_GET['user_id'])) {
    die('User ID not provided');
}

$user_id = intval($_GET['user_id']);

// Get user details
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if (!$user) {
    die('User not found');
}

// Get user's transactions
$transaction_sql = "SELECT t.*, 
                          ba1.account_number as from_account_number,
                          ba1.bank_name as from_bank_name,
                          ba2.account_number as to_account_number,
                          ba2.bank_name as to_bank_name
                   FROM transactions t
                   JOIN bank_accounts ba1 ON t.from_account_id = ba1.account_id
                   LEFT JOIN bank_accounts ba2 ON t.to_account_id = ba2.account_id
                   WHERE ba1.user_id = ? OR (ba2.user_id = ? AND t.status = 'success')
                   ORDER BY t.created_at DESC";
$transaction_stmt = $conn->prepare($transaction_sql);
$transaction_stmt->bind_param("ii", $user_id, $user_id);
$transaction_stmt->execute();
$transactions = $transaction_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="mb-3">
    <h6>Transactions for <?php echo htmlspecialchars($user['full_name']); ?></h6>
</div>

<?php if (empty($transactions)): ?>
    <p class="text-muted">No transactions found for this user.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                    <td><?php echo ucfirst($transaction['transaction_type']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($transaction['from_bank_name'] . ' - ' . $transaction['from_account_number']); ?>
                    </td>
                    <td>
                        <?php
                        if ($transaction['transaction_type'] === 'phone_transfer') {
                            echo htmlspecialchars($transaction['to_phone_number']);
                        } else {
                            echo htmlspecialchars($transaction['to_bank_name'] . ' - ' . $transaction['to_account_number']);
                        }
                        ?>
                    </td>
                    <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $transaction['status'] === 'success' ? 'success' : 
                            ($transaction['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                            <?php echo ucfirst($transaction['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?> 