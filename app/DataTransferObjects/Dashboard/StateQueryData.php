<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Dashboard;

final readonly class StateQueryData
{
    public function __construct(
        public ?string $cartUuid,
        public ?string $orderUuid,
    ) {}
}
