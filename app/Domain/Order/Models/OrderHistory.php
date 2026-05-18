<?php

declare(strict_types=1);

namespace App\Domain\Order\Models;

use Illuminate\Support\Carbon;
use Spatie\EventSourcing\Projections\Projection;

/**
 * @property int $id
 * @property string $order_uuid
 * @property string $event_type
 * @property string $event_data
 * @property Carbon|null $occurred_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class OrderHistory extends Projection
{
    protected $table = 'order_history';

    protected $fillable = [
        'order_uuid',
        'event_type',
        'event_data',
        'occurred_at',
    ];

    protected $casts = [
        'event_data' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function getKeyName(): string
    {
        return 'id';
    }

    public function getKeyType(): string
    {
        return 'int';
    }

    public function getIncrementing(): bool
    {
        return true;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
