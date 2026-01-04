<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

// Set Permissions-Policy header for microphone access (must be before any output)
header('Permissions-Policy: microphone=*, camera=*');

$pageTitle = 'New Session';
require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">
            <i class="bi bi-chat-dots me-2"></i>
            New Session
        </h2>
        <p class="text-muted">Start a new conversation with the AI assistant via Chat, Web Call, or Phone Call</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Call/Web Call Interface -->
        <div id="callInterface" class="card border-0 shadow-sm mb-3" style="display: none;">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-telephone me-2"></i>
                    <span id="callTypeLabel">Call</span>
                </h5>
                <div>
                    <span id="callStatus" class="badge bg-secondary">Connecting...</span>
                    <button class="btn btn-sm btn-danger ms-2" onclick="endCall()" id="endCallBtn">
                        <i class="bi bi-telephone-x"></i> End Call
                    </button>
                </div>
            </div>
            <div class="card-body text-center py-5">
                <div id="callControls">
                    <div class="mb-3">
                        <i class="bi bi-telephone-fill text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <div id="callTimer" class="h4 mb-3">00:00</div>
                    <div id="callerInfo" class="text-muted"></div>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Chat Interface</h5>
            </div>
            <div class="card-body">
                <div id="chatMessages" class="mb-3" style="height: 500px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; background: #f8fafc;">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-chat-left-text fs-1 d-block mb-2"></i>
                        <p>Select a session type and start a conversation</p>
                    </div>
                </div>
                <form id="chatForm" class="d-flex gap-2">
                    <input type="text" class="form-control" id="messageInput" placeholder="Type your message..." autocomplete="off" disabled>
                    <button type="submit" class="btn btn-primary" disabled id="sendBtn">
                        <i class="bi bi-send"></i> Send
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Session Type</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="selectSessionType('chat')" id="btnChat">
                        <i class="bi bi-chat-dots me-2"></i> Chat
                    </button>
                    <button class="btn btn-outline-success" onclick="selectSessionType('web')" id="btnWeb">
                        <i class="bi bi-camera-video me-2"></i> Web Call
                    </button>
                    <button class="btn btn-outline-info" onclick="selectSessionType('phone')" id="btnPhone">
                        <i class="bi bi-telephone me-2"></i> Phone Call
                    </button>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Session Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Language</label>
                    <select class="form-select" id="languageSelect">
                        <option value="english">English</option>
                        <option value="hindi">Hindi</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Caller Name</label>
                    <input type="text" class="form-control" id="callerName" placeholder="Enter caller name">
                </div>
                <div class="mb-3" id="phoneNumberGroup" style="display: none;">
                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="phoneNumber" placeholder="+1234567890">
                </div>
                <button class="btn btn-success w-100" onclick="startSession()" id="startBtn">
                    <i class="bi bi-play-circle me-2"></i> Start Session
                </button>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-outline-primary w-100 mb-2" onclick="clearChat()">
                    <i class="bi bi-arrow-clockwise me-2"></i> Clear Chat
                </button>
                <button class="btn btn-outline-danger w-100" onclick="endSession()" id="endBtn">
                    <i class="bi bi-x-circle me-2"></i> End Session
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentSessionId = null;
let currentChatId = null;
let currentCallId = null;
let isSessionActive = false;
let sessionType = null; // 'chat', 'web', 'phone'
let callTimer = null;
let callStartTime = null;

function selectSessionType(type) {
    sessionType = type;
    
    // Update button states
    document.getElementById('btnChat').classList.remove('active');
    document.getElementById('btnWeb').classList.remove('active');
    document.getElementById('btnPhone').classList.remove('active');
    
    if (type === 'chat') {
        document.getElementById('btnChat').classList.add('active');
        document.getElementById('phoneNumberGroup').style.display = 'none';
    } else if (type === 'web') {
        document.getElementById('btnWeb').classList.add('active');
        document.getElementById('phoneNumberGroup').style.display = 'none';
    } else if (type === 'phone') {
        document.getElementById('btnPhone').classList.add('active');
        document.getElementById('phoneNumberGroup').style.display = 'block';
    }
}

async function startSession() {
    if (!sessionType) {
        showToast('Please select a session type first', 'warning');
        return;
    }
    
    const language = document.getElementById('languageSelect').value;
    const callerName = document.getElementById('callerName').value || 'Anonymous';
    const phoneNumber = document.getElementById('phoneNumber').value;
    
    if (sessionType === 'phone' && !phoneNumber) {
        showToast('Phone number is required for phone calls', 'warning');
        return;
    }
    
    try {
        if (sessionType === 'chat') {
            // Start chat session
            const response = await fetch('api/omnidimension.php?action=create_chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    caller_name: callerName,
                    language: language
                })
            });
            
            const data = await response.json();
            if (data.success) {
                currentSessionId = data.session_id;
                currentChatId = data.chat_id;
                isSessionActive = true;
                document.getElementById('messageInput').disabled = false;
                document.getElementById('sendBtn').disabled = false;
                addMessage('assistant', 'Hello! I am your AI assistant. How can I help you today?');
                showToast('Chat session started successfully', 'success');
            } else {
                showToast('Failed to start chat session', 'danger');
            }
        } else if (sessionType === 'web' || sessionType === 'phone') {
            // Start call (web or phone)
            const callType = sessionType === 'web' ? 'inbound' : 'outbound';
            const response = await fetch('api/omnidimension.php?action=create_call', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    call_type: callType,
                    phone_number: phoneNumber,
                    caller_name: callerName,
                    language: language
                })
            });
            
            const data = await response.json();
            if (data.success) {
                currentSessionId = data.session_id;
                currentCallId = data.call_id;
                isSessionActive = true;
                
                // Show call interface
                document.getElementById('callInterface').style.display = 'block';
                document.getElementById('callTypeLabel').textContent = sessionType === 'web' ? 'Web Call' : 'Phone Call';
                document.getElementById('callerInfo').textContent = `Calling: ${callerName}`;
                document.getElementById('callStatus').textContent = 'Connecting...';
                document.getElementById('callStatus').className = 'badge bg-warning';
                
                // Start call timer
                callStartTime = Date.now();
                startCallTimer();
                
                // Poll for call status
                pollCallStatus();
                
                showToast('Call initiated successfully', 'success');
            } else {
                showToast('Failed to start call', 'danger');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error starting session', 'danger');
    }
}

