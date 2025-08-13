@extends('layouts.vertical', ['title' => 'Chat Management'])

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    /* Same CSS as before */
    .chat-container {
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
    }
    .conversation-list {
        height: 100%;
        border-right: 1px solid #e9ecef;
        background-color: #fff;
        display: flex;
        flex-direction: column;
    }

    #conversations-list {
        flex: 1;
        overflow-y: auto;
        max-height: calc(100vh - 280px);
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 #f1f1f1;
    }

    #conversations-list::-webkit-scrollbar {
        width: 6px;
    }

    #conversations-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    #conversations-list::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    #conversations-list::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    .conversation-item {
        padding: 15px;
        border-bottom: 1px solid #f8f9fa;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .conversation-item:hover {
        background-color: #f8f9fa;
    }
    .conversation-item.active {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
    }
    .conversation-item.unread {
        background-color: #fff3cd;
    }
    .chat-messages {
        height: 100%;
        display: flex;
        flex-direction: column;
        background-color: #fff;
    }
    .messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        max-height: calc(100vh - 300px);
        display: flex;
        flex-direction: column;
    }
    .message {
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
        cursor: pointer;
    }
    .message.sent {
        align-items: flex-end;
    }
    .message.received {
        align-items: flex-start;
    }
    .message-content {
        max-width: 70%;
        padding: 10px 15px;
        border-radius: 18px;
        word-wrap: break-word;
    }
    .message.sent .message-content {
        background-color: #2196f3;
        color: white;
    }
    .message.received .message-content {
        background-color: #f1f3f4;
        color: #333;
    }
    .message-time {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
        display: none;
    }
    .message.sent .message-time {
        text-align: right;
    }
    .message.received .message-time {
        text-align: left;
    }
    .message-time.visible {
        display: block;
    }
    .message-input {
        border-top: 1px solid #e9ecef;
        padding: 15px;
        background-color: #fff;
    }
    .status-badge {
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 12px;
    }
    .status-open {
        background-color: #d4edda;
        color: #155724;
    }
    .status-assigned {
        background-color: #cce5ff;
        color: #004085;
    }
    .status-closed {
        background-color: #f8d7da;
        color: #721c24;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #2196f3;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 10px;
    }
    .conversation-info {
        flex: 1;
    }
    .conversation-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .no-conversation {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #6c757d;
        font-size: 18px;
    }
</style>
@endsection

