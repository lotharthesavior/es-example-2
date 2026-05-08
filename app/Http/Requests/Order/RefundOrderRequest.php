<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\DataTransferObjects\Order\RefundOrderData;
use Illuminate\Foundation\Http\FormRequest;

final class RefundOrderRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'order_uuid' => ['nullable', 'uuid'],
            'amount_in_cents' => ['nullable', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toDto(): RefundOrderData
    {
        $v = $this->validated();

        return new RefundOrderData(
            orderUuid: $v['order_uuid'] ?? null,
            amountInCents: isset($v['amount_in_cents']) ? (int) $v['amount_in_cents'] : null,
            reason: $v['reason'] ?? null,
        );
    }
}
