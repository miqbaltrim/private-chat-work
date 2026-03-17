<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;
use App\Services\ChatWebSocket;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:serve {--port=8090}';
    protected $description = 'Start the WebSocket server for real-time chat (Workerman)';

    public function handle(): void
    {
        $port = $this->option('port');
        $this->info("WebSocket server starting on port {$port}...");

        $ws = new Worker("websocket://0.0.0.0:{$port}");
        $ws->count = 1;
        $ws->name = 'ChatWebSocket';

        $handler = new ChatWebSocket();

        $ws->onConnect = [$handler, 'onConnect'];
        $ws->onMessage = [$handler, 'onMessage'];
        $ws->onClose = [$handler, 'onClose'];

        $this->info("WebSocket server running on ws://0.0.0.0:{$port}");

        global $argv;
        $argv = ['websocket:serve', 'start'];

        Worker::runAll();
    }
}