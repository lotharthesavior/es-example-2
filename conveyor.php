<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Conveyor\Constants;
use Conveyor\ConveyorServer;
use Conveyor\Events\MessageReceivedEvent;
use Conveyor\Events\PreServerStartEvent;
use Conveyor\SubProtocols\Conveyor\Persistence\WebSockets\Table\SocketChannelPersistenceTable;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

$port = (int) (getenv('CONVEYOR_PORT') ?: 8181);
$host = (string) (getenv('CONVEYOR_HOST') ?: '0.0.0.0');

$channelPersistence = new SocketChannelPersistenceTable;

$listeners = [
    Constants::EVENT_MESSAGE_RECEIVED => static function (MessageReceivedEvent $event): void {
        fwrite(STDOUT, '[conveyor] ' . $event->data . PHP_EOL);
    },
    Constants::EVENT_PRE_SERVER_START => static function (PreServerStartEvent $event) use ($channelPersistence): void {
        $event->server->on('request', static function (Request $req, Response $res) use ($event, $channelPersistence): void {
            echo '=============================== BEGIN' . PHP_EOL;
            echo '[conveyor] Received HTTP request: ' . $req->server['request_uri'] . PHP_EOL;
            echo '[conveyor] Request method: ' . $req->server['request_method'] . PHP_EOL;
            echo '[conveyor] Request body: ' . $req->rawContent() . PHP_EOL;
            echo '=============================== END' . PHP_EOL;

            if ($req->server['request_uri'] !== '/conveyor/message' || $req->server['request_method'] !== 'POST') {
                $res->status(404);
                $res->end();

                return;
            }

            $channel = $req->post['channel'] ?? null;
            $message = $req->post['message'] ?? null;

            // Fall back to JSON body if form params are absent.
            if ($channel === null || $message === null) {
                $body = json_decode($req->rawContent(), true) ?? [];
                $channel = $channel ?? ($body['channel'] ?? null);
                $message = $message ?? ($body['message'] ?? null);
            }

            if ($channel === null || $message === null) {
                $res->status(400);
                $res->end();

                return;
            }

            $payload = json_decode($message, true);
            $push = json_encode(['action' => 'broadcast-action', 'data' => $payload, 'fd' => 0]);

            foreach ($channelPersistence->getAllConnections() as $fd => $ch) {
                if ($ch === $channel && $event->server->isEstablished((int) $fd)) {
                    $event->server->push((int) $fd, $push);
                }
            }

            $res->status(200);
            $res->header('Content-Type', 'application/json');
            $res->end(json_encode(['ok' => true]));
        });
    },
];

new ConveyorServer(
    host: $host,
    port: $port,
    eventListeners: $listeners,
    persistence: ['channels' => $channelPersistence],
);
