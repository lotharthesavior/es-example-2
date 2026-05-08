<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Cart;

final readonly class AddToCartData
{
    public function __construct(
        public ?int $productId,
        public ?string $cartUuid,
        public int $quantity = 1,
    ) {}
}
