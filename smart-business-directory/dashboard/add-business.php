<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];
$conversations = getConversations($user_id);

include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="fas fa-comments me-2"></i>Messages</h4>
        </div>
        <div class="card-body p-0">
            <?php if(!empty($conversations)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach($conversations as $conv): ?>
                    <a href="chat.php?user=<?php echo $conv['other_user_id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo UPLOAD_URL . 'avatars/' . ($conv['profile_image'] ?? 'default-avatar.png'); ?>" 
                                 class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-0"><?php echo $conv['fullname']; ?></h6>
                                    <small class="text-muted"><?php echo timeAgo($conv['last_time']); ?></small>
                                </div>
                                <p class="mb-0 small text-muted"><?php echo substr($conv['last_message'], 0, 50); ?></p>
                            </div>
                            <?php if($conv['unread_count'] > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-2"><?php echo $conv['unread_count']; ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p>No conversations yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>