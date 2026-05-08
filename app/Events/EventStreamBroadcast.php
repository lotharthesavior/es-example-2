<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class EventStreamBroadcast implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly string $eventName,
        public readonly string $aggregateType,
        public readonly string $aggregateUuid,
        /** @var array<string, mixed> */
        public readonly array $payload,
        public readonly string $occurredAt,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel('event-stream')];
    }

    public function broadcastAs(): string
    {
        return 'event.recorded';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'eventName' => $this->eventName,
            'aggregateType' => $this->aggregateType,
            'aggregateUuid' => $this->aggregateUuid,
            'payload' => $this->payload,
            'occurredAt' => $this->occurredAt,
        ];
    }
}
