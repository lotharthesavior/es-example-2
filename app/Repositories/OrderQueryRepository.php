<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Order\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

final class OrderQueryRepository
{
    public function findByUuid(string $uuid): ?Order
    {
        return Order::find($uuid);
    }

    /** @return Collection<int, Order> */
    public function getToday(): Collection
    {
        return Order::whereDate('created_at', today())->get();
    }

    public function countToday(): int
    {
        return Order::whereDate('created_at', today())->count();
    }

    public function revenueToday(): int
    {
        return (int) Order::whereDate('created_at', today())
            ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Refunded->value])
            ->sum('total_in_cents');
    }

    /** @return Collection<int, Order> */
    public function getRecent(int $limit = 10): Collection
    {
        return Order::latest()->limit($limit)->get();
    }

    public function findLatestByStatus(OrderStatus $status): ?Order
    {
        $order = Order::where('status', $status->value)->latest()->first();

        return $order instanceof Order ? $order : null;
    }

    public function findMostRecentForCart(string $cartUuid): ?Order
    {
        $order = Order::where('cart_uuid', $cartUuid)->orderByDesc('updated_at')->first();

        return $order instanceof Order ? $order : null;
    }
}
