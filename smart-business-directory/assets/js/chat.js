// Real-time Chat Functionality
let chatPollInterval = null;
let lastMessageId = 0;

function initChat(receiverId, currentUserId) {
    const messageContainer = document.getElementById('chatMessages');
    const messageForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    
    if (!messageContainer || !messageForm) return;
    
    // Scroll to bottom
    messageContainer.scrollTop = messageContainer.scrollHeight;
    
    // Get last message ID
    const lastMsg = messageContainer.querySelector('.chat-message:last-child');
    if (lastMsg) {
        const msgId = lastMsg.dataset.messageId;
        if (msgId) lastMessageId = parseInt(msgId);
    }
    
    // Send message
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) return;
        
        try {
            const response = await fetch('send.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `receiver_id=${receiverId}&message=${encodeURIComponent(message)}`
            });
            
            if (response.ok) {
                messageInput.value = '';
                loadMessages(receiverId, currentUserId);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    });
    
    // Start polling for new messages
    if (chatPollInterval) clearInterval(chatPollInterval);
    chatPollInterval = setInterval(() => {
        loadMessages(receiverId, currentUserId);
    }, 3000);
    
    // Enter key to send
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });
}

async function loadMessages(receiverId, currentUserId) {
    try {
        const response = await fetch(`get-messages.php?user=${receiverId}&last_id=${lastMessageId}`);
        const messages = await response.json();
        
        if (messages.length > 0) {
            const messageContainer = document.getElementById('chatMessages');
            const shouldScroll = messageContainer.scrollHeight - messageContainer.scrollTop - messageContainer.clientHeight < 100;
            
            messages.forEach(msg => {
                if (msg.message_id > lastMessageId) {
                    appendMessage(msg, currentUserId);
                    lastMessageId = msg.message_id;
                }
            });
            
            if (shouldScroll) {
                messageContainer.scrollTop = messageContainer.scrollHeight;
            }
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

function appendMessage(message, currentUserId) {
    const messageContainer = document.getElementById('chatMessages');
    const isSent = message.sender_id == currentUserId;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${isSent ? 'sent' : 'received'} mb-3`;
    messageDiv.dataset.messageId = message.message_id;
    
    const time = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    messageDiv.innerHTML = `
        <div class="message-bubble d-inline-block p-3 rounded-3 ${isSent ? 'bg-primary text-white' : 'bg-light'}">
            ${escapeHtml(message.message)}
            <div class="message-time small ${isSent ? 'text-white-50' : 'text-muted'} mt-1">
                ${time}
                ${isSent ? '<i class="fas fa-check ms-1"></i>' : ''}
            </div>
        </div>
    `;
    
    messageContainer.appendChild(messageDiv);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Stop polling when leaving page
window.addEventListener('beforeunload', function() {
    if (chatPollInterval) {
        clearInterval(chatPollInterval);
    }
});