@extends('layouts.app')
@section('title', 'Chat - Private Chat')

@section('styles')
<style>
    *, *::before, *::after { box-sizing: border-box; }

    .chat-app {
        display: flex;
        height: 100vh;
        height: 100dvh;
        overflow: hidden;
    }

    /* ===== SIDEBAR ===== */
    .sidebar {
        width: 320px;
        min-width: 320px;
        background: var(--bg-secondary);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        z-index: 100;
    }
    .sidebar-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .sidebar-header .logo {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .sidebar-header .logo span {
        background: var(--accent-light);
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .user-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: var(--bg-tertiary);
        border-radius: 20px;
        font-size: 13px;
        color: var(--text-secondary);
    }
    .user-badge .dot {
        width: 8px; height: 8px;
        background: var(--green);
        border-radius: 50%;
    }
    .sidebar-actions {
        padding: 12px 16px;
        display: flex;
        gap: 8px;
    }
    .btn-action {
        flex: 1;
        padding: 10px;
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--text-primary);
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }
    .btn-action:hover { background: var(--bg-hover); border-color: var(--border-light); }
    .btn-action.accent { background: var(--accent-light); border-color: rgba(108,92,231,0.3); color: var(--accent-hover); }
    .btn-action.accent:hover { background: rgba(108,92,231,0.25); }

    .room-list { flex: 1; overflow-y: auto; padding: 8px 12px; }
    .room-section-title {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 1px; color: var(--text-muted); padding: 12px 8px 8px;
    }
    .room-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 14px; border-radius: var(--radius-sm);
        cursor: pointer; transition: all 0.15s; margin-bottom: 2px;
    }
    .room-item:hover { background: var(--bg-hover); }
    .room-item.active { background: var(--accent-light); border: 1px solid rgba(108,92,231,0.2); }
    .room-icon {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .room-icon.public { background: var(--accent-light); }
    .room-icon.private { background: var(--green-light); }
    .room-info { flex: 1; min-width: 0; }
    .room-name { font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .room-preview { font-size: 12px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }

    .sidebar-footer {
        padding: 14px 16px; border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .sidebar-footer .user-info { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .avatar {
        width: 36px; height: 36px; border-radius: 10px;
        background: linear-gradient(135deg, var(--accent), var(--green));
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 14px; color: white; flex-shrink: 0;
    }
    .btn-logout {
        padding: 8px 14px; background: var(--bg-tertiary); border: 1px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-secondary);
        font-family: 'DM Sans', sans-serif; font-size: 12px; cursor: pointer; flex-shrink: 0;
    }
    .btn-logout:hover { color: var(--red); border-color: var(--red); }

    /* ===== MAIN CHAT ===== */
    .chat-main { flex: 1; display: flex; flex-direction: column; background: var(--bg-primary); min-width: 0; }

    .chat-header {
        padding: 12px 16px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        background: var(--bg-secondary); gap: 10px; flex-shrink: 0;
    }
    .chat-header-left { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }
    .btn-sidebar-toggle {
        display: none; width: 38px; height: 38px; background: var(--bg-tertiary);
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        color: var(--text-primary); align-items: center; justify-content: center;
        cursor: pointer; font-size: 20px; flex-shrink: 0;
    }
    .chat-header-info { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
    .chat-header-info .room-icon { width: 36px; height: 36px; flex-shrink: 0; }
    .chat-header-info h2 { font-size: 15px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .chat-header-info .status { font-size: 12px; color: var(--text-secondary); }
    .chat-header-actions { display: flex; gap: 6px; flex-shrink: 0; }
    .btn-icon {
        width: 38px; height: 38px; background: var(--bg-tertiary); border: 1px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-secondary);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 17px; transition: all 0.2s; flex-shrink: 0;
    }
    .btn-icon:hover { background: var(--bg-hover); color: var(--text-primary); }
    .btn-icon.call:hover { background: var(--green-light); color: var(--green); }
    .btn-icon.video:hover { background: var(--accent-light); color: var(--accent); }
    .online-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; background: var(--green-light); border: 1px solid rgba(0,210,160,0.2);
        border-radius: 12px; font-size: 10px; color: var(--green); font-weight: 600; white-space: nowrap;
    }

    /* Messages */
    .messages-area { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 6px; }
    .message-group { display: flex; gap: 10px; max-width: 80%; animation: fadeIn 0.2s ease; }
    .message-group.self { align-self: flex-end; flex-direction: row-reverse; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .msg-avatar {
        width: 34px; height: 34px; border-radius: 10px; background: var(--bg-tertiary);
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 13px; color: var(--accent); flex-shrink: 0; margin-top: 2px;
    }
    .msg-content { flex: 1; min-width: 0; }
    .msg-header { display: flex; align-items: center; gap: 8px; margin-bottom: 3px; }
    .msg-username { font-weight: 600; font-size: 12px; color: var(--accent); }
    .self .msg-username { color: var(--green); }
    .msg-time { font-size: 10px; color: var(--text-muted); }
    .msg-bubble {
        background: var(--bg-secondary); border: 1px solid var(--border);
        border-radius: 14px; border-top-left-radius: 4px;
        padding: 10px 14px; font-size: 14px; line-height: 1.5;
        word-wrap: break-word; overflow-wrap: break-word;
    }
    .self .msg-bubble {
        background: var(--accent); border-color: var(--accent); color: white;
        border-radius: 14px; border-top-right-radius: 4px;
    }
    .msg-bubble.file-msg { display: flex; align-items: center; gap: 10px; padding: 12px 16px; }
    .file-icon { font-size: 24px; flex-shrink: 0; }
    .file-info .file-name { font-weight: 600; font-size: 13px; }
    .file-info .file-size { font-size: 11px; color: var(--text-secondary); margin-top: 2px; }
    .self .file-info .file-size { color: rgba(255,255,255,0.7); }
    .file-download { color: var(--accent); text-decoration: none; font-size: 12px; font-weight: 600; }
    .self .file-download { color: rgba(255,255,255,0.9); }
    .msg-image { max-width: 100%; max-height: 300px; border-radius: 10px; cursor: pointer; }
    .system-message { text-align: center; padding: 8px 0; }
    .system-message span {
        font-size: 11px; color: var(--text-muted); background: var(--bg-secondary);
        padding: 4px 12px; border-radius: 12px; border: 1px solid var(--border);
    }
    .typing-indicator { padding: 6px 16px; font-size: 12px; color: var(--text-secondary); min-height: 24px; font-style: italic; flex-shrink: 0; }

    /* Input */
    .input-area { padding: 12px 16px; border-top: 1px solid var(--border); background: var(--bg-secondary); flex-shrink: 0; }
    .input-container {
        display: flex; align-items: flex-end; gap: 8px; background: var(--bg-primary);
        border: 1px solid var(--border); border-radius: 14px; padding: 6px 10px;
    }
    .input-container:focus-within { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-light); }
    .input-container .attach-btn {
        background: none; border: none; color: var(--text-secondary);
        cursor: pointer; font-size: 20px; padding: 6px; border-radius: 8px; flex-shrink: 0;
    }
    .input-container .attach-btn:hover { background: var(--bg-hover); color: var(--text-primary); }
    .input-container textarea {
        flex: 1; background: transparent; border: none; color: var(--text-primary);
        font-family: 'DM Sans', sans-serif; font-size: 14px; resize: none;
        outline: none; max-height: 100px; line-height: 1.5; padding: 6px 0; min-width: 0;
    }
    .input-container textarea::placeholder { color: var(--text-muted); }
    .send-btn {
        background: var(--accent); border: none; color: white; width: 36px; height: 36px;
        border-radius: 10px; cursor: pointer; font-size: 16px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .send-btn:hover { background: var(--accent-hover); }
    .send-btn:disabled { opacity: 0.4; cursor: not-allowed; }

    /* Members Panel */
    .members-panel {
        width: 240px; min-width: 240px; background: var(--bg-secondary);
        border-left: 1px solid var(--border); display: none; flex-direction: column;
    }
    .members-panel.open { display: flex; }
    .members-panel-header { padding: 16px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .members-panel-header h3 { font-size: 14px; font-weight: 700; }
    .members-list { flex: 1; overflow-y: auto; padding: 8px; }
    .member-item { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: var(--radius-sm); }
    .member-item:hover { background: var(--bg-hover); }
    .member-avatar { position: relative; width: 34px; height: 34px; flex-shrink: 0; }
    .member-avatar .av {
        width: 34px; height: 34px; border-radius: 10px; background: var(--bg-tertiary);
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: var(--accent);
    }
    .member-status { width: 10px; height: 10px; border-radius: 50%; border: 2px solid var(--bg-secondary); position: absolute; right: -2px; bottom: -2px; }
    .member-status.online { background: var(--green); }
    .member-status.offline { background: var(--text-muted); }
    .member-name { font-size: 13px; font-weight: 600; }
    .member-role { font-size: 11px; color: var(--text-muted); }

    /* Empty State */
    .empty-state { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-secondary); padding: 20px; text-align: center; }
    .empty-state .icon { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
    .empty-state h3 { font-size: 18px; margin-bottom: 6px; }
    .empty-state p { font-size: 14px; color: var(--text-muted); }

    /* Modal */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
    .modal-overlay.open { display: flex; }
    .modal { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 16px; padding: 24px; width: 100%; max-width: 420px; max-height: 80vh; overflow-y: auto; }
    .modal h2 { font-size: 18px; margin-bottom: 16px; }
    .modal .form-group { margin-bottom: 14px; }
    .modal .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .modal .form-group input, .modal .form-group textarea, .modal .form-group select {
        width: 100%; padding: 10px 12px; background: var(--bg-primary); border: 1px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 14px;
    }
    .modal .form-group input:focus, .modal .form-group textarea:focus { outline: none; border-color: var(--accent); }
    .modal-actions { display: flex; gap: 8px; margin-top: 20px; justify-content: flex-end; }
    .btn-cancel { padding: 10px 16px; background: var(--bg-tertiary); border: 1px solid var(--border); border-radius: var(--radius-sm); color: var(--text-secondary); font-family: 'DM Sans', sans-serif; cursor: pointer; }
    .btn-confirm { padding: 10px 20px; background: var(--accent); border: none; border-radius: var(--radius-sm); color: white; font-family: 'DM Sans', sans-serif; font-weight: 600; cursor: pointer; }
    .btn-confirm:hover { background: var(--accent-hover); }
    .member-checkbox-list { max-height: 160px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 6px; }
    .member-checkbox-item { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 6px; cursor: pointer; font-size: 13px; }
    .member-checkbox-item:hover { background: var(--bg-hover); }
    .member-checkbox-item input { accent-color: var(--accent); }

    /* Call UI */
    .call-overlay { display: none; position: fixed; inset: 0; background: rgba(10,10,15,0.95); z-index: 2000; flex-direction: column; align-items: center; justify-content: center; padding: 20px; }
    .call-overlay.open { display: flex; }
    .call-info { text-align: center; margin-bottom: 24px; }
    .call-info .call-avatar { width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--green)); display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 14px; color: white; font-weight: 700; }
    .call-info h2 { font-size: 20px; margin-bottom: 4px; }
    .call-info p { color: var(--text-secondary); font-size: 14px; }
    .call-timer { font-family: 'JetBrains Mono', monospace; font-size: 16px; color: var(--green); margin-top: 8px; }
    .call-actions { display: flex; gap: 14px; }
    .call-btn { width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer; font-size: 22px; display: flex; align-items: center; justify-content: center; }
    .call-btn.end { background: var(--red); color: white; }
    .call-btn.mute { background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border); }
    .call-btn.mute.active { background: var(--red-light); color: var(--red); }
    .video-container { display: none; width: 100%; max-width: 800px; aspect-ratio: 16/9; position: relative; border-radius: var(--radius); overflow: hidden; margin-bottom: 20px; }
    .video-container.open { display: block; }
    .video-container video { width: 100%; height: 100%; object-fit: cover; background: #000; }
    .video-container .local-video { position: absolute; bottom: 12px; right: 12px; width: 140px; height: 105px; border-radius: var(--radius-sm); border: 2px solid var(--border); z-index: 1; }
    .incoming-call { display: none; position: fixed; top: 16px; right: 16px; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 14px; padding: 16px 20px; z-index: 3000; box-shadow: 0 8px 32px rgba(0,0,0,0.5); animation: slideIn 0.3s ease; min-width: 260px; max-width: 340px; }
    .incoming-call.open { display: block; }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    .incoming-call h4 { font-size: 14px; margin-bottom: 4px; }
    .incoming-call p { font-size: 12px; color: var(--text-secondary); margin-bottom: 12px; }
    .incoming-call .actions { display: flex; gap: 8px; }
    .incoming-call .btn-acc, .incoming-call .btn-rej { flex: 1; padding: 10px; border: none; border-radius: var(--radius-sm); color: white; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; font-size: 13px; }
    .incoming-call .btn-acc { background: var(--green); }
    .incoming-call .btn-rej { background: var(--red); }
    .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; }
    .sidebar-overlay.open { display: block; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .sidebar { position: fixed; left: -320px; top: 0; bottom: 0; transition: left 0.3s ease; box-shadow: 4px 0 24px rgba(0,0,0,0.3); }
        .sidebar.open { left: 0; }
        .btn-sidebar-toggle { display: flex; }
        .members-panel { display: none !important; }
        .chat-header-actions .btn-icon.members-btn { display: none; }
        .message-group { max-width: 90%; }
        .msg-avatar { width: 30px; height: 30px; font-size: 11px; }
        .video-container .local-video { width: 100px; height: 75px; }
        .incoming-call { left: 16px; right: 16px; min-width: auto; }
    }
    @media (max-width: 480px) {
        .sidebar { width: 280px; min-width: 280px; left: -280px; }
        .chat-header { padding: 10px 12px; }
        .chat-header-info .room-icon { display: none; }
        .messages-area { padding: 12px; }
        .input-area { padding: 10px 12px; }
        .online-badge { display: none; }
    }
</style>
@endsection

@section('content')
<div class="chat-app" id="app">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo"><span>🔒</span> Private Chat</div>
            <div class="user-badge"><div class="dot"></div> {{ $user->display_name }}</div>
        </div>
        <div class="sidebar-actions">
            <button class="btn-action accent" onclick="openCreateRoomModal()">+ Room Baru</button>
            <button class="btn-action" onclick="openJoinRoomModal()">Gabung</button>
        </div>
        <div class="room-list" id="roomList">
            <div class="room-section-title">Room Saya</div>
            @foreach($rooms as $room)
            <div class="room-item" data-room-id="{{ $room->id }}" data-room-name="{{ $room->name }}" onclick="selectRoom({{ $room->id }}, '{{ addslashes($room->name) }}')">
                <div class="room-icon {{ $room->is_private ? 'private' : 'public' }}">{{ $room->is_private ? '🔐' : '#' }}</div>
                <div class="room-info">
                    <div class="room-name">{{ $room->name }}</div>
                    <div class="room-preview">{{ $room->members->count() }} anggota</div>
                </div>
            </div>
            @endforeach
            @if($publicRooms->count())
            <div class="room-section-title">Room Publik</div>
            @foreach($publicRooms as $pubRoom)
                @if(!$rooms->contains('id', $pubRoom->id))
                <div class="room-item" onclick="joinAndSelectRoom({{ $pubRoom->id }}, '{{ addslashes($pubRoom->name) }}')">
                    <div class="room-icon public">#</div>
                    <div class="room-info">
                        <div class="room-name">{{ $pubRoom->name }}</div>
                        <div class="room-preview">Klik untuk bergabung</div>
                    </div>
                </div>
                @endif
            @endforeach
            @endif
        </div>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar">{{ strtoupper(substr($user->display_name, 0, 1)) }}</div>
                <div style="min-width:0;">
                    <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user->display_name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">@{{ $user->username }}</div>
                </div>
            </div>
            <form method="POST" action="/logout" style="display:inline;">@csrf<button type="submit" class="btn-logout">Keluar</button></form>
        </div>
    </div>

    <!-- MAIN CHAT -->
    <div class="chat-main">
        <div class="empty-state" id="emptyState">
            <div class="icon">💬</div>
            <h3>Pilih Room untuk Mulai</h3>
            <p>Atau buat room baru untuk mengundang teman</p>
            <button class="btn-action accent" style="margin-top:16px;max-width:200px;" onclick="openSidebar()">Lihat Room</button>
        </div>

        <div id="activeChat" style="display:none;flex-direction:column;height:100%;">
            <div class="chat-header">
                <div class="chat-header-left">
                    <button class="btn-sidebar-toggle" onclick="openSidebar()">☰</button>
                    <div class="chat-header-info">
                        <div class="room-icon public" id="chatRoomIcon">#</div>
                        <div style="min-width:0;">
                            <h2 id="chatRoomName">Room</h2>
                            <div class="status">
                                <span class="online-badge">🟢 <span id="chatOnlineCount">0 online</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="chat-header-actions">
                    <button class="btn-icon call" onclick="startCall('voice')" title="Voice Call">📞</button>
                    <button class="btn-icon video" onclick="startCall('video')" title="Video Call">🎥</button>
                    <button class="btn-icon members-btn" onclick="toggleMembers()" title="Members">👥</button>
                </div>
            </div>
            <div class="messages-area" id="messagesArea"></div>
            <div class="typing-indicator" id="typingIndicator"></div>
            <div class="input-area">
                <div class="input-container">
                    <button class="attach-btn" onclick="document.getElementById('fileInput').click()">📎</button>
                    <input type="file" id="fileInput" style="display:none;" onchange="handleFileUpload(this)" multiple>
                    <textarea id="messageInput" placeholder="Ketik pesan..." rows="1" onkeydown="handleKeyDown(event)" oninput="handleTyping()"></textarea>
                    <button class="send-btn" id="sendBtn" onclick="sendMessage()" disabled>➤</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MEMBERS PANEL -->
    <div class="members-panel" id="membersPanel">
        <div class="members-panel-header">
            <h3>Anggota</h3>
            <button class="btn-icon" onclick="toggleMembers()" style="width:30px;height:30px;font-size:14px;">✕</button>
        </div>
        <div class="members-list" id="membersList"></div>
    </div>
</div>

<!-- CREATE ROOM MODAL -->
<div class="modal-overlay" id="createRoomModal">
    <div class="modal">
        <h2>🏠 Buat Room Baru</h2>
        <div class="form-group"><label>Nama Room</label><input type="text" id="newRoomName" placeholder="contoh: Tim Proyek"></div>
        <div class="form-group"><label>Deskripsi (opsional)</label><textarea id="newRoomDesc" placeholder="Deskripsi singkat..." rows="2"></textarea></div>
        <div class="form-group"><label>Tipe Room</label>
            <select id="newRoomPrivate" onchange="toggleRoomPassword()"><option value="0">Publik</option><option value="1">Private (password)</option></select>
        </div>
        <div class="form-group" id="roomPasswordGroup" style="display:none;"><label>Password Room</label><input type="password" id="newRoomPassword" placeholder="Password"></div>
        <div class="form-group"><label>Undang Anggota</label>
            <div class="member-checkbox-list" id="inviteList">
                @foreach($allUsers as $u)
                <label class="member-checkbox-item"><input type="checkbox" value="{{ $u->id }}"> {{ $u->display_name }} (@{{ $u->username }})</label>
                @endforeach
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('createRoomModal')">Batal</button>
            <button class="btn-confirm" onclick="createRoom()">Buat Room</button>
        </div>
    </div>
</div>

<!-- JOIN ROOM MODAL -->
<div class="modal-overlay" id="joinRoomModal">
    <div class="modal">
        <h2>🚪 Gabung Room Private</h2>
        <div class="form-group"><label>ID Room</label><input type="text" id="joinRoomId" placeholder="ID room"></div>
        <div class="form-group"><label>Password (jika ada)</label><input type="password" id="joinRoomPass" placeholder="Password room"></div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('joinRoomModal')">Batal</button>
            <button class="btn-confirm" onclick="joinRoomWithPassword()">Gabung</button>
        </div>
    </div>
</div>

<!-- CALL OVERLAY -->
<div class="call-overlay" id="callOverlay">
    <div class="video-container" id="videoContainer">
        <video id="remoteVideo" autoplay playsinline></video>
        <video id="localVideo" class="local-video" autoplay playsinline muted></video>
    </div>
    <div class="call-info">
        <div class="call-avatar" id="callAvatar">?</div>
        <h2 id="callName">Calling...</h2>
        <p id="callStatus">Menghubungkan...</p>
        <div class="call-timer" id="callTimer" style="display:none;">00:00</div>
    </div>
    <div class="call-actions">
        <button class="call-btn mute" id="btnMute" onclick="toggleMute()">🎤</button>
        <button class="call-btn end" onclick="endCall()">📵</button>
    </div>
</div>

<!-- INCOMING CALL -->
<div class="incoming-call" id="incomingCall">
    <h4 id="incomingCallerName">Panggilan masuk...</h4>
    <p id="incomingCallType">Voice call</p>
    <div class="actions">
        <button class="btn-rej" onclick="rejectCall()">Tolak</button>
        <button class="btn-acc" onclick="acceptCall()">Terima</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ===== STATE =====
const currentUser = { id: {{ $user->id }}, username: '{{ $user->username }}', displayName: '{{ addslashes($user->display_name) }}' };
let currentRoom = null, ws = null, signalingWs = null, peerConnection = null;
let localStream = null, callTimerInterval = null, callSeconds = 0;
let incomingOffer = null, incomingCallerId = null, isMuted = false, typingTimeout = null;
const displayedMsgIds = new Set();

// ===== SIDEBAR =====
function openSidebar() { document.getElementById('sidebar').classList.add('open'); document.getElementById('sidebarOverlay').classList.add('open'); }
function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('open'); }

// ===== WEBSOCKET =====
function connectWebSocket() {
    try {
        ws = new WebSocket('ws://' + WS_HOST + ':8090');
    } catch(e) { console.error('WS create failed:', e); setTimeout(connectWebSocket, 3000); return; }

    ws.onopen = function() { console.log('WS connected');
        if (currentRoom) {
            ws.send(JSON.stringify({ action:'join', room_id:currentRoom.id, user_id:currentUser.id, username:currentUser.username, display_name:currentUser.displayName }));
        }
    };
    ws.onmessage = function(e) {
        var d = JSON.parse(e.data);
        if (d.action === 'new_message') {
            if (d.message && d.message.id && displayedMsgIds.has(d.message.id)) return;
            if (d.message && d.message.id) displayedMsgIds.add(d.message.id);
            displayMessage(d.message);
        } else if (d.action === 'user_joined') {
            addSystemMessage(d.display_name + ' bergabung');
            document.getElementById('chatOnlineCount').textContent = d.online_count + ' online';
        } else if (d.action === 'user_left') {
            addSystemMessage(d.display_name + ' keluar');
            document.getElementById('chatOnlineCount').textContent = d.online_count + ' online';
        } else if (d.action === 'typing') {
            showTyping(d.username, d.is_typing);
        } else if (d.action === 'online_users') {
            document.getElementById('chatOnlineCount').textContent = d.users.length + ' online';
        }
    };
    ws.onerror = function(e) { console.error('WS error:', e); };
    ws.onclose = function() { console.log('WS disconnected'); setTimeout(connectWebSocket, 3000); };
}

// ===== SIGNALING =====
function connectSignaling() {
    try {
        signalingWs = new WebSocket('ws://' + WS_HOST + ':8091');
    } catch(e) { setTimeout(connectSignaling, 3000); return; }

    signalingWs.onopen = function() { signalingWs.send(JSON.stringify({ type:'register', user_id:currentUser.id })); };
    signalingWs.onmessage = async function(e) {
        var d = JSON.parse(e.data);
        if (d.type==='call-offer') handleIncomingCall(d);
        else if (d.type==='call-answer' && peerConnection) { await peerConnection.setRemoteDescription(new RTCSessionDescription(d.answer)); showCallConnected(); }
        else if (d.type==='ice-candidate' && peerConnection && d.candidate) await peerConnection.addIceCandidate(new RTCIceCandidate(d.candidate));
        else if (d.type==='call-rejected') { endCallCleanup(); alert('Panggilan ditolak'); }
        else if (d.type==='call-ended') endCallCleanup();
    };
    signalingWs.onclose = function() { setTimeout(connectSignaling, 3000); };
}

// ===== ROOM =====
async function selectRoom(roomId, roomName) {
    document.querySelectorAll('.room-item').forEach(function(el) { el.classList.remove('active'); });
    var el = document.querySelector('.room-item[data-room-id="'+roomId+'"]');
    if (el) el.classList.add('active');
    currentRoom = { id: roomId, name: roomName };
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('activeChat').style.display = 'flex';
    document.getElementById('chatRoomName').textContent = roomName;
    closeSidebar();
    if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({ action:'join', room_id:roomId, user_id:currentUser.id, username:currentUser.username, display_name:currentUser.displayName }));
    }
    await loadMessages(roomId);
    loadMembers(roomId);
}

