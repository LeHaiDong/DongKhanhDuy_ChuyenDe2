@once
<div class="chat-widget-container">
    <button id="chatToggle" class="chat-toggle" type="button" aria-label="Mở chatbot tư vấn">
        <span class="chat-toggle-inner">
            <i class="fas fa-comments"></i>
            <span class="pulse-ring"></span>
        </span>
    </button>

    <div id="chatBox" class="chat-box" aria-live="polite">
        <div class="chat-header">
            <div class="chat-header-content">
                <div class="assistant-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="assistant-info">
                    <h3>Chatbot MienTayShop</h3>
                    <p>Kết nối tư vấn: 127.0.0.1:8001</p>
                </div>
            </div>
            <div class="chat-actions">
                <button id="clearChat" class="action-btn" type="button" title="Xóa cuộc trò chuyện">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button id="minimizeChat" class="action-btn" type="button" title="Thu nhỏ">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="message ai-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        <p>Xin chào! Mình là chatbot tư vấn của MienTayShop. Bạn có thể hỏi về sản phẩm, giá, cách đặt hàng, thanh toán, voucher hoặc tình trạng đơn hàng.</p>
                        <p>Mình sẽ ưu tiên gợi ý sản phẩm đúng với tên hàng, thương hiệu hoặc danh mục bạn đang cần.</p>
                    </div>
                    <div class="message-time">Bây giờ</div>
                </div>
            </div>
        </div>

        <div class="chat-input-container">
            <form id="chatForm" class="chat-form">
                @csrf
                <div class="input-group">
                    <input type="text" id="chatInput" aria-label="Nội dung cần hỏi chatbot" autocomplete="off" maxlength="2000">
                    <button type="submit" class="send-btn" id="sendBtn" aria-label="Gửi tin nhắn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>

            <div id="typingIndicator" class="typing-indicator" style="display: none;">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span>Trợ lý đang trả lời...</span>
            </div>
        </div>
    </div>
</div>

<style>
.chat-widget-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 10000;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.chat-toggle {
    width: 62px;
    height: 62px;
    background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    position: relative;
    box-shadow: 0 14px 32px rgba(37, 99, 235, 0.34);
    transition: all 0.3s ease;
}

.chat-toggle:hover {
    transform: translateY(-2px);
}

.chat-toggle-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: white;
    font-size: 24px;
    position: relative;
}

.pulse-ring {
    position: absolute;
    inset: 0;
    border: 3px solid rgba(37, 99, 235, 0.28);
    border-radius: 50%;
    animation: chatPulse 2s infinite;
}

@keyframes chatPulse {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(1.45); opacity: 0; }
}

.chat-box {
    width: 400px;
    height: 620px;
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 28px 64px rgba(15, 23, 42, 0.2);
    position: absolute;
    bottom: 86px;
    right: 0;
    display: none;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(15, 23, 42, 0.08);
}

.chat-box.active {
    display: flex;
}

.chat-header {
    background: linear-gradient(135deg, #172554 0%, #1d4ed8 100%);
    color: white;
    padding: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
}

.chat-header-content {
    display: flex;
    align-items: center;
    min-width: 0;
}

.assistant-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.14);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
}

.assistant-info h3,
.assistant-info p {
    margin: 0;
}

.assistant-info h3 {
    font-size: 15px;
    font-weight: 800;
}

.assistant-info p {
    font-size: 12px;
    opacity: 0.82;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 210px;
}

.chat-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    color: white;
    cursor: pointer;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8fafc;
}

.message {
    display: flex;
    margin-bottom: 18px;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
}

.ai-message .message-avatar {
    background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%);
    color: white;
}

.user-message {
    flex-direction: row-reverse;
}

.user-message .message-avatar {
    margin-left: 12px;
    margin-right: 0;
    background: #2563eb;
    color: white;
}

.message-content {
    flex: 1;
}

.user-message .message-content {
    text-align: right;
}

.message-bubble {
    display: inline-block;
    max-width: 290px;
    background: white;
    padding: 12px 16px;
    border-radius: 18px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
    text-align: left;
}

.user-message .message-bubble {
    background: #2563eb;
    color: white;
}

.message-bubble p {
    margin: 0;
    line-height: 1.5;
}

.message-bubble p + p {
    margin-top: 8px;
}

.message-bubble a {
    color: #1d4ed8;
    font-weight: 800;
    text-decoration: underline;
}

.user-message .message-bubble a {
    color: #ffffff;
}

.message-time {
    margin-top: 6px;
    font-size: 11px;
    color: #94a3b8;
}

