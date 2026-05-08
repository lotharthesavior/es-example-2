<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Results;

use App\Models\Cart;
use App\Models\Order;

final readonly class DashboardStateResult
{
    public function __construct(
        public ?Cart $cart,
        public ?Order $order,
    ) {}
}
