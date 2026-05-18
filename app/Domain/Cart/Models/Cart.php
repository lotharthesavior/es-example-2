<?php

declare(strict_types=1);

namespace App\Domain\Cart\Models;

use Illuminate\Support\Carbon;
use Spatie\EventSourcing\Projections\Projection;

/**
 * @property string $uuid
 * @property string $user_id
 * @property string $status
 * @property array<mixed>|null $items
 * @property int $total_in_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Cart extends Projection
{
    protected $table = 'carts';

    protected $fillable = [
        'uuid',
        'user_id',
        'status',
        'items',
        'total_in_cents',
    ];

    protected $casts = [
        'items' => 'array',
        'total_in_cents' => 'integer',
    ];
}
