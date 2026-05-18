<?php

declare(strict_types=1);

namespace App\Domain\Order\Projectors;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderDelivered;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Events\OrderRefunded;
use App\Domain\Order\Events\OrderShipped;
use App\Domain\Order\Models\Order;
use App\Projectors\BaseProjector;
use Illuminate\Support\Carbon;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class OrderProjector extends BaseProjector
{
    public function onOrderPlaced(OrderPlaced $event, StoredEvent $storedEvent): void
    {
        $occurredAt = Carbon::parse($storedEvent->created_at);

        $order = Order::new()->writeable();
        $order->fill([
            'uuid' => $storedEvent->aggregate_uuid,
            'user_id' => $event->userId,
            'cart_uuid' => $event->cartUuid,
            'items' => $event->items,
            'total_in_cents' => $event->totalInCents,
            'status' => OrderStatus::Placed,
        ]);
        $order->created_at = $occurredAt;
        $order->updated_at = $occurredAt;
        $order->timestamps = false;
        $order->save();
    }

    public function onOrderPaid(OrderPaid $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $occurredAt = Carbon::parse($storedEvent->created_at);
        $order = $order->writeable();
        $order->status = OrderStatus::Paid;
        $order->payment_reference = $event->paymentReference;
        $order->paid_at = $occurredAt;
        $order->updated_at = $occurredAt;
        $order->timestamps = false;
        $order->save();
    }

    public function onOrderShipped(OrderShipped $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $occurredAt = Carbon::parse($storedEvent->created_at);
        $order = $order->writeable();
        $order->status = OrderStatus::Shipped;
        $order->tracking_number = $event->trackingNumber;
        $order->shipped_at = $occurredAt;
        $order->updated_at = $occurredAt;
        $order->timestamps = false;
        $order->save();
    }

    public function onOrderDelivered(OrderDelivered $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $occurredAt = Carbon::parse($storedEvent->created_at);
        $order = $order->writeable();
        $order->status = OrderStatus::Delivered;
        $order->delivered_at = $occurredAt;
        $order->updated_at = $occurredAt;
        $order->timestamps = false;
        $order->save();
    }

    public function onOrderCancelled(OrderCancelled $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $occurredAt = Carbon::parse($storedEvent->created_at);
        $order = $order->writeable();
        $order->status = OrderStatus::Cancelled;
        $order->cancellation_reason = $event->reason;
        $order->cancelled_at = $occurredAt;
        $order->updated_at = $occurredAt;
        $order->timestamps = false;
        $order->save();
    }

    public function onOrderRefunded(OrderRefunded $event, StoredEvent $storedEvent): void
    {
        $order = Order::find($storedEvent->aggregate_uuid);
        if ($order === null) {
            return;
        }

        $occurredAt = Carbon::parse($storedEvent->created_at);
        $order = $order->writeable();
        $order->status = OrderStatus::Refunded;
        $order->updated_at = $occurredAt;
        $order->timestamps = false;
        $order->save();
    }
}
