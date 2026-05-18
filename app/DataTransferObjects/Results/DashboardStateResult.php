<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Results;

use App\Domain\Cart\Models\Cart;
use App\Domain\Order\Models\Order;

final readonly class DashboardStateResult
{
    public function __construct(
        public ?Cart $cart,
        public ?Order $order,
    ) {}
}
