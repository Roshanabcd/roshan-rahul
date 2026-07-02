<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;

if ($other_user_id == 0) {
    redirect('index.php');
}

// Get other user info
$other_user = getUserById($other_user_id);
$messages = getMessages($user_id, $other_user_id, 100, 0);

include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex align-items-center">
                <img src="<?php echo UPLOAD_URL . 'avatars/' . ($other_user['profile_image'] ?? 'default-avatar.png'); ?>" 
                     class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                <div>
                    <h5 class="mb-0"><?php echo $other_user['fullname']; ?></h5>
                    <small class="text-muted">Online</small>
                </div>
                <a href="javascript:history.back()" class="btn btn-sm btn-secondary ms-auto">Back</a>
            </div>
        </div>
        
        <div class="card-body" style="height: 500px; overflow-y: auto;" id="chatMessages">
            <?php foreach($messages as $msg): ?>
            <div class="chat-message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?> mb-3">
                <div class="message-bubble d-inline-block p-3 rounded-3 <?php echo $msg['sender_id'] == $user_id ? 'bg-primary text-white' : 'bg-light'; ?>">
                    <?php echo nl2br($msg['message']); ?>
                    <div class="message-time small <?php echo $msg['sender_id'] == $user_id ? 'text-white-50' : 'text-muted'; ?>">
                        <?php echo date('h:i A', strtotime($msg['created_at'])); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="card-footer bg-white">
            <form id="chatForm" class="d-flex gap-2">
                <input type="hidden" name="receiver_id" value="<?php echo $other_user_id; ?>">
                <input type="text" name="message" class="form-control" placeholder="Type your message..." required>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send</button>
            </form>
        </div>
    </div>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');

// Scroll to bottom
chatMessages.scrollTop = chatMessages.scrollHeight;

// Send message
chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(chatForm);
    
    const response = await fetch('send.php', {
        method: 'POST',
        body: formData
    });
    
    if (response.ok) {
        chatForm.reset();
        loadMessages();
    }
});

// Load new messages
function loadMessages() {
    const receiverId = document.querySelector('input[name="receiver_id"]').value;
    
    fetch(`get-messages.php?user=${receiverId}`)
        .then(response => response.json())
        .then(messages => {
            chatMessages.innerHTML = '';
            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = `chat-message ${msg.sender_id == <?php echo $user_id; ?> ? 'sent' : 'received'} mb-3`;
                div.innerHTML = `
                    <div class="message-bubble d-inline-block p-3 rounded-3 ${msg.sender_id == <?php echo $user_id; ?> ? 'bg-primary text-white' : 'bg-light'}">
                        ${msg.message}
                        <div class="message-time small ${msg.sender_id == <?php echo $user_id; ?> ? 'text-white-50' : 'text-muted'}">
                            ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </div>
                    </div>
                `;
                chatMessages.appendChild(div);
            });
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
}

// Poll for new messages every 3 seconds
setInterval(loadMessages, 3000);
</script>

<style>
.chat-message.sent {
    text-align: right;
}
.chat-message.sent .message-bubble {
    display: inline-block;
}
.chat-message.received .message-bubble {
    display: inline-block;
}
</style>

<?php include '../includes/footer.php'; ?>