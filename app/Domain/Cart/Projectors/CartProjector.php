<?php

declare(strict_types=1);

namespace App\Domain\Cart\Projectors;

use App\Domain\Cart\Events\CartCleared;
use App\Domain\Cart\Events\CheckoutCancelled;
use App\Domain\Cart\Events\CheckoutStarted;
use App\Domain\Cart\Events\ItemAddedToCart;
use App\Domain\Cart\Events\ItemRemovedFromCart;
use App\Domain\Cart\Models\Cart;
use App\Projectors\BaseProjector;
use Illuminate\Support\Carbon;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class CartProjector extends BaseProjector
{
    public function onItemAddedToCart(ItemAddedToCart $event, StoredEvent $storedEvent): void
    {
        $occurredAt = Carbon::parse($storedEvent->created_at);

        $existing = Cart::find($storedEvent->aggregate_uuid);

        if ($existing === null) {
            $cart = Cart::new()->writeable();
            $cart->fill([
                'uuid' => $storedEvent->aggregate_uuid,
                'user_id' => 'anonymous',
                'status' => 'active',
                'items' => [],
                'total_in_cents' => 0,
            ]);
            $cart->created_at = $occurredAt;
        } else {
            $cart = $existing->writeable();
        }

        /** @var array<string, array{productId: string, productName: string, quantity: int, priceInCents: int}> $items */
        $items = is_array($cart->items) ? $cart->items : [];

        if (isset($items[$event->productId])) {
            $items[$event->productId]['quantity'] += $event->quantity;
        } else {
            $items[$event->productId] = [
                'productId' => $event->productId,
                'productName' => $event->productName,
                'quantity' => $event->quantity,
                'priceInCents' => $event->priceInCents,
            ];
        }

        $total = array_sum(array_map(
            /** @param array{priceInCents: int, quantity: int} $item */
            static fn (array $item): int => $item['priceInCents'] * $item['quantity'],
            $items
        ));

        $cart->items = $items;
        $cart->total_in_cents = $total;
        $cart->updated_at = $occurredAt;
        $cart->timestamps = false;
        $cart->save();
    }

    public function onItemRemovedFromCart(ItemRemovedFromCart $event, StoredEvent $storedEvent): void
    {
        $cart = Cart::find($storedEvent->aggregate_uuid);
        if ($cart === null) {
            return;
        }

        $cart = $cart->writeable();

        /** @var array<string, array{productId: string, productName: string, quantity: int, priceInCents: int}> $items */
        $items = is_array($cart->items) ? $cart->items : [];

        if (isset($items[$event->productId])) {
            if ($event->quantity === null || $event->quantity >= $items[$event->productId]['quantity']) {
                unset($items[$event->productId]);
            } else {
                $items[$event->productId]['quantity'] -= $event->quantity;
            }
        }

        $total = array_sum(array_map(
            /** @param array{priceInCents: int, quantity: int} $item */
            static fn (array $item): int => $item['priceInCents'] * $item['quantity'],
            $items
        ));

        $cart->items = $items;
        $cart->total_in_cents = $total;
        $cart->updated_at = Carbon::parse($storedEvent->created_at);
        $cart->timestamps = false;
        $cart->save();
    }

    public function onCartCleared(CartCleared $event, StoredEvent $storedEvent): void
    {
        $cart = Cart::find($storedEvent->aggregate_uuid);
        if ($cart === null) {
            return;
        }

        $cart = $cart->writeable();
        $cart->items = [];
        $cart->total_in_cents = 0;
        $cart->updated_at = Carbon::parse($storedEvent->created_at);
        $cart->timestamps = false;
        $cart->save();
    }

    public function onCheckoutStarted(CheckoutStarted $event, StoredEvent $storedEvent): void
    {
        $cart = Cart::find($storedEvent->aggregate_uuid);
        if ($cart === null) {
            return;
        }

        $cart = $cart->writeable();
        $cart->user_id = $event->userId;
        $cart->status = 'checking_out';
        $cart->updated_at = Carbon::parse($storedEvent->created_at);
        $cart->timestamps = false;
        $cart->save();
    }

    public function onCheckoutCancelled(CheckoutCancelled $event, StoredEvent $storedEvent): void
    {
        $cart = Cart::find($storedEvent->aggregate_uuid);
        if ($cart === null) {
            return;
        }

        $cart = $cart->writeable();
        $cart->status = 'active';
        $cart->updated_at = Carbon::parse($storedEvent->created_at);
        $cart->timestamps = false;
        $cart->save();
    }
}
