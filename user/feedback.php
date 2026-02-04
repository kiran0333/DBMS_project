<?php
session_start();
require_once __DIR__ . '/../config/database.php';


// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $comment = $conn->real_escape_string($_POST['comment']);
    
    $sql = "INSERT INTO feedback (user_id, rating, comment) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $rating, $comment);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Thank you for your feedback!";
    } else {
        $_SESSION['error'] = "Failed to submit feedback. Please try again.";
    }
}

// Get user's previous feedback
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM feedback WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$feedback_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/user-components.css" rel="stylesheet">
    <style>
        .star-rating {
            font-size: 2rem;
            cursor: pointer;
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin: 1rem 0;
        }
        .star-rating i {
            transition: all 0.3s ease;
        }
        .star-rating i:hover {
            transform: scale(1.2);
        }
        .feedback-card {
            transition: all 0.3s ease;
        }
        .feedback-card:hover {
            transform: translateY(-5px);
        }
    </style>
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
                        <a class="nav-link active" href="feedback.php">
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
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi bi-chat-square-text me-2"></i>Submit Feedback
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

                        <form action="feedback.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label class="form-label text-center d-block">
                                    <i class="bi bi-star me-2"></i>Your Rating
                                </label>
                                <div class="star-rating" id="star-rating">
                                    <i class="bi bi-star-fill" data-rating="1"></i>
                                    <i class="bi bi-star-fill" data-rating="2"></i>
                                    <i class="bi bi-star-fill" data-rating="3"></i>
                                    <i class="bi bi-star-fill" data-rating="4"></i>
                                    <i class="bi bi-star-fill" data-rating="5"></i>
                                </div>
                                <input type="hidden" name="rating" id="rating" required>
                                <div class="invalid-feedback text-center">Please select a rating.</div>
                            </div>

                            <div class="mb-4">
                                <label for="comment" class="form-label">
                                    <i class="bi bi-chat-dots me-2"></i>Your Feedback
                                </label>
                                <textarea class="form-control" id="comment" name="comment" rows="4" 
                                          required placeholder="Tell us about your experience..."></textarea>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-2"></i>Your feedback helps us improve our services
                                </div>
                                <div class="invalid-feedback">Please provide your feedback.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Submit Feedback
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>Your Previous Feedback
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($feedback_history)): ?>
                            <div class="text-center text-muted">
                                <i class="bi bi-chat-square-text display-4 mb-3"></i>
                                <p>You haven't submitted any feedback yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($feedback_history as $feedback): ?>
                                <div class="feedback-card border-bottom mb-4 pb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="star-display me-3">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $feedback['rating'] ? '-fill' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                        <small class="text-muted ms-auto">
                                            <i class="bi bi-clock me-1"></i>
                                            <?php echo date('F j, Y g:i A', strtotime($feedback['created_at'])); ?>
                                    </small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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

        // Star rating functionality
        const starRating = document.getElementById('star-rating');
        const ratingInput = document.getElementById('rating');
        const stars = starRating.getElementsByTagName('i');

        function setRating(rating) {
            ratingInput.value = rating;
            for (let i = 0; i < stars.length; i++) {
                if (i < rating) {
                    stars[i].style.color = 'var(--button)';
                    stars[i].classList.replace('bi-star', 'bi-star-fill');
                } else {
                    stars[i].style.color = 'rgba(250, 204, 21, 0.3)';
                    stars[i].classList.replace('bi-star-fill', 'bi-star');
                }
            }
        }

        // Initialize with no rating
        setRating(0);

        // Add hover effect
        Array.from(stars).forEach((star, index) => {
            star.addEventListener('mouseover', () => {
                for (let i = 0; i < stars.length; i++) {
                    if (i <= index) {
                        stars[i].style.color = 'var(--button)';
                        stars[i].classList.replace('bi-star', 'bi-star-fill');
                    } else {
                        stars[i].style.color = 'rgba(250, 204, 21, 0.3)';
                        stars[i].classList.replace('bi-star-fill', 'bi-star');
            }
        }
            });
        });

        starRating.addEventListener('mouseleave', () => {
            const rating = parseInt(ratingInput.value) || 0;
            setRating(rating);
        });

        starRating.addEventListener('click', (e) => {
            if (e.target.tagName === 'I') {
                const rating = e.target.getAttribute('data-rating');
                setRating(rating);
                ratingInput.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 