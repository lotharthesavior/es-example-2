<?php

declare(strict_types=1);

namespace App\Projectors;

use App\Domain\Cart\Events\CartCleared;
use App\Domain\Cart\Events\CheckoutStarted;
use App\Domain\Cart\Events\ItemAddedToCart;
use App\Domain\Cart\Events\ItemRemovedFromCart;
use App\Models\Cart;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

final class CartProjector extends BaseProjector
{
    public function onItemAddedToCart(ItemAddedToCart $event, StoredEvent $storedEvent): void
    {
        /** @var Cart $cart */
        $cart = Cart::query()->firstOrCreate(
            ['uuid' => $storedEvent->aggregate_uuid],
            [
                'uuid' => $storedEvent->aggregate_uuid,
                'user_id' => 'anonymous',
                'status' => 'active',
                'items' => [],
                'total_in_cents' => 0,
            ]
        );

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
        $cart->save();
    }

    public function onItemRemovedFromCart(ItemRemovedFromCart $event, StoredEvent $storedEvent): void
    {
        /** @var Cart|null $cart */
        $cart = Cart::query()->where('uuid', $storedEvent->aggregate_uuid)->first();
        if ($cart === null) {
            return;
        }

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
        $cart->save();
    }

    public function onCartCleared(CartCleared $event, StoredEvent $storedEvent): void
    {
        /** @var Cart|null $cart */
        $cart = Cart::query()->where('uuid', $storedEvent->aggregate_uuid)->first();
        if ($cart === null) {
            return;
        }

        $cart->items = [];
        $cart->total_in_cents = 0;
        $cart->save();
    }

    public function onCheckoutStarted(CheckoutStarted $event, StoredEvent $storedEvent): void
    {
        /** @var Cart|null $cart */
        $cart = Cart::query()->where('uuid', $storedEvent->aggregate_uuid)->first();
        if ($cart === null) {
            return;
        }

        $cart->user_id = $event->userId;
        $cart->status = 'checking_out';
        $cart->save();
    }
}
