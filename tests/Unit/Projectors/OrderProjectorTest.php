<?php

declare(strict_types=1);

namespace Tests\Unit\Projectors;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderDelivered;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Events\OrderRefunded;
use App\Domain\Order\Events\OrderShipped;
use App\Models\Order;
use App\Projectors\OrderProjector;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Tests\TestCase;

final class OrderProjectorTest extends TestCase
{
    private OrderProjector $projector;

    /** @var array<int, array{productId: string, productName: string, quantity: int, priceInCents: int}> */
    private array $items;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projector = new OrderProjector;
        $this->items = [
            ['productId' => 'p1', 'productName' => 'Product 1', 'quantity' => 1, 'priceInCents' => 9999],
        ];
    }

    private function makeStoredEvent(string $aggregateUuid, ShouldBeStored $event, int $version = 1): StoredEvent
    {
        return new StoredEvent(
            [
                'id' => $version,
                'event_properties' => '{}',
                'aggregate_uuid' => $aggregateUuid,
                'aggregate_version' => (string) $version,
                'event_version' => 1,
                'event_class' => get_class($event),
                'meta_data' => '{}',
                'created_at' => now()->toIso8601String(),
            ],
            $event
        );
    }

    private function createPlacedOrder(string $orderUuid): void
    {
        $event = new OrderPlaced(
            userId: 'user-1',
            cartUuid: 'cart-1',
            items: $this->items,
            totalInCents: 9999,
        );
        $this->projector->onOrderPlaced($event, $this->makeStoredEvent($orderUuid, $event));
    }

    public function test_creates_order_on_placed(): void
    {
        $orderUuid = 'test-order-uuid';
        $this->createPlacedOrder($orderUuid);

        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertNotNull($order);
        $this->assertSame(OrderStatus::Placed, $order->status);
        $this->assertSame(9999, $order->total_in_cents);
        $this->assertSame('user-1', $order->user_id);
    }

    public function test_updates_status_on_paid(): void
    {
        $orderUuid = 'test-order-uuid';
        $this->createPlacedOrder($orderUuid);

        $event = new OrderPaid(paymentReference: 'PAY-123');
        $this->projector->onOrderPaid($event, $this->makeStoredEvent($orderUuid, $event, 2));

        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame('PAY-123', $order->payment_reference);
        $this->assertNotNull($order->paid_at);
    }

    public function test_updates_status_on_shipped(): void
    {
        $orderUuid = 'test-order-uuid';
        $this->createPlacedOrder($orderUuid);

        $event = new OrderShipped(trackingNumber: 'TRACK-123');
        $this->projector->onOrderShipped($event, $this->makeStoredEvent($orderUuid, $event, 2));

        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertSame(OrderStatus::Shipped, $order->status);
        $this->assertSame('TRACK-123', $order->tracking_number);
        $this->assertNotNull($order->shipped_at);
    }

    public function test_updates_status_on_delivered(): void
    {
        $orderUuid = 'test-order-uuid';
        $this->createPlacedOrder($orderUuid);

        $event = new OrderDelivered;
        $this->projector->onOrderDelivered($event, $this->makeStoredEvent($orderUuid, $event, 2));

        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertSame(OrderStatus::Delivered, $order->status);
        $this->assertNotNull($order->delivered_at);
    }

    public function test_updates_status_on_cancelled(): void
    {
        $orderUuid = 'test-order-uuid';
        $this->createPlacedOrder($orderUuid);

        $event = new OrderCancelled(reason: 'Changed my mind');
        $this->projector->onOrderCancelled($event, $this->makeStoredEvent($orderUuid, $event, 2));

        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame('Changed my mind', $order->cancellation_reason);
        $this->assertNotNull($order->cancelled_at);
    }

    public function test_updates_status_on_refunded(): void
    {
        $orderUuid = 'test-order-uuid';
        $this->createPlacedOrder($orderUuid);

        $event = new OrderRefunded(amountInCents: 9999, reason: 'Defective');
        $this->projector->onOrderRefunded($event, $this->makeStoredEvent($orderUuid, $event, 2));

        /** @var Order $order */
        $order = Order::query()->where('uuid', $orderUuid)->first();
        $this->assertSame(OrderStatus::Refunded, $order->status);
    }
}
