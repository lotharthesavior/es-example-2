<?php

declare(strict_types=1);

namespace App\Domain\Cart\Commands;

use App\Domain\Cart\Aggregates\CartAggregate;
use Spatie\EventSourcing\Commands\AggregateUuid;
use Spatie\EventSourcing\Commands\HandledBy;

#[HandledBy(CartAggregate::class)]
final readonly class RemoveItemFromCart
{
    public function __construct(
        #[AggregateUuid] public string $cartUuid,
        public string $productId,
        public int $quantity = 1,
    ) {}
}
