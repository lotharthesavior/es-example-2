<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DataTransferObjects\Results\ReplayResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ReplayResult $resource
 */
final class ReplayResultResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'replayed' => true,
            'deleted_orders' => $this->resource->deletedOrders,
            'deleted_carts' => $this->resource->deletedCarts,
        ];
    }
}
