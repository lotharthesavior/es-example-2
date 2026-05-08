<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $price_in_cents
 * @property string|null $description
 * @property string|null $image_url
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'slug',
        'price_in_cents',
        'description',
        'image_url',
        'active',
    ];

    protected $casts = [
        'price_in_cents' => 'integer',
        'active' => 'boolean',
    ];

    public function formattedPrice(): string
    {
        return '$'.number_format($this->price_in_cents / 100, 2);
    }
}