async function loadMessages(roomId) {
    var area = document.getElementById('messagesArea');
    area.innerHTML = '<div class="system-message"><span>🔒 Private Chat - Pesan tersimpan di server lokal</span></div>';
    displayedMsgIds.clear();
    try {
        var res = await fetch('/chat/' + roomId + '/messages', { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' } });
        if (!res.ok) { console.error('Load messages HTTP ' + res.status); return; }
        var msgs = await res.json();
        if (Array.isArray(msgs)) {
            for (var i = 0; i < msgs.length; i++) {
                if (msgs[i].id) displayedMsgIds.add(msgs[i].id);
                displayMessage(msgs[i], false);
            }
        }
        area.scrollTop = area.scrollHeight;
    } catch(e) { console.error('Load messages failed:', e); }
}

async function loadMembers(roomId) {
    try {
        var res = await fetch('/chat/rooms/' + roomId + '/members', { headers: { 'Accept': 'application/json' } });
        var members = await res.json();
        var html = '';
        for (var i = 0; i < members.length; i++) {
            var m = members[i];
            html += '<div class="member-item"><div class="member-avatar"><div class="av">' + m.display_name.charAt(0).toUpperCase() + '</div>';
            html += '<div class="member-status ' + (m.is_online ? 'online' : 'offline') + '"></div></div>';
            html += '<div><div class="member-name">' + m.display_name + '</div><div class="member-role">@' + m.username + '</div></div></div>';
        }
        document.getElementById('membersList').innerHTML = html;
    } catch(e) {}
}

// ===== SEND MESSAGE =====
async function sendMessage() {
    var input = document.getElementById('messageInput');
    var text = input.value.trim();
    if (!text || !currentRoom) return;
    input.value = '';
    input.style.height = 'auto';
    document.getElementById('sendBtn').disabled = true;

    try {
        var res = await fetch('/chat/' + currentRoom.id + '/messages', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ content: text, iv: null, type: 'text' })
        });

        if (!res.ok) {
            var errText = await res.text();
            console.error('Send failed HTTP ' + res.status + ':', errText);
            return;
        }

        var msg = await res.json();
        if (msg.error) { console.error('Send error:', msg.error); return; }

        // Display own message immediately
        displayedMsgIds.add(msg.id);
        displayMessage(msg);

        // Broadcast to others
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ action: 'message', message: msg }));
        }
    } catch(e) { console.error('Send failed:', e); }
}

