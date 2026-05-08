<?php

declare(strict_types=1);

namespace Tests\Browser;

use Illuminate\Support\Facades\Http;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class ConveyorRealtimeTest extends DuskTestCase
{
    public function test_event_card_renders_via_js_injection(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/');

            $browser->script("prependEvent({
                eventName: 'OrderPaid',
                aggregateType: 'order',
                aggregateUuid: 'uuid-ui-test-1',
                payload: { total: 4200 },
                occurredAt: '2026-05-02T00:00:00+00:00'
            })");

            $browser->waitFor('#event-stream > div', 3)
                ->assertSeeIn('#event-stream', 'OrderPaid')
                ->assertSeeIn('#event-stream', 'total: 4200');
        });
    }

    public function test_websocket_push_delivers_event_card(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/');

            // Wait for WS open + channel-connect sent (onReady fires after both).
            $browser->waitUntil('window._wsReady === true', 5);

            // Brief pause for server to process the channel-connect task.
            $browser->pause(800);

            $response = Http::asForm()->post('http://127.0.0.1:8181/conveyor/message', [
                'channel' => 'event-stream',
                'message' => json_encode([
                    'eventName' => 'OrderShipped',
                    'aggregateType' => 'order',
                    'aggregateUuid' => 'uuid-ws-e2e-001',
                    'payload' => ['tracking' => 'TRK999'],
                    'occurredAt' => '2026-05-02T12:00:00+00:00',
                ]),
            ]);

            $this->assertEquals(200, $response->status(), 'Conveyor HTTP push failed: '.$response->body());

            // 'tracking: TRK999' is unique — not in initial page source.
            $browser->waitForText('tracking: TRK999', 5)
                ->assertSeeIn('#event-stream', 'OrderShipped')
                ->assertSeeIn('#event-stream', 'tracking: TRK999');
        });
    }
}
