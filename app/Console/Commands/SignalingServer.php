<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;
use App\Services\SignalingWebSocket;

class SignalingServer extends Command
{
    protected $signature = 'signaling:serve {--port=8091}';
    protected $description = 'Start the WebRTC signaling server (Workerman)';

    public function handle(): void
    {
        $port = $this->option('port');
        $this->info("Signaling server starting on port {$port}...");

        $ws = new Worker("websocket://0.0.0.0:{$port}");
        $ws->count = 1;
        $ws->name = 'SignalingServer';

        $handler = new SignalingWebSocket();

        $ws->onConnect = [$handler, 'onConnect'];
        $ws->onMessage = [$handler, 'onMessage'];
        $ws->onClose = [$handler, 'onClose'];

        $this->info("Signaling server running on ws://0.0.0.0:{$port}");

        global $argv;
        $argv = ['signaling:serve', 'start'];

        Worker::runAll();
    }
}