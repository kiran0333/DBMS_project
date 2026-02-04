<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $from_account = intval($_POST['from_account']);
    $to_account = intval($_POST['to_account']);
    $amount = floatval($_POST['amount']);
    $password = $_POST['password'];

    // Check transaction limit
    if ($amount > 100000) {
        $_SESSION['error'] = "Transaction amount exceeds the limit of $100,000";
        header('Location: dashboard.php');
        exit();
    }

    // Check if from and to accounts are different
    if ($from_account === $to_account) {
        $_SESSION['error'] = "Cannot transfer to the same account";
        header('Location: dashboard.php');
        exit();
    }

    // Verify password
    $sql = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Invalid password";
        header('Location: dashboard.php');
        exit();
    }

    // Get source account details
    $sql = "SELECT * FROM bank_accounts WHERE account_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $from_account, $user_id);
    $stmt->execute();
    $from_account_details = $stmt->get_result()->fetch_assoc();

    // Get destination account details
    $sql = "SELECT * FROM bank_accounts WHERE account_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $to_account, $user_id);
    $stmt->execute();
    $to_account_details = $stmt->get_result()->fetch_assoc();

    // Validate accounts exist and belong to user
    if (!$from_account_details || !$to_account_details) {
        $_SESSION['error'] = "Invalid account selection";
        header('Location: dashboard.php');
        exit();
    }

    // Check sufficient balance
    if ($from_account_details['balance'] < $amount) {
        $_SESSION['error'] = "Insufficient balance in source account";
        header('Location: dashboard.php');
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // First insert the transaction record with pending status
        $sql = "INSERT INTO transactions (from_account_id, to_account_id, amount, transaction_type, status) 
                VALUES (?, ?, ?, 'Self', 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iid", $from_account, $to_account, $amount);
        $stmt->execute();
        
        $transaction_id = $conn->insert_id;

        // Deduct from source account
        $sql = "UPDATE bank_accounts SET balance = balance - ? WHERE account_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $amount, $from_account);
        $stmt->execute();

        // Add to destination account
        $sql = "UPDATE bank_accounts SET balance = balance + ? WHERE account_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $amount, $to_account);
        $stmt->execute();

        // Update transaction status to success
        $sql = "UPDATE transactions SET status = 'success' WHERE transaction_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // Create success message with transfer details
        $success_message = array(
            'message' => 'Self transfer successful',
            'details' => array(
                'amount' => number_format($amount, 2),
                'from_account_number' => $from_account_details['account_number'],
                'from_bank' => $from_account_details['bank_name'],
                'to_account_number' => $to_account_details['account_number'],
                'to_bank' => $to_account_details['bank_name'],
                'date' => date('Y-m-d H:i:s')
            )
        );
        $_SESSION['success'] = json_encode($success_message);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Transfer failed: " . $e->getMessage();
    }

    header('Location: dashboard.php');
    exit();
}

header('Location: dashboard.php');
exit();
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Self Transfer - Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/user-components.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi bi-arrow-left-right me-2"></i>Self Transfer
                        </h4>
                    </div>
                    <div class="card-body">
                        <form id="selfTransferForm" action="self_transfer.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="from_account" class="form-label">
                                    <i class="bi bi-box-arrow-left me-2"></i>From Account
                                </label>
                                <select class="form-control" id="from_account" name="from_account" required>
                                    <?php
                                    $sql = "SELECT * FROM bank_accounts WHERE user_id = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $_SESSION['user_id']);
                                    $stmt->execute();
                                    $accounts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    
                                    foreach ($accounts as $account) {
                                        echo "<option value='" . $account['account_id'] . "'>" . 
                                             htmlspecialchars($account['bank_name']) . " - " . 
                                             htmlspecialchars($account['account_number']) . 
                                             " ($" . number_format($account['balance'], 2) . ")</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="to_account" class="form-label">
                                    <i class="bi bi-box-arrow-right me-2"></i>To Account
                                </label>
                                <select class="form-control" id="to_account" name="to_account" required>
                                    <?php
                                    foreach ($accounts as $account) {
                                        echo "<option value='" . $account['account_id'] . "'>" . 
                                             htmlspecialchars($account['bank_name']) . " - " . 
                                             htmlspecialchars($account['account_number']) . 
                                             " ($" . number_format($account['balance'], 2) . ")</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Please select a different account.</div>
                            </div>

                            <div class="mb-4">
                                <label for="amount" class="form-label">
                                    <i class="bi bi-currency-dollar me-2"></i>Amount
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="amount" name="amount" 
                                           placeholder="Enter amount" step="0.01" min="0.01" max="100000" required>
                                    <div class="invalid-feedback">Please enter a valid amount (max $100,000).</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="bi bi-shield-lock me-2"></i>Confirm Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-shield-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password" required>
                                    <div class="invalid-feedback">Please enter your password.</div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-left-right me-2"></i>Transfer Money
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('selfTransferForm');
        const fromAccount = document.getElementById('from_account');
        const toAccount = document.getElementById('to_account');

        function validateAccounts() {
            if (fromAccount.value === toAccount.value) {
                toAccount.setCustomValidity('Please select a different account');
            } else {
                toAccount.setCustomValidity('');
            }
        }

        fromAccount.addEventListener('change', validateAccounts);
        toAccount.addEventListener('change', validateAccounts);

        form.addEventListener('submit', function(event) {
            validateAccounts();
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    </script>
</body>
</html> 