// ===== DISPLAY MESSAGE =====
function displayMessage(msg, scroll) {
    if (scroll === undefined) scroll = true;
    var area = document.getElementById('messagesArea');
    if (!msg) return;
    var isSelf = msg.user_id === currentUser.id;

    if (msg.type === 'system') { addSystemMessage(msg.content); return; }

    // Read content directly - NO decryption
    var content = msg.content || '';
    var time = '';
    try { time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' }); } catch(e) { time = ''; }
    var userName = (msg.user && msg.user.display_name) ? msg.user.display_name : 'Unknown';
    var initial = userName.charAt(0).toUpperCase();

    var bubbleHtml = '';
    if (msg.type === 'image' && msg.file_path) {
        bubbleHtml = '<div class="msg-bubble"><img src="' + msg.file_path + '" class="msg-image" onclick="window.open(\'' + msg.file_path + '\',\'_blank\')"></div>';
    } else if (msg.type === 'file' && msg.file_path) {
        var ext = (msg.file_name || '').split('.').pop().toLowerCase();
        var icons = { pdf:'📄', doc:'📝', docx:'📝', xls:'📊', xlsx:'📊', zip:'📦', rar:'📦' };
        var icon = icons[ext] || '📁';
        var size = msg.file_size ? formatFileSize(msg.file_size) : '';
        bubbleHtml = '<div class="msg-bubble file-msg"><div class="file-icon">' + icon + '</div><div class="file-info"><div class="file-name">' + (msg.file_name||'file') + '</div><div class="file-size">' + size + '</div></div><a href="' + msg.file_path + '" download class="file-download">⬇</a></div>';
    } else {
        // Escape HTML to prevent XSS
        content = content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        // Make links clickable
        content = content.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" style="color:inherit;text-decoration:underline;">$1</a>');
        // Newlines to <br>
        content = content.replace(/\n/g, '<br>');
        bubbleHtml = '<div class="msg-bubble">' + content + '</div>';
    }

    var html = '<div class="message-group ' + (isSelf ? 'self' : '') + '">';
    html += '<div class="msg-avatar">' + initial + '</div>';
    html += '<div class="msg-content">';
    html += '<div class="msg-header"><span class="msg-username">' + userName + '</span><span class="msg-time">' + time + '</span></div>';
    html += bubbleHtml;
    html += '</div></div>';

    area.insertAdjacentHTML('beforeend', html);
    if (scroll) area.scrollTop = area.scrollHeight;
}

function addSystemMessage(text) {
    var area = document.getElementById('messagesArea');
    area.insertAdjacentHTML('beforeend', '<div class="system-message"><span>' + text + '</span></div>');
    area.scrollTop = area.scrollHeight;
}

// ===== FILE UPLOAD =====
async function handleFileUpload(input) {
    if (!currentRoom || !input.files.length) return;
    for (var i = 0; i < input.files.length; i++) {
        var file = input.files[i];
        var fd = new FormData();
        fd.append('file', file);
        fd.append('room_id', currentRoom.id);
        try {
            var res = await fetch('/chat/upload', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }, body: fd });
            var data = await res.json();
            if (data.error) { console.error(data.error); continue; }
            var isImg = file.type.startsWith('image/');
            var msgRes = await fetch('/chat/' + currentRoom.id + '/messages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify({ content: file.name, iv: null, type: isImg ? 'image' : 'file', file_path: data.file_path, file_name: data.file_name, file_size: data.file_size })
            });
            var msg = await msgRes.json();
            if (msg.id) { displayedMsgIds.add(msg.id); displayMessage(msg); }
            if (ws && ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ action: 'file', message: msg }));
        } catch(e) { console.error('Upload failed:', e); }
    }
    input.value = '';
}

