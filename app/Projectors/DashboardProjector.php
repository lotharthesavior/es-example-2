<?php

declare(strict_types=1);

namespace App\Projectors;

use App\Domain\Cart\Events\CartCleared;
use App\Domain\Cart\Events\CheckoutStarted;
use App\Domain\Cart\Events\ItemAddedToCart;
use App\Domain\Cart\Events\ItemRemovedFromCart;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderDelivered;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Events\OrderRefunded;
use App\Domain\Order\Events\OrderShipped;
use App\Events\EventStreamBroadcast;
use Illuminate\Support\Facades\Log;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class DashboardProjector extends BaseProjector
{
    public function onItemAddedToCart(ItemAddedToCart $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'ItemAddedToCart', 'cart', [
            'productName' => $event->productName,
            'quantity' => $event->quantity,
            'price' => '$'.number_format($event->priceInCents / 100, 2),
        ]);
    }

    public function onItemRemovedFromCart(ItemRemovedFromCart $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'ItemRemovedFromCart', 'cart', [
            'productId' => $event->productId,
        ]);
    }

    public function onCartCleared(CartCleared $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'CartCleared', 'cart', []);
    }

    public function onCheckoutStarted(CheckoutStarted $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'CheckoutStarted', 'cart', [
            'userId' => $event->userId,
        ]);
    }

    public function onOrderPlaced(OrderPlaced $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'OrderPlaced', 'order', [
            'userId' => $event->userId,
            'total' => '$'.number_format($event->totalInCents / 100, 2),
            'itemCount' => count($event->items),
        ]);
    }

    public function onOrderPaid(OrderPaid $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'OrderPaid', 'order', [
            'paymentReference' => $event->paymentReference,
        ]);
    }

    public function onOrderShipped(OrderShipped $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'OrderShipped', 'order', [
            'trackingNumber' => $event->trackingNumber,
        ]);
    }

    public function onOrderDelivered(OrderDelivered $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'OrderDelivered', 'order', []);
    }

    public function onOrderCancelled(OrderCancelled $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'OrderCancelled', 'order', [
            'reason' => $event->reason,
        ]);
    }

    public function onOrderRefunded(OrderRefunded $event, StoredEvent $storedEvent): void
    {
        $this->broadcast($storedEvent, 'OrderRefunded', 'order', [
            'amount' => '$'.number_format($event->amountInCents / 100, 2),
            'reason' => $event->reason,
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function broadcast(StoredEvent $storedEvent, string $eventName, string $aggregateType, array $payload): void
    {
        try {
            broadcast(new EventStreamBroadcast(
                eventName: $eventName,
                aggregateType: $aggregateType,
                aggregateUuid: $storedEvent->aggregate_uuid,
                payload: $payload,
                occurredAt: $storedEvent->created_at !== '' ? $storedEvent->created_at : \now()->toIso8601String(),
            ));
        } catch (\Throwable $e) {
            // Broadcast failures should not break the projection pipeline.
            // In production, Conveyor handles this. In tests/dev, this is a no-op.
            Log::warning('Dashboard broadcast failed', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
