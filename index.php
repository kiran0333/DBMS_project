<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Easy Money Transfer - Modern Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/login-theme.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-currency-exchange"></i> 
                Easy Money Transfer
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#benefits">Benefits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                    <a href="register.php" class="btn btn-primary">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Transform Your Money Transfers</h1>
                <p class="lead">Experience lightning-fast, secure, and borderless money transfers with Easy Money Transfer</p>
                <div class="hero-buttons">
                    <button class="btn btn-primary btn-custom me-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Start Transferring
                    </button>
                    <a href="#benefits" class="btn btn-outline-light btn-custom">
                        Discover More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="benefits-section">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Easy Money Transfer?</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="benefit-card">
                        <i class="bi bi-lightning-charge benefit-icon"></i>
                        <h4>Lightning Fast</h4>
                        <p>Transfer money within seconds to anywhere in the world. No more waiting for days.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card">
                        <i class="bi bi-shield-check benefit-icon"></i>
                        <h4>Bank-Grade Security</h4>
                        <p>Your money is protected with military-grade encryption and advanced security measures.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card">
                        <i class="bi bi-graph-down benefit-icon"></i>
                        <h4>Low Fees</h4>
                        <p>Enjoy competitive rates and transparent pricing with no hidden charges.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="benefit-card">
                        <i class="bi bi-globe benefit-icon"></i>
                        <h4>Global Coverage</h4>
                        <p>Send money to over 100 countries worldwide with real-time exchange rates.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card">
                        <i class="bi bi-phone benefit-icon"></i>
                        <h4>Mobile First</h4>
                        <p>Transfer money on the go with our user-friendly mobile experience.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card">
                        <i class="bi bi-headset benefit-icon"></i>
                        <h4>24/7 Support</h4>
                        <p>Our dedicated support team is always here to help you, anytime, anywhere.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-content text-center">
                <h2 class="mb-5">Our Story</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="about-card">
                            <i class="bi bi-rocket-takeoff display-4 mb-4"></i>
                            <h4>Our Mission</h4>
                            <p>To make money transfers accessible, affordable, and instant for everyone, everywhere.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="about-card">
                            <i class="bi bi-eye display-4 mb-4"></i>
                            <h4>Our Vision</h4>
                            <p>To become the world's most trusted and innovative money transfer platform.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="about-card">
                            <i class="bi bi-heart display-4 mb-4"></i>
                            <h4>Our Values</h4>
                            <p>Trust, Innovation, Customer Focus, and Global Accessibility drive everything we do.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-5">
    <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Easy Money Transfer</h5>
                    <p>Making global money transfers swift, secure, and simple.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#benefits" class="text-white">Benefits</a></li>
                        <li><a href="#about" class="text-white">About Us</a></li>
                        <li><a href="#contact" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-geo-alt"></i> 123 Transfer Street, City</li>
                        <li><i class="bi bi-envelope"></i> support@easymoneytransfer.com</li>
                        <li><i class="bi bi-phone"></i> +1 234 567 8900</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>&copy; 2024 Easy Money Transfer. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-circle me-2"></i>
                        Login to Your Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form action="auth/login.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" 
                                    value="<?php echo isset($_SESSION['old_username']) ? htmlspecialchars($_SESSION['old_username']) : ''; ?>"
                                    required>
                            </div>
                                <div class="invalid-feedback">Please enter your username.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>
                            
                        <div class="mb-4">
                            <label class="form-label d-block">Login Type</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="userType" value="user" checked>
                                <label class="form-check-label" for="userType">
                                    <i class="bi bi-person-fill me-1"></i>User
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="adminType" value="admin">
                                <label class="form-check-label" for="adminType">
                                    <i class="bi bi-shield-fill me-1"></i>Admin
                                </label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </div>
                        </form>
                    </div>
                <div class="modal-footer justify-content-center">
                    <p class="mb-0">Don't have an account? 
                        <a href="register.php" class="text-decoration-none" style="color: var(--accent);">Register here</a>
                    </p>
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

        // Toggle between user and admin login
        document.getElementById('userLogin').addEventListener('change', function() {
            document.getElementById('loginTypeInput').value = 'user';
            document.getElementById('registerLink').style.display = 'block';
        });

        document.getElementById('adminLogin').addEventListener('change', function() {
            document.getElementById('loginTypeInput').value = 'admin';
            document.getElementById('registerLink').style.display = 'none';
        });
    </script>
</body>
</html>
