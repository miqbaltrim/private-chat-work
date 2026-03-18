<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;

class SecureServe extends Command
{
    protected $signature = 'serve:secure {--port=8443} {--backend=8000}';
    protected $description = 'Start HTTPS reverse proxy (requires ssl:generate first)';

    public function handle(): void
    {
        $port = $this->option('port');
        $backendPort = $this->option('backend');
        $certFile = base_path('ssl/cert.pem');
        $keyFile = base_path('ssl/key.pem');

        if (!file_exists($certFile) || !file_exists($keyFile)) {
            $this->error('SSL certificate not found! Run: php artisan ssl:generate');
            return;
        }

        $this->info("HTTPS reverse proxy starting...");
        $this->info("  HTTPS: https://0.0.0.0:{$port}");
        $this->info("  Backend: http://127.0.0.1:{$backendPort}");
        $this->info("");
        $this->info("Users access: https://[YOUR_IP]:{$port}");

        $context = [
            'ssl' => [
                'local_cert' => $certFile,
                'local_pk' => $keyFile,
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];

        $worker = new Worker("http://0.0.0.0:{$port}", $context);
        $worker->transport = 'ssl';
        $worker->count = 1;
        $worker->name = 'HTTPS-Proxy';

        $backend = "http://127.0.0.1:{$backendPort}";

        $worker->onMessage = function (TcpConnection $connection, Request $request) use ($backend) {
            $method = $request->method();
            $uri = $request->uri();
            $headers = $request->header();
            $body = $request->rawBody();

            // Build headers for forwarding
            $headerLines = [];
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'host') continue;
                if (strtolower($key) === 'transfer-encoding') continue;
                $headerLines[] = ucwords($key, '-') . ': ' . $value;
            }
            $headerLines[] = 'Host: 127.0.0.1';
            $headerLines[] = 'X-Forwarded-Proto: https';
            $headerLines[] = 'X-Forwarded-For: ' . $connection->getRemoteIp();
            $headerStr = implode("\r\n", $headerLines);

            $opts = [
                'http' => [
                    'method' => $method,
                    'header' => $headerStr,
                    'content' => $body,
                    'ignore_errors' => true,
                    'timeout' => 30,
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ];

            $ctx = stream_context_create($opts);
            $responseBody = @file_get_contents($backend . $uri, false, $ctx);

            // Parse response
            $statusCode = 200;
            $responseHeaders = [];

            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $h) {
                    if (preg_match('/^HTTP\/[\d.]+ (\d+)/', $h, $m)) {
                        $statusCode = (int)$m[1];
                    } elseif (strpos($h, ':') !== false) {
                        $parts = explode(': ', $h, 2);
                        $key = $parts[0];
                        $val = $parts[1] ?? '';
                        // Skip transfer-encoding as Workerman handles it
                        if (strtolower($key) === 'transfer-encoding') continue;
                        $responseHeaders[$key] = $val;
                    }
                }
            }

            if ($responseBody === false) {
                $response = new Response(502, [], 'Backend not available. Make sure php artisan serve is running on port ' . explode(':', $backend)[2]);
            } else {
                $response = new Response($statusCode, $responseHeaders, $responseBody);
            }

            $connection->send($response);
        };

        global $argv;
        $argv = ['serve:secure', 'start'];
        Worker::runAll();
    }
}