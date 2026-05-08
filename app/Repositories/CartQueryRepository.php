<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;

final class CartQueryRepository
{
    public function findByUuid(string $uuid): ?Cart
    {
        return Cart::find($uuid);
    }

    /** @return Collection<int, Cart> */
    public function getActive(): Collection
    {
        return Cart::where('status', 'active')->get();
    }

    public function countActive(): int
    {
        return Cart::whereIn('status', ['active', 'checking_out'])->count();
    }

    public function findActiveWithItems(): ?Cart
    {
        $cart = Cart::where('status', 'active')
            ->whereNotNull('items')
            ->whereRaw("items != '{}' AND items != '[]'")
            ->first();

        return $cart instanceof Cart ? $cart : null;
    }

    public function findMostRecentActive(): ?Cart
    {
        $cart = Cart::where('status', 'active')->orderByDesc('updated_at')->first();

        return $cart instanceof Cart ? $cart : null;
    }
}
