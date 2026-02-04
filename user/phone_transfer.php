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
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $amount = floatval($_POST['amount']);
    $password = $_POST['password'];
    
    // Check transaction limit
    if ($amount > 100000) {
        $_SESSION['error'] = "Transaction amount exceeds the limit of $100,000";
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
    
    // Check if recipient exists and get their details
    $sql = "SELECT user_id, full_name, phone_number FROM users WHERE phone_number = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $recipient = $stmt->get_result()->fetch_assoc();
    
    if (!$recipient) {
        $_SESSION['error'] = "Recipient not found or account is inactive";
        header('Location: dashboard.php');
        exit();
    }
    
    $recipient_id = $recipient['user_id'];
    
    // Check if sender has sufficient balance and get account details
    $sql = "SELECT * FROM bank_accounts WHERE account_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $from_account, $user_id);
    $stmt->execute();
    $sender_account = $stmt->get_result()->fetch_assoc();
    
    if (!$sender_account || $sender_account['balance'] < $amount) {
        $_SESSION['error'] = "Insufficient balance";
        header('Location: dashboard.php');
        exit();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get recipient's account
        $sql = "SELECT * FROM bank_accounts WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$recipient_account = $stmt->get_result()->fetch_assoc();
        
        if (!$recipient_account) {
            throw new Exception("Recipient has no bank account");
        }
        
$to_account_id = $recipient_account['account_id'];
        
        // First insert the transaction record with pending status
        $sql = "INSERT INTO transactions (from_account_id, to_account_id, amount, transaction_type, status) 
                VALUES (?, ?, ?, 'phone', 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iid", $from_account, $to_account_id, $amount);
        $stmt->execute();
        
        $transaction_id = $conn->insert_id;

        // Deduct amount from sender's account
        $sql = "UPDATE bank_accounts SET balance = balance - ? WHERE account_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $amount, $from_account);
        $stmt->execute();
        
        // Add amount to recipient's account
        $sql = "UPDATE bank_accounts SET balance = balance + ? WHERE account_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $amount, $to_account_id);
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
            'message' => 'Transfer successful',
            'details' => array(
                'amount' => number_format($amount, 2),
                'from_account_number' => $sender_account['account_number'],
                'from_bank' => $sender_account['bank_name'],
                'to_account_number' => $recipient_account['account_number'],
                'to_bank' => $recipient_account['bank_name'],
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

// AJAX endpoint to validate phone number
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['validate_phone'])) {
    $phone_number = $conn->real_escape_string($_GET['validate_phone']);
    
    $sql = "SELECT full_name, phone_number FROM users WHERE phone_number = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['valid' => true, 'name' => $result['full_name']]);
    } else {
        echo json_encode(['valid' => false]);
    }
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
    <title>Phone Transfer - Money Transfer System</title>
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
                            <i class="bi bi-phone me-2"></i>Phone Transfer
                        </h4>
                    </div>
                    <div class="card-body">
                        <form id="phoneTransferForm" action="phone_transfer.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="from_account" class="form-label">
                                    <i class="bi bi-bank me-2"></i>From Account
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
                                <label for="phone_number" class="form-label">
                                    <i class="bi bi-phone me-2"></i>Recipient Phone Number
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-phone"></i>
                                    </span>
                                    <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                           placeholder="Enter recipient's phone number" required>
                                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                                </div>
                                <div id="recipientDetails" class="mt-2" style="display: none;">
                                    <div class="badge bg-primary px-3 py-2">
                                        Recipient: <span id="recipientName" style="color:rgb(29, 29, 29); font-weight: 600;"></span>
                                    </div>
                                </div>
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
                                    <i class="bi bi-send me-2"></i>Send Money
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
        const phoneInput = document.getElementById('phone_number');
        const recipientDetails = document.getElementById('recipientDetails');
        const recipientName = document.getElementById('recipientName');
        let typingTimer;

        phoneInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            recipientDetails.style.display = 'none';
            typingTimer = setTimeout(validatePhoneNumber, 300);
        });

        function validatePhoneNumber() {
            const phoneNumber = phoneInput.value.trim();
            if (phoneNumber.length >= 10) {
                fetch(`phone_transfer.php?validate_phone=${encodeURIComponent(phoneNumber)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            recipientName.textContent = data.name;
                            recipientDetails.style.display = 'block';
                            phoneInput.classList.remove('is-invalid');
                            phoneInput.classList.add('is-valid');
                        } else {
                            recipientDetails.style.display = 'none';
                            phoneInput.classList.remove('is-valid');
                            phoneInput.classList.add('is-invalid');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        recipientDetails.style.display = 'none';
                        phoneInput.classList.remove('is-valid', 'is-invalid');
                    });
            } else {
                recipientDetails.style.display = 'none';
                phoneInput.classList.remove('is-valid', 'is-invalid');
            }
        }

        // Form validation
        const form = document.getElementById('phoneTransferForm');
        form.addEventListener('submit', function(event) {
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