<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_phone = $_POST['new_phone'] ?? '';
    $error = '';
    $success = '';

    // Verify password
    if (password_verify($current_password, $user['password'])) {
        // Check if phone number is already in use
        $check_sql = "SELECT user_id FROM users WHERE phone_number = ? AND user_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $new_phone, $user_id);
        $check_stmt->execute();
        $existing_user = $check_stmt->get_result()->fetch_assoc();

        if ($existing_user) {
            $error = "This phone number is already registered with another account.";
        } else {
            // Update phone number
            $update_sql = "UPDATE users SET phone_number = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_phone, $user_id);
            
            if ($update_stmt->execute()) {
                $success = "Phone number updated successfully!";
                // Update session user data
                $user['phone_number'] = $new_phone;
            } else {
                $error = "Failed to update phone number. Please try again.";
            }
        }
    } else {
        $error = "Incorrect password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Phone Number - Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/user-components.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
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
                        <a class="nav-link active" href="change_phone.php">
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
                            <i class="bi bi-phone me-2"></i>Change Phone Number
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="change_phone.php" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="current_phone" class="form-label">
                                    <i class="bi bi-phone me-2"></i>Current Phone Number
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-telephone"></i>
                                    </span>
                                    <input type="text" class="form-control" id="current_phone" 
                                           value="<?php echo htmlspecialchars($user['phone_number']); ?>" disabled>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="new_phone" class="form-label">
                                    <i class="bi bi-phone-fill me-2"></i>New Phone Number
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-telephone-fill"></i>
                                    </span>
                                    <input type="tel" class="form-control" id="new_phone" name="new_phone" 
                                           required pattern="[0-9]+" minlength="10" maxlength="15"
                                           placeholder="Enter your new phone number">
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-2"></i>Enter numbers only, 10-15 digits
                                </div>
                                <div class="invalid-feedback">Please enter a valid phone number (10-15 digits).</div>
                            </div>

                            <div class="mb-4">
                                <label for="current_password" class="form-label">
                                    <i class="bi bi-shield-lock me-2"></i>Current Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="password" class="form-control" id="current_password" 
                                           name="current_password" required>
                                    <button class="btn btn-secondary" type="button" onclick="togglePassword('current_password')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-shield-check me-2"></i>Enter your current password to confirm the change
                                </div>
                                <div class="invalid-feedback">Please enter your current password.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2-circle me-2"></i>Update Phone Number
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

        // Phone number validation
        const phoneInput = document.getElementById('new_phone');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value;
            // Remove any non-digit characters
            value = value.replace(/\D/g, '');
            // Limit to 15 digits
            if (value.length > 15) {
                value = value.slice(0, 15);
            }
            e.target.value = value;
        });
    </script>
</body>
</html> 