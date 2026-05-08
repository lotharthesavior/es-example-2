<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Order;

final readonly class RefundOrderData
{
    public function __construct(
        public ?string $orderUuid,
        public ?int $amountInCents,
        public ?string $reason,
    ) {}
}
