<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Cart;

final readonly class CheckoutData
{
    public function __construct(
        public ?string $cartUuid,
        public ?string $userId,
    ) {}
}
