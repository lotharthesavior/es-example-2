<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final class ProductRepository
{
    public function findById(int $id): ?Product
    {
        return Product::query()->where('id', $id)->first();
    }

    public function findRandomActive(): ?Product
    {
        return Product::query()->where('active', true)->inRandomOrder()->first();
    }

    /** @return Collection<int, Product> */
    public function getActive(): Collection
    {
        return Product::where('active', true)->get();
    }
}
