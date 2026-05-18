<?php

declare(strict_types=1);

namespace App\Domain\Order\Projectors;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderDelivered;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Events\OrderRefunded;
use App\Domain\Order\Events\OrderShipped;
use App\Domain\Order\Models\OrderHistory;
use App\Projectors\BaseProjector;
use Illuminate\Support\Carbon;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class OrderHistoryProjector extends BaseProjector
{
    public function onOrderPlaced(OrderPlaced $event, StoredEvent $storedEvent): void
    {
        $this->record($storedEvent, 'OrderPlaced', [
            'userId' => $event->userId,
            'totalInCents' => $event->totalInCents,
            'itemCount' => count($event->items),
        ]);
    }

    public function onOrderPaid(OrderPaid $event, StoredEvent $storedEvent): void
    {
        $this->record($storedEvent, 'OrderPaid', [
            'paymentReference' => $event->paymentReference,
        ]);
    }

    public function onOrderShipped(OrderShipped $event, StoredEvent $storedEvent): void
    {
        $this->record($storedEvent, 'OrderShipped', [
            'trackingNumber' => $event->trackingNumber,
        ]);
    }

    public function onOrderDelivered(OrderDelivered $event, StoredEvent $storedEvent): void
    {
        $this->record($storedEvent, 'OrderDelivered', []);
    }

    public function onOrderCancelled(OrderCancelled $event, StoredEvent $storedEvent): void
    {
        $this->record($storedEvent, 'OrderCancelled', [
            'reason' => $event->reason,
        ]);
    }

    public function onOrderRefunded(OrderRefunded $event, StoredEvent $storedEvent): void
    {
        $this->record($storedEvent, 'OrderRefunded', [
            'amountInCents' => $event->amountInCents,
            'reason' => $event->reason,
        ]);
    }

    /** @param array<string, mixed> $data */
    private function record(StoredEvent $storedEvent, string $eventType, array $data): void
    {
        $occurredAt = $storedEvent->created_at !== ''
            ? Carbon::parse($storedEvent->created_at)
            : Carbon::now();

        $history = OrderHistory::new()->writeable();
        $history->fill([
            'order_uuid' => $storedEvent->aggregate_uuid,
            'event_type' => $eventType,
            'event_data' => $data,
            'occurred_at' => $occurredAt,
        ]);
        $history->created_at = $occurredAt;
        $history->updated_at = $occurredAt;
        $history->timestamps = false;
        $history->save();
    }
}
