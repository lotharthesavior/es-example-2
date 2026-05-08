<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\DataTransferObjects\Cart\RemoveFromCartData;
use Illuminate\Foundation\Http\FormRequest;

final class RemoveFromCartRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'cart_uuid' => ['nullable', 'uuid'],
            'product_id' => ['nullable', 'string'],
        ];
    }

    public function toDto(): RemoveFromCartData
    {
        $v = $this->validated();

        return new RemoveFromCartData(
            cartUuid: $v['cart_uuid'] ?? null,
            productId: $v['product_id'] ?? null,
        );
    }
}
