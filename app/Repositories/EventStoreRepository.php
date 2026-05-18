<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DataTransferObjects\Results\EventHistoryResult;
use App\DataTransferObjects\Results\ReplayResult;
use App\Domain\Cart\Models\Cart;
use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

final class EventStoreRepository
{
    /** @return Collection<int, EloquentStoredEvent> */
    public function getRecent(int $limit = 50): Collection
    {
        return EloquentStoredEvent::latest()->limit($limit)->get();
    }

    public function countAll(): int
    {
        return EloquentStoredEvent::count();
    }

    public function getHistoryForAggregateUntil(string $aggregateUuid, string $timestamp): EventHistoryResult
    {
        $events = EloquentStoredEvent::query()
            ->where('aggregate_uuid', $aggregateUuid)
            ->where('created_at', '<=', $timestamp)
            ->orderBy('id')
            ->get();

        $history = $events->map(fn ($e) => [
            'event' => class_basename($e->event_class),
            'occurred_at' => $e->created_at,
            'data' => $e->event_properties,
        ])->all();

        return new EventHistoryResult(
            orderUuid: $aggregateUuid,
            at: $timestamp,
            eventCount: $events->count(),
            events: $history,
        );
    }

    public function replayAll(): ReplayResult
    {
        $deletedOrders = Order::query()->count();
        $deletedCarts = Cart::query()->count();

        DB::table('orders')->delete();
        DB::table('carts')->delete();
        DB::table('order_history')->delete();

        Artisan::call('event-sourcing:replay', ['--no-interaction' => true]);

        return new ReplayResult(
            deletedOrders: $deletedOrders,
            deletedCarts: $deletedCarts,
        );
    }
}
