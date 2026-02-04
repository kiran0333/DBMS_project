<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    die('Unauthorized access');
}

if (!isset($_GET['user_id'])) {
    die('User ID not provided');
}

$user_id = intval($_GET['user_id']);

// Get user details
$user_sql = "SELECT * FROM users WHERE user_id = ? AND user_type = 'user'";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if (!$user) {
    die('User not found');
}

// Get user's feedback
$feedback_sql = "SELECT * FROM feedback WHERE user_id = ? ORDER BY created_at DESC";
$feedback_stmt = $conn->prepare($feedback_sql);
$feedback_stmt->bind_param("i", $user_id);
$feedback_stmt->execute();
$feedback = $feedback_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="mb-3">
    <h6>Feedback from <?php echo htmlspecialchars($user['full_name']); ?></h6>
</div>

<?php if (empty($feedback)): ?>
    <p class="text-muted">No feedback found for this user.</p>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($feedback as $item): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?php echo $i <= $item['rating'] ? '-fill' : ''; ?> text-warning"></i>
                        <?php endfor; ?>
                    </div>
                    <small class="text-muted">
                        <?php echo date('F j, Y g:i A', strtotime($item['created_at'])); ?>
                    </small>
                </div>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($item['comment'])); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?> 