<?php

namespace App\Services;

use Workerman\Connection\TcpConnection;

class ChatWebSocket
{
    protected array $rooms = [];
    protected array $userMap = [];
    protected array $userConns = [];

    public function onConnect(TcpConnection $conn): void
    {
        echo "New connection: {$conn->id}\n";
    }

    public function onMessage(TcpConnection $from, $msg): void
    {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['action'])) return;

        match($data['action']) {
            'join' => $this->handleJoin($from, $data),
            'leave' => $this->handleLeave($from),
            'message' => $this->handleMessage($from, $data),
            'typing' => $this->handleTyping($from, $data),
            'file' => $this->handleFile($from, $data),
            default => null,
        };
    }

    protected function handleJoin(TcpConnection $conn, array $data): void
    {
        $roomId = $data['room_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        $username = $data['username'] ?? 'Anonymous';
        $displayName = $data['display_name'] ?? $username;
        if (!$roomId || !$userId) return;

        $this->handleLeave($conn);

        $this->rooms[$roomId][$conn->id] = $conn;
        $this->userMap[$conn->id] = [
            'user_id' => $userId,
            'username' => $username,
            'display_name' => $displayName,
            'room_id' => $roomId,
        ];
        $this->userConns[$userId] = $conn;

        $this->broadcastToRoom($roomId, [
            'action' => 'user_joined',
            'user_id' => $userId,
            'username' => $username,
            'display_name' => $displayName,
            'online_count' => count($this->rooms[$roomId] ?? []),
        ], $conn);

        $onlineUsers = [];
        foreach ($this->rooms[$roomId] ?? [] as $client) {
            if (isset($this->userMap[$client->id])) {
                $onlineUsers[] = $this->userMap[$client->id];
            }
        }
        $conn->send(json_encode(['action' => 'online_users', 'users' => $onlineUsers]));
        echo "User {$username} joined room {$roomId}\n";
    }

    protected function handleLeave(TcpConnection $conn): void
    {
        $info = $this->userMap[$conn->id] ?? null;
        if (!$info) return;

        $roomId = $info['room_id'];
        unset($this->rooms[$roomId][$conn->id], $this->userMap[$conn->id], $this->userConns[$info['user_id']]);
        if (empty($this->rooms[$roomId])) unset($this->rooms[$roomId]);

        $this->broadcastToRoom($roomId, [
            'action' => 'user_left',
            'user_id' => $info['user_id'],
            'username' => $info['username'],
            'display_name' => $info['display_name'],
            'online_count' => count($this->rooms[$roomId] ?? []),
        ]);
    }

    protected function handleMessage(TcpConnection $from, array $data): void
    {
        $info = $this->userMap[$from->id] ?? null;
        if (!$info) return;
        $this->broadcastToRoom($info['room_id'], ['action' => 'new_message', 'message' => $data['message'] ?? []]);
    }

    protected function handleFile(TcpConnection $from, array $data): void
    {
        $info = $this->userMap[$from->id] ?? null;
        if (!$info) return;
        $this->broadcastToRoom($info['room_id'], ['action' => 'new_message', 'message' => $data['message'] ?? []]);
    }

    protected function handleTyping(TcpConnection $from, array $data): void
    {
        $info = $this->userMap[$from->id] ?? null;
        if (!$info) return;
        $this->broadcastToRoom($info['room_id'], [
            'action' => 'typing',
            'user_id' => $info['user_id'],
            'username' => $info['display_name'],
            'is_typing' => $data['is_typing'] ?? false,
        ], $from);
    }

    protected function broadcastToRoom(string $roomId, array $data, ?TcpConnection $exclude = null): void
    {
        $payload = json_encode($data);
        foreach ($this->rooms[$roomId] ?? [] as $client) {
            if ($exclude && $client === $exclude) continue;
            $client->send($payload);
        }
    }

    public function onClose(TcpConnection $conn): void
    {
        $this->handleLeave($conn);
        echo "Connection {$conn->id} closed\n";
    }
}