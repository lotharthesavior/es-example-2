<?php

declare(strict_types=1);

namespace App\Domain\Order\Commands;

use App\Domain\Order\Aggregates\OrderAggregate;
use Spatie\EventSourcing\Commands\AggregateUuid;
use Spatie\EventSourcing\Commands\HandledBy;

#[HandledBy(OrderAggregate::class)]
final readonly class MarkAsPaid
{
    public function __construct(
        #[AggregateUuid] public string $orderUuid,
        public string $paymentReference,
    ) {}
}
