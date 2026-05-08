<?php

declare(strict_types=1);

namespace Tests\Unit\Projectors;

use App\Domain\Cart\Events\CartCleared;
use App\Domain\Cart\Events\CheckoutStarted;
use App\Domain\Cart\Events\ItemAddedToCart;
use App\Domain\Cart\Events\ItemRemovedFromCart;
use App\Models\Cart;
use App\Projectors\CartProjector;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Tests\TestCase;

final class CartProjectorTest extends TestCase
{
    private CartProjector $projector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projector = new CartProjector;
    }

    private function makeStoredEvent(string $aggregateUuid, ShouldBeStored $event): StoredEvent
    {
        return new StoredEvent(
            [
                'id' => 1,
                'event_properties' => '{}',
                'aggregate_uuid' => $aggregateUuid,
                'aggregate_version' => '1',
                'event_version' => 1,
                'event_class' => get_class($event),
                'meta_data' => '{}',
                'created_at' => now()->toIso8601String(),
            ],
            $event
        );
    }

    public function test_creates_cart_on_first_item_added(): void
    {
        $cartUuid = 'test-cart-uuid';
        $event = new ItemAddedToCart('product-1', 'Test Product', 1, 9999);
        $storedEvent = $this->makeStoredEvent($cartUuid, $event);

        $this->projector->onItemAddedToCart($event, $storedEvent);

        /** @var Cart $cart */
        $cart = Cart::query()->where('uuid', $cartUuid)->first();
        $this->assertNotNull($cart);
        $this->assertSame(9999, $cart->total_in_cents);
        $items = is_array($cart->items) ? $cart->items : [];
        $this->assertArrayHasKey('product-1', $items);
    }

    public function test_increments_total_when_item_added(): void
    {
        $cartUuid = 'test-cart-uuid';

        $event1 = new ItemAddedToCart('product-1', 'Product 1', 1, 5000);
        $event2 = new ItemAddedToCart('product-2', 'Product 2', 2, 3000);

        $this->projector->onItemAddedToCart($event1, $this->makeStoredEvent($cartUuid, $event1));
        $this->projector->onItemAddedToCart($event2, $this->makeStoredEvent($cartUuid, $event2));

        /** @var Cart $cart */
        $cart = Cart::query()->where('uuid', $cartUuid)->first();
        $this->assertSame(11000, $cart->total_in_cents); // 5000 + 2*3000
    }

    public function test_removes_item_and_updates_total(): void
    {
        $cartUuid = 'test-cart-uuid';

        $addEvent1 = new ItemAddedToCart('product-1', 'Product 1', 1, 5000);
        $addEvent2 = new ItemAddedToCart('product-2', 'Product 2', 1, 3000);
        $removeEvent = new ItemRemovedFromCart('product-1');

        $this->projector->onItemAddedToCart($addEvent1, $this->makeStoredEvent($cartUuid, $addEvent1));
        $this->projector->onItemAddedToCart($addEvent2, $this->makeStoredEvent($cartUuid, $addEvent2));
        $this->projector->onItemRemovedFromCart($removeEvent, $this->makeStoredEvent($cartUuid, $removeEvent));

        /** @var Cart $cart */
        $cart = Cart::query()->where('uuid', $cartUuid)->first();
        $this->assertSame(3000, $cart->total_in_cents);
        $items = is_array($cart->items) ? $cart->items : [];
        $this->assertArrayNotHasKey('product-1', $items);
    }

    public function test_clears_all_items(): void
    {
        $cartUuid = 'test-cart-uuid';

        $addEvent = new ItemAddedToCart('product-1', 'Product 1', 1, 5000);
        $clearEvent = new CartCleared;

        $this->projector->onItemAddedToCart($addEvent, $this->makeStoredEvent($cartUuid, $addEvent));
        $this->projector->onCartCleared($clearEvent, $this->makeStoredEvent($cartUuid, $clearEvent));

        /** @var Cart $cart */
        $cart = Cart::query()->where('uuid', $cartUuid)->first();
        $this->assertSame(0, $cart->total_in_cents);
        $items = is_array($cart->items) ? $cart->items : [];
        $this->assertEmpty($items);
    }

    public function test_updates_status_on_checkout_started(): void
    {
        $cartUuid = 'test-cart-uuid';

        $addEvent = new ItemAddedToCart('product-1', 'Product 1', 1, 5000);
        $checkoutEvent = new CheckoutStarted(userId: 'user-1');

        $this->projector->onItemAddedToCart($addEvent, $this->makeStoredEvent($cartUuid, $addEvent));
        $this->projector->onCheckoutStarted($checkoutEvent, $this->makeStoredEvent($cartUuid, $checkoutEvent));

        /** @var Cart $cart */
        $cart = Cart::query()->where('uuid', $cartUuid)->first();
        $this->assertSame('checking_out', $cart->status);
        $this->assertSame('user-1', $cart->user_id);
    }
}
