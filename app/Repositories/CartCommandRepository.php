<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DataTransferObjects\Cart\AddToCartData;
use App\DataTransferObjects\Cart\CheckoutData;
use App\DataTransferObjects\Cart\RemoveFromCartData;
use App\DataTransferObjects\Results\CheckoutResult;
use App\Domain\Cart\Commands\AddItemToCart;
use App\Domain\Cart\Commands\RemoveItemFromCart;
use App\Domain\Cart\Commands\StartCheckout;
use App\Domain\Order\Commands\PlaceOrder;
use App\Exceptions\Domain\NoActiveCartToCheckoutException;
use App\Exceptions\Domain\NoActiveCartWithItemsException;
use App\Exceptions\Domain\NoProductsAvailableException;
use App\Domain\Cart\Models\Cart;
use Illuminate\Support\Str;
use Spatie\EventSourcing\Commands\CommandBus;

final class CartCommandRepository
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly CartQueryRepository $query,
        private readonly ProductRepository $products,
        private readonly OrderQueryRepository $orders,
    ) {}

    public function addItem(AddToCartData $dto): Cart
    {
        $product = $dto->productId !== null
            ? $this->products->findById($dto->productId)
            : $this->products->findRandomActive();

        if ($product === null) {
            throw new NoProductsAvailableException;
        }

        $cartUuid = $dto->cartUuid ?? Str::uuid()->toString();

        $this->commandBus->dispatch(new AddItemToCart(
            cartUuid: $cartUuid,
            productId: (string) $product->id,
            productName: $product->name,
            quantity: $dto->quantity,
            priceInCents: $product->price_in_cents,
        ));

        return $this->query->findByUuid($cartUuid)
            ?? throw new \RuntimeException('Cart projection missing after dispatch');
    }

    public function removeItem(RemoveFromCartData $dto): Cart
    {
        $cartUuid = $dto->cartUuid;
        $productId = $dto->productId;

        if ($cartUuid === null) {
            $cart = $this->query->findActiveWithItems()
                ?? throw new NoActiveCartWithItemsException;
            $cartUuid = $cart->uuid;
        }

        if ($productId === null) {
            $cart = $this->query->findByUuid($cartUuid)
                ?? throw new NoActiveCartWithItemsException;
            /** @var array<string, array{productId: string}> $items */
            $items = $cart->items ?? [];
            $productId = (string) (array_key_first($items) ?? '');
        }

        $this->commandBus->dispatch(new RemoveItemFromCart(
            cartUuid: $cartUuid,
            productId: $productId,
        ));

        return $this->query->findByUuid($cartUuid)
            ?? throw new \RuntimeException('Cart projection missing after dispatch');
    }

    public function checkout(CheckoutData $dto): CheckoutResult
    {
        $cartUuid = $dto->cartUuid;
        if ($cartUuid === null) {
            $cart = $this->query->findActiveWithItems();
            if ($cart === null) {
                throw new NoActiveCartToCheckoutException;
            }
            $cartUuid = $cart->uuid;
        }

        $userId = $dto->userId ?? ('user-'.Str::random(6));
        $orderUuid = Str::uuid()->toString();

        $this->commandBus->dispatch(new StartCheckout(
            cartUuid: $cartUuid,
            userId: $userId,
        ));

        $cart = $this->query->findByUuid($cartUuid);
        /** @var array<string, array{productId: string, productName: string, quantity: int, priceInCents: int}> $items */
        $items = $cart === null ? [] : ($cart->items ?? []);
        $total = $cart === null ? 0 : (int) $cart->total_in_cents;

        $this->commandBus->dispatch(new PlaceOrder(
            orderUuid: $orderUuid,
            userId: $userId,
            cartUuid: $cartUuid,
            items: array_values($items),
            totalInCents: $total,
        ));

        $order = $this->orders->findByUuid($orderUuid);

        return new CheckoutResult($orderUuid, $cartUuid, $total, $order);
    }
}
