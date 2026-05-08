<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DataTransferObjects\Results\EventHistoryResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read EventHistoryResult $resource
 */
final class EventHistoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'order_uuid' => $this->resource->orderUuid,
            'at' => $this->resource->at,
            'event_count' => $this->resource->eventCount,
            'history' => array_map(
                fn ($e) => [
                    'event' => $e['event'],
                    'occurred_at' => optional($e['occurred_at'])->toIso8601String(),
                    'data' => $e['data'],
                ],
                $this->resource->events,
            ),
        ];
    }
}
