<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DataTransferObjects\Results\CheckoutResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read CheckoutResult $resource
 */
final class CheckoutResultResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'order_uuid' => $this->resource->orderUuid,
            'cart_uuid' => $this->resource->cartUuid,
            'total' => '$'.number_format($this->resource->totalInCents / 100, 2),
            'order' => $this->resource->order !== null
                ? new OrderResource($this->resource->order)
                : null,
        ];
    }
}