@section('content')
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Chat Management</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">Chat Management</a></li>
            <li class="breadcrumb-item active">Conversations</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Live Chat Support</h5>
            </div>
            <div class="card-body p-0">
                <div class="chat-container">
                    <div class="row h-100 m-0">
                        <!-- Conversation List -->
                        <div class="col-md-4 p-0">
                            <div class="conversation-list">
                                <div class="p-3 border-bottom">
                                    <h6 class="mb-0">Conversations</h6>
                                </div>
                                <div id="conversations-list">
                                    @if($conversations->count() > 0)
                                    @foreach($conversations as $conversation)
                                    <div class="conversation-item {{ $conversation->unreadMessages()->count() > 0 ? 'unread' : '' }}" data-conversation-id="{{ $conversation->id }}">
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar">
                                                {{ strtoupper(substr($conversation->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="conversation-info">
                                                <div class="conversation-header">
                                                    <strong>{{ $conversation->user->name ?? 'Unknown User' }}</strong>
                                                    <span class="status-badge status-{{ $conversation->status }}">
                                                        {{ ucfirst($conversation->status) }}
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    {{ $conversation->messages->last() ? $conversation->messages->last()->content : 'No messages yet' }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $conversation->updated_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="p-3 text-center text-muted">
                                        No conversations yet
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Chat Area -->
                        <div class="col-md-8 p-0">
                            <div class="chat-messages">
                                <div id="chat-header" class="p-3 border-bottom" style="display: none;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2">
                                                <span id="selected-user-avatar">U</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0" id="selected-user-name">User Name</h6>
                                                <small class="text-muted" id="selected-conversation-status">Status</small>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-danger" id="close-conversation-btn" style="display: none;">
                                                Close Conversation
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="messages-container" class="messages-container">
                                    <div class="no-conversation">
                                        Select a conversation to start chatting
                                    </div>
                                </div>

                                <div id="message-input-container" class="message-input">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="message-input" placeholder="Type your message...">
                                        <button class="btn btn-primary" id="send-message-btn">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@vite(['resources/js/echo.js'])
<script>
    dayjs.extend(dayjs_plugin_relativeTime);

    const auth = {
        userId: {{ Auth::id() ?? 'null' }},
        isAdmin: {{ Auth::check() && Auth::user()->hasRole([App\Enums\Role::MANAGER, App\Enums\Role::SUPER_ADMIN]) ? 'true' : 'false' }}
    };

    let currentConversationId = null;
    let currentChannel = null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    Pusher.logToConsole = true;

    document.addEventListener('DOMContentLoaded', () => {
        console.log('Initializing Laravel Echo...');
        if (!window.Echo) {
            console.error('Echo not initialized! Retrying in 1s...');
            setTimeout(initializeChat, 1000);
            return;
        }

        console.log('Echo initialized with config:', {
            broadcaster: window.Echo.options.broadcaster,
            key: window.Echo.options.key,
            cluster: window.Echo.options.cluster,
            encrypted: window.Echo.options.encrypted
        });

        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('Pusher connection error:', err);
            Swal.fire('Error', 'Failed to connect to real-time service. Please refresh the page.', 'error');
        });

        window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('Pusher connected, socket_id:', window.Echo.socketId());
        });

        initializeChat();
    });

    function initializeChat() {
        if (auth.isAdmin) {
            window.Echo.channel('support')
                .listen('.new-message', (data) => {
                    console.log('Received new-message on public support channel:', data);
                    updateConversationList();
                    if (data.message && data.message.conversation_id == currentConversationId) {
                        console.log('Appending message from support channel:', data.message);
                        appendMessage(data.message);
                        markAsRead(currentConversationId);
                    }
                })
                .error((error) => {
                    console.error('Support channel subscription error:', error);
                    Swal.fire('Error', 'Failed to subscribe to support channel: ' + error.message, 'error');
                });
        }

        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', function() {
                const conversationId = this.dataset.conversationId;
                selectConversation(conversationId);
            });
        });

        document.getElementById('messages-container').addEventListener('click', function(e) {
            const message = e.target.closest('.message');
            if (message) {
                const timeDiv = message.querySelector('.message-time');
                if (timeDiv) {
                    document.querySelectorAll('.message-time').forEach(t => t.classList.remove('visible'));
                    timeDiv.classList.toggle('visible');
                }
            }
        });
    }

    function selectConversation(conversationId) {
        if (currentChannel && window.Echo) {
            window.Echo.leave(currentChannel);
            console.log('Unsubscribed from channel:', currentChannel);
        }

        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        const activeItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
        if (activeItem) {
            activeItem.classList.add('active');
            activeItem.classList.remove('unread');
        }

        currentConversationId = conversationId;

        loadConversationDetails(conversationId);
        loadMessages(conversationId);

        document.getElementById('chat-header').style.display = 'block';
        document.getElementById('message-input-container').style.display = 'flex';
        document.getElementById('messages-container').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

        if (window.Echo) {
            fetch(`/chat-management/${conversationId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Conversation details for channel:', data);
                    const channelName = data.responder_id ? `conversation.${conversationId}` : 'support';
                    currentChannel = channelName;
                    console.log('Subscribing to channel:', channelName);

                    function subscribeWithRetry(attempts = 3, delay = 1000) {
                        const socketId = window.Echo.socketId();
                        if (!socketId) {
                            console.error('Socket ID not available, retrying...');
                            if (attempts > 1) {
                                setTimeout(() => subscribeWithRetry(attempts - 1, delay), delay);
                                return;
                            }
                            Swal.fire('Error', 'Real-time connection not established. Please refresh the page.', 'error');
                            return;
                        }

                        // Using public channels - no authentication needed
                        console.log('Subscribing to public channel:', channelName);
                        window.Echo.channel(channelName)
                                    .listen('.new-message', (data) => {
                                        console.log('Received new-message event on conversation channel:', data);
                                        const message = data.message; // Extract the message object
                                        if (!message) {
                                            console.error('No message object in event data:', data);
                                            return;
                                        }
                                        if (message.conversation_id == currentConversationId) {
                                            console.log('Appending message from conversation channel:', message);
                                            appendMessage(message);
                                            markAsRead(currentConversationId);
                                        } else {
                                            console.log('Message ignored, wrong conversation ID:', message.conversation_id, 'Expected:', currentConversationId);
                                        }
                                        updateConversationList();
                                    })
                                    .listen('.conversation-claimed', (data) => {
                                        console.log('Received conversation-claimed event:', data);
                                        if (data.conversation.id == currentConversationId) {
                                            loadConversationDetails(currentConversationId);
                                        }
                                        updateConversationList();
                                    })
                                    .error((error) => {
                                        console.error('Channel subscription error:', error);
                                        if (attempts > 1) {
                                            console.log('Retrying subscription...');
                                            setTimeout(() => subscribeWithRetry(attempts - 1, delay), delay);
                                        } else {
                                            Swal.fire('Error', 'Failed to subscribe to real-time updates: ' + error.message, 'error');
                                        }
                                    })
                                    .subscribed(() => {
                                        console.log('Successfully subscribed to public channel:', channelName);
                                    });
                    }

                    subscribeWithRetry();
                })
                .catch(error => {
                    console.error('Error fetching conversation details for channel:', error);
                    Swal.fire('Error', 'Failed to load conversation details: ' + error.message, 'error');
                });
        } else {
            console.error('Cannot subscribe to channel: Echo is not initialized');
            Swal.fire('Error', 'Real-time messaging is unavailable. Please refresh the page.', 'error');
        }
    }

    function loadConversationDetails(conversationId) {
        fetch(`/chat-management/${conversationId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(`HTTP error! Status: ${response.status} ${response.statusText} - ${err.message || 'Unknown error'}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Conversation details loaded:', data);
                const userName = data.user?.name || 'Unknown User';
                const userInitial = userName.charAt(0).toUpperCase();
                document.getElementById('selected-user-name').textContent = userName;
                document.getElementById('selected-user-avatar').textContent = userInitial;
                document.getElementById('selected-conversation-status').textContent = data.status || 'Unknown';

                const closeBtn = document.getElementById('close-conversation-btn');
                if (auth.isAdmin && data.status === 'assigned' && data.responder_id === auth.userId) {
                    closeBtn.style.display = 'block';
                } else {
                    closeBtn.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading conversation details:', error);
                document.getElementById('selected-user-name').textContent = 'Unknown User';
                document.getElementById('selected-user-avatar').textContent = 'U';
                Swal.fire('Error', `Failed to load conversation details: ${error.message}`, 'error');
            });
    }

    function loadMessages(conversationId) {
        fetch(`/chat-management/${conversationId}/messages`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(`HTTP error! Status: ${response.status} ${response.statusText} - ${err.message || 'Unknown error'}`);
                    });
                }
                return response.json();
            })
            .then(messages => {
                console.log('Messages loaded:', messages);
                displayMessages(messages);
                markAsRead(conversationId);
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                document.getElementById('messages-container').innerHTML = '<div class="text-center text-danger">Failed to load messages</div>';
                Swal.fire('Error', `Failed to load messages: ${error.message}`, 'error');
            });
    }

    function displayMessages(messages) {
        const container = document.getElementById('messages-container');
        container.innerHTML = '';

        messages.forEach(message => {
            appendMessage(message);
        });

        container.scrollTop = container.scrollHeight;
    }

    function appendMessage(message) {
        console.log('Appending message:', message);
        const container = document.getElementById('messages-container');
        const existingMessage = container.querySelector(`[data-message-id="${message.id}"]`);
        if (existingMessage) {
            console.log('Message already exists, skipping:', message.id);
            return;
        }

        if (!message.id || !message.content || !message.created_at) {
            console.error('Invalid message structure, skipping:', message);
            return;
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.sender_id === auth.userId ? 'sent' : 'received'}`;
        messageDiv.dataset.messageId = message.id;

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = message.content;

        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.textContent = message.created_at ? dayjs(message.created_at).fromNow() : 'Unknown time';

        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(timeDiv);
        container.appendChild(messageDiv);

        container.scrollTop = container.scrollHeight;
    }

    function markAsRead(conversationId) {
        fetch(`/chat-management/${conversationId}/mark-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                console.log('Messages marked as read for conversation:', conversationId);
            })
            .catch(error => console.error('Error marking messages as read:', error));
    }

    function sendMessage() {
        const input = document.getElementById('message-input');
        const content = input.value.trim();

        if (!content || !currentConversationId) {
            Swal.fire('Warning', 'Please select a conversation and enter a message', 'warning');
            return;
        }

        console.log('Sending message:', { conversationId: currentConversationId, content });

        fetch(`/chat-management/${currentConversationId}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                content: content
            })
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(`HTTP error! Status: ${response.status} ${response.statusText} - ${err.message || 'Unknown error'}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Message sent successfully:', data);
                if (data.message) {
                    input.value = '';
                    appendMessage(data.message);
                    markAsRead(currentConversationId);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                Swal.fire('Error', `Failed to send message: ${error.message}`, 'error');
            });
    }

    document.getElementById('send-message-btn').addEventListener('click', sendMessage);

    document.getElementById('message-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    document.getElementById('close-conversation-btn').addEventListener('click', function() {
        if (!currentConversationId) {
            Swal.fire('Warning', 'No conversation selected', 'warning');
            return;
        }

        Swal.fire({
            title: 'Close Conversation?',
            text: 'Are you sure you want to close this conversation?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, close it!'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Closing conversation:', currentConversationId);
                fetch(`/chat-management/${currentConversationId}/close`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw new Error(`HTTP error! Status: ${response.status} ${response.statusText} - ${err.message || 'Unknown error'}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Conversation closed:', data);
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error closing conversation:', error);
                        Swal.fire('Error', `Failed to close conversation: ${error.message}`, 'error');
                    });
            }
        });
    });

    function updateConversationList() {
        fetch('/chat-management/unassigned/list', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(`HTTP error! Status: ${response.status} ${response.statusText} - ${err.message || 'Unknown error'}`);
                    });
                }
                return response.json();
            })
            .then(conversations => {
                console.log('Conversation list updated:', conversations);
                const list = document.getElementById('conversations-list');
                list.innerHTML = conversations.length > 0 ? '' : '<div class="p-3 text-center text-muted">No conversations yet</div>';

                conversations.forEach(conversation => {
                    const isActive = conversation.id == currentConversationId ? 'active' : '';
                    const isUnread = conversation.unread_messages_count > 0 ? 'unread' : '';
                    const lastMessage = conversation.messages && conversation.messages.length > 0 ? conversation.messages[conversation.messages.length - 1].content : 'No messages yet';

                    const item = `
                        <div class="conversation-item ${isActive} ${isUnread}" data-conversation-id="${conversation.id}">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar">
                                    ${conversation.user?.name?.charAt(0).toUpperCase() || 'U'}
                                </div>
                                <div class="conversation-info">
                                    <div class="conversation-header">
                                        <strong>${conversation.user?.name || 'Unknown User'}</strong>
                                        <span class="status-badge status-${conversation.status}">
                                            ${conversation.status.charAt(0).toUpperCase() + conversation.status.slice(1)}
                                        </span>
                                    </div>
                                    <small class="text-muted">${lastMessage}</small>
                                    <br>
                                    <small class="text-muted">${dayjs(conversation.updated_at).fromNow()}</small>
                                </div>
                            </div>
                        </div>
                    `;
                    list.insertAdjacentHTML('beforeend', item);
                });

                document.querySelectorAll('.conversation-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const conversationId = this.dataset.conversationId;
                        selectConversation(conversationId);
                    });
                });
            })
            .catch(error => {
                console.error('Error updating conversation list:', error);
                Swal.fire('Error', `Failed to update conversation list: ${error.message}`, 'error');
            });
    }
</script>
@endsection
