<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DataTransferObjects\Results\DashboardStateResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read DashboardStateResult $resource
 */
final class DashboardStateResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'cart' => $this->resource->cart !== null
                ? new CartResource($this->resource->cart)
                : null,
            'order' => $this->resource->order !== null
                ? new OrderResource($this->resource->order)
                : null,
        ];
    }
}