async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) return;
    if (!isSessionActive || !currentChatId) {
        showToast('Please start a chat session first', 'warning');
        return;
    }
    
    addMessage('user', message);
    messageInput.value = '';
    
    try {
        // Send to Omni Dimension
        const response = await fetch(`api/omnidimension.php?action=send_chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                chat_id: currentChatId,
                session_id: currentSessionId,
                message: message
            })
        });
        
        const data = await response.json();
        if (data.success && data.response) {
            const responseText = typeof data.response === 'string' ? data.response : JSON.stringify(data.response);
            addMessage('assistant', responseText);
        } else {
            addMessage('assistant', 'Sorry, I encountered an error. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        addMessage('assistant', 'Sorry, I encountered an error. Please try again.');
    }
}

async function endCall() {
    if (!currentCallId) return;
    
    try {
        const response = await fetch('api/omnidimension.php?action=end_call', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                call_id: currentCallId,
                session_id: currentSessionId
            })
        });
        
        const data = await response.json();
        if (data.success) {
            stopCallTimer();
            document.getElementById('callInterface').style.display = 'none';
            document.getElementById('callStatus').textContent = 'Ended';
            document.getElementById('callStatus').className = 'badge bg-danger';
            showToast('Call ended', 'info');
            currentCallId = null;
            isSessionActive = false;
        }
    } catch (error) {
        console.error('Error ending call:', error);
    }
}

function startCallTimer() {
    callTimer = setInterval(() => {
        if (callStartTime) {
            const elapsed = Math.floor((Date.now() - callStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            document.getElementById('callTimer').textContent = 
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }
    }, 1000);
}

function stopCallTimer() {
    if (callTimer) {
        clearInterval(callTimer);
        callTimer = null;
    }
    callStartTime = null;
}

async function pollCallStatus() {
    if (!currentCallId) return;
    
    const statusInterval = setInterval(async () => {
        if (!currentCallId) {
            clearInterval(statusInterval);
            return;
        }
        
        try {
            const response = await fetch(`api/omnidimension.php?action=call_status&call_id=${currentCallId}`);
            const data = await response.json();
            
            if (data.success) {
                const status = data.status;
                const statusBadge = document.getElementById('callStatus');
                
                if (status === 'active' || status === 'connected') {
                    statusBadge.textContent = 'Active';
                    statusBadge.className = 'badge bg-success';
                } else if (status === 'ended' || status === 'disconnected') {
                    statusBadge.textContent = 'Ended';
                    statusBadge.className = 'badge bg-danger';
                    stopCallTimer();
                    clearInterval(statusInterval);
                    setTimeout(() => {
                        document.getElementById('callInterface').style.display = 'none';
                        currentCallId = null;
                        isSessionActive = false;
                    }, 2000);
                }
            }
        } catch (error) {
            console.error('Error polling call status:', error);
        }
    }, 3000); // Poll every 3 seconds
}

function addMessage(role, content) {
    const chatMessages = document.getElementById('chatMessages');
    
    // Remove empty state if exists
    const emptyState = chatMessages.querySelector('.text-center');
    if (emptyState) {
        emptyState.remove();
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `mb-3 d-flex ${role === 'user' ? 'justify-content-end' : 'justify-content-start'}`;
    
    const messageContent = document.createElement('div');
    messageContent.className = `p-3 rounded ${role === 'user' ? 'bg-primary text-white' : 'bg-white border'}`;
    messageContent.style.maxWidth = '70%';
    messageContent.textContent = content;
    
    messageDiv.appendChild(messageContent);
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function clearChat() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.innerHTML = `
        <div class="text-center text-muted py-4">
            <i class="bi bi-chat-left-text fs-1 d-block mb-2"></i>
            <p>Start a conversation by typing a message below</p>
        </div>
    `;
}

async function endSession() {
    if (!currentSessionId) return;
    
    // End call if active
    if (currentCallId) {
        await endCall();
    }
    
    // End chat if active
    if (currentChatId) {
        try {
            await fetch('api/omnidimension.php?action=end_chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    chat_id: currentChatId,
                    session_id: currentSessionId
                })
            });
        } catch (error) {
            console.error('Error ending chat:', error);
        }
    }
    
    // Update session status
    try {
        await fetch(`api/session.php?id=${currentSessionId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: 'completed'
            })
        });
    } catch (error) {
        console.error('Error:', error);
    }
    
    isSessionActive = false;
    currentSessionId = null;
    currentChatId = null;
    currentCallId = null;
    sessionType = null;
    document.getElementById('messageInput').disabled = true;
    document.getElementById('sendBtn').disabled = true;
    document.getElementById('callInterface').style.display = 'none';
    stopCallTimer();
    showToast('Session ended', 'info');
    clearChat();
}

document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendMessage();
});

document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

