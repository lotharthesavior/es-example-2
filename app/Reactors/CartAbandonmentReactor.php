<?php

declare(strict_types=1);

namespace App\Reactors;

use App\Domain\Cart\Events\CheckoutStarted;
use App\Models\Cart;
use Illuminate\Support\Facades\Log;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class CartAbandonmentReactor extends Reactor
{
    public function onCheckoutStarted(CheckoutStarted $event, StoredEvent $storedEvent): void
    {
        $cart = Cart::find($storedEvent->aggregate_uuid);

        if ($cart !== null) {
            $cart->status = 'converting';
            $cart->save();
        }

        Log::info('Cart converting to order', [
            'cart_uuid' => $storedEvent->aggregate_uuid,
            'user_id' => $event->userId,
        ]);
    }
}
