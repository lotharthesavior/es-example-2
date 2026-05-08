<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\DataTransferObjects\Dashboard\StateQueryData;
use Illuminate\Foundation\Http\FormRequest;

final class StateRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'cart_uuid' => ['nullable', 'uuid'],
            'order_uuid' => ['nullable', 'uuid'],
        ];
    }

    public function toDto(): StateQueryData
    {
        return new StateQueryData(
            cartUuid: $this->query('cart_uuid'),
            orderUuid: $this->query('order_uuid'),
        );
    }
}
