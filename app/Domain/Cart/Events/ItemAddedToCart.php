<?php

declare(strict_types=1);

namespace App\Domain\Cart\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class ItemAddedToCart extends ShouldBeStored
{
    public function __construct(
        public readonly string $productId,
        public readonly string $productName,
        public readonly int $quantity,
        public readonly int $priceInCents,
    ) {}
}
