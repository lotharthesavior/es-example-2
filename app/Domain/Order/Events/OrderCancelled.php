<?php

declare(strict_types=1);

namespace App\Domain\Order\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class OrderCancelled extends ShouldBeStored
{
    public function __construct(
        public readonly string $reason,
    ) {}
}