// ===== TYPING =====
function handleTyping() {
    var input = document.getElementById('messageInput');
    document.getElementById('sendBtn').disabled = !input.value.trim();
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 100) + 'px';
    if (ws && ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ action: 'typing', is_typing: true }));
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(function() { if (ws && ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ action: 'typing', is_typing: false })); }, 2000);
}
var typingUsers = {};
function showTyping(u, t) {
    if (t) typingUsers[u] = true; else delete typingUsers[u];
    var names = Object.keys(typingUsers);
    document.getElementById('typingIndicator').textContent = names.length ? names.join(', ') + ' sedang mengetik...' : '';
}
function handleKeyDown(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } }

// ===== WEBRTC =====
var rtcConfig = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }, { urls: 'stun:stun1.l.google.com:19302' }] };

async function startCall(type) {
    if (!currentRoom) return;
    var res = await fetch('/chat/rooms/' + currentRoom.id + '/members', { headers: { 'Accept': 'application/json' } });
    var members = await res.json();
    var others = members.filter(function(m) { return m.id !== currentUser.id; });
    if (!others.length) { alert('Tidak ada anggota lain'); return; }
    var target = others[0];
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: type === 'video' });
        peerConnection = new RTCPeerConnection(rtcConfig);
        localStream.getTracks().forEach(function(t) { peerConnection.addTrack(t, localStream); });
        peerConnection.ontrack = function(e) { document.getElementById('remoteVideo').srcObject = e.streams[0]; };
        peerConnection.onicecandidate = function(e) { if (e.candidate && signalingWs) signalingWs.send(JSON.stringify({ type:'ice-candidate', target_user_id:target.id, from_user_id:currentUser.id, candidate:e.candidate })); };
        if (type === 'video') { document.getElementById('localVideo').srcObject = localStream; document.getElementById('videoContainer').classList.add('open'); }
        var offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        signalingWs.send(JSON.stringify({ type:'call-offer', target_user_id:target.id, from_user_id:currentUser.id, from_username:currentUser.displayName, call_type:type, offer:offer, room_id:currentRoom.id }));
        document.getElementById('callOverlay').classList.add('open');
        document.getElementById('callAvatar').textContent = target.display_name.charAt(0).toUpperCase();
        document.getElementById('callName').textContent = target.display_name;
        document.getElementById('callStatus').textContent = 'Memanggil...';
    } catch(e) { console.error(e); alert('Gagal memulai panggilan.'); }
}

