<?php
session_start();
require_once __DIR__ . '/../config/database.php';


// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's bank accounts
$sql = "SELECT * FROM bank_accounts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bank_accounts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Money Transfer System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="account_details.php">Account Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Change Password</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_phone.php">Change Phone</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback.php">Feedback</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-4">
        <!-- Notifications -->
        <?php if (isset($_SESSION['success'])): ?>
            <?php 
            $success_data = json_decode($_SESSION['success'], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($success_data['details'])): 
            ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_data['message']); ?></strong>
                    <div class="alert-details">
                        <p class="mb-1"><strong>Amount:</strong> $<?php echo htmlspecialchars($success_data['details']['amount']); ?></p>
                        <p class="mb-1"><strong>From Account:</strong> <?php echo htmlspecialchars($success_data['details']['from_account_number']); ?> (<?php echo htmlspecialchars($success_data['details']['from_bank']); ?>)</p>
                        <p class="mb-1"><strong>To Account:</strong> <?php echo htmlspecialchars($success_data['details']['to_account_number']); ?> (<?php echo htmlspecialchars($success_data['details']['to_bank']); ?>)</p>
                        <p class="mb-0"><strong>Date:</strong> <?php echo htmlspecialchars($success_data['details']['date']); ?></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <h2 class="welcome-text">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>

        <!-- Feature Cards -->
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card feature-card add-account">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <h5 class="card-title">Add Bank Account</h5>
                        <p class="card-text">Link a new bank account to your profile</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal">
                            Add Account
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card feature-card phone-transfer">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h5 class="card-title">Transfer to Phone</h5>
                        <p class="card-text">Send money using phone number</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#phoneTransferModal">
                            Transfer Now
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card feature-card self-transfer">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <h5 class="card-title">Self Transfer</h5>
                        <p class="card-text">Transfer between your accounts</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#selfTransferModal">
                            Transfer Now
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card feature-card transaction-history">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h5 class="card-title">Transaction History</h5>
                        <p class="card-text">View your transaction history</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transactionHistoryModal">
                            View History
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Card -->
        <div class="balance-card">
            <h3 class="mb-4">Account Balance</h3>
            <div class="mb-3">
                <label for="balance_account" class="form-label">Select Account</label>
                <select class="form-select" id="balance_account" onchange="updateBalance(this.value)">
                    <option value="">Choose an account...</option>
                    <?php foreach ($bank_accounts as $account): ?>
                    <option value="<?php echo $account['account_id']; ?>">
                        <?php echo htmlspecialchars($account['bank_name'] . ' - ' . $account['account_number']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="balance_display" class="balance-display" style="display: none;">
                <h4 class="mb-3">Current Balance</h4>
                <h2 class="mb-2" id="balance_amount">$0.00</h2>
                <p class="mb-0" id="account_details"></p>
            </div>
        </div>
    </div>

    <!-- Transaction History Modal -->
    <div class="modal fade" id="transactionHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Transaction History</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="history_account" class="form-label">Select Account</label>
                        <select class="form-select" id="history_account" onchange="loadTransactionHistory(this.value)">
                            <option value="">Choose an account...</option>
                            <?php foreach ($bank_accounts as $account): ?>
                            <option value="<?php echo $account['account_id']; ?>">
                                <?php echo htmlspecialchars($account['bank_name'] . ' - ' . $account['account_number']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="transaction_history" class="mt-3">
                        <!-- Transaction history will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Bank Account Modal -->
    <div class="modal fade" id="addBankModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Bank Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_bank_account.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="account_number" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="account_type" class="form-label">Account Type</label>
                            <select class="form-control" id="account_type" name="account_type" required>
                                <option value="Savings">Savings</option>
                                <option value="Checking">Checking</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Phone Transfer Modal -->
    <div class="modal fade" id="phoneTransferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transfer to Phone Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="phone_transfer.php" method="POST" id="phoneTransferForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="from_account" class="form-label">From Account</label>
                            <select class="form-control" id="from_account" name="from_account" required>
                                <?php foreach ($bank_accounts as $account): ?>
                                <option value="<?php echo $account['account_id']; ?>">
                                    <?php echo htmlspecialchars($account['bank_name'] . ' - ' . $account['account_number']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Recipient Phone Number</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                            <div id="recipient_details" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="bi bi-person-check me-2"></i>
                                    Recipient: <strong id="recipient_name"></strong>
                                </div>
                            </div>
                            <div id="recipient_not_found" class="mt-2" style="display: none;">
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Recipient not found
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required max="100000">
                            <div class="form-text">Maximum transfer limit: $100,000</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="transfer_submit" disabled>Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Self Transfer Modal -->
    <div class="modal fade" id="selfTransferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transfer Between Accounts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="self_transfer.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="from_account_self" class="form-label">From Account</label>
                            <select class="form-control" id="from_account_self" name="from_account" required>
                                <?php foreach ($bank_accounts as $account): ?>
                                <option value="<?php echo $account['account_id']; ?>">
                                    <?php echo htmlspecialchars($account['bank_name'] . ' - ' . $account['account_number']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="to_account_self" class="form-label">To Account</label>
                            <select class="form-control" id="to_account_self" name="to_account" required>
                                <?php foreach ($bank_accounts as $account): ?>
                                <option value="<?php echo $account['account_id']; ?>">
                                    <?php echo htmlspecialchars($account['bank_name'] . ' - ' . $account['account_number']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount_self" class="form-label">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="amount_self" name="amount" required max="100000">
                            <div class="form-text">Maximum transfer limit: $100,000</div>
                        </div>
                        <div class="mb-3">
                            <label for="password_self" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_self" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to update balance display
        function updateBalance(accountId) {
            if (!accountId) {
                document.getElementById('balance_display').style.display = 'none';
                return;
            }

            // Find the selected account details
            const accounts = <?php echo json_encode($bank_accounts); ?>;
            const account = accounts.find(acc => acc.account_id == accountId);
            
            if (account) {
                document.getElementById('balance_amount').textContent = '$' + parseFloat(account.balance).toFixed(2);
                document.getElementById('account_details').textContent = account.bank_name + ' - ' + account.account_number;
                document.getElementById('balance_display').style.display = 'block';
            }
        }

        // Function to load transaction history
        function loadTransactionHistory(accountId) {
            if (!accountId) {
                document.getElementById('transaction_history').innerHTML = '';
                return;
            }

            const historyDiv = document.getElementById('transaction_history');
            historyDiv.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading transactions...</p>
                </div>`;

            fetch('get_transactions.php?account_id=' + accountId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.length === 0) {
                        historyDiv.innerHTML = `
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                No transactions found for this account.
                            </div>`;
                        return;
                    }

                    let html = `
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Description</th>
                                        <th>From Account</th>
                                        <th>To Account</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    data.forEach(transaction => {
                        const isDebit = transaction.type === 'debit';
                        
                        html += `
                            <tr>
                                <td>${transaction.date}</td>
                                <td>${transaction.description}</td>
                                <td>${transaction.from_account}</td>
                                <td>${transaction.to_account}</td>
                                <td>
                                    <span class="badge bg-${isDebit ? 'danger' : 'success'}">
                                        ${isDebit ? 'Sent' : 'Received'}
                                    </span>
                                </td>
                                <td class="text-${isDebit ? 'danger' : 'success'} fw-bold">
                                    ${isDebit ? '-' : '+'}$${transaction.amount}
                                </td>
                            </tr>`;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>`;
                    historyDiv.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    historyDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Error loading transactions. Please try again.
                        </div>`;
                });
        }

        // Phone number validation
        let phoneValidationTimeout;
        document.getElementById('phone_number').addEventListener('input', function(e) {
            clearTimeout(phoneValidationTimeout);
            const phoneNumber = e.target.value;
            const submitButton = document.getElementById('transfer_submit');
            
            if (phoneNumber.length >= 10) {
                phoneValidationTimeout = setTimeout(() => {
                    fetch('phone_transfer.php?validate_phone=' + encodeURIComponent(phoneNumber))
                        .then(response => response.json())
                        .then(data => {
                            if (data.valid) {
                                document.getElementById('recipient_name').textContent = data.name;
                                document.getElementById('recipient_details').style.display = 'block';
                                document.getElementById('recipient_not_found').style.display = 'none';
                                submitButton.disabled = false;
                            } else {
                                document.getElementById('recipient_details').style.display = 'none';
                                document.getElementById('recipient_not_found').style.display = 'block';
                                submitButton.disabled = true;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            document.getElementById('recipient_details').style.display = 'none';
                            document.getElementById('recipient_not_found').style.display = 'none';
                            submitButton.disabled = true;
                        });
                }, 500);
            } else {
                document.getElementById('recipient_details').style.display = 'none';
                document.getElementById('recipient_not_found').style.display = 'none';
                submitButton.disabled = true;
            }
        });
    </script>
</body>
</html> 