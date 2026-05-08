<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\DataTransferObjects\Order\MarkAsShippedData;
use Illuminate\Foundation\Http\FormRequest;

final class MarkAsShippedRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'order_uuid' => ['nullable', 'uuid'],
        ];
    }

    public function toDto(): MarkAsShippedData
    {
        return new MarkAsShippedData(
            orderUuid: $this->validated('order_uuid'),
        );
    }
}
