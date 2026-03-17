<?php

namespace App\Services;

use Workerman\Connection\TcpConnection;

class SignalingWebSocket
{
    protected array $userConns = [];
    protected array $connUsers = [];

    public function onConnect(TcpConnection $conn): void
    {
        echo "Signaling: New connection {$conn->id}\n";
    }

    public function onMessage(TcpConnection $from, $msg): void
    {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['type'])) return;

        switch ($data['type']) {
            case 'register':
                $this->userConns[$data['user_id']] = $from;
                $this->connUsers[$from->id] = $data['user_id'];
                echo "Signaling: User {$data['user_id']} registered\n";
                break;
            case 'call-offer':
                $this->sendTo($data['target_user_id'], [
                    'type' => 'call-offer',
                    'from_user_id' => $data['from_user_id'],
                    'from_username' => $data['from_username'],
                    'call_type' => $data['call_type'],
                    'offer' => $data['offer'],
                    'room_id' => $data['room_id'] ?? null,
                ]);
                break;
            case 'call-answer':
                $this->sendTo($data['target_user_id'], [
                    'type' => 'call-answer',
                    'from_user_id' => $data['from_user_id'],
                    'answer' => $data['answer'],
                ]);
                break;
            case 'ice-candidate':
                $this->sendTo($data['target_user_id'], [
                    'type' => 'ice-candidate',
                    'from_user_id' => $data['from_user_id'],
                    'candidate' => $data['candidate'],
                ]);
                break;
            case 'call-reject':
                $this->sendTo($data['target_user_id'], [
                    'type' => 'call-rejected',
                    'from_user_id' => $data['from_user_id'],
                ]);
                break;
            case 'call-end':
                $this->sendTo($data['target_user_id'], [
                    'type' => 'call-ended',
                    'from_user_id' => $data['from_user_id'],
                ]);
                break;
        }
    }

    protected function sendTo($userId, array $data): void
    {
        if (isset($this->userConns[$userId])) {
            $this->userConns[$userId]->send(json_encode($data));
        }
    }

    public function onClose(TcpConnection $conn): void
    {
        $userId = $this->connUsers[$conn->id] ?? null;
        if ($userId) {
            unset($this->userConns[$userId], $this->connUsers[$conn->id]);
            echo "Signaling: User {$userId} disconnected\n";
        }
    }
}