<?php

declare(strict_types=1);

namespace App\Domain\Order\Reactors;

use App\Domain\Cart\Aggregates\CartAggregate;
use App\Domain\Order\Events\OrderCancelled;
use App\Reactors\BaseReactor;
use Illuminate\Support\Facades\Log;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class OrderCancelledReactor extends BaseReactor
{
    public function onOrderCancelled(OrderCancelled $event, StoredEvent $storedEvent): void
    {
        if ($event->cartUuid === '') {
            return;
        }

        CartAggregate::retrieve($event->cartUuid)
            ->cancelCheckout()
            ->persist();

        Log::info('Cart reopened after order cancellation', [
            'order_uuid' => $storedEvent->aggregate_uuid,
            'cart_uuid' => $event->cartUuid,
            'reason' => $event->reason,
        ]);
    }
}
