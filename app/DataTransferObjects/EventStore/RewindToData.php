<?php

declare(strict_types=1);

namespace App\DataTransferObjects\EventStore;

final readonly class RewindToData
{
    public function __construct(
        public string $orderUuid,
        public string $timestamp,
    ) {}
}
