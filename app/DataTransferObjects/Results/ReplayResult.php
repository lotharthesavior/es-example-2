<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Results;

final readonly class ReplayResult
{
    public function __construct(
        public int $deletedOrders,
        public int $deletedCarts,
    ) {}
}
