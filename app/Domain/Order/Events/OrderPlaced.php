<?php

declare(strict_types=1);

namespace App\Domain\Order\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class OrderPlaced extends ShouldBeStored
{
    /**
     * @param  array<int, array{productId: string, productName: string, quantity: int, priceInCents: int}>  $items
     */
    public function __construct(
        public readonly string $userId,
        public readonly string $cartUuid,
        public readonly array $items,
        public readonly int $totalInCents,
    ) {}
}
