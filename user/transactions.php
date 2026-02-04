<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

// Get account ID from query parameter
$account_id = isset($_GET['account_id']) ? intval($_GET['account_id']) : 0;

// Verify account belongs to user
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM bank_accounts WHERE account_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $account_id, $user_id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();

if (!$account) {
    header('Location: dashboard.php');
    exit();
}

// Get transactions for this account
$sql = "SELECT t.*, 
        ba_from.account_number as from_account_number,
        ba_from.bank_name as from_bank_name,
        ba_to.account_number as to_account_number,
        ba_to.bank_name as to_bank_name,
        CASE 
            WHEN t.transaction_type = 'phone_transfer' THEN t.to_phone_number
            ELSE CONCAT(ba_to.bank_name, ' - ', ba_to.account_number)
        END as recipient_details
        FROM transactions t
        LEFT JOIN bank_accounts ba_from ON t.from_account_id = ba_from.account_id
        LEFT JOIN bank_accounts ba_to ON t.to_account_id = ba_to.account_id
        WHERE t.from_account_id = ? OR t.to_account_id = ?
        ORDER BY t.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $account_id, $account_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/user-components.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-cash-coin me-2"></i>Money Transfer System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="account_details.php">
                            <i class="bi bi-person-circle me-2"></i>Account Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">
                            <i class="bi bi-shield-lock me-2"></i>Change Password
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_phone.php">
                            <i class="bi bi-phone me-2"></i>Change Phone
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback.php">
                            <i class="bi bi-chat-dots me-2"></i>Feedback
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card shadow">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Transaction History
                    </h4>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="account-info mb-4 p-3" style="background: rgba(34, 211, 238, 0.1); border-radius: 10px;">
                    <h5 class="mb-3">
                        <i class="bi bi-bank me-2"></i>Account Details
                    </h5>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="text-muted">Account Number</div>
                            <div class="fw-bold text-white"><?php echo htmlspecialchars($account['account_number']); ?></div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="text-muted">Bank Name</div>
                            <div class="fw-bold text-white"><?php echo htmlspecialchars($account['bank_name']); ?></div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="text-muted">Current Balance</div>
                            <div class="fw-bold text-white">$<?php echo number_format($account['balance'], 2); ?></div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <i class="bi bi-calendar me-2"></i>Date
                                </th>
                                <th scope="col">
                                    <i class="bi bi-arrow-left-right me-2"></i>Type
                                </th>
                                <th scope="col">
                                    <i class="bi bi-box-arrow-left me-2"></i>From
                                </th>
                                <th scope="col">
                                    <i class="bi bi-box-arrow-right me-2"></i>To
                                </th>
                                <th scope="col">
                                    <i class="bi bi-currency-dollar me-2"></i>Amount
                                </th>
                                <th scope="col">
                                    <i class="bi bi-check-circle me-2"></i>Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst(str_replace('_', ' ', $transaction['transaction_type'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($transaction['from_account_id'] == $account_id) {
                                            echo '<span class="text-warning">Your Account</span>';
                                        } else {
                                            echo '<span class="text-white">' . htmlspecialchars($transaction['from_bank_name'] . ' - ' . $transaction['from_account_number']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($transaction['transaction_type'] == 'phone_transfer') {
                                            echo '<span class="text-info">Phone: ' . htmlspecialchars($transaction['to_phone_number']) . '</span>';
                                        } else {
                                            if ($transaction['to_account_id'] == $account_id) {
                                                echo '<span class="text-warning">Your Account</span>';
                                            } else {
                                                echo '<span class="text-white">' . htmlspecialchars($transaction['recipient_details']) . '</span>';
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="<?php echo $transaction['from_account_id'] == $account_id ? 'text-danger' : 'text-success'; ?> fw-bold">
                                        <?php echo $transaction['from_account_id'] == $account_id ? '-' : '+'; ?>
                                        $<?php echo number_format($transaction['amount'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($transaction['status'] == 'success'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Success
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i>Failed
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No transactions found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>