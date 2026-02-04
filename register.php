<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Transfer System - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/register-theme.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center">
                            <i class="bi bi-person-plus-fill me-2"></i>
                            Register New Account
                        </h2>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <form action="auth/register.php" method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        <i class="bi bi-person me-2"></i>Username
                                    </label>
                                    <input type="text" class="form-control" id="username" name="username" required
                                           value="<?php echo isset($_SESSION['old_data']['username']) ? htmlspecialchars($_SESSION['old_data']['username']) : ''; ?>"
                                           placeholder="Choose a username">
                                    <div class="invalid-feedback">Please choose a username.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-lock me-2"></i>Password
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" required
                                           placeholder="Enter your password">
                                    <div class="invalid-feedback">Please enter a password.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">
                                        <i class="bi bi-person-badge me-2"></i>Full Name
                                    </label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required
                                           value="<?php echo isset($_SESSION['old_data']['full_name']) ? htmlspecialchars($_SESSION['old_data']['full_name']) : ''; ?>"
                                           placeholder="Enter your full name">
                                    <div class="invalid-feedback">Please enter your full name.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-2"></i>Email
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?php echo isset($_SESSION['old_data']['email']) ? htmlspecialchars($_SESSION['old_data']['email']) : ''; ?>"
                                           placeholder="Enter your email">
                                    <div class="invalid-feedback">Please enter a valid email.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone_number" class="form-label">
                                        <i class="bi bi-phone me-2"></i>Phone Number
                                    </label>
                                    <input type="tel" class="form-control" id="phone_number" name="phone_number" required
                                           pattern="[0-9]+" minlength="10" maxlength="15"
                                           value="<?php echo isset($_SESSION['old_data']['phone_number']) ? htmlspecialchars($_SESSION['old_data']['phone_number']) : ''; ?>"
                                           placeholder="Enter your phone number">
                                    <div class="invalid-feedback">Please enter a valid phone number (10-15 digits).</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">
                                        <i class="bi bi-geo-alt me-2"></i>Address
                                    </label>
                                    <textarea class="form-control" id="address" name="address" required
                                              placeholder="Enter your address"><?php echo isset($_SESSION['old_data']['address']) ? htmlspecialchars($_SESSION['old_data']['address']) : ''; ?></textarea>
                                    <div class="invalid-feedback">Please enter your address.</div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Register
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Login
                                </a>
                            </div>
                        </form>
                        <?php unset($_SESSION['old_data']); // Clear old form data after displaying ?>
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
    </script>
</body>
</html> 