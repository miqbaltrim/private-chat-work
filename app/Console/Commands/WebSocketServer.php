<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;
use App\Services\ChatWebSocket;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:serve {--port=8090}';
    protected $description = 'Start the WebSocket server (auto-detects SSL for WSS)';

    public function handle(): void
    {
        $port = $this->option('port');
        $certFile = base_path('ssl/cert.pem');
        $keyFile = base_path('ssl/key.pem');
        $hasSSL = file_exists($certFile) && file_exists($keyFile);

        if ($hasSSL) {
            $context = [
                'ssl' => [
                    'local_cert' => $certFile,
                    'local_pk' => $keyFile,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ];
            $ws = new Worker("websocket://0.0.0.0:{$port}", $context);
            $ws->transport = 'ssl';
            $this->info("WSS (Secure) server starting on port {$port}...");
        } else {
            $ws = new Worker("websocket://0.0.0.0:{$port}");
            $this->info("WS server starting on port {$port}...");
            $this->warn("No SSL cert found. Run 'php artisan ssl:generate' for WSS.");
        }

        $ws->count = 1;
        $ws->name = 'ChatWebSocket';

        $handler = new ChatWebSocket();
        $ws->onConnect = [$handler, 'onConnect'];
        $ws->onMessage = [$handler, 'onMessage'];
        $ws->onClose = [$handler, 'onClose'];

        $protocol = $hasSSL ? 'wss' : 'ws';
        $this->info("{$protocol}://0.0.0.0:{$port} running");

        global $argv;
        $argv = ['websocket:serve', 'start'];
        Worker::runAll();
    }
}