<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Cart;

use App\Domain\Cart\Aggregates\CartAggregate;
use App\Domain\Cart\Commands\AddItemToCart;
use App\Domain\Cart\Commands\ClearCart;
use App\Domain\Cart\Commands\RemoveItemFromCart;
use App\Domain\Cart\Commands\StartCheckout;
use App\Domain\Cart\Events\CartCleared;
use App\Domain\Cart\Events\CheckoutStarted;
use App\Domain\Cart\Events\ItemAddedToCart;
use App\Domain\Cart\Events\ItemRemovedFromCart;
use Tests\TestCase;

final class CartAggregateTest extends TestCase
{
    private string $cartUuid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartUuid = 'test-cart-uuid-'.uniqid();
    }

    public function test_can_add_item_to_cart(): void
    {
        CartAggregate::fake($this->cartUuid)
            ->given([])
            ->when(function (CartAggregate $aggregate): void {
                $aggregate->addItem(new AddItemToCart(
                    cartUuid: $this->cartUuid,
                    productId: 'product-1',
                    productName: 'Test Product',
                    quantity: 2,
                    priceInCents: 1999,
                ));
            })
            ->assertRecorded([
                new ItemAddedToCart(
                    productId: 'product-1',
                    productName: 'Test Product',
                    quantity: 2,
                    priceInCents: 1999,
                ),
            ]);
    }

    public function test_can_remove_item_from_cart(): void
    {
        CartAggregate::fake($this->cartUuid)
            ->given([
                new ItemAddedToCart(
                    productId: 'product-1',
                    productName: 'Test Product',
                    quantity: 1,
                    priceInCents: 1999,
                ),
            ])
            ->when(function (CartAggregate $aggregate): void {
                $aggregate->removeItem(new RemoveItemFromCart(
                    cartUuid: $this->cartUuid,
                    productId: 'product-1',
                ));
            })
            ->assertRecorded([
                new ItemRemovedFromCart(productId: 'product-1'),
            ]);
    }

    public function test_cannot_remove_item_not_in_cart(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/not in the cart/');

        CartAggregate::fake($this->cartUuid)
            ->given([])
            ->when(function (CartAggregate $aggregate): void {
                $aggregate->removeItem(new RemoveItemFromCart(
                    cartUuid: $this->cartUuid,
                    productId: 'non-existent-product',
                ));
            });
    }

    public function test_can_clear_cart(): void
    {
        CartAggregate::fake($this->cartUuid)
            ->given([
                new ItemAddedToCart(
                    productId: 'product-1',
                    productName: 'Test Product',
                    quantity: 1,
                    priceInCents: 1999,
                ),
            ])
            ->when(function (CartAggregate $aggregate): void {
                $aggregate->clearCart(new ClearCart(cartUuid: $this->cartUuid));
            })
            ->assertRecorded([new CartCleared]);
    }

    public function test_can_start_checkout(): void
    {
        CartAggregate::fake($this->cartUuid)
            ->given([
                new ItemAddedToCart(
                    productId: 'product-1',
                    productName: 'Test Product',
                    quantity: 1,
                    priceInCents: 1999,
                ),
            ])
            ->when(function (CartAggregate $aggregate): void {
                $aggregate->startCheckout(new StartCheckout(
                    cartUuid: $this->cartUuid,
                    userId: 'user-1',
                ));
            })
            ->assertRecorded([new CheckoutStarted(userId: 'user-1')]);
    }

    public function test_cannot_checkout_empty_cart(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/empty cart/');

        CartAggregate::fake($this->cartUuid)
            ->given([])
            ->when(function (CartAggregate $aggregate): void {
                $aggregate->startCheckout(new StartCheckout(
                    cartUuid: $this->cartUuid,
                    userId: 'user-1',
                ));
            });
    }

    public function test_cannot_modify_cart_after_checkout_started(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/started checkout/');

        CartAggregate::fake($this->cartUuid)
            ->given([
                new ItemAddedToCart(
                    productId: 'product-1',
                    productName: 'Test Product',
                    quantity: 1,
                    priceInCents: 1999,
                ),
                new CheckoutStarted(userId: 'user-1'),
            ])
            ->when(function (CartAggregate $aggregate): void {
                $aggregate->addItem(new AddItemToCart(
                    cartUuid: $this->cartUuid,
                    productId: 'product-2',
                    productName: 'Another Product',
                    quantity: 1,
                    priceInCents: 999,
                ));
            });
    }

    public function test_adding_same_product_increments_state(): void
    {
        $fakeAggregate = CartAggregate::fake($this->cartUuid)
            ->given([
                new ItemAddedToCart(
                    productId: 'product-1',
                    productName: 'Test Product',
                    quantity: 1,
                    priceInCents: 1999,
                ),
                new ItemAddedToCart(
                    productId: 'product-1',
                    productName: 'Test Product',
                    quantity: 2,
                    priceInCents: 1999,
                ),
            ]);

        /** @var CartAggregate $cartAggregate */
        $cartAggregate = $fakeAggregate->aggregateRoot();
        $items = $cartAggregate->getItems();
        $this->assertArrayHasKey('product-1', $items);
        $this->assertSame(3, $items['product-1']['quantity']);
    }

    public function test_items_cleared_after_cart_cleared(): void
    {
        $fakeAggregate = CartAggregate::fake($this->cartUuid)
            ->given([
                new ItemAddedToCart(
                    productId: 'product-1',
                    productName: 'Test Product',
                    quantity: 1,
                    priceInCents: 1999,
                ),
                new CartCleared,
            ]);

        /** @var CartAggregate $cartAggregate */
        $cartAggregate = $fakeAggregate->aggregateRoot();
        $this->assertEmpty($cartAggregate->getItems());
    }
}
