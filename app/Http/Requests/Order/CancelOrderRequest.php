<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\DataTransferObjects\Order\CancelOrderData;
use Illuminate\Foundation\Http\FormRequest;

final class CancelOrderRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'order_uuid' => ['nullable', 'uuid'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toDto(): CancelOrderData
    {
        $v = $this->validated();

        return new CancelOrderData(
            orderUuid: $v['order_uuid'] ?? null,
            reason: $v['reason'] ?? null,
        );
    }
}