function handleIncomingCall(d) {
    incomingOffer = d.offer; incomingCallerId = d.from_user_id;
    document.getElementById('incomingCallerName').textContent = d.from_username + ' memanggil...';
    document.getElementById('incomingCallType').textContent = d.call_type === 'video' ? '🎥 Video call' : '📞 Voice call';
    document.getElementById('incomingCall').classList.add('open');
    document.getElementById('incomingCall').dataset.callType = d.call_type;
}

async function acceptCall() {
    document.getElementById('incomingCall').classList.remove('open');
    var ct = document.getElementById('incomingCall').dataset.callType || 'voice';
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: ct === 'video' });
        peerConnection = new RTCPeerConnection(rtcConfig);
        localStream.getTracks().forEach(function(t) { peerConnection.addTrack(t, localStream); });
        peerConnection.ontrack = function(e) { document.getElementById('remoteVideo').srcObject = e.streams[0]; };
        peerConnection.onicecandidate = function(e) { if (e.candidate && signalingWs) signalingWs.send(JSON.stringify({ type:'ice-candidate', target_user_id:incomingCallerId, from_user_id:currentUser.id, candidate:e.candidate })); };
        await peerConnection.setRemoteDescription(new RTCSessionDescription(incomingOffer));
        var answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        signalingWs.send(JSON.stringify({ type:'call-answer', target_user_id:incomingCallerId, from_user_id:currentUser.id, answer:answer }));
        if (ct === 'video') { document.getElementById('localVideo').srcObject = localStream; document.getElementById('videoContainer').classList.add('open'); }
        document.getElementById('callOverlay').classList.add('open');
        showCallConnected();
    } catch(e) { console.error(e); }
}

