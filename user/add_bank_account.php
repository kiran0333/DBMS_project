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
    $account_number = $conn->real_escape_string($_POST['account_number']);
    $bank_name = $conn->real_escape_string($_POST['bank_name']);
    $account_type = $conn->real_escape_string($_POST['account_type']);
    
    // Check if account number already exists
    $check_sql = "SELECT * FROM bank_accounts WHERE account_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $account_number);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "This account number is already registered";
        header('Location: dashboard.php');
        exit();
    }

    // Check if bank name already exists for this user
    $check_bank_sql = "SELECT * FROM bank_accounts WHERE user_id = ? AND bank_name = ?";
    $check_bank_stmt = $conn->prepare($check_bank_sql);
    $check_bank_stmt->bind_param("is", $user_id, $bank_name);
    $check_bank_stmt->execute();
    
    if ($check_bank_stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "You already have an account registered with this bank";
        header('Location: dashboard.php');
        exit();
    }
    
    // Add new bank account
    $sql = "INSERT INTO bank_accounts (user_id, account_number, bank_name, account_type) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $account_number, $bank_name, $account_type);
    
    if ($stmt->execute()) {
        // Get the newly inserted account ID
        $new_account_id = $stmt->insert_id;
        
        // Format success message with account details
        $success_message = array(
            'message' => 'Bank account added successfully',
            'details' => array(
                'bank_name' => $bank_name,
                'account_number' => $account_number,
                'account_type' => $account_type,
                // Add empty transaction details to prevent undefined index errors
                'amount' => '',
                'from_account_number' => '',
                'from_bank' => '',
                'to_account_number' => '',
                'to_bank' => '',
                'date' => date('Y-m-d H:i:s')
            )
        );
        $_SESSION['success'] = json_encode($success_message);
    } else {
        $_SESSION['error'] = "Failed to add bank account";
    }
}

header('Location: dashboard.php');
exit();
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bank Account - Money Transfer System</title>
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
                            <i class="bi bi-bank me-2"></i>Add Bank Account
                        </h4>
                    </div>
                    <div class="card-body">
                        <form id="addBankForm" action="add_bank_account.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="bank_name" class="form-label">
                                    <i class="bi bi-building me-2"></i>Bank Name
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-building"></i>
                                    </span>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                           placeholder="Enter bank name" required>
                                    <div class="invalid-feedback">Please enter a bank name.</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="account_number" class="form-label">
                                    <i class="bi bi-credit-card me-2"></i>Account Number
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-credit-card"></i>
                                    </span>
                                    <input type="text" class="form-control" id="account_number" name="account_number" 
                                           placeholder="Enter account number" required pattern="[0-9]+" 
                                           minlength="8" maxlength="20">
                                    <div class="invalid-feedback">Please enter a valid account number (8-20 digits).</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="account_type" class="form-label">
                                    <i class="bi bi-wallet2 me-2"></i>Account Type
                                </label>
                                <select class="form-control" id="account_type" name="account_type" required>
                                    <option value="">Select account type</option>
                                    <option value="Savings">Savings Account</option>
                                    <option value="Checking">Checking Account</option>
                                    <option value="Business">Business Account</option>
                                </select>
                                <div class="invalid-feedback">Please select an account type.</div>
                            </div>

                            <div class="alert alert-info" role="alert">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Important:</strong> Please ensure all bank account details are accurate. 
                                Incorrect information may lead to failed transactions.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Add Bank Account
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
        const form = document.getElementById('addBankForm');
        const accountNumber = document.getElementById('account_number');

        accountNumber.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });

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