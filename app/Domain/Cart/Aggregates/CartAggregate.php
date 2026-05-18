<?php

declare(strict_types=1);

namespace App\Domain\Cart\Aggregates;

use App\Domain\Cart\Commands\AddItemToCart;
use App\Domain\Cart\Commands\ClearCart;
use App\Domain\Cart\Commands\RemoveItemFromCart;
use App\Domain\Cart\Commands\StartCheckout;
use App\Domain\Cart\Events\CartCleared;
use App\Domain\Cart\Events\CheckoutCancelled;
use App\Domain\Cart\Events\CheckoutStarted;
use App\Domain\Cart\Events\ItemAddedToCart;
use App\Domain\Cart\Events\ItemRemovedFromCart;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

final class CartAggregate extends AggregateRoot
{
    /** @var array<string, array{productId: string, productName: string, quantity: int, priceInCents: int}> */
    private array $items = [];

    private bool $checkedOut = false;

    public function addItem(AddItemToCart $command): self
    {
        if ($this->checkedOut) {
            throw new \DomainException('Cannot modify a cart that has started checkout.');
        }

        $this->recordThat(new ItemAddedToCart(
            productId: $command->productId,
            productName: $command->productName,
            quantity: $command->quantity,
            priceInCents: $command->priceInCents,
        ));

        return $this;
    }

    public function removeItem(RemoveItemFromCart $command): self
    {
        if ($this->checkedOut) {
            throw new \DomainException('Cannot modify a cart that has started checkout.');
        }

        if (! array_key_exists($command->productId, $this->items)) {
            throw new \DomainException("Product {$command->productId} is not in the cart.");
        }

        $this->recordThat(new ItemRemovedFromCart(
            productId: $command->productId,
            quantity: $command->quantity,
        ));

        return $this;
    }

    public function clearCart(ClearCart $command): self
    {
        if ($this->checkedOut) {
            throw new \DomainException('Cannot modify a cart that has started checkout.');
        }

        $this->recordThat(new CartCleared);

        return $this;
    }

    public function startCheckout(StartCheckout $command): self
    {
        if ($this->checkedOut) {
            throw new \DomainException('Checkout has already been started.');
        }

        if (empty($this->items)) {
            throw new \DomainException('Cannot checkout an empty cart.');
        }

        $this->recordThat(new CheckoutStarted(
            userId: $command->userId,
        ));

        return $this;
    }

    public function cancelCheckout(): self
    {
        if (! $this->checkedOut) {
            throw new \DomainException('Checkout has not been started.');
        }

        $this->recordThat(new CheckoutCancelled);

        return $this;
    }

    protected function applyCheckoutCancelled(CheckoutCancelled $event): void
    {
        $this->checkedOut = false;
    }

    protected function applyItemAddedToCart(ItemAddedToCart $event): void
    {
        if (isset($this->items[$event->productId])) {
            $this->items[$event->productId]['quantity'] += $event->quantity;
        } else {
            $this->items[$event->productId] = [
                'productId' => $event->productId,
                'productName' => $event->productName,
                'quantity' => $event->quantity,
                'priceInCents' => $event->priceInCents,
            ];
        }
    }

    protected function applyItemRemovedFromCart(ItemRemovedFromCart $event): void
    {
        if (! isset($this->items[$event->productId])) {
            return;
        }

        if ($event->quantity === null || $event->quantity >= $this->items[$event->productId]['quantity']) {
            unset($this->items[$event->productId]);

            return;
        }

        $this->items[$event->productId]['quantity'] -= $event->quantity;
    }

    protected function applyCartCleared(CartCleared $event): void
    {
        $this->items = [];
    }

    protected function applyCheckoutStarted(CheckoutStarted $event): void
    {
        $this->checkedOut = true;
    }

    /** @return array<string, array{productId: string, productName: string, quantity: int, priceInCents: int}> */
    public function getItems(): array
    {
        return $this->items;
    }

    public function isCheckedOut(): bool
    {
        return $this->checkedOut;
    }
}
