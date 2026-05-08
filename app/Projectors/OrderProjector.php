<?php

declare(strict_types=1);

namespace App\Projectors;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderDelivered;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Events\OrderRefunded;
use App\Domain\Order\Events\OrderShipped;
use App\Models\Order;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class OrderProjector extends BaseProjector
{
    public function onOrderPlaced(OrderPlaced $event, StoredEvent $storedEvent): void
    {
        Order::create([
            'uuid' => $storedEvent->aggregate_uuid,
            'user_id' => $event->userId,
            'cart_uuid' => $event->cartUuid,
            'items' => $event->items,
            'total_in_cents' => $event->totalInCents,
            'status' => OrderStatus::Placed,
        ]);
    }

    public function onOrderPaid(OrderPaid $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $order->status = OrderStatus::Paid;
        $order->payment_reference = $event->paymentReference;
        $order->paid_at = \now();
        $order->save();
    }

    public function onOrderShipped(OrderShipped $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $order->status = OrderStatus::Shipped;
        $order->tracking_number = $event->trackingNumber;
        $order->shipped_at = \now();
        $order->save();
    }

    public function onOrderDelivered(OrderDelivered $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $order->status = OrderStatus::Delivered;
        $order->delivered_at = \now();
        $order->save();
    }

    public function onOrderCancelled(OrderCancelled $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $order->status = OrderStatus::Cancelled;
        $order->cancellation_reason = $event->reason;
        $order->cancelled_at = \now();
        $order->save();
    }

    public function onOrderRefunded(OrderRefunded $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $order->status = OrderStatus::Refunded;
        $order->save();
    }
}