function rejectCall() {
    document.getElementById('incomingCall').classList.remove('open');
    if (signalingWs && incomingCallerId) signalingWs.send(JSON.stringify({ type:'call-reject', target_user_id:incomingCallerId, from_user_id:currentUser.id }));
    incomingOffer = null; incomingCallerId = null;
}
function endCall() {
    if (signalingWs && incomingCallerId) signalingWs.send(JSON.stringify({ type:'call-end', target_user_id:incomingCallerId, from_user_id:currentUser.id }));
    endCallCleanup();
}
function endCallCleanup() {
    if (localStream) { localStream.getTracks().forEach(function(t) { t.stop(); }); localStream = null; }
    if (peerConnection) { peerConnection.close(); peerConnection = null; }
    clearInterval(callTimerInterval); callSeconds = 0;
    document.getElementById('callOverlay').classList.remove('open');
    document.getElementById('videoContainer').classList.remove('open');
    document.getElementById('callTimer').style.display = 'none';
    document.getElementById('localVideo').srcObject = null;
    document.getElementById('remoteVideo').srcObject = null;
    isMuted = false; document.getElementById('btnMute').classList.remove('active');
}
function showCallConnected() {
    document.getElementById('callStatus').textContent = 'Terhubung';
    document.getElementById('callTimer').style.display = 'block';
    callTimerInterval = setInterval(function() { callSeconds++; var m = String(Math.floor(callSeconds/60)).padStart(2,'0'); var s = String(callSeconds%60).padStart(2,'0'); document.getElementById('callTimer').textContent = m+':'+s; }, 1000);
}
function toggleMute() {
    if (!localStream) return;
    isMuted = !isMuted;
    localStream.getAudioTracks().forEach(function(t) { t.enabled = !isMuted; });
    document.getElementById('btnMute').classList.toggle('active', isMuted);
    document.getElementById('btnMute').textContent = isMuted ? '🔇' : '🎤';
}

