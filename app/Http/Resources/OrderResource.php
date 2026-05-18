<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Order\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Order $resource
 */
final class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $rawItems = $this->resource->items ?? [];

        $items = [];
        foreach ($rawItems as $item) {
            if (! is_array($item)) {
                continue;
            }
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (int) ($item['priceInCents'] ?? 0);
            $items[] = [
                'productId' => (string) ($item['productId'] ?? ''),
                'productName' => (string) ($item['productName'] ?? ''),
                'quantity' => $qty,
                'priceInCents' => $price,
                'lineTotal' => $qty * $price,
            ];
        }

        $total = (int) $this->resource->total_in_cents;

        return [
            'uuid' => $this->resource->uuid,
            'order_uuid' => $this->resource->uuid,
            'status' => $this->resource->status->value,
            'totalInCents' => $total,
            'totalFormatted' => '$'.number_format($total / 100, 2),
            'items' => $items,
            'paymentReference' => $this->resource->payment_reference,
            'trackingNumber' => $this->resource->tracking_number,
            'timestamps' => [
                'placed' => optional($this->resource->created_at)->toIso8601String(),
                'paid' => optional($this->resource->paid_at)->toIso8601String(),
                'shipped' => optional($this->resource->shipped_at)->toIso8601String(),
                'delivered' => optional($this->resource->delivered_at)->toIso8601String(),
                'cancelled' => optional($this->resource->cancelled_at)->toIso8601String(),
            ],
            'cancellationReason' => $this->resource->cancellation_reason,
        ];
    }
}
