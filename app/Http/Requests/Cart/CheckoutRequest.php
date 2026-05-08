<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\DataTransferObjects\Cart\CheckoutData;
use Illuminate\Foundation\Http\FormRequest;

final class CheckoutRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'cart_uuid' => ['nullable', 'uuid'],
            'user_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDto(): CheckoutData
    {
        $v = $this->validated();

        return new CheckoutData(
            cartUuid: $v['cart_uuid'] ?? null,
            userId: $v['user_id'] ?? null,
        );
    }
}
