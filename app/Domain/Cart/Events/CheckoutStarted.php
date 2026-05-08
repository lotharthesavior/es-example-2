<?php

declare(strict_types=1);

namespace App\Domain\Cart\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class CheckoutStarted extends ShouldBeStored
{
    public function __construct(
        public readonly string $userId,
    ) {}
}
