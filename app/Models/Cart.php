<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string $user_id
 * @property string $status
 * @property array<mixed>|null $items
 * @property int $total_in_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Cart extends Model
{
    protected $table = 'carts';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

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
