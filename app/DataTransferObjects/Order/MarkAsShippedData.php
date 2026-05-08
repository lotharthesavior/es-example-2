<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Order;

final readonly class MarkAsShippedData
{
    public function __construct(
        public ?string $orderUuid,
    ) {}
}
