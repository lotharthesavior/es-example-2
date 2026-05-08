<?php

declare(strict_types=1);

namespace App\Domain\Order\Enums;

enum OrderStatus: string
{
    case Placed = 'placed';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Placed => 'Placed',
            self::Paid => 'Paid',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Placed => 'green',
            self::Paid => 'blue',
            self::Shipped => 'purple',
            self::Delivered => 'teal',
            self::Cancelled => 'red',
            self::Refunded => 'orange',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Placed => in_array($next, [self::Paid, self::Cancelled]),
            self::Paid => in_array($next, [self::Shipped, self::Cancelled]),
            self::Shipped => in_array($next, [self::Delivered]),
            self::Delivered => in_array($next, [self::Refunded]),
            self::Cancelled => false,
            self::Refunded => false,
        };
    }
}
