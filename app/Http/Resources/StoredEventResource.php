<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

/**
 * @property-read EloquentStoredEvent $resource
 */
final class StoredEventResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'eventClass' => $this->resource->event_class,
            'eventName' => class_basename($this->resource->event_class),
            'aggregateUuid' => $this->resource->aggregate_uuid,
            'eventProperties' => $this->resource->event_properties,
            'createdAt' => optional($this->resource->created_at)->toIso8601String(),
        ];
    }
}
