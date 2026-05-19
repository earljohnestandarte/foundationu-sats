<?php

namespace App\Commands;

use App\Realtime\TicketRealtimeServer;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\Http\HttpServer as RatchetHttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;

class ServeRealtime extends BaseCommand
{
    protected $group = 'Realtime';
    protected $name = 'realtime:serve';
    protected $description = 'Starts the ticket websocket and local publish server.';

    public function run(array $params)
    {
        $config = config('Realtime');
        $loop = Loop::get();
        $realtime = new TicketRealtimeServer($config);

        $wsAddress = $config->websocketHost . ':' . $config->websocketPort;
        $wsSocket = new SocketServer($wsAddress, [], $loop);
        new IoServer(
            new RatchetHttpServer(new WsServer($realtime)),
            $wsSocket,
            $loop
        );

        $publishServer = new HttpServer(function (ServerRequestInterface $request) use ($config, $realtime) {
            if ($request->getMethod() !== 'POST' || $request->getUri()->getPath() !== '/publish') {
                return new Response(404, ['Content-Type' => 'application/json'], json_encode(['success' => false]));
            }

            if (($request->getHeaderLine('X-Realtime-Secret')) !== $config->secret) {
                return new Response(403, ['Content-Type' => 'application/json'], json_encode(['success' => false, 'message' => 'Forbidden']));
            }

            $payload = json_decode((string) $request->getBody(), true);
            if (! is_array($payload)) {
                return new Response(422, ['Content-Type' => 'application/json'], json_encode(['success' => false, 'message' => 'Invalid payload']));
            }

            $sent = $realtime->publish($payload);

            return new Response(202, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'sent'    => $sent,
            ]));
        });

        $publishAddress = $config->publishHost . ':' . $config->publishPort;
        $publishSocket = new SocketServer($publishAddress, [], $loop);
        $publishServer->listen($publishSocket);

        CLI::write('Realtime websocket server listening on ws://' . $wsAddress, 'green');
        CLI::write('Realtime publish endpoint listening on http://' . $publishAddress . '/publish', 'green');
        CLI::write('Press Ctrl+C to stop.', 'yellow');

        $loop->run();
    }
}
