<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Results;

final readonly class EventHistoryResult
{
    /**
     * @param  array<int, array{event: string, occurred_at: mixed, data: array<string, mixed>}>  $events
     */
    public function __construct(
        public string $orderUuid,
        public string $at,
        public int $eventCount,
        public array $events,
    ) {}
}
