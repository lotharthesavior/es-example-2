<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DataTransferObjects\Results\DashboardStatsResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read DashboardStatsResult $resource
 */
final class DashboardStatsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'totalEvents' => $this->resource->totalEvents,
            'activeCarts' => $this->resource->activeCarts,
            'ordersToday' => $this->resource->ordersToday,
            'revenueToday' => $this->resource->revenueTodayFormatted,
        ];
    }
}
