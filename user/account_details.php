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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details - Money Transfer System</title>
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
                        <a class="nav-link active" href="account_details.php">
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi bi-person-badge me-2"></i>Account Details
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4 fw-bold text-white">
                                <i class="bi bi-person me-2"></i>Username:
                            </div>
                            <div class="col-md-8"><?php echo htmlspecialchars($user['username']); ?></div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4 fw-bold text-white">
                                <i class="bi bi-person-badge me-2"></i>Full Name:
                            </div>
                            <div class="col-md-8"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4 fw-bold text-white">
                                <i class="bi bi-envelope me-2"></i>Email:
                            </div>
                            <div class="col-md-8"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4 fw-bold text-white">
                                <i class="bi bi-phone me-2"></i>Phone Number:
                            </div>
                            <div class="col-md-8"><?php echo htmlspecialchars($user['phone_number']); ?></div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4 fw-bold text-white">
                                <i class="bi bi-geo-alt me-2"></i>Address:
                            </div>
                            <div class="col-md-8"><?php echo nl2br(htmlspecialchars($user['address'])); ?></div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4 fw-bold text-white">
                                <i class="bi bi-shield-check me-2"></i>Account Status:
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>">
                                    <i class="bi bi-<?php echo $user['status'] === 'active' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                                    <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4 fw-bold text-white">
                                <i class="bi bi-calendar-event me-2"></i>Member Since:
                            </div>
                            <div class="col-md-8"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                            </a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="bi bi-trash me-2"></i>Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="delete_account.php" method="POST">
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Warning: This action cannot be undone. All your data will be permanently deleted.
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-shield-lock me-2"></i>Confirm Password
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="password" required>
                            <div class="form-text">Please enter your password to confirm account deletion</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 