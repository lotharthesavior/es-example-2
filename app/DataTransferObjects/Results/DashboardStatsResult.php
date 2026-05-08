<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Results;

final readonly class DashboardStatsResult
{
    public function __construct(
        public int $totalEvents,
        public int $activeCarts,
        public int $ordersToday,
        public int $revenueTodayInCents,
        public string $revenueTodayFormatted,
    ) {}
}
