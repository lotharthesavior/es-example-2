<?php

declare(strict_types=1);

namespace Tests\Unit\Projectors;

use App\Domain\Cart\Events\ItemAddedToCart;
use App\Domain\Order\Events\OrderPlaced;
use App\Events\EventStreamBroadcast;
use App\Projectors\DashboardProjector;
use Illuminate\Support\Facades\Event;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Tests\TestCase;

final class DashboardProjectorTest extends TestCase
{
    private DashboardProjector $projector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projector = new DashboardProjector;
    }

    private function makeStoredEvent(string $aggregateUuid, ShouldBeStored $event): StoredEvent
    {
        return new StoredEvent(
            [
                'id' => 1,
                'event_properties' => '{}',
                'aggregate_uuid' => $aggregateUuid,
                'aggregate_version' => '1',
                'event_version' => 1,
                'event_class' => get_class($event),
                'meta_data' => '{}',
                'created_at' => now()->toIso8601String(),
            ],
            $event
        );
    }

    public function test_broadcasts_item_added_to_cart(): void
    {
        Event::fake([EventStreamBroadcast::class]);

        $event = new ItemAddedToCart(
            productId: 'product-1',
            productName: 'Test Product',
            quantity: 1,
            priceInCents: 9999,
        );

        $this->projector->onItemAddedToCart(
            $event,
            $this->makeStoredEvent('cart-uuid-1', $event)
        );

        Event::assertDispatched(EventStreamBroadcast::class, function (EventStreamBroadcast $event): bool {
            return $event->eventName === 'ItemAddedToCart'
                && $event->aggregateType === 'cart'
                && $event->payload['productName'] === 'Test Product';
        });
    }

    public function test_broadcasts_order_placed(): void
    {
        Event::fake([EventStreamBroadcast::class]);

        $event = new OrderPlaced(
            userId: 'user-1',
            cartUuid: 'cart-1',
            items: [['productId' => 'p1', 'productName' => 'P1', 'quantity' => 1, 'priceInCents' => 9999]],
            totalInCents: 9999,
        );

        $this->projector->onOrderPlaced(
            $event,
            $this->makeStoredEvent('order-uuid-1', $event)
        );

        Event::assertDispatched(EventStreamBroadcast::class, function (EventStreamBroadcast $event): bool {
            return $event->eventName === 'OrderPlaced'
                && $event->aggregateType === 'order'
                && $event->payload['total'] === '$99.99';
        });
    }
}
