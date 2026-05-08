<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\EventStreamBroadcast;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ConveyorBroadcastTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('broadcasting.default', 'conveyor');
        config()->set('broadcasting.connections.conveyor', [
            'driver' => 'conveyor',
            'protocol' => 'ws',
            'host' => '127.0.0.1',
            'port' => 8181,
        ]);
        config()->set('conveyor.protocol', 'ws');
        config()->set('conveyor.uri', '127.0.0.1');
        config()->set('conveyor.port', 8181);
        config()->set('conveyor.query', '');
    }

    public function test_event_is_posted_to_conveyor_http_endpoint(): void
    {
        Http::fake([
            'http://127.0.0.1:8181/conveyor/message' => Http::response('', 200),
        ]);

        broadcast(new EventStreamBroadcast(
            eventName: 'OrderPaid',
            aggregateType: 'order',
            aggregateUuid: 'uuid-123',
            payload: ['total' => 4200],
            occurredAt: '2026-05-02T00:00:00+00:00',
        ));

        Http::assertSent(function ($request): bool {
            $body = $request->data();
            $msg = json_decode((string) ($body['message'] ?? '{}'), true);

            return $request->url() === 'http://127.0.0.1:8181/conveyor/message'
                && ($body['channel'] ?? null) === 'event-stream'
                && is_array($msg)
                && ($msg['eventName'] ?? null) === 'OrderPaid'
                && ($msg['aggregateUuid'] ?? null) === 'uuid-123'
                && ($msg['payload']['total'] ?? null) === 4200;
        });
    }
}
