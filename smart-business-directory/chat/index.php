<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];
$conversations = getConversations($user_id);

include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white rounded-top-4">
            <h4 class="mb-0"><i class="fas fa-comments me-2 text-primary"></i>Messages</h4>
        </div>
        <div class="card-body p-0">
            <?php if(!empty($conversations)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach($conversations as $conv): ?>
                    <a href="chat.php?user=<?php echo $conv['other_user_id']; ?>" class="list-group-item list-group-item-action p-3">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo UPLOAD_URL . 'avatars/' . ($conv['profile_image'] ?? 'default-avatar.png'); ?>" 
                                 class="rounded-circle me-3" width="55" height="55" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($conv['fullname']); ?></h6>
                                    <small class="text-muted"><?php echo timeAgo($conv['last_time']); ?></small>
                                </div>
                                <p class="mb-0 small text-muted"><?php echo htmlspecialchars(substr($conv['last_message'], 0, 60)); ?></p>
                            </div>
                            <?php if($conv['unread_count'] > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-3"><?php echo $conv['unread_count']; ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5>No messages yet</h5>
                    <p class="text-muted">Start a conversation with businesses or customers</p>
                    <a href="../businesses.php" class="btn btn-primary">Browse Businesses</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>