// ===== ROOM ACTIONS =====
function openCreateRoomModal() { document.getElementById('createRoomModal').classList.add('open'); closeSidebar(); }
function openJoinRoomModal() { document.getElementById('joinRoomModal').classList.add('open'); closeSidebar(); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function toggleRoomPassword() { document.getElementById('roomPasswordGroup').style.display = document.getElementById('newRoomPrivate').value === '1' ? 'block' : 'none'; }
function toggleMembers() { document.getElementById('membersPanel').classList.toggle('open'); }

async function createRoom() {
    var name = document.getElementById('newRoomName').value.trim();
    if (!name) { alert('Nama room harus diisi'); return; }
    var cbs = document.querySelectorAll('#inviteList input:checked');
    var members = [];
    cbs.forEach(function(cb) { members.push(parseInt(cb.value)); });
    try {
        var res = await fetch('/chat/rooms', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ name: name, description: document.getElementById('newRoomDesc').value, is_private: document.getElementById('newRoomPrivate').value === '1', password: document.getElementById('newRoomPassword').value, members: members })
        });
        if (res.ok) { closeModal('createRoomModal'); window.location.reload(); }
        else { var err = await res.json(); alert(err.error || 'Gagal membuat room'); }
    } catch(e) { console.error(e); }
}

async function joinAndSelectRoom(roomId, roomName) {
    try {
        await fetch('/chat/rooms/' + roomId + '/join', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify({}) });
        window.location.reload();
    } catch(e) { console.error(e); }
}

async function joinRoomWithPassword() {
    var roomId = document.getElementById('joinRoomId').value.trim();
    var pw = document.getElementById('joinRoomPass').value;
    if (!roomId) return;
    try {
        var res = await fetch('/chat/rooms/' + roomId + '/join', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify({ password: pw }) });
        if (res.ok) { closeModal('joinRoomModal'); window.location.reload(); }
        else { var err = await res.json(); alert(err.error || 'Gagal bergabung'); }
    } catch(e) { console.error(e); }
}

function formatFileSize(b) { if (b < 1024) return b + ' B'; if (b < 1048576) return (b/1024).toFixed(1) + ' KB'; return (b/1048576).toFixed(1) + ' MB'; }

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(function(o) { o.addEventListener('click', function(e) { if (e.target === o) o.classList.remove('open'); }); });

// ===== INIT =====
document.addEventListener('DOMContentLoaded', function() {
    connectWebSocket();
    connectSignaling();
    var first = document.querySelector('.room-item[data-room-id]');
    if (first) first.click();
});
</script>
@endsection