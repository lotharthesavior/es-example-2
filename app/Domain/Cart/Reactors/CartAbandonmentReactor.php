<?php

declare(strict_types=1);

namespace App\Domain\Cart\Reactors;

use App\Domain\Cart\Events\CheckoutStarted;
use App\Reactors\BaseReactor;
use Illuminate\Support\Facades\Log;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class CartAbandonmentReactor extends BaseReactor
{
    public function onCheckoutStarted(CheckoutStarted $event, StoredEvent $storedEvent): void
    {
        Log::info('Cart converting to order', [
            'cart_uuid' => $storedEvent->aggregate_uuid,
            'user_id' => $event->userId,
        ]);
    }
}