.chat-input-container {
    padding: 18px;
    background: white;
    border-top: 1px solid #e2e8f0;
}

.chat-form {
    display: flex;
}

.input-group {
    display: flex;
    width: 100%;
    background: #f8fafc;
    border-radius: 18px;
    padding: 6px;
    border: 1px solid transparent;
}

.input-group:focus-within {
    border-color: #2563eb;
}

#chatInput {
    flex: 1;
    border: none;
    background: transparent;
    padding: 12px 14px;
    outline: none;
}

.send-btn {
    width: 42px;
    height: 42px;
    border: none;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%);
    color: white;
    cursor: pointer;
    flex-shrink: 0;
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.typing-indicator {
    display: flex;
    align-items: center;
    margin-top: 10px;
    font-size: 12px;
    color: #64748b;
}

.typing-dots {
    display: flex;
    margin-right: 8px;
}

.typing-dots span {
    width: 6px;
    height: 6px;
    margin-right: 4px;
    background: #64748b;
    border-radius: 50%;
    animation: chatTyping 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(2) { animation-delay: -0.16s; }
.typing-dots span:nth-child(3) { animation-delay: -0.32s; }

@keyframes chatTyping {
    0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}

@media (max-width: 768px) {
    .chat-widget-container {
        right: 16px;
        bottom: 16px;
    }

    .chat-box {
        width: calc(100vw - 32px);
        height: calc(100vh - 132px);
        right: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatToggle = document.getElementById('chatToggle');
    const chatBox = document.getElementById('chatBox');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');
    const sendBtn = document.getElementById('sendBtn');
    const minimizeChat = document.getElementById('minimizeChat');
    const clearChat = document.getElementById('clearChat');
    const typingIndicator = document.getElementById('typingIndicator');

    if (!chatToggle || !chatBox || !chatForm || !chatMessages) {
        return;
    }

    const welcomeMessage = chatMessages.firstElementChild.outerHTML;
    let historyLoaded = false;

    chatToggle.addEventListener('click', function() {
        chatBox.classList.add('active');
        chatToggle.style.display = 'none';

        if (!historyLoaded) {
            loadChatHistory();
            historyLoaded = true;
        }

        chatInput.focus();
    });

    minimizeChat.addEventListener('click', function() {
        chatBox.classList.remove('active');
        chatToggle.style.display = 'flex';
    });

    clearChat.addEventListener('click', function() {
        if (!confirm('Bạn có chắc muốn xóa lịch sử chat không?')) return;

        fetch('/chat/clear', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                chatMessages.innerHTML = welcomeMessage;
            }
        });
    });

    chatForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;

        addMessage(message, true);
        chatInput.value = '';
        sendBtn.disabled = true;
        chatInput.disabled = true;
        typingIndicator.style.display = 'flex';
        scrollToBottom();

        fetch('/chat/send', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message })
        })
        .then(response => response.json())
        .then(data => {
            addMessage(data.message || 'Xin lỗi, mình chưa trả lời được câu này. Bạn thử hỏi lại rõ hơn nhé.', false);
        })
        .catch(() => {
            addMessage('Không thể kết nối tới chatbot. Nếu bạn dùng service riêng, hãy kiểm tra lại 127.0.0.1:8001 nhé.', false);
        })
        .finally(() => {
            typingIndicator.style.display = 'none';
            sendBtn.disabled = false;
            chatInput.disabled = false;
            chatInput.focus();
        });
    });

    function addMessage(text, isUser) {
        const wrapper = document.createElement('div');
        wrapper.className = `message ${isUser ? 'user-message' : 'ai-message'}`;
        const timeString = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

        wrapper.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-${isUser ? 'user' : 'robot'}"></i>
            </div>
            <div class="message-content">
                <div class="message-bubble">
                    ${formatMessage(text)}
                </div>
                <div class="message-time">${timeString}</div>
            </div>
        `;

        chatMessages.appendChild(wrapper);
        scrollToBottom();
    }

    function formatMessage(text) {
        const escaped = String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        const withLinks = escaped.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
        const withBreaks = withLinks.replace(/\n/g, '<br>');

        return `<p>${withBreaks}</p>`;
    }

    function loadChatHistory() {
        fetch('/chat/history')
            .then(response => response.json())
            .then(data => {
                if (!data.success || !Array.isArray(data.messages) || data.messages.length === 0) return;

                chatMessages.innerHTML = welcomeMessage;
                data.messages.forEach(message => addMessage(message.message, message.is_user));
            });
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});
</script>
@endonce
