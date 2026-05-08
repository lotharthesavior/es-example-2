<?php

declare(strict_types=1);

namespace App\Reactors;

use App\Domain\Order\Events\OrderPlaced;
use Illuminate\Support\Facades\Log;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class OrderPlacedReactor extends Reactor
{
    public function onOrderPlaced(OrderPlaced $event, StoredEvent $storedEvent): void
    {
        $total = number_format($event->totalInCents / 100, 2);
        $itemCount = count($event->items);

        Log::info('Order confirmation sent', [
            'order_uuid' => $storedEvent->aggregate_uuid,
            'user_id' => $event->userId,
            'total' => "\${$total}",
            'items' => $itemCount,
            'message' => "Order confirmation email sent to user {$event->userId} for \${$total} ({$itemCount} items)",
        ]);
    }
}
