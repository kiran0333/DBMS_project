<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get all users except admin
$sql = "SELECT * FROM users WHERE user_type = 'user' ORDER BY created_at DESC";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

// Get all feedback with user information
$feedback_sql = "SELECT f.*, u.username, u.full_name 
                FROM feedback f 
                JOIN users u ON f.user_id = u.user_id 
                ORDER BY f.created_at DESC";
$feedback_result = $conn->query($feedback_sql);
$feedback = $feedback_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .search-sort-container {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .badge.feedback-count {
            background: rgba(34, 211, 238, 0.1);
            color: #22D3EE;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#customers">Customers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#feedback">Feedback</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <!-- Customers Section -->
        <section id="customers" class="mb-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Customer Management</h4>
                </div>
                <div class="card-body">
                    <!-- Search and Sort Controls -->
                    <div class="search-sort-container mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search by username...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="sortSelect">
                                    <option value="name">Sort by Name</option>
                                    <option value="highest_transaction">Sort by Highest Transaction</option>
                                    <option value="first_register">Sort by Registration Date</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Total Transactions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- Table content will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feedback Section -->
        <section id="feedback">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Customer Feedback</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feedback as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $item['rating'] ? '-fill' : ''; ?> text-warning"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><?php echo nl2br(htmlspecialchars($item['comment'])); ?></td>
                                    <td><?php echo date('F j, Y g:i A', strtotime($item['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Transaction Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Transactions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="transactionContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userFeedbackContent">
                    <!-- Feedback content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to load users
        function loadUsers(search = '', sortBy = 'name') {
            fetch(`get_users.php?search=${encodeURIComponent(search)}&sort_by=${encodeURIComponent(sortBy)}`)
                .then(response => response.json())
                .then(users => {
                    const tbody = document.getElementById('usersTableBody');
                    tbody.innerHTML = '';
                    
                    users.forEach(user => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${user.username}</td>
                            <td>${user.full_name}</td>
                            <td>${user.email}</td>
                            <td>${user.phone_number}</td>
                            <td>
                                <span class="badge bg-${user.status === 'active' ? 'success' : 'warning'}">
                                    ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                                </span>
                            </td>
                            <td>$${parseFloat(user.total_transactions).toFixed(2)}</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-info" onclick="viewTransactions(${user.user_id})">
                                        View
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="toggleStatus(${user.user_id}, '${user.status}')">
                                        ${user.status === 'active' ? 'Hold' : 'Activate'}
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.user_id})">
                                        Delete
                                    </button>
                                    ${user.feedback_count > 0 ? `
                                        <button class="btn btn-sm btn-primary" onclick="viewFeedback(${user.user_id})">
                                            Feedback <span class="badge feedback-count">${user.feedback_count}</span>
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                });
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            loadUsers();

            // Search input handler
            let searchTimeout;
            document.getElementById('searchInput').addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadUsers(e.target.value, document.getElementById('sortSelect').value);
                }, 300);
            });

            // Sort select handler
            document.getElementById('sortSelect').addEventListener('change', (e) => {
                loadUsers(document.getElementById('searchInput').value, e.target.value);
            });
        });

        function viewTransactions(userId) {
            const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
            fetch(`get_transactions.php?user_id=${userId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('transactionContent').innerHTML = html;
                    modal.show();
                });
        }

        function toggleStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'hold' : 'active';
            if (confirm(`Are you sure you want to ${currentStatus === 'active' ? 'hold' : 'activate'} this user?`)) {
                window.location.href = `toggle_status.php?user_id=${userId}&status=${newStatus}`;
            }
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                window.location.href = `delete_user.php?user_id=${userId}`;
            }
        }

        function viewFeedback(userId) {
            const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
            fetch(`get_user_feedback.php?user_id=${userId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('userFeedbackContent').innerHTML = html;
                    modal.show();
                });
        }
    </script>
</body>
</html> 