<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Results;

use App\Domain\Order\Models\Order;

final readonly class CheckoutResult
{
    public function __construct(
        public string $orderUuid,
        public string $cartUuid,
        public int $totalInCents,
        public ?Order $order,
    ) {}
}
