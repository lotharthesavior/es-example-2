<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Cart\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Cart $resource
 */
final class CartResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $rawItems = is_array($this->resource->items) ? $this->resource->items : [];
        $items = [];
        $count = 0;

        foreach ($rawItems as $item) {
            if (! is_array($item)) {
                continue;
            }
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (int) ($item['priceInCents'] ?? 0);
            $count += $qty;
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
            'cart_uuid' => $this->resource->uuid,
            'status' => $this->resource->status,
            'items' => $items,
            'totalInCents' => $total,
            'totalFormatted' => '$'.number_format($total / 100, 2),
            'itemCount' => $count,
        ];
    }
}
