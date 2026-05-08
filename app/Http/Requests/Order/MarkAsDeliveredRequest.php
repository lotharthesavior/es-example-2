<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\DataTransferObjects\Order\MarkAsDeliveredData;
use Illuminate\Foundation\Http\FormRequest;

final class MarkAsDeliveredRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'order_uuid' => ['nullable', 'uuid'],
        ];
    }

    public function toDto(): MarkAsDeliveredData
    {
        return new MarkAsDeliveredData(
            orderUuid: $this->validated('order_uuid'),
        );
    }
}
