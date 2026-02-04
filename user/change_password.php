<?php
session_start();
require_once __DIR__ . '/../config/database.php';


// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];
    
    // Get current user's password
    $sql = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (password_verify($old_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Password changed successfully!";
            } else {
                $_SESSION['error'] = "Failed to change password. Please try again.";
            }
        } else {
            $_SESSION['error'] = "New passwords do not match!";
        }
    } else {
        $_SESSION['error'] = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Money Transfer System</title>
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
                        <a class="nav-link active" href="change_password.php">
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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i>Change Password
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php 
                                    echo $_SESSION['success'];
                                    unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php 
                                    echo $_SESSION['error'];
                                    unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form action="change_password.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="old_password" class="form-label">
                                    <i class="bi bi-lock me-2"></i>Current Password
                                </label>
                                <div class="input-group">
                                <input type="password" class="form-control" id="old_password" name="old_password" required>
                                    <button class="btn btn-secondary" type="button" onclick="togglePassword('old_password')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter your current password.</div>
                            </div>

                            <div class="mb-4">
                                <label for="new_password" class="form-label">
                                    <i class="bi bi-key me-2"></i>New Password
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" required>
                                    <button class="btn btn-secondary" type="button" onclick="togglePassword('new_password')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter a valid password.</div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-check2-circle me-2"></i>Confirm New Password
                                </label>
                                <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <button class="btn btn-secondary" type="button" onclick="togglePassword('confirm_password')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please confirm your new password.</div>
                            </div>

                            <div class="password-requirements mb-4">
                                <h6 class="mb-3">
                                    <i class="bi bi-shield-check me-2"></i>Password Requirements:
                                </h6>
                                <div class="requirement-item" id="length-check">
                                    <i class="bi bi-circle"></i> At least 8 characters long
                                </div>
                                <div class="requirement-item" id="uppercase-check">
                                    <i class="bi bi-circle"></i> Contains at least one uppercase letter
                                </div>
                                <div class="requirement-item" id="lowercase-check">
                                    <i class="bi bi-circle"></i> Contains at least one lowercase letter
                                </div>
                                <div class="requirement-item" id="number-check">
                                    <i class="bi bi-circle"></i> Contains at least one number
                                </div>
                                <div class="requirement-item" id="special-check">
                                    <i class="bi bi-circle"></i> Contains at least one special character
                                </div>
                                <div class="requirement-item" id="match-check">
                                    <i class="bi bi-circle"></i> Passwords match
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-shield-check me-2"></i>Change Password
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
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Password toggle visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = event.currentTarget.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        // Password requirements validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function updateRequirements() {
            const password = newPassword.value;
            const confirm = confirmPassword.value;
            
            // Update check icons
            document.getElementById('length-check').querySelector('i').className = 
                password.length >= 8 ? 'bi bi-check-circle text-success' : 'bi bi-circle';
            
            document.getElementById('uppercase-check').querySelector('i').className = 
                /[A-Z]/.test(password) ? 'bi bi-check-circle text-success' : 'bi bi-circle';
            
            document.getElementById('lowercase-check').querySelector('i').className = 
                /[a-z]/.test(password) ? 'bi bi-check-circle text-success' : 'bi bi-circle';
            
            document.getElementById('number-check').querySelector('i').className = 
                /\d/.test(password) ? 'bi bi-check-circle text-success' : 'bi bi-circle';
            
            document.getElementById('special-check').querySelector('i').className = 
                /[@$!%*?&]/.test(password) ? 'bi bi-check-circle text-success' : 'bi bi-circle';
            
            document.getElementById('match-check').querySelector('i').className = 
                password && password === confirm ? 'bi bi-check-circle text-success' : 'bi bi-circle';
        }

        newPassword.addEventListener('input', updateRequirements);
        confirmPassword.addEventListener('input', updateRequirements);
    </script>
</body>
</html> 