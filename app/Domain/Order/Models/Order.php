<?php

declare(strict_types=1);

namespace App\Domain\Order\Models;

use App\Domain\Order\Enums\OrderStatus;
use Illuminate\Support\Carbon;
use Spatie\EventSourcing\Projections\Projection;

/**
 * @property string $uuid
 * @property string $user_id
 * @property string $cart_uuid
 * @property array<mixed>|null $items
 * @property int $total_in_cents
 * @property OrderStatus $status
 * @property string|null $payment_reference
 * @property string|null $tracking_number
 * @property Carbon|null $paid_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Order extends Projection
{
    protected $table = 'orders';

    protected $fillable = [
        'uuid',
        'user_id',
        'cart_uuid',
        'items',
        'total_in_cents',
        'status',
        'payment_reference',
        'tracking_number',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'items' => 'array',
        'total_in_cents' => 'integer',
        'status' => OrderStatus::class,
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
}
