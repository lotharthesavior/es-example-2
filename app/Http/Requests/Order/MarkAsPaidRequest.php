<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\DataTransferObjects\Order\MarkAsPaidData;
use Illuminate\Foundation\Http\FormRequest;

final class MarkAsPaidRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'order_uuid' => ['nullable', 'uuid'],
        ];
    }

    public function toDto(): MarkAsPaidData
    {
        return new MarkAsPaidData(
            orderUuid: $this->validated('order_uuid'),
        );
    }
